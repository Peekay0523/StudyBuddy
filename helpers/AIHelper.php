<?php
/**
 * AI Helper - OpenAI Integration
 */

class AIHelper {
    private $apiKey;
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct() {
        $this->apiKey = OPENAI_API_KEY;
    }

    public function isValidApiKey() {
        return !empty($this->apiKey) &&
               $this->apiKey !== 'your-openai-api-key-here' &&
               $this->apiKey !== 'YOUR_OPENAI_API_KEY_HERE' &&
               strlen($this->apiKey) > 20;
    }

    public function makeRequest($messages, $maxTokens = 500, $temperature = 0.7) {
        if (!$this->isValidApiKey()) {
            return null;
        }

        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response) {
            $result = json_decode($response, true);

            // Check for API errors
            if (isset($result['error'])) {
                error_log("OpenAI API Error: " . json_encode($result['error']));
                return null;
            }

            // Track token usage
            if (isset($result['usage'])) {
                $this->logTokenUsage($result['usage']);
            }

            return $result['choices'][0]['message']['content'] ?? null;
        }

        if ($curlError) {
            error_log("cURL Error: " . $curlError);
        }
        if ($httpCode !== 200) {
            error_log("HTTP Error Code: " . $httpCode);
        }

        return null;
    }

    /**
     * Log token usage to database
     */
    private function logTokenUsage($usage) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Get current user if logged in
            $userId = null;
            if (function_exists('getCurrentUser')) {
                $user = getCurrentUser();
                if ($user && isset($user['id'])) {
                    $userId = $user['id'];
                }
            }
            
            $stmt = $db->prepare("
                INSERT INTO openai_usage_logs (user_id, prompt_tokens, completion_tokens, total_tokens, model_used, created_at)
                VALUES (?, ?, ?, ?, ?, datetime('now'))
            ");
            $stmt->execute([
                $userId,
                $usage['prompt_tokens'] ?? 0,
                $usage['completion_tokens'] ?? 0,
                $usage['total_tokens'] ?? 0,
                'openai'
            ]);
        } catch (Exception $e) {
            // Silently fail - don't break functionality for logging
            error_log("Failed to log OpenAI usage: " . $e->getMessage());
        }
    }

    /**
     * Fallback response when OpenAI API is unavailable
     */
    private function getFallbackResponse($userMessage) {
        $message = strtolower($userMessage);
        
        // Simple keyword-based responses
        if (strpos($message, 'hello') !== false || strpos($message, 'hi ') !== false || strpos($message, 'hey') !== false) {
            return "Hello! I'm your AI Study Assistant. I'm here to help you with your studies. What would you like to learn today?";
        }
        
        if (strpos($message, 'thank') !== false) {
            return "You're welcome! Feel free to ask me anything else about your studies. I'm here to help!";
        }
        
        if (strpos($message, 'help') !== false) {
            return "I can help you with: explaining concepts, study tips, quiz questions, and answering subject-related questions. What topic would you like to explore?";
        }
        
        if (strpos($message, 'quiz') !== false || strpos($message, 'test') !== false) {
            return "I'd love to help you with a quiz! Tell me what subject you'd like to be quizzed on (math, science, history, etc.), and I'll create some practice questions for you.";
        }
        
        if (strpos($message, 'study') !== false || strpos($message, 'learn') !== false) {
            return "Great! Effective studying involves: 1) Breaking topics into smaller chunks, 2) Using active recall, 3) Taking regular breaks, 4) Teaching concepts to others. What subject are you studying?";
        }
        
        if (strpos($message, 'math') !== false || strpos($message, 'science') !== false) {
            return "Math and science are fascinating! Practice is key - work through problems step by step, understand the concepts behind formulas, and don't hesitate to ask for clarification on specific topics.";
        }
        
        if (strpos($message, '?') !== false) {
            return "That's a great question! To give you the best answer, could you provide a bit more context? What specific aspect would you like to know more about?";
        }
        
        // Default response
        return "That's interesting! I'd love to help you explore this topic further. Could you tell me more about what specifically you'd like to know? The more details you share, the better I can assist you!";
    }
    
    public function chat($userMessage, $systemPrompt = 'You are a helpful AI Study Assistant.') {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage]
        ];

        $response = $this->makeRequest($messages, 500, 0.7);

        // Use fallback if OpenAI fails
        if (!$response) {
            return $this->getFallbackResponse($userMessage);
        }

        // Remove markdown formatting for cleaner display
        $response = preg_replace('/\*\*(.*?)\*\*/', '$1', $response); // Remove ** bold
        $response = preg_replace('/\*(.*?)\*/', '$1', $response); // Remove * italic
        $response = preg_replace('/^#+\s*/m', '', $response); // Remove # headers
        $response = str_replace(['**', '__', '_'], '', $response); // Remove any remaining markdown chars

        return $response;
    }
    
    /**
     * Extract text from image using OpenAI Vision API (OCR)
     */
    public function extractTextFromImage($imageData, $mimeType = 'image/jpeg') {
        if (!$this->isValidApiKey()) {
            return null;
        }

        // Convert binary image data to base64
        $base64Image = base64_encode($imageData);
        
        $messages = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Extract all readable text from this image. Return only the text content, nothing else.'
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:{$mimeType};base64,{$base64Image}"
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->makeVisionRequest($messages);
        return $response;
    }

    /**
     * Make request to OpenAI Vision API
     */
    private function makeVisionRequest($messages, $maxTokens = 1000) {
        if (!$this->isValidApiKey()) {
            return null;
        }

        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
            'max_tokens' => $maxTokens
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['error'])) {
                error_log("OpenAI Vision API Error: " . json_encode($result['error']));
                return null;
            }
            return $result['choices'][0]['message']['content'] ?? null;
        }

        error_log("Vision API HTTP Error: " . $httpCode);
        return null;
    }

    public function analyzeDocumentTopics($content) {
        if (!$this->isValidApiKey()) {
            // Fallback: simple keyword extraction
            $words = preg_split('/\s+/', $content);
            $keywords = array_filter($words, function($word) {
                return strlen($word) > 5 && ctype_alpha($word);
            });
            return array_slice(array_values(array_unique($keywords)), 0, 10);
        }
        
        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant that identifies key topics in educational documents. Extract the main topics covered in the following content. Return them as a bulleted list.'],
            ['role' => 'user', 'content' => 'Identify the main topics in this educational content: ' . substr($content, 0, 4000)]
        ];
        
        $response = $this->makeRequest($messages, 200, 0.3);
        
        if ($response) {
            $topics = array_filter(array_map('trim', explode("\n", $response)));
            $topics = array_map(function($t) {
                return trim($t, '- •*');
            }, $topics);
            return array_filter($topics);
        }
        
        return [];
    }
    
    public function identifyChallengingTopics($topics, $content) {
        if (!$this->isValidApiKey() || empty($topics)) {
            return array_slice($topics, 0, 3);
        }
        
        $topicsStr = implode(', ', $topics);
        $messages = [
            ['role' => 'system', 'content' => 'You are an educational assistant that identifies complex or challenging topics in educational content.'],
            ['role' => 'user', 'content' => "Based on this educational content, identify which of these topics might be most challenging for a student: {$topicsStr}. Content: " . substr($content, 0, 3000)]
        ];
        
        $response = $this->makeRequest($messages, 150, 0.3);
        
        if ($response) {
            return array_slice($topics, 0, 3);
        }
        
        return array_slice($topics, 0, 2);
    }
    
    public function generateMemorandum($content, $topics) {
        if (!$this->isValidApiKey()) {
            return "This memorandum summarizes the key topics: " . implode(', ', array_slice($topics, 0, 5)) . ".";
        }

        // Validate content is not binary/corrupted
        if (empty($content) ||
            strpos($content, '%PDF-') !== false ||
            strpos($content, '/Type /Catalog') !== false ||
            preg_match('/[^\x20-\x7E\x0A\x0D]{50,}/', $content)) {
            error_log("generateMemorandum: Invalid or corrupted content detected");
            return "Unable to generate memorandum: The uploaded file content could not be processed. Please ensure you are uploading a text-based PDF or other supported document format.";
        }

        $topicsStr = implode(', ', $topics);
        $messages = [
            ['role' => 'system', 'content' => 'You are an expert educational assistant that creates detailed memorandum answers for exam papers and assignments. You prioritize MATHEMATICAL ACCURACY and CONSISTENCY above all else.

Follow these formatting rules STRICTLY:

1. ACCURACY FIRST (THINK STEP-BY-STEP):
   - For every question, especially math, solve it internally first before writing the Answer.
   - DOUBLE-CHECK your arithmetic.
   - Ensure the final value you state in the "Answer" field is the EXACT result of your solution steps.

2. ONE CORRECT ANSWER ONLY:
   - Provide ONLY ONE final answer per question.
   - Do NOT give conflicting or multiple different answers.
   - The answer stated in the "Answer" section MUST match the final result in the "Solution/Explanation" section.
   - NEVER state one answer (e.g. "Answer: 1") and then calculate a different one (e.g. "Solution: result is -2/3").

3. COMPLETE COVERAGE:
   - Answer EVERY question in the content (do not skip any).
   - If the content has questions 5.1 through 5.5, answer ALL of them.
   - Check that you have covered all sub-questions before finishing.

4. STRUCTURE FOR EACH QUESTION:
   Question [number]: [Full question text]

   Solution/Explanation:
   - State the formula or method used.
   - Show ALL substitution and calculation steps clearly.
   - Explain the logic for each step.
   - For coordinate geometry, double-check all signs (e.g., subtracting a negative: -3 - (-6) = -3 + 6 = 3).
   - VERIFY that the final result is mathematically sound.

   Answer: [ONE clear final answer only - this must be the EXACT result of the solution above]

5. FOR MATHEMATICAL PROBLEMS:
   - Always perform the full calculation in the Solution section BEFORE writing the Answer.
   - Use LaTeX formatting for ALL mathematical expressions, formulas, and fractions.
   - Wrap LaTeX content STRICTLY in [math]...[/math] tags. 
   - DO NOT use \(...\) or \[...\] or $$...$$ delimiters. Use [math] tags for every single variable, coordinate, and formula.
   - Example: [math](x_1, y_1)[/math], [math]m = \frac{y_2 - y_1}{x_2 - x_1}[/math], [math]\theta \approx 29.74^\circ[/math]
   - For mixed fractions, use: [math]2 \frac{1}{3}[/math]
   - State the final answer with units if applicable, also using [math] tags for any math symbols.

6. FOR THEORY QUESTIONS:
   - Provide clear, complete explanations.
   - Use bullet points for key points.

7. FOR DIAGRAMS/DRAWINGS:
   - Create ASCII art representations when needed.
   - Label all parts clearly.
   - Explain what each part represents.

8. FORMATTING:
   - Do NOT use markdown (no **, ##, etc.).
   - Use plain text with clear spacing between sections.
   - Use blank lines to separate different questions.
   - Keep formatting clean and consistent.

9. FINAL CONSISTENCY CHECK:
   - Read your entire output. The "Answer" field MUST match the final result of your "Solution/Explanation". If there is any discrepancy, fix the Answer to match the calculated Solution.

TONE: Educational, clear, and professional.'],
            ['role' => 'user', 'content' => "Create a comprehensive memorandum for this educational content. Ensure 100% mathematical accuracy and consistency between answers and solutions. The key topics are: {$topicsStr}.

CRITICAL REQUIREMENTS:
1. Answer EVERY question in the content - do not skip any sub-questions
2. For each question show:
   - The complete question text
   - Complete step-by-step solution showing how to reach that answer
   - ONE final answer (clearly stated at the END of the question block)
3. For diagrams: Create ASCII art representations with labels
4. Verify all calculations are correct and consistent

IMPORTANT: Before finishing, check that you have answered ALL questions (e.g., if questions go up to 5.5, make sure you answer 5.1, 5.2, 5.3, 5.4, AND 5.5).

Content to process: " . substr($content, 0, 8000)]
        ];

        // Using 2000 tokens and 0.1 temperature for accuracy and completeness
        $response = $this->makeRequest($messages, 2000, 0.1);

        // Clean up any remaining markdown formatting
        if ($response) {
            $response = preg_replace('/\*\*(.*?)\*\*/', '$1', $response); // Remove ** bold
            $response = preg_replace('/\*(.*?)\*/', '$1', $response); // Remove * italic
            $response = preg_replace('/^#+\s*/m', '', $response); // Remove # headers
            $response = str_replace(['**', '__', '```'], '', $response); // Remove any remaining markdown chars
        }

        return $response ?: "Memorandum for topics: " . implode(', ', array_slice($topics, 0, 5));
    }
    
    public function generateStudyPlan($challengingTopics, $studentName) {
        if (!$this->isValidApiKey() || empty($challengingTopics)) {
            $topicsStr = !empty($challengingTopics) ? implode(', ', array_slice($challengingTopics, 0, 3)) : 'General Study';
            error_log("GenerateStudyPlan: Using fallback response. Topics: " . json_encode($challengingTopics));
            return [
                'title' => "Study Plan for {$topicsStr}",
                'content' => "Focus on these challenging topics: {$topicsStr}. Spend extra time practicing problems related to these concepts. Review your notes regularly and create summary sheets for each topic."
            ];
        }

        $topicsStr = implode(', ', $challengingTopics);
        $messages = [
            ['role' => 'system', 'content' => 'You are an educational advisor that creates personalized DAILY study plans focusing on challenging topics. 
            STRICT FORMATTING RULES:
            1. Use "Day" numbering instead of "Week" (e.g., "Day 1-2:", "Day 3:").
            2. Every section MUST start with a header like "Day X: [Topic Name]" or "Day X-Y: [Topic Name]".
            3. Use the header sentence to clearly define what is being studied.'],
            ['role' => 'user', 'content' => "Create a personalized DAILY study plan for a student named {$studentName} who finds these topics challenging: {$topicsStr}. 
            Format the plan using 'Day X:' headers. Include specific tasks, study tips and resources for each day."]
        ];

        $response = $this->makeRequest($messages, 500, 0.5);
        error_log("GenerateStudyPlan: API Response: " . substr($response ?: 'NULL', 0, 100));

        // Remove markdown formatting from response
        if ($response) {
            $response = preg_replace('/\*\*(.*?)\*\*/', '$1', $response); // Remove ** bold
            $response = preg_replace('/\*(.*?)\*/', '$1', $response); // Remove * italic
            $response = preg_replace('/^#+\s*/m', '', $response); // Remove # headers
            $response = preg_replace('/^---\s*$/m', '', $response); // Remove horizontal rules
            $response = preg_replace('/\[(.*?)\]\(.*?\)/', '$1', $response); // Remove markdown links
            $response = str_replace(['\\'], '', $response); // Remove escaped characters
            $response = preg_replace('/\n{3,}/', "\n\n", $response); // Clean up multiple newlines
            $response = trim($response);
        }

        return [
            'title' => "Daily Study Plan for {$studentName}",
            'content' => $response ?: "Focus on these challenging topics: {$topicsStr}. Review regularly and practice problems."
        ];
    }
    
    /**
     * Generate a structured study schedule from study plan content
     */
    public function generateStudySchedule($title, $content, $startDate) {
        if (!$this->isValidApiKey()) {
            return [];
        }

        $messages = [
            ['role' => 'system', 'content' => 'You are an educational assistant that converts study plans into structured calendar schedules. 
            Analyze the study plan and extract a sequence of study sessions. 
            Return a JSON array of objects, where each object has:
            - date_offset: number of days from start date (0 = start date, 1 = next day, etc.)
            - title: The EXACT header sentence from the study plan for that day (e.g., "Day 1-2: Review and Foundation")
            - description: brief description of what to study (max 200 chars)
            - time: recommended start time (HH:MM format)
            
            Guidelines:
            - Use the "Day X" or "Day X-Y" markers in the plan to determine date_offset.
            - CRITICAL: The "title" field MUST be the full header line from the plan (e.g. "Day 3-4: Calculating the Gradient of a Line").
            - Spread sessions across the timeline mentioned in the plan.
            - TIME RULES:
              * Weekdays (Mon-Fri): Use times between 15:00 and 22:00 (after school).
              * Sundays: Use times between 12:00 and 22:00.
              * Saturdays: Use times between 10:00 and 22:00.
            - Return ONLY the JSON array starting with [ and ending with ].'],
            ['role' => 'user', 'content' => "Convert this daily study plan into a schedule starting from {$startDate}:\n\nTitle: {$title}\n\nContent: " . substr($content, 0, 4000)]
        ];

        $response = $this->makeRequest($messages, 800, 0.3);
        
        if ($response) {
            $jsonMatch = [];
            if (preg_match('/\[.*\]/s', $response, $jsonMatch)) {
                $decoded = json_decode($jsonMatch[0], true);
                return is_array($decoded) ? $decoded : [];
            }
        }
        
        return [];
    }

    /**
     * Calculate APS (Admission Point Score) from grades
     * South African NSC APS calculation
     */
    private function calculateAPS($gradesData) {
        $total = 0;
        $count = 0;
        
        foreach ($gradesData as $subject => $grade) {
            // Skip Life Orientation for APS (doesn't count for most universities)
            if (stripos($subject, 'Life Orientation') !== false || stripos($subject, 'LO') !== false) {
                continue;
            }
            
            $percentage = $this->extractPercentage($grade);
            $points = $this->percentageToAPSPoints($percentage);
            $total += $points;
            $count++;
        }
        
        return $count > 0 ? $total : 0;
    }
    
    /**
     * Extract percentage from grade string
     */
    private function extractPercentage($grade) {
        // Handle percentage format like "75%"
        if (preg_match('/(\d+)/', $grade, $matches)) {
            return intval($matches[1]);
        }
        
        // Handle level format like "Level 5" or just "5"
        if (preg_match('/[Ll]evel\s*(\d+)/', $grade, $matches)) {
            $level = intval($matches[1]);
            return $this->levelToPercentage($level);
        }
        
        // Handle range like "70-79%"
        if (preg_match('/(\d+)-(\d+)/', $grade, $matches)) {
            return intval(($matches[1] + $matches[2]) / 2);
        }
        
        // Default to 65% if can't parse
        return 65;
    }
    
    /**
     * Convert percentage to APS points (SA NSC scale)
     */
    private function percentageToAPSPoints($percentage) {
        if ($percentage >= 80) return 7;
        if ($percentage >= 70) return 6;
        if ($percentage >= 60) return 5;
        if ($percentage >= 50) return 4;
        if ($percentage >= 40) return 3;
        if ($percentage >= 30) return 2;
        if ($percentage >= 0) return 1;
        return 0;
    }
    
    /**
     * Convert NSC level to approximate percentage
     */
    private function levelToPercentage($level) {
        $levels = [
            7 => 85, // 80-100%
            6 => 75, // 70-79%
            5 => 65, // 60-69%
            4 => 55, // 50-59%
            3 => 45, // 40-49%
            2 => 35, // 30-39%
            1 => 20  // 0-29%
        ];
        return $levels[$level] ?? 65;
    }
    
    /**
     * Extract institutions from courses array
     */
    private function extractInstitutionsFromCourses($courses) {
        $institutions = [];
        
        if (!is_array($courses)) {
            return [];
        }
        
        foreach ($courses as $course) {
            if (isset($course['institutions']) && is_array($course['institutions'])) {
                foreach ($course['institutions'] as $inst) {
                    $name = is_array($inst) ? ($inst['name'] ?? $inst[0] ?? null) : $inst;
                    if ($name && !in_array($name, $institutions)) {
                        $institutions[] = $name;
                    }
                }
            }
        }
        
        return array_values(array_unique($institutions));
    }
    
    /**
     * Determine career theme based on subjects
     */
    private function determineCareerTheme($gradesData) {
        $subjects = array_keys($gradesData);
        
        // Check for STEM subjects
        $hasMath = false;
        $hasScience = false;
        $hasGeography = false;
        $hasBusiness = false;
        $hasIT = false;
        $hasEducation = false;
        
        foreach ($subjects as $subject) {
            $subjectLower = strtolower($subject);
            
            if (strpos($subjectLower, 'math') !== false) $hasMath = true;
            if (strpos($subjectLower, 'physics') !== false || strpos($subjectLower, 'chemistry') !== false || strpos($subjectLower, 'science') !== false) $hasScience = true;
            if (strpos($subjectLower, 'geography') !== false || strpos($subjectLower, 'geo') !== false) $hasGeography = true;
            if (strpos($subjectLower, 'accounting') !== false || strpos($subjectLower, 'business') !== false || strpos($subjectLower, 'economics') !== false) $hasBusiness = true;
            if (strpos($subjectLower, 'computer') !== false || strpos($subjectLower, 'it ') !== false || strpos($subjectLower, 'programming') !== false) $hasIT = true;
            if (strpos($subjectLower, 'education') !== false || strpos($subjectLower, 'teaching') !== false) $hasEducation = true;
        }
        
        // Determine theme based on subject combination
        if ($hasMath && $hasScience) {
            return 'Science, Technology, Engineering and Mathematics (STEM)';
        } elseif ($hasGeography && $hasScience) {
            return 'Environmental and Earth Sciences';
        } elseif ($hasMath && $hasBusiness) {
            return 'Business, Finance and Commerce';
        } elseif ($hasIT || ($hasMath && !$hasScience)) {
            return 'Information Technology and Computer Science';
        } elseif ($hasBusiness) {
            return 'Business and Management';
        } elseif ($hasEducation) {
            return 'Education and Teaching';
        } else {
            return 'General Academic Studies';
        }
    }

    public function generateCareerRecommendations($gradesData) {
        $defaultRecommendations = [
            'careers' => ['Teacher', 'Engineer', 'Doctor'],
            'strengths' => ['Mathematics', 'Science'],
            'areas_for_improvement' => ['Writing', 'History'],
            'courses' => [],
            'institutions' => [],
            'bursaries' => [],
            'aps' => 0
        ];

        // Calculate APS score locally (always do this, even without OpenAI)
        $aps = $this->calculateAPS($gradesData);
        error_log("Calculated APS: $aps");

        if (!$this->isValidApiKey() || empty($gradesData)) {
            error_log("Using fallback - API key invalid or no grades");
            // Return fallback with calculated APS
            $defaultRecommendations['aps'] = $aps;

            // Generate meaningful strengths from subjects
            $subjectList = array_keys($gradesData);
            $strengths = [];

            foreach ($subjectList as $subject) {
                if (stripos($subject, 'Math') !== false) {
                    $strengths[] = 'Mathematical proficiency';
                } elseif (stripos($subject, 'Science') !== false || stripos($subject, 'Physics') !== false || stripos($subject, 'Chemistry') !== false) {
                    $strengths[] = 'Scientific understanding';
                } elseif (stripos($subject, 'English') !== false || stripos($subject, 'Language') !== false) {
                    $strengths[] = 'Language skills';
                } elseif (stripos($subject, 'Geography') !== false) {
                    $strengths[] = 'Geographical knowledge';
                } elseif (stripos($subject, 'History') !== false) {
                    $strengths[] = 'Historical analysis';
                } elseif (stripos($subject, 'Accounting') !== false || stripos($subject, 'Business') !== false) {
                    $strengths[] = 'Business acumen';
                } else {
                    $strengths[] = $subject;
                }
            }

            $defaultRecommendations['strengths'] = array_slice(array_unique($strengths), 0, 5);
            $defaultRecommendations['careers'] = $this->getCareersForSubjects($gradesData);

            return $defaultRecommendations;
        }

        // Determine career theme based on strongest subjects
        $careerTheme = $this->determineCareerTheme($gradesData);

        $subjectsGrades = implode(', ', array_map(function($k, $v) {
            return "$k: $v";
        }, array_keys($gradesData), $gradesData));

        error_log("Sending to OpenAI API with APS: $aps");

        $messages = [
            ['role' => 'system', 'content' => 'You are an expert South African career counselor. Analyze academic performance and provide comprehensive career guidance. IMPORTANT: All recommendations must be CONSISTENT and RELATED to each other. Focus on the suggested career theme based on the student\'s subjects. Consider APS scores and subject requirements for South African universities. Format as JSON.'],
            ['role' => 'user', 'content' => "Based on these South African National Senior Certificate results: {$subjectsGrades}, with an APS score of {$aps}.

The student's strongest subjects suggest a career theme of: {$careerTheme}

CRITICAL INSTRUCTION - QUALIFICATION CHECKING:
The grades shown above are the student's ACTUAL achievements. You MUST convert these percentages to NSC Achievement Levels and ONLY recommend courses where the student's levels meet or exceed the entry requirements.

NSC Achievement Level Conversion:
- Level 7 = 80-100% (Distinction)
- Level 6 = 70-79% (Merit)
- Level 5 = 60-69% (Substantial achievement)
- Level 4 = 50-59% (Adequate achievement)
- Level 3 = 40-49% (Moderate achievement)
- Level 2 = 30-39% (Elementary achievement)
- Level 1 = 0-29% (Not achieved)

EXAMPLE: If Physical Sciences shows 45%, that's Level 3. DO NOT suggest courses requiring Level 5 in Physical Sciences.

IMPORTANT RULES FOR COURSE RECOMMENDATIONS:
1. Check EACH subject requirement against the student's ACTUAL grade/level
2. ONLY recommend courses where the student QUALIFIES based on their current levels
3. If the student doesn't qualify for degree programs, suggest diploma or certificate pathways
4. Provide alternative courses that match what they DO qualify for
5. Be realistic - don't suggest Engineering (requires Level 6 Math & Science) to someone with Level 3 in those subjects

REQUIRED OUTPUT - ALL items must be thematically consistent with {$careerTheme}:

1. 5 recommended careers - ALL from the {$careerTheme} field, appropriate for the student's achievement level
2. 5 suitable bachelor's degree/diploma/certificate courses - ALL must be {$careerTheme}-related, with:
   - OVERALL APS SCORE REQUIRED for each course
   - DETAILED SUBJECT REQUIREMENTS listing EACH subject with its minimum level
   - Include ALL compulsory subjects (Mathematics, Physical Sciences, Life Sciences, English, etc.)
   - Example: \"APS 28, Mathematics (Level 5), Physical Sciences (Level 4), English (Level 4), Life Sciences (Level 4)\"
3. For EACH course, list 3-5 South African institutions that offer it, with their specific entry requirements
4. 3 bursaries/scholarships related to {$careerTheme}

Return as JSON with keys:
- careers (array of career names, all from {$careerTheme} field, appropriate for achievement level)
- courses (array with: name, aps_required, requirements, subject_requirements (array of {subject, min_level}), duration, institutions (array with name, location, website, entry_requirements, aps_required)) - ONLY courses student qualifies for
- bursaries (array with: name, provider, eligibility, deadline, apply_url)"]
        ];

        $response = $this->makeRequest($messages, 1200, 0.5);

        error_log("Career Recommendations API Response: " . substr($response ?: 'NULL', 0, 500));

        if ($response) {
            // Try to parse JSON from response
            $jsonMatch = [];
            if (preg_match('/\{.*\}/s', $response, $jsonMatch)) {
                $parsed = json_decode($jsonMatch[0], true);
                error_log("Parsed JSON: " . json_encode($parsed));
                if ($parsed) {
                    // Generate meaningful strengths from subjects
                    $subjectList = array_keys($gradesData);
                    $strengths = [];

                    // Create strength statements based on subjects
                    foreach ($subjectList as $subject) {
                        if (stripos($subject, 'Math') !== false) {
                            $strengths[] = 'Strong analytical and problem-solving skills';
                        } elseif (stripos($subject, 'Science') !== false || stripos($subject, 'Physics') !== false || stripos($subject, 'Chemistry') !== false) {
                            $strengths[] = 'Scientific thinking and research abilities';
                        } elseif (stripos($subject, 'English') !== false || stripos($subject, 'Language') !== false) {
                            $strengths[] = 'Effective communication skills';
                        } elseif (stripos($subject, 'Geography') !== false) {
                            $strengths[] = 'Spatial awareness and environmental understanding';
                        } elseif (stripos($subject, 'History') !== false) {
                            $strengths[] = 'Critical thinking and research skills';
                        } elseif (stripos($subject, 'Accounting') !== false || stripos($subject, 'Business') !== false) {
                            $strengths[] = 'Financial literacy and business acumen';
                        } else {
                            $strengths[] = "Proficiency in $subject";
                        }
                    }

                    // Limit to top 5 strengths
                    $strengths = array_slice(array_unique($strengths), 0, 5);

                    $result = [
                        'careers' => $parsed['careers'] ?? $defaultRecommendations['careers'],
                        'strengths' => !empty($strengths) ? $strengths : $subjectList,
                        'areas_for_improvement' => array_slice($subjectList, 0, 2),
                        'courses' => $parsed['courses'] ?? [],
                        'institutions' => $this->extractInstitutionsFromCourses($parsed['courses'] ?? []),
                        'bursaries' => $parsed['bursaries'] ?? [],
                        'aps' => $aps  // Always use calculated APS
                    ];
                    
                    error_log("Returning AI recommendations with APS: " . $result['aps']);
                    return $result;
                }
            }
        }

        // Fallback: Return calculated APS with default recommendations
        error_log("API failed, returning fallback with APS: $aps");
        $defaultRecommendations['aps'] = $aps;
        $defaultRecommendations['strengths'] = array_keys($gradesData);
        $defaultRecommendations['careers'] = $this->getCareersForSubjects($gradesData);
        return $defaultRecommendations;
    }

    /**
     * Get career suggestions based on subjects (fallback)
     */
    private function getCareersForSubjects($gradesData) {
        $careers = [];
        $hasMath = false;
        $hasScience = false;
        $hasEnglish = false;
        
        foreach (array_keys($gradesData) as $subject) {
            if (stripos($subject, 'Math') !== false) $hasMath = true;
            if (stripos($subject, 'Science') !== false || stripos($subject, 'Physics') !== false) $hasScience = true;
            if (stripos($subject, 'English') !== false) $hasEnglish = true;
        }
        
        if ($hasMath && $hasScience) {
            $careers = ['Engineer', 'Data Scientist', 'Architect', 'Software Developer', 'Actuary'];
        } elseif ($hasMath) {
            $careers = ['Accountant', 'Financial Analyst', 'Statistician', 'Economist', 'Teacher'];
        } elseif ($hasScience) {
            $careers = ['Nurse', 'Medical Technician', 'Environmental Scientist', 'Lab Technician', 'Teacher'];
        } elseif ($hasEnglish) {
            $careers = ['Journalist', 'Teacher', 'Content Writer', 'Public Relations Officer', 'Librarian'];
        } else {
            $careers = ['Teacher', 'Administrator', 'Sales Representative', 'Customer Service Manager', 'Entrepreneur'];
        }
        
        return $careers;
    }

    /**
     * Search for bursaries using web search
     */
    public function searchBursaries($subjects, $gradeAverage) {
        // This would use a web search API in production
        // For now, return curated bursary data based on subjects
        
        $bursaries = [
            [
                'name' => 'National Student Financial Aid Scheme (NSFAS)',
                'provider' => 'South African Government',
                'eligibility' => 'South African citizens from households earning less than R350,000 per annum',
                'covers' => 'Tuition fees, accommodation, living allowance, learning materials',
                'deadline' => 'January 31, 2026',
                'apply_url' => 'https://www.nsfas.org.za',
                'contact' => '08000 67227'
            ],
            [
                'name' => 'Funza Lushaka Bursary',
                'provider' => 'Department of Basic Education',
                'eligibility' => 'Students pursuing teaching qualifications in priority areas',
                'covers' => 'Full tuition, accommodation, meals, learning materials',
                'deadline' => 'January 15, 2026',
                'apply_url' => 'https://funzalushaka.education.gov.za',
                'contact' => '0800 87 22 22'
            ],
            [
                'name' => 'Allan Gray Orbis Foundation Bursary',
                'provider' => 'Allan Gray Orbis Foundation',
                'eligibility' => 'High-achieving Grade 12s and university students with entrepreneurial potential',
                'covers' => 'Tuition, accommodation, living allowance, laptop',
                'deadline' => 'October 31, 2026',
                'apply_url' => 'https://www.allangrayorbis.org',
                'contact' => '021 421 2800'
            ],
            [
                'name' => 'Department of Science & Innovation Bursary',
                'provider' => 'DSI',
                'eligibility' => 'Students pursuing STEM qualifications with strong math/science results',
                'covers' => 'Tuition, accommodation, study materials',
                'deadline' => 'September 30, 2026',
                'apply_url' => 'https://www.dst.gov.za',
                'contact' => '012 843 6300'
            ],
            [
                'name' => 'Transnet Bursary',
                'provider' => 'Transnet SOC Ltd',
                'eligibility' => 'Students in engineering, logistics, supply chain related fields',
                'covers' => 'Full tuition, accommodation, meals, book allowance',
                'deadline' => 'January 20, 2026',
                'apply_url' => 'https://www.transnet.net',
                'contact' => '011 375 0000'
            ]
        ];

        // Filter based on subjects
        $hasMath = stripos(implode(' ', $subjects), 'math') !== false;
        $hasScience = stripos(implode(' ', $subjects), 'science') !== false || stripos(implode(' ', $subjects), 'physics') !== false;
        
        $filtered = $bursaries;
        if ($hasMath && $hasScience && $gradeAverage >= 70) {
            // Add more STEM bursaries for high achievers
            $filtered[] = [
                'name' => 'Eskom Bursary',
                'provider' => 'Eskom Holdings',
                'eligibility' => 'Engineering students with strong math/physics results',
                'covers' => 'Full tuition, accommodation, meals, books',
                'deadline' => 'February 28, 2026',
                'apply_url' => 'https://www.eskom.co.za',
                'contact' => '0800 375 663'
            ];
        }

        return $filtered;
    }

    /**
     * Get course information and requirements
     */
    public function getCourseInformation($careerField, $subjects) {
        $careerStr = is_array($careerField) ? implode(' ', $careerField) : $careerField;
        $careerLower = strtolower($careerStr);
        
        $courses = [
            'Engineering' => [
                [
                    'name' => 'Bachelor of Science in Engineering',
                    'requirements' => 'Mathematics (Level 6), Physical Sciences (Level 5), English (Level 5)',
                    'duration' => '4 years',
                    'institutions' => ['University of Cape Town', 'Wits University', 'Stellenbosch University', 'University of Pretoria']
                ],
                [
                    'name' => 'Bachelor of Engineering in Civil Engineering',
                    'requirements' => 'Mathematics (Level 6), Physical Sciences (Level 5)',
                    'duration' => '4 years',
                    'institutions' => ['UCT', 'Wits', 'UP', 'UKZN']
                ]
            ],
            'Medicine' => [
                [
                    'name' => 'Bachelor of Medicine and Bachelor of Surgery (MBChB)',
                    'requirements' => 'Mathematics (Level 6), Physical Sciences (Level 6), Life Sciences (Level 7), English (Level 6)',
                    'duration' => '6 years',
                    'institutions' => ['University of Cape Town', 'Wits University', 'Stellenbosch University', 'UP', 'UKZN']
                ],
                [
                    'name' => 'Bachelor of Nursing',
                    'requirements' => 'Life Sciences (Level 5), English (Level 5), Mathematics/Math Literacy (Level 4)',
                    'duration' => '4 years',
                    'institutions' => ['UCT', 'Wits', 'UKZN', 'UWC']
                ]
            ],
            'Commerce' => [
                [
                    'name' => 'Bachelor of Commerce',
                    'requirements' => 'Mathematics (Level 5), English (Level 5)',
                    'duration' => '3 years',
                    'institutions' => ['UCT', 'Wits', 'Stellenbosch', 'UJ', 'UP']
                ],
                [
                    'name' => 'Bachelor of Accounting Sciences',
                    'requirements' => 'Mathematics (Level 5), Accounting (Level 5)',
                    'duration' => '3 years',
                    'institutions' => ['Wits', 'UJ', 'UNISA']
                ]
            ],
            'Education' => [
                [
                    'name' => 'Bachelor of Education',
                    'requirements' => 'English (Level 5), 2 official languages, Mathematics/Math Literacy (Level 4)',
                    'duration' => '4 years',
                    'institutions' => ['UP', 'UJ', 'Wits', 'UNISA']
                ]
            ],
            'Law' => [
                [
                    'name' => 'Bachelor of Laws (LLB)',
                    'requirements' => 'English (Level 6), 2 other languages (Level 5)',
                    'duration' => '4 years',
                    'institutions' => ['UCT', 'Wits', 'UP', 'Stellenbosch', 'UKZN']
                ]
            ],
            'IT' => [
                [
                    'name' => 'Bachelor of Science in Computer Science',
                    'requirements' => 'Mathematics (Level 6), Physical Sciences (Level 5)',
                    'duration' => '3 years',
                    'institutions' => ['UCT', 'Wits', 'UP', 'Stellenbosch']
                ],
                [
                    'name' => 'Bachelor of Information Technology',
                    'requirements' => 'Mathematics (Level 5)',
                    'duration' => '3 years',
                    'institutions' => ['UP', 'UJ', 'NWU']
                ]
            ],
            'Computer Science' => [
                [
                    'name' => 'Bachelor of Science in Computer Science',
                    'requirements' => 'Mathematics (Level 6), Physical Sciences (Level 5)',
                    'duration' => '3 years',
                    'institutions' => ['UCT', 'Wits', 'UP', 'Stellenbosch']
                ],
                [
                    'name' => 'Bachelor of Information Technology',
                    'requirements' => 'Mathematics (Level 5)',
                    'duration' => '3 years',
                    'institutions' => ['UP', 'UJ', 'NWU']
                ]
            ],
            'Software' => [
                [
                    'name' => 'Bachelor of Science in Software Engineering',
                    'requirements' => 'Mathematics (Level 6), Physical Sciences (Level 5)',
                    'duration' => '4 years',
                    'institutions' => ['UCT', 'Wits', 'UP', 'Stellenbosch']
                ],
                [
                    'name' => 'Bachelor of Computer and Information Sciences',
                    'requirements' => 'Mathematics (Level 5)',
                    'duration' => '3 years',
                    'institutions' => ['Wits', 'UP', 'UJ', 'NWU']
                ]
            ],
            'Science' => [
                [
                    'name' => 'Bachelor of Science',
                    'requirements' => 'Mathematics (Level 5), Physical Sciences (Level 5)',
                    'duration' => '3 years',
                    'institutions' => ['UCT', 'Wits', 'UP', 'Stellenbosch']
                ],
                [
                    'name' => 'Bachelor of Science in Data Science',
                    'requirements' => 'Mathematics (Level 6), Physical Sciences (Level 5)',
                    'duration' => '3 years',
                    'institutions' => ['Wits', 'UP', 'UCT']
                ]
            ],
            'Health' => [
                [
                    'name' => 'Bachelor of Health Sciences',
                    'requirements' => 'Life Sciences (Level 5), English (Level 5)',
                    'duration' => '3 years',
                    'institutions' => ['UCT', 'Wits', 'UKZN', 'Stellenbosch']
                ],
                [
                    'name' => 'Bachelor of Pharmacy',
                    'requirements' => 'Mathematics (Level 6), Physical Sciences (Level 5), Life Sciences (Level 6)',
                    'duration' => '4 years',
                    'institutions' => ['Wits', 'UP', 'UKZN', 'NWU']
                ]
            ],
            'Arts' => [
                [
                    'name' => 'Bachelor of Arts',
                    'requirements' => 'English (Level 5), Second Language (Level 4)',
                    'duration' => '3 years',
                    'institutions' => ['UCT', 'Wits', 'Stellenbosch', 'UJ']
                ],
                [
                    'name' => 'Bachelor of Fine Arts',
                    'requirements' => 'Visual Arts/Design (Level 5), Portfolio',
                    'duration' => '3-4 years',
                    'institutions' => ['Wits', 'UCT', 'UKZN']
                ]
            ]
        ];

        // Match career field to courses with improved matching
        foreach ($courses as $field => $courseList) {
            if (stripos($careerLower, strtolower($field)) !== false) {
                return $courseList;
            }
        }
        
        // Additional keyword matching for common career terms
        if (preg_match('/(software|developer|programmer|coding|it|computer|tech)/i', $careerStr)) {
            return $courses['IT'];
        }
        if (preg_match('/(engineer|engineering)/i', $careerStr)) {
            return $courses['Engineering'];
        }
        if (preg_match('/(doctor|medical|nurse|health|hospital)/i', $careerStr)) {
            return $courses['Medicine'];
        }
        if (preg_match('/(account|finance|business|commerce|economic)/i', $careerStr)) {
            return $courses['Commerce'];
        }
        if (preg_match('/(teach|educat|professor|lecturer)/i', $careerStr)) {
            return $courses['Education'];
        }
        if (preg_match('/(law|attorney|advocate|legal)/i', $careerStr)) {
            return $courses['Law'];
        }
        if (preg_match('/(science|data|analyst|research)/i', $careerStr)) {
            return $courses['Science'];
        }
        if (preg_match('/(art|design|creative|media|communicat)/i', $careerStr)) {
            return $courses['Arts'];
        }

        // Return empty array instead of defaulting to Commerce
        // This allows the AI-generated courses to be used instead
        return [];
    }

    /**
     * Generate an AI recitation of a study plan - converts text to spoken-word friendly format
     */
    public function reciteStudyPlan($title, $content) {
        if (!$this->isValidApiKey()) {
            // Fallback: simple text-to-speech friendly version
            return [
                'recitation' => "Study Plan: {$title}. " . strip_tags($content),
                'summary' => "This study plan covers: {$title}",
                'key_points' => []
            ];
        }

        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful AI study assistant that recites study plans in a clear, spoken-word friendly format. Convert the study plan into a natural, conversational recitation that can be read aloud. Include a brief summary and key bullet points.'],
            ['role' => 'user', 'content' => "Recite this study plan in a clear, spoken format:\n\nTitle: {$title}\n\nContent: " . substr($content, 0, 3000)]
        ];

        $response = $this->makeRequest($messages, 600, 0.7);

        if ($response) {
            return [
                'recitation' => $response,
                'summary' => substr($response, 0, 200) . '...',
                'key_points' => []
            ];
        }

        return [
            'recitation' => "Study Plan: {$title}. " . strip_tags($content),
            'summary' => "This study plan covers: {$title}",
            'key_points' => []
        ];
    }
}
