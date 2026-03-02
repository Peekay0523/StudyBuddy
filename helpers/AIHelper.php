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
        return !empty($this->apiKey) && $this->apiKey !== 'your-openai-api-key-here';
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

        return $response;
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
        
        $topicsStr = implode(', ', $topics);
        $messages = [
            ['role' => 'system', 'content' => 'You are an educational assistant that creates concise memorandums summarizing educational content.'],
            ['role' => 'user', 'content' => "Create a concise memorandum summarizing this educational content focusing on these key topics: {$topicsStr}. Content: " . substr($content, 0, 4000)]
        ];
        
        $response = $this->makeRequest($messages, 300, 0.4);
        
        return $response ?: "Memorandum for topics: " . implode(', ', array_slice($topics, 0, 5));
    }
    
    public function generateStudyPlan($challengingTopics, $studentName) {
        if (!$this->isValidApiKey() || empty($challengingTopics)) {
            $topicsStr = implode(', ', array_slice($challengingTopics, 0, 3));
            return [
                'title' => "Study Plan for {$topicsStr}",
                'content' => "Focus on these challenging topics: {$topicsStr}. Spend extra time practicing problems related to these concepts."
            ];
        }
        
        $topicsStr = implode(', ', $challengingTopics);
        $messages = [
            ['role' => 'system', 'content' => 'You are an educational advisor that creates personalized study plans focusing on challenging topics.'],
            ['role' => 'user', 'content' => "Create a personalized study plan for a student named {$studentName} who finds these topics challenging: {$topicsStr}. Include study tips and resources."]
        ];
        
        $response = $this->makeRequest($messages, 400, 0.5);
        
        return [
            'title' => "Personalized Study Plan for {$studentName}",
            'content' => $response ?: "Focus on these challenging topics: {$topicsStr}."
        ];
    }
    
    public function generateCareerRecommendations($gradesData) {
        $defaultRecommendations = [
            'careers' => ['Teacher', 'Engineer', 'Doctor'],
            'strengths' => ['Mathematics', 'Science'],
            'areas_for_improvement' => ['Writing', 'History'],
            'courses' => [],
            'institutions' => [],
            'bursaries' => []
        ];

        if (!$this->isValidApiKey() || empty($gradesData)) {
            return $defaultRecommendations;
        }

        $subjectsGrades = implode(', ', array_map(function($k, $v) {
            return "$k: $v";
        }, array_keys($gradesData), $gradesData));

        $messages = [
            ['role' => 'system', 'content' => 'You are an expert career counselor. Analyze academic performance and provide comprehensive career guidance including: recommended careers, suitable courses with requirements, institutions offering those courses, and available bursaries/scholarships. Format as JSON.'],
            ['role' => 'user', 'content' => "Based on these academic results: {$subjectsGrades}, provide:
1. 5 recommended careers based on strengths
2. 3 suitable courses/degrees with entry requirements for each
3. 3 South African institutions offering these courses
4. 3 bursaries/scholarships the student might qualify for

Return as JSON with keys: careers, courses (array with name, requirements, duration), institutions (array with name, location, website), bursaries (array with name, provider, eligibility, deadline, apply_url)"]
        ];

        $response = $this->makeRequest($messages, 800, 0.5);

        if ($response) {
            // Try to parse JSON from response
            $jsonMatch = [];
            if (preg_match('/\{.*\}/s', $response, $jsonMatch)) {
                $parsed = json_decode($jsonMatch[0], true);
                if ($parsed) {
                    return [
                        'careers' => $parsed['careers'] ?? $defaultRecommendations['careers'],
                        'strengths' => array_keys($gradesData),
                        'areas_for_improvement' => array_slice(array_keys($gradesData), 0, 2),
                        'courses' => $parsed['courses'] ?? [],
                        'institutions' => $parsed['institutions'] ?? [],
                        'bursaries' => $parsed['bursaries'] ?? []
                    ];
                }
            }
        }

        return $defaultRecommendations;
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
            if (stripos($careerField, $field) !== false) {
                return $courseList;
            }
        }

        // Return general courses if no match
        return $courses['Commerce'];
    }
}
