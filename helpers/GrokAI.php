<?php
/**
 * GrokAI Helper - Groq/LLaMA API Integration
 * Handles requests to Groq or LLaMA models via various API providers
 * Supports: Groq API, xAI Grok API, Together AI, Replicate, or any OpenAI-compatible endpoint
 */

require_once __DIR__ . '/../config/database.php';

class GrokAI {
    private $apiKey;
    private $apiUrl;
    private $model;
    private $provider;
    
    // Supported providers
    const PROVIDER_XAI = 'xai';          // xAI Grok API
    const PROVIDER_GROQ = 'groq';        // Groq (Fast inference for LLaMA)
    const PROVIDER_TOGETHER = 'together'; // Together AI
    const PROVIDER_REPLICATE = 'replicate'; // Replicate
    const PROVIDER_OPENAI_COMPATIBLE = 'openai_compatible'; // Any OpenAI-compatible endpoint
    
    public function __construct() {
        // Load configuration
        $this->provider = defined('GROK_PROVIDER') ? GROK_PROVIDER : self::PROVIDER_XAI;
        $this->apiKey = defined('GROK_API_KEY') ? GROK_API_KEY : '';
        
        // Set API URL based on provider
        switch ($this->provider) {
            case self::PROVIDER_XAI:
                $defaultUrl = 'https://api.x.ai/v1/chat/completions';
                $this->apiUrl = (defined('GROK_API_URL') && !empty(GROK_API_URL)) ? GROK_API_URL : $defaultUrl;
                $this->model = (defined('GROK_MODEL') && !empty(GROK_MODEL)) ? GROK_MODEL : 'grok-beta';
                break;
                
            case self::PROVIDER_GROQ:
                $defaultUrl = 'https://api.groq.com/openai/v1/chat/completions';
                $this->apiUrl = (defined('GROK_API_URL') && !empty(GROK_API_URL)) ? GROK_API_URL : $defaultUrl;
                $this->model = (defined('GROK_MODEL') && !empty(GROK_MODEL)) ? GROK_MODEL : 'llama-3.3-70b-versatile';
                break;
                
            case self::PROVIDER_TOGETHER:
                $defaultUrl = 'https://api.together.xyz/v1/chat/completions';
                $this->apiUrl = (defined('GROK_API_URL') && !empty(GROK_API_URL)) ? GROK_API_URL : $defaultUrl;
                $this->model = (defined('GROK_MODEL') && !empty(GROK_MODEL)) ? GROK_MODEL : 'meta-llama/Llama-3.3-70B-Instruct-Turbo';
                break;
                
            case self::PROVIDER_REPLICATE:
                $defaultUrl = 'https://api.replicate.com/v1/models/';
                $this->apiUrl = (defined('GROK_API_URL') && !empty(GROK_API_URL)) ? GROK_API_URL : $defaultUrl;
                $this->model = (defined('GROK_MODEL') && !empty(GROK_MODEL)) ? GROK_MODEL : 'meta/meta-llama-3-70b-instruct';
                break;
                
            case self::PROVIDER_OPENAI_COMPATIBLE:
                $this->apiUrl = defined('GROK_API_URL') ? GROK_API_URL : '';
                $this->model = defined('GROK_MODEL') ? GROK_MODEL : 'llama-3-70b';
                break;
                
            default:
                $this->apiUrl = 'https://api.x.ai/v1/chat/completions';
                $this->model = 'grok-beta';
        }
    }
    
    /**
     * Check if API key is valid
     */
    public function isValidApiKey() {
        return !empty($this->apiKey) &&
               $this->apiKey !== 'YOUR_GROK_API_KEY_HERE' &&
               $this->apiKey !== 'your-grok-api-key-here' &&
               strlen($this->apiKey) > 10;
    }
    
    /**
     * Make a chat completion request
     */
    public function makeRequest($messages, $maxTokens = 500, $temperature = 0.7) {
        if (!$this->isValidApiKey()) {
            return null;
        }
        
        // Handle Replicate differently
        if ($this->provider === self::PROVIDER_REPLICATE) {
            return $this->makeReplicateRequest($messages, $maxTokens, $temperature);
        }
        
        // Standard OpenAI-compatible endpoint
        return $this->makeStandardRequest($messages, $maxTokens, $temperature);
    }
    
