<?php
/**
 * AI Router - Intelligent AI Model Router
 * Routes requests between Grok/LLaMA and OpenAI based on task complexity
 */

require_once __DIR__ . '/AIHelper.php';
require_once __DIR__ . '/GrokAI.php';

class AIRouter {
    private $openAIHelper;
    private $grokAI;
    private $defaultModel;
    
    // Task complexity levels
    const COMPLEXITY_BASIC = 'basic';
    const COMPLEXITY_INTERMEDIATE = 'intermediate';
    const COMPLEXITY_ADVANCED = 'advanced';
    
    // Model assignments
    const MODEL_GROK = 'grok';
    const MODEL_OPENAI = 'openai';
    
    public function __construct() {
        $this->openAIHelper = new AIHelper();
        $this->grokAI = new GrokAI();
        
        // Default to OpenAI for backward compatibility
        $this->defaultModel = defined('AI_DEFAULT_MODEL') ? AI_DEFAULT_MODEL : self::MODEL_OPENAI;
    }
    
    /**
     * Route a chat request to the appropriate AI model
     */
    public function chat($userMessage, $systemPrompt = 'You are a helpful AI Study Assistant.', $complexity = null) {
        // Auto-detect complexity if not provided
        if ($complexity === null) {
            $complexity = $this->detectComplexity($userMessage);
        }
        
        $model = $this->selectModel($complexity);
        
        // Route to selected model
        if ($model === self::MODEL_GROK && $this->grokAI->isValidApiKey()) {
            return $this->grokAI->chat($userMessage, $systemPrompt);
        }
        
        // Fallback to OpenAI
        return $this->openAIHelper->chat($userMessage, $systemPrompt);
    }
    
    /**
     * Route career recommendations
     */
    public function generateCareerRecommendations($gradesData) {
        // Career recommendations are complex - use OpenAI
        return $this->openAIHelper->generateCareerRecommendations($gradesData);
    }
    
    /**
     * Route report card extraction (Vision API needed)
     */
    public function extractTextFromImage($imageData, $mimeType = 'image/jpeg') {
        // Image extraction requires Vision API - use OpenAI
        return $this->openAIHelper->extractTextFromImage($imageData, $mimeType);
    }
    
    /**
     * Route document topic analysis
     */
    public function analyzeDocumentTopics($content, $complexity = null) {
        if ($complexity === null) {
            $complexity = self::COMPLEXITY_BASIC;
        }
        
        $model = $this->selectModel($complexity);
        
        if ($model === self::MODEL_GROK && $this->grokAI->isValidApiKey()) {
            return $this->grokAI->analyzeDocumentTopics($content);
        }
        
        return $this->openAIHelper->analyzeDocumentTopics($content);
    }
    
    /**
     * Route challenging topics identification
     */
    public function identifyChallengingTopics($topics, $content) {
        // This is intermediate complexity
        $model = $this->selectModel(self::COMPLEXITY_INTERMEDIATE);
        
        if ($model === self::MODEL_GROK && $this->grokAI->isValidApiKey()) {
            return $this->grokAI->identifyChallengingTopics($topics, $content);
        }
        
        return $this->openAIHelper->identifyChallengingTopics($topics, $content);
    }
    
    /**
     * Route memorandum generation
     */
    public function generateMemorandum($content, $topics) {
        // Memorandum generation is advanced - use OpenAI
        return $this->openAIHelper->generateMemorandum($content, $topics);
    }
    
    /**
     * Route study plan generation
     */
    public function generateStudyPlan($challengingTopics, $studentName) {
        // Study plans are intermediate complexity
        $model = $this->selectModel(self::COMPLEXITY_INTERMEDIATE);
        
        if ($model === self::MODEL_GROK && $this->grokAI->isValidApiKey()) {
            return $this->grokAI->generateStudyPlan($challengingTopics, $studentName);
        }
        
        return $this->openAIHelper->generateStudyPlan($challengingTopics, $studentName);
    }
    
