<?php
/**
 * AI-Powered Search API
 * Handles natural language queries like "what am I left with to study"
 * or "how to improve maths marks"
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/AIRouter.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Require login for personalized responses
if (!isLoggedIn()) {
    echo json_encode([
        'success' => true,
        'type' => 'message',
        'response' => 'Please log in to get personalized study assistance.',
        'suggestions' => [
            ['text' => 'Go to Login', 'url' => '/login', 'icon' => 'fas fa-sign-in-alt'],
            ['text' => 'Go to Register', 'url' => '/register', 'icon' => 'fas fa-user-plus'],
        ]
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$query = trim($input['query'] ?? '');

if (empty($query)) {
    echo json_encode(['success' => false, 'error' => 'Query is required']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $user = getCurrentUser();
    $student = getCurrentStudent();
    
    // Gather user context for personalized responses
    $context = [];
    
    if ($student) {
        $studentId = $student['id'];
        $userId = $user['id'];
        
        // Get study plans (with content for AI analysis)
        try {
            $stmt = $db->prepare("SELECT id, title, content, created_at, is_active FROM study_plans WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$studentId]);
            $context['study_plans'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['study_plans'] = [];
        }
        
        // Get uploaded scripts
        try {
            $stmt = $db->prepare("SELECT id, title, subject, grade_level, created_at, processed_topics, challenging_topics FROM uploaded_scripts WHERE student_id = ? ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$studentId]);
            $context['scripts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['scripts'] = [];
        }
        
        // Get report cards (with grades_data for AI analysis)
        try {
            $stmt = $db->prepare("SELECT id, file_name, grade, term, average, grades_data, uploaded_at FROM report_cards WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 5");
            $stmt->execute([$userId]);
            $context['report_cards'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['report_cards'] = [];
        }
        
        // Get career recommendations
        try {
            $stmt = $db->prepare("SELECT recommended_careers, strengths, areas_for_improvement FROM career_recommendations WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$studentId]);
            $context['career_recommendations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['career_recommendations'] = [];
        }
        
        // Get scanned documents (with OCR text)
        try {
            // First try to ensure ocr_text column exists
            try {
                $db->exec("ALTER TABLE scans ADD COLUMN ocr_text TEXT DEFAULT ''");
            } catch (PDOException $e) {
                // Column already exists, that's fine
            }
            try {
                $db->exec("ALTER TABLE scans ADD COLUMN ocr_extracted_at DATETIME DEFAULT NULL");
            } catch (PDOException $e) {
                // Column already exists, that's fine
            }
            
            $stmt = $db->prepare("SELECT id, original_filename, ocr_text, is_saved, created_at FROM scans WHERE user_id = ? AND ocr_text != '' ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$userId]);
            $context['scanned_documents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['scanned_documents'] = [];
        }
        
        // Get bursary applications
        try {
            $stmt = $db->prepare("SELECT institution_name, application_status FROM bursary_applications WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$studentId]);
            $context['bursary_applications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['bursary_applications'] = [];
        }
        
        // Get institution applications
        try {
            $stmt = $db->prepare("SELECT institution_name, application_status FROM institution_applications WHERE student_id = ? ORDER BY applied_date DESC LIMIT 5");
            $stmt->execute([$studentId]);
            $context['institution_applications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['institution_applications'] = [];
        }
        
        // Get simulate/exam attempts
        try {
            $stmt = $db->prepare("SELECT subject, score, total_questions, created_at FROM simulate_attempts WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$studentId]);
            $context['exam_attempts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context['exam_attempts'] = [];
        }
    }
    
    // Available pages/features in the system
    $availableFeatures = [
        'Mathematics Lab' => '/math - Interactive math tools, calculator, formulas',
        'Physics Lab' => '/physics - Physics simulations, experiments, formulas',
        'Chemistry Lab' => '/chemistry - Periodic table, reactions, molecular viewer',
        'Study Plan' => '/study-plan - Create and manage study schedules with calendar',
        'Browse Scripts' => '/browse-scripts/{grade} - Past papers, memorandums by grade (8-12)',
        'Upload Script' => '/upload-script - Upload scripts for AI memorandum generation',
        'AI Chat' => '/ai-chat - Ask AI questions about any subject',
        'Simulate' => '/simulate - Practice exams and mock tests',
        'Scan to PDF' => '/scan - Scan documents and convert to PDF',
        'Data Sheets' => '/data-sheets - Formula sheets and reference materials',
        'Report Card' => '/upload-report-card - Upload report cards for career recommendations',
        'Apply For Me' => '/apply-for-me - University and bursary applications',
        'Study Group' => '/study-group - Collaborative study groups',
        'Dashboard' => '/dashboard - View progress, streaks, and achievements',
    ];
    
    // Build context string
    $contextStr = "Student Name: " . ($user['username'] ?? 'Student') . "\n";
    $contextStr .= "Grade: " . ($student['grade_level'] ?? 'Not specified') . "\n\n";
    
    if (!empty($context['study_plans'])) {
        $contextStr .= "Existing Study Plans:\n";
        foreach ($context['study_plans'] as $plan) {
            $contextStr .= "- " . $plan['title'] . " (Status: " . ($plan['is_active'] ? 'Active' : 'Completed') . ")\n";
            if (!empty($plan['content'])) {
                $contentPreview = substr(strip_tags($plan['content']), 0, 300);
                $contextStr .= "  Content preview: " . $contentPreview . "...\n";
            }
        }
        $contextStr .= "\n";
    }
    
    if (!empty($context['report_cards'])) {
        $contextStr .= "Report Cards (Grades):\n";
        foreach ($context['report_cards'] as $rc) {
            $contextStr .= "- " . ($rc['file_name'] ?? 'Report Card') . " - Grade: " . ($rc['grade'] ?? 'N/A') . " - Term: " . ($rc['term'] ?? 'N/A') . " - Average: " . ($rc['average'] ?? 'N/A') . "%\n";
            if (!empty($rc['grades_data']) && $rc['grades_data'] !== '{}') {
                $grades = json_decode($rc['grades_data'], true);
                if (is_array($grades)) {
                    foreach (array_slice($grades, 0, 10) as $subject => $mark) {
                        if (is_array($mark)) {
                            $contextStr .= "  " . ($subject ?? 'Subject') . ": " . ($mark['mark'] ?? $mark['score'] ?? json_encode($mark)) . "\n";
                        } else {
                            $contextStr .= "  " . $subject . ": " . $mark . "\n";
                        }
                    }
                }
            }
        }
        $contextStr .= "\n";
    }
    
    if (!empty($context['career_recommendations'])) {
        $cr = $context['career_recommendations'][0];
        if (!empty($cr['strengths'])) {
            $strengths = json_decode($cr['strengths'], true);
            if (!empty($strengths)) {
                $contextStr .= "Student Strengths: " . implode(', ', array_slice($strengths, 0, 5)) . "\n";
            }
        }
        if (!empty($cr['areas_for_improvement'])) {
            $improvements = json_decode($cr['areas_for_improvement'], true);
            if (!empty($improvements)) {
                $contextStr .= "Areas for Improvement: " . implode(', ', array_slice($improvements, 0, 5)) . "\n";
            }
        }
        $contextStr .= "\n";
    }
    
    if (!empty($context['scanned_documents'])) {
        $contextStr .= "Scanned Documents (OCR Content Available):\n";
        foreach ($context['scanned_documents'] as $scan) {
            $contextStr .= "- " . ($scan['original_filename'] ?? 'Scan') . " (Saved: " . ($scan['is_saved'] ? 'Yes' : 'No') . ")\n";
            if (!empty($scan['ocr_text'])) {
                $ocrPreview = substr($scan['ocr_text'], 0, 500);
                $contextStr .= "  OCR Content: " . $ocrPreview . "...\n";
            }
        }
        $contextStr .= "\n";
    }
    
    if (!empty($context['scripts'])) {
        $contextStr .= "Uploaded Scripts/Papers:\n";
        foreach ($context['scripts'] as $script) {
            $contextStr .= "- " . $script['title'] . " (" . ($script['subject'] ?? '') . " Grade " . ($script['grade_level'] ?? '') . ")\n";
            if (!empty($script['challenging_topics']) && $script['challenging_topics'] !== '[]') {
                $topics = json_decode($script['challenging_topics'], true);
                if (!empty($topics)) {
                    $contextStr .= "  Challenging topics: " . implode(', ', array_slice($topics, 0, 5)) . "\n";
                }
            }
        }
        $contextStr .= "\n";
    }
    
    if (!empty($context['exam_attempts'])) {
        $contextStr .= "Recent Exam Results:\n";
        foreach ($context['exam_attempts'] as $attempt) {
            $pct = $attempt['total_questions'] > 0 ? round(($attempt['score'] / $attempt['total_questions']) * 100) : 0;
            $contextStr .= "- " . ($attempt['subject'] ?? 'Unknown') . ": " . $pct . "% (" . $attempt['score'] . "/" . $attempt['total_questions'] . ")\n";
        }
        $contextStr .= "\n";
    }
    
    if (!empty($context['institution_applications'])) {
        $contextStr .= "University Applications:\n";
        foreach ($context['institution_applications'] as $app) {
            $contextStr .= "- " . $app['institution_name'] . " - " . $app['application_status'] . "\n";
        }
        $contextStr .= "\n";
    }
    
    $contextStr .= "Available Features in StudySmart App:\n";
    foreach ($availableFeatures as $name => $desc) {
        $contextStr .= "- $name: $desc\n";
    }
    
    // System prompt for the AI
    $systemPrompt = "You are StudySmart Bot, a friendly and intelligent personal study assistant for South African high school students. You help students with their studies, recommend resources, and guide them through the app's features.

IMPORTANT RULES:
1. Always respond in a helpful, encouraging, and concise manner.
2. When suggesting actions, provide a JSON array of 'suggestions' with objects containing 'text', 'url', and 'icon' fields.
3. For navigation suggestions, use these URLs: /math, /physics, /chemistry, /study-plan, /browse-scripts/{grade}, /upload-script, /ai-chat, /simulate, /scan, /data-sheets, /upload-report-card, /apply-for-me, /study-group, /dashboard
4. For grade-specific content, use /browse-scripts/8 through /browse-scripts/12
5. Keep responses under 200 words.
6. Use the student's context (study plans, scripts, exam results) to give personalized advice.
7. If the student asks about improving marks in a subject, suggest relevant labs and scripts.
8. If asked 'what am I left with to study', analyze their study plans vs completed work and suggest gaps.
9. Always include a 'response' field with your text reply.
10. Format your response as JSON with this structure:
{
  \"response\": \"Your helpful text response here\",
  \"suggestions\": [
    {\"text\": \"Action label\", \"url\": \"/path\", \"icon\": \"fas fa-icon-name\"}
  ]
}

Available icons: fas fa-square-root-variable (math), fas fa-atom (physics), fas fa-flask (chemistry), fas fa-calendar-alt (study plan), fas fa-folder-open (scripts), fas fa-upload (upload), fas fa-robot (AI chat), fas fa-vial (simulate), fas fa-camera (scan), fas fa-table (data sheets), fas fa-graduation-cap (report card), fas fa-paper-plane (apply), fas fa-users (study group), fas fa-chart-line (dashboard)";

    // Build the user query with context
    $fullQuery = "Student Context:\n$contextStr\n\nStudent Question: $query\n\nRespond with JSON containing 'response' and 'suggestions' fields.";
    
    // Use AIRouter (Grok/LLaMA preferred for this task)
    $aiRouter = new AIRouter();
    $aiResponse = $aiRouter->chat($fullQuery, $systemPrompt, 'intermediate');
    
    if (!$aiResponse) {
        // Fallback response if AI fails
        echo json_encode([
            'success' => true,
            'type' => 'message',
            'response' => 'I had trouble processing that. Could you try rephrasing your question?',
            'suggestions' => [
                ['text' => 'AI Chat Assistant', 'url' => '/ai-chat', 'icon' => 'fas fa-robot'],
                ['text' => 'Browse Study Materials', 'url' => '/browse-scripts/12', 'icon' => 'fas fa-folder-open'],
            ]
        ]);
        exit;
    }
    
    // Try to parse JSON from AI response
    $parsed = null;
    
    // Try to extract JSON from the response (AI might wrap it in markdown code blocks)
    $jsonMatch = [];
    if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $aiResponse, $jsonMatch)) {
        $parsed = json_decode($jsonMatch[1], true);
    }
    
    if (!$parsed) {
        $parsed = json_decode($aiResponse, true);
    }
    
    if ($parsed && isset($parsed['response'])) {
        echo json_encode([
            'success' => true,
            'type' => 'ai_response',
            'response' => $parsed['response'],
            'suggestions' => $parsed['suggestions'] ?? []
        ]);
    } else {
        // AI returned plain text, use it as the response
        echo json_encode([
            'success' => true,
            'type' => 'ai_response',
            'response' => trim($aiResponse),
            'suggestions' => [
                ['text' => 'AI Chat for more help', 'url' => '/ai-chat', 'icon' => 'fas fa-robot'],
            ]
        ]);
    }
    
} catch (Exception $e) {
    error_log('AI Search error: ' . $e->getMessage());
    echo json_encode([
        'success' => true,
        'type' => 'message',
        'response' => 'I encountered an error. Please try again or use the AI Chat for assistance.',
        'suggestions' => [
            ['text' => 'AI Chat', 'url' => '/ai-chat', 'icon' => 'fas fa-robot'],
        ]
    ]);
}