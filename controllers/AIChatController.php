<?php
/**
 * AI Chat Controller - Updated to use Hybrid AI Router
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/AIRouter.php';
require_once __DIR__ . '/../controllers/SubscriptionController.php';

class AIChatController {
    private $aiRouter;

    public function __construct() {
        $this->aiRouter = new AIRouter();
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

        $systemPrompt = 'You are a helpful AI Study Assistant. Help students with their questions about various subjects, explain concepts clearly, provide study tips, and create quiz questions when asked. Be encouraging and supportive.

IMPORTANT FORMATTING RULES:
- Use **bold** for key terms and definitions
- Use headings (##, ###) for sections
- Use bullet points (- or *) for lists
- Use numbered lists (1., 2., etc.) for steps and sequences
- Use code blocks (```) for formulas and code
- Use blockquotes (>) for important notes
- Break content into well-structured sections with clear headings
- NEVER write everything in one paragraph - always structure your response with proper spacing';

        $response = $this->aiRouter->chat($message, $systemPrompt);

        if ($response) {
            echo json_encode(['reply' => $response]);
        } else {
            echo json_encode(['error' => 'Failed to get response']);
        }
    }
}