    /**
     * Route study plan recitation
     */
    public function reciteStudyPlan($studyPlanContent) {
        // Recitation is basic
        $model = $this->selectModel(self::COMPLEXITY_BASIC);
        
        if ($model === self::MODEL_GROK && $this->grokAI->isValidApiKey()) {
            return $this->grokAI->reciteStudyPlan($studyPlanContent);
        }
        
        return $this->openAIHelper->reciteStudyPlan($studyPlanContent);
    }
    
    /**
     * Route SEO content generation (always OpenAI for quality)
     */
    public function generateSEOContent($topic, $contentType = 'article') {
        // SEO content needs high quality - always use OpenAI
        return $this->openAIHelper->generateSEOContent($topic, $contentType);
    }
    
    /**
     * Select model based on complexity
     */
    private function selectModel($complexity) {
        // Configuration: which model to use for each complexity level
        $routing = $this->getRoutingConfig();
        
        return $routing[$complexity] ?? $this->defaultModel;
    }
    
    /**
     * Get routing configuration from constants/env
     */
    private function getRoutingConfig() {
        // Default routing: Grok for basic tasks, OpenAI for complex
        return [
            self::COMPLEXITY_BASIC => defined('AI_BASIC_MODEL') ? AI_BASIC_MODEL : self::MODEL_GROK,
            self::COMPLEXITY_INTERMEDIATE => defined('AI_INTERMEDIATE_MODEL') ? AI_INTERMEDIATE_MODEL : self::MODEL_GROK,
            self::COMPLEXITY_ADVANCED => defined('AI_ADVANCED_MODEL') ? AI_ADVANCED_MODEL : self::MODEL_OPENAI,
        ];
    }
    
    /**
     * Detect task complexity from user message
     */
    private function detectComplexity($userMessage) {
        $message = strtolower($userMessage);
        $wordCount = str_word_count($userMessage);
        
        // Advanced complexity indicators
        $advancedKeywords = ['analyze', 'compare', 'evaluate', 'critique', 'synthesize', 'memorandum', 'exam paper', 'test paper'];
        foreach ($advancedKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return self::COMPLEXITY_ADVANCED;
            }
        }
        
        // Intermediate complexity indicators
        $intermediateKeywords = ['study plan', 'explain', 'how does', 'why is', 'describe', 'process', 'method'];
        foreach ($intermediateKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return self::COMPLEXITY_INTERMEDIATE;
            }
        }
        
        // Long messages are usually more complex
        if ($wordCount > 50) {
            return self::COMPLEXITY_INTERMEDIATE;
        }
        
        // Basic by default
        return self::COMPLEXITY_BASIC;
    }
    
    /**
     * Get available models
     */
    public function getAvailableModels() {
        $models = [];
        
        if ($this->openAIHelper->isValidApiKey()) {
            $models[self::MODEL_OPENAI] = [
                'name' => 'OpenAI (GPT-4o-mini)',
                'status' => 'available',
                'use_for' => 'Advanced tasks, memorandums, career recommendations'
            ];
        }
        
        if ($this->grokAI->isValidApiKey()) {
            $models[self::MODEL_GROK] = [
                'name' => 'Grok/LLaMA',
                'status' => 'available',
                'use_for' => 'Basic and intermediate tasks, chat, study plans'
            ];
        }
        
        return $models;
    }
    
    /**
     * Get current routing configuration
     */
    public function getRoutingInfo() {
        return [
            'default_model' => $this->defaultModel,
            'routing' => $this->getRoutingConfig(),
            'available_models' => $this->getAvailableModels()
        ];
    }
    
    /**
     * Make a direct request with model selection
     */
    public function makeRequest($messages, $maxTokens = 500, $temperature = 0.7, $model = null) {
        if ($model === null) {
            $model = $this->defaultModel;
        }
        
        if ($model === self::MODEL_GROK && $this->grokAI->isValidApiKey()) {
            return $this->grokAI->makeRequest($messages, $maxTokens, $temperature);
        }
        
        return $this->openAIHelper->makeRequest($messages, $maxTokens, $temperature);
    }
}
