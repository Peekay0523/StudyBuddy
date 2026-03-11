<?php
/**
 * AI Chat Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/AIHelper.php';
require_once __DIR__ . '/../controllers/SubscriptionController.php';

class AIChatController {
    private $aiHelper;

    public function __construct() {
        $this->aiHelper = new AIHelper();
    }

    public function index() {
        requireStudent();
        
        // Check user's subscription plan
        $subscriptionController = new SubscriptionController();
        $user = getCurrentUser();
        $userSubscription = $subscriptionController->getUserSubscription($user['id']);
        $currentPlan = $userSubscription['plan'] ?? 'free';
        $canUseVoiceMode = ($currentPlan !== 'free');
        
        include __DIR__ . '/../templates/pages/ai_chat.php';
    }

    public function chat() {
        requireStudent();

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

        $systemPrompt = 'You are a helpful AI Study Assistant. Help students with their questions about various subjects, explain concepts clearly, provide study tips, and create quiz questions when asked. Be encouraging and supportive. Do NOT use markdown formatting (no **, ##, *, or other markdown symbols). Write in plain text only.';

        $response = $this->aiHelper->chat($message, $systemPrompt);

        if ($response) {
            echo json_encode(['reply' => $response]);
        } else {
            echo json_encode(['error' => 'Failed to get response']);
        }
    }
}
