<?php
/**
 * AI Chat Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/AIHelper.php';

class AIChatController {
    private $aiHelper;
    
    public function __construct() {
        $this->aiHelper = new AIHelper();
    }
    
    public function index() {
        requireLogin();
        include __DIR__ . '/../templates/pages/ai_chat.php';
    }
    
    public function chat() {
        requireLogin();

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Invalid request method']);
            exit;
        }

        $message = $_POST['message'] ?? '';

        if (empty($message)) {
            echo json_encode(['error' => 'No message provided']);
            exit;
        }

        $systemPrompt = 'You are a helpful AI Study Assistant. Help students with their questions about various subjects, explain concepts clearly, provide study tips, and create quiz questions when asked. Be encouraging and supportive.';

        $response = $this->aiHelper->chat($message, $systemPrompt);

        if ($response) {
            echo json_encode(['reply' => $response]);
        } else {
            echo json_encode(['error' => 'Failed to get response']);
        }
    }
}
