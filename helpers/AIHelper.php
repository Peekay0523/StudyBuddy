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

    private function isValidApiKey() {
        return !empty($this->apiKey) && 
               $this->apiKey !== 'your-openai-api-key-here' && 
               $this->apiKey !== 'YOUR_OPENAI_API_KEY_HERE' &&
               strlen($this->apiKey) > 20;
    }

    private function makeRequest($messages, $maxTokens = 500, $temperature = 0.7) {
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
            ['role' => 'system', 'content' => 'You are an educational assistant that creates concise memorandums summarizing educational content. Do NOT use markdown formatting (no **, ##, **, or other markdown symbols). Write in plain text only. Use simple formatting with clear headings and bullet points without special characters.'],
            ['role' => 'user', 'content' => "Create a concise memorandum summarizing this educational content focusing on these key topics: {$topicsStr}. Content: " . substr($content, 0, 4000)]
        ];

        $response = $this->makeRequest($messages, 300, 0.4);

        // Remove any remaining markdown formatting
        if ($response) {
            $response = preg_replace('/\*\*(.*?)\*\*/', '$1', $response); // Remove ** bold
            $response = preg_replace('/\*(.*?)\*/', '$1', $response); // Remove * italic
            $response = preg_replace('/^#+\s*/m', '', $response); // Remove # headers
            $response = str_replace(['**', '__', '_'], '', $response); // Remove any remaining markdown chars
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
            ['role' => 'system', 'content' => 'You are an educational advisor that creates personalized study plans focusing on challenging topics.'],
            ['role' => 'user', 'content' => "Create a personalized study plan for a student named {$studentName} who finds these topics challenging: {$topicsStr}. Include study tips and resources."]
        ];

        $response = $this->makeRequest($messages, 400, 0.5);
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
            'title' => "Personalized Study Plan for {$studentName}",
            'content' => $response ?: "Focus on these challenging topics: {$topicsStr}. Review regularly and practice problems."
        ];
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

IMPORTANT: All careers and courses must be THEMATICALLY CONSISTENT with the suggested theme above.

1. 5 recommended careers - ALL from the {$careerTheme} field
2. 5 suitable bachelor's degree courses - ALL must be {$careerTheme}-related and lead to the careers above, with specific entry requirements for each
3. For EACH course, list 3-5 South African institutions that offer it, with their specific entry requirements
4. 3 bursaries/scholarships related to {$careerTheme}

Return as JSON with keys:
- careers (array of career names, all from {$careerTheme} field)
- courses (array with: name, requirements, duration, institutions (array with name, location, website, entry_requirements)) - ALL courses must relate to {$careerTheme}
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
            'IT/Computer Science' => [
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
            ]
        ];

        // Match career field to courses
        foreach ($courses as $field => $courseList) {
            if (stripos(is_array($careerField) ? implode(' ', $careerField) : $careerField, $field) !== false) {
                return $courseList;
            }
        }

        // Return general courses if no match
        return $courses['Commerce'];
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