    /**
     * Standard OpenAI-compatible API request
     */
    private function makeStandardRequest($messages, $maxTokens = 500, $temperature = 0.7) {
        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature
        ];
        
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connection timeout
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        // For Together AI, use different auth header format
        if ($this->provider === self::PROVIDER_TOGETHER) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($response) {
            $result = json_decode($response, true);

            // Check for API errors
            if (isset($result['error'])) {
                error_log("GrokAI API Error: " . json_encode($result['error']));
                return null;
            }

            // Track token usage
            if (isset($result['usage'])) {
                error_log("GrokAI Token Usage: " . json_encode($result['usage']));
                $this->logTokenUsage($result['usage'], 'grok');
            } else {
                error_log("GrokAI: No 'usage' field in response. Full response keys: " . implode(', ', array_keys($result)));
            }

            return $result['choices'][0]['message']['content'] ?? null;
        }
        
        if ($curlError) {
            error_log("GrokAI cURL Error: " . $curlError);
        }
        if ($httpCode !== 200) {
            error_log("GrokAI HTTP Error Code: " . $httpCode);
        }
        
        return null;
    }
    
    /**
     * Replicate API request (different format)
     */
    private function makeReplicateRequest($messages, $maxTokens = 500, $temperature = 0.7) {
        // Convert messages to prompt format for Replicate
        $prompt = '';
        foreach ($messages as $msg) {
            $role = $msg['role'];
            $content = $msg['content'];
            $prompt .= "[{$role}] {$content}\n";
        }
        
        $data = [
            'input' => [
                'prompt' => $prompt,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature
            ]
        ];
        
        $url = $this->apiUrl . $this->model . '/predictions';
        
        $ch = curl_init($url);
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
        
        if ($response && $httpCode === 201) {
            $result = json_decode($response, true);
            
            // Replicate returns a prediction URL, need to poll for result
            if (isset($result['urls']['get'])) {
                return $this->pollReplicateResult($result['urls']['get']);
            }
            
            return $result['output'] ?? null;
        }
        
        error_log("Replicate HTTP Error: " . $httpCode);
        return null;
    }
    
    /**
     * Poll Replicate for prediction result
     */
    private function pollReplicateResult($getUrl, $maxAttempts = 30) {
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            sleep(2);
            $attempts++;
            
            $ch = curl_init($getUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->apiKey
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($response && $httpCode === 200) {
                $result = json_decode($response, true);
                
                if (isset($result['status']) && $result['status'] === 'succeeded') {
                    $output = $result['output'];
                    // Output can be array or string
                    return is_array($output) ? implode('', $output) : $output;
                }
                
                if (isset($result['status']) && $result['status'] === 'failed') {
                    error_log("Replicate prediction failed: " . json_encode($result['error']));
                    return null;
                }
            }
        }
        
        error_log("Replicate prediction timed out");
        return null;
    }
    
    /**
     * Simple chat method
     */
    public function chat($userMessage, $systemPrompt = 'You are a helpful AI Study Assistant.') {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage]
        ];
        
        $response = $this->makeRequest($messages, 500, 0.7);
        
        // Use fallback if Grok fails
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
     * Fallback response when Grok API is unavailable
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
    
    /**
     * Analyze document topics
     */
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
    
    /**
     * Identify challenging topics
     */
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
    
    /**
     * Generate study plan
     */
    public function generateStudyPlan($challengingTopics, $studentName) {
        if (!$this->isValidApiKey() || empty($challengingTopics)) {
            $topicsStr = !empty($challengingTopics) ? implode(', ', array_slice($challengingTopics, 0, 3)) : 'General Study';
            error_log("GrokAI GenerateStudyPlan: Using fallback response. Topics: " . json_encode($challengingTopics));
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
        error_log("GrokAI GenerateStudyPlan: API Response: " . substr($response ?: 'NULL', 0, 100));
        
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
     * Recite study plan (conversational format)
     */
    public function reciteStudyPlan($studyPlanContent) {
        if (!$this->isValidApiKey()) {
            return $studyPlanContent;
        }
        
        $messages = [
            ['role' => 'system', 'content' => 'You are a friendly tutor. Convert this study plan into a conversational, easy-to-understand spoken format.'],
            ['role' => 'user', 'content' => "Please explain this study plan in a friendly, conversational way: " . $studyPlanContent]
        ];
        
        $response = $this->makeRequest($messages, 600, 0.7);
        
        if ($response) {
            // Clean markdown
            $response = preg_replace('/\*\*(.*?)\*\*/', '$1', $response);
            $response = preg_replace('/\*(.*?)\*/', '$1', $response);
            $response = preg_replace('/^#+\s*/m', '', $response);
        }
        
        return $response ?: $studyPlanContent;
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
     * Log token usage to database
     */
    private function logTokenUsage($usage, $model = 'grok') {
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
                $model
            ]);
        } catch (Exception $e) {
            // Silently fail - don't break functionality for logging
            error_log("Failed to log GrokAI usage: " . $e->getMessage());
        }
    }
}
