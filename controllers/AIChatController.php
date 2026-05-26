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
- Use code blocks (```) for code snippets
- Use blockquotes (>) for important notes

MATHEMATICAL CALCULATIONS (SOUTH AFRICAN / CAPS STYLE):
For all numerical calculations and formulas, ALWAYS use the following format to match our official Data Sheets:
1. Wrap the entire solution in a "mathlab" block:
   ```mathlab
   [Step-by-step solution here]
   ```
2. Inside the mathlab block, use standard LaTeX format:
   - Use \( ... \) for inline math
   - Use \[ ... \] for block math
   - DECIMAL SEPARATOR: ALWAYS use a COMMA (e.g., 9,8 instead of 9.8)
   - MULTIPLICATION: Use \times for scientific notation (e.g., 6 \times 10^{24})
   - UNITS: Use \cdot for combined units (e.g., m \cdot s^{-2} or kg \cdot m \cdot s^{-1})
3. Clearly label steps (e.g., "Step 1: ...", "Final Answer: ...") unless the user explicitly asks for the answer only.

GENERAL FORMATTING:
- Break content into well-structured sections with clear headings
- NEVER write everything in one paragraph - always structure your response with proper spacing';

        // Custom override for calculators or specific "answer only" requests
        if (stripos($message, 'FINAL ANSWER ONLY') !== false || 
            stripos($message, 'ANSWER ONLY') !== false || 
            stripos($message, 'PURE MATHEMATICAL/SCIENTIFIC ENGINE') !== false) {
            $systemPrompt = "ACT AS A PURE MATHEMATICAL/SCIENTIFIC ENGINE. 
            Your task is to provide the FINAL ANSWER ONLY. 
            - ABSOLUTELY NO WORDS, NO STEPS, NO EXPLANATIONS.
            - If it's an equation for x, output 'x = [value]'.
            - Use the 'mathlab' block for the output: ```mathlab [result] ```
            - ALWAYS use a COMMA as a decimal separator (e.g., 9,8).
            - Do not include any introductory or concluding text.";
        }

        $response = $this->aiRouter->chat($message, $systemPrompt);

        if ($response) {
            echo json_encode(['reply' => $response]);
        } else {
            echo json_encode(['error' => 'Failed to get response']);
        }
    }
}
