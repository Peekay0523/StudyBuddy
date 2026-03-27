<?php
/**
 * Subscription Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/bobpay.php';

class SubscriptionController {
    
    private $plans = [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'period' => 'Forever',
            'features' => [
                '10 script uploads per month',
                'Basic AI chat (limited messages)',
                'Simple study plans',
                'Limited scripts',
                'Ad Support'
            ],
            'limitations' => [
                'No voice recitation',
                'No Memorundum generation',
                'No career recommendations'
            ]
        ],
        'basic' => [
            'name' => 'Basic',
            'price' => 39,
            'period' => 'per month',
            'features' => [
                'Unlimited uploads per month',
                'Unlimited AI chat with voice mode',
                'AI study plan voice recitation',
                'Career and Institution recommendations',
                'Daily uploaded bursaries',
                'Memorundum generation and voice recitation'
            ],
            'limitations' => [
                'Standard storage (1GB)',
                'Basic analytics'
            ]
        ],
        'premium' => [
            'name' => 'Premium',
            'price' => 69,
            'period' => 'per month',
            'features' => [
                'Unlimited script uploads',
                'Unlimited AI chat with GPT-4',
                'Voice recitation',
                '24/7 priority support',
                'Ad-free experience',
                'Advanced analytics & insights',
                'Download study materials',
                'Custom study schedules'
            ],
            'limitations' => []
        ]
    ];

    public function index() {
        requireStudent();
        
        $user = getCurrentUser();
        $subscription = $this->getUserSubscription($user['id']);
        
        $pageTitle = 'Subscription Plans - StudySmart';
        $currentPage = 'subscription';
        
        include __DIR__ . '/../templates/pages/subscription_plans.php';
    }

    public function checkout() {
        requireStudent();

        $plan = $_GET['plan'] ?? 'basic';

        // Premium is not available yet
        if ($plan === 'premium') {
            setFlashMessage('error', 'Premium plan is coming soon. Please select Basic plan for now.');
            header('Location: /subscription');
            exit;
        }

        if (!isset($this->plans[$plan])) {
            setFlashMessage('error', 'Invalid plan selected');
            header('Location: /subscription');
            exit;
        }

        $user = getCurrentUser();
        $planDetails = $this->plans[$plan];

        $pageTitle = 'Checkout - ' . $planDetails['name'] . ' Plan';
        $currentPage = 'subscription';

        include __DIR__ . '/../templates/pages/subscription_checkout.php';
    }

    public function processPayment() {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /subscription');
            exit;
        }

        $user = getCurrentUser();
        
        // Debug: Log user data
        error_log('Subscription: User data - ' . json_encode($user));
        
        $plan = $_POST['plan'] ?? 'basic';
        $paymentMethod = $_POST['payment_method'] ?? 'card';

        // Premium is not available yet
        if ($plan === 'premium') {
            setFlashMessage('error', 'Premium plan is coming soon. Please select Basic plan.');
            header('Location: /subscription');
            exit;
        }

        if (!isset($this->plans[$plan])) {
            setFlashMessage('error', 'Invalid plan selected');
            header('Location: /subscription');
            exit;
        }

        // Check if user already has an active or pending subscription
        if ($this->userHasActiveSubscription($user['id'])) {
            setFlashMessage('error', 'You already have an active subscription. Please cancel your current subscription before subscribing to a new plan.');
            header('Location: /subscription');
            exit;
        }

        // Handle different payment methods
        if ($paymentMethod === 'eft') {
            // EFT Payment - requires manual verification
            $eftReference = $_POST['eft_reference'] ?? '';
            $eftAmount = $_POST['eft_amount'] ?? 0;
            $eftDate = $_POST['eft_date'] ?? '';

            if (empty($eftReference) || empty($eftAmount) || empty($eftDate)) {
                setFlashMessage('error', 'Please fill in all EFT payment details');
                header('Location: /subscription/checkout?plan=' . $plan);
                exit;
            }

            // Handle proof of payment upload
            $proofPath = null;
            if (isset($_FILES['proof_upload']) && $_FILES['proof_upload']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Upload directory - files go in public/uploads/eft_proofs/ for web access
                $uploadDir = __DIR__ . '/../../../public/uploads/eft_proofs/';

                // Create upload directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $file = $_FILES['proof_upload'];
                $originalName = $file['name'];
                $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                // Check for upload errors
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    setFlashMessage('error', 'File upload error: ' . $file['error']);
                    header('Location: /subscription/checkout?plan=' . $plan);
                    exit;
                }

                // Validate file type
                $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!in_array($fileExt, $allowedExts)) {
                    setFlashMessage('error', 'Invalid file type. Only PDF, JPG, and PNG are allowed.');
                    header('Location: /subscription/checkout?plan=' . $plan);
                    exit;
                }

                // Validate file size (5MB max)
                if ($file['size'] > 5 * 1024 * 1024) {
                    setFlashMessage('error', 'File size must be less than 5MB.');
                    header('Location: /subscription/checkout?plan=' . $plan);
                    exit;
                }

                // Generate unique filename with full extension
                $newFilename = 'eft_proof_' . $user['id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                $destination = $uploadDir . $newFilename;

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $proofPath = 'uploads/eft_proofs/' . $newFilename;
                } else {
                    setFlashMessage('error', 'Failed to save uploaded file. Please try again.');
                    header('Location: /subscription/checkout?plan=' . $plan);
                    exit;
                }
            } else {
                setFlashMessage('error', 'Please upload proof of payment (PDF, JPG, or PNG).');
                header('Location: /subscription/checkout?plan=' . $plan);
                exit;
            }

            // Create pending subscription for EFT
            $this->activateSubscriptionEFT($user['id'], $plan, $eftReference, $eftAmount, $eftDate, $proofPath);

            setFlashMessage(
                'info',
                "Your EFT payment reference <strong>{$eftReference}</strong> has been recorded. " .
                "Your subscription will be activated within 24-48 hours after payment verification. " .
                "Please email proof of payment to <strong>billing@studysmart.co.za</strong>"
            );
            header('Location: /subscription/success?plan=' . $plan . '&status=pending');
            exit;
        } else if ($paymentMethod === 'bobpay') {
            // BobPay - redirect to BobPay payment page
            $this->processBobPayPayment($user, $plan);
            exit;
        }
        exit;
    }

    /**
     * Process BobPay Payment
     */
    private function processBobPayPayment($user, $plan) {
        error_log('BobPay: Processing payment for user: ' . json_encode($user) . ', plan: ' . $plan);
        
        $bobPay = new BobPayHelper();

        if (!$bobPay->isConfigured()) {
            error_log('BobPay: Not configured');
            setFlashMessage('error', 'BobPay is not configured. Please contact the administrator.');
            header('Location: /subscription/checkout?plan=' . $plan);
            exit;
        }

        $planDetails = $this->plans[$plan];
        error_log('BobPay: Plan details: ' . json_encode($planDetails));

        // Generate a unique token for this transaction
        $token = bin2hex(random_bytes(16));

        // Store transaction data in session for verification on return
        $_SESSION['bobpay_transaction'] = [
            'user_id' => $user['id'],
            'plan' => $plan,
            'amount' => $planDetails['price'],
            'token' => $token,
            'timestamp' => time()
        ];
        error_log('BobPay: Session data stored');

        // Prepare BobPay data
        $bobPayData = $bobPay->preparePaymentData([
            'amount' => $planDetails['price'],
            'customer_email' => $user['email'] ?? 'customer@example.com', // Use placeholder if no email
            'customer_name' => $user['username'] ?? 'Customer',
            'success_url' => APP_URL . '/subscription/bobpay/return',
            'cancel_url' => APP_URL . '/subscription/bobpay/cancel',
            'callback_url' => APP_URL . '/subscription/bobpay/webhook',
            'metadata' => [
                'user_id' => $user['id'],
                'plan' => $plan,
                'payment_id' => 'sub_' . $user['id'] . '_' . time()
            ]
        ]);
        
        error_log('BobPay: Payment data prepared: ' . json_encode($bobPayData));

        // Create payment via BobPay API
        error_log('BobPay: Calling createPayment...');
        $response = $bobPay->createPayment($bobPayData);
        error_log('BobPay: Response received: ' . json_encode($response));

        if (!$response || (!isset($response['url']) && !isset($response['short_url']))) {
            error_log('BobPay Payment Failed: No URL in response. Response: ' . json_encode($response));
            setFlashMessage('error', 'Failed to initiate BobPay payment. Please try again or contact support.');
            header('Location: /subscription/checkout?plan=' . $plan);
            exit;
        }

        // Get payment URL (prefer short_url if available)
        $paymentUrl = $response['short_url'] ?? $response['url'];
        error_log('BobPay: Payment URL: ' . $paymentUrl);

        // Store transaction ID for verification
        $_SESSION['bobpay_transaction']['payment_url'] = $paymentUrl;

        // Redirect user to BobPay checkout
        header('Location: ' . $paymentUrl);
        exit;
    }

    /**
     * BobPay Return URL - Handle successful payment
     */
    public function bobpayReturn() {
        requireStudent();

        $user = getCurrentUser();

        // Get transaction data from GET parameters or session
        $transactionId = $_GET['transaction_id'] ?? $_SESSION['bobpay_transaction']['transaction_id'] ?? null;
        $status = $_GET['status'] ?? $_GET['payment_status'] ?? '';

        // Verify session token
        $sessionToken = $_SESSION['bobpay_transaction']['token'] ?? '';
        $postToken = $_GET['token'] ?? $_SESSION['bobpay_transaction']['token'] ?? '';

        if ($sessionToken !== $postToken && empty($transactionId)) {
            setFlashMessage('error', 'Invalid transaction token. Please contact support.');
            header('Location: /subscription');
            exit;
        }

        // Get plan and user ID from session
        $plan = $_SESSION['bobpay_transaction']['plan'] ?? 'basic';
        $userId = (int)($_SESSION['bobpay_transaction']['user_id'] ?? $user['id']);

        // Verify payment status
        if ($status !== 'successful' && $status !== 'completed' && $status !== 'success') {
            // Try to verify with BobPay API
            $bobPay = new BobPayHelper();
            if ($bobPay->isConfigured() && $transactionId) {
                $verification = $bobPay->verifyPayment($transactionId);
                if ($verification && isset($verification['status'])) {
                    $status = $verification['status'];
                }
            }
        }

        // Check if payment was successful
        if (in_array(strtolower($status), ['successful', 'completed', 'success'])) {
            // Get transaction ID from GET parameters
            $transactionId = $_GET['transaction_id'] ?? $_SESSION['bobpay_transaction']['transaction_id'] ?? null;

            // Activate subscription
            $this->activateSubscription($userId, $plan, 'bobpay', $transactionId);

            // Clear session transaction data
            unset($_SESSION['bobpay_transaction']);

            $planDetails = $this->plans[$plan];
            setFlashMessage('success', "Successfully subscribed to {$planDetails['name']} plan! Your subscription is now active.");
            header('Location: /subscription/success?plan=' . $plan);
        } else {
            // Payment not confirmed - show pending message
            setFlashMessage('info', 'Your payment is being processed. Please wait for confirmation.');
            header('Location: /subscription/success?plan=' . $plan . '&status=pending');
        }
        exit;
    }

    /**
     * BobPay Cancel URL - Handle cancelled payment
     */
    public function bobpayCancel() {
        requireStudent();

        // Clear session transaction data
        unset($_SESSION['bobpay_transaction']);

        setFlashMessage('info', 'Payment was cancelled. You can try again when you\'re ready.');
        header('Location: /subscription');
        exit;
    }

    /**
     * BobPay Webhook URL - Instant Transaction Notification
     * This is called by BobPay servers to notify of payment status
     */
    public function bobpayWebhook() {
        // Get raw POST data
        $payload = file_get_contents('php://input');
        $postData = json_decode($payload, true);

        // Log the webhook data for debugging
        $logFile = __DIR__ . '/../logs/bobpay_webhook.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Webhook Received: " . print_r($postData, true) . "\n", FILE_APPEND);

        // Verify signature if provided
        $bobPay = new BobPayHelper();
        $signature = $_SERVER['HTTP_X_BOBPAY_SIGNATURE'] ?? $_SERVER['HTTP_X_SIGNATURE'] ?? null;
        
        if ($signature && !$bobPay->verifyWebhookSignature($payload, $signature)) {
            http_response_code(400);
            echo 'Invalid signature';
            exit;
        }

        // Check transaction status
        $transactionStatus = $postData['status'] ?? '';

        if (!in_array(strtolower($transactionStatus), ['successful', 'completed', 'success'])) {
            http_response_code(200);
            echo 'Status not complete';
            exit;
        }

        // Get transaction details
        $metadata = $postData['metadata'] ?? [];
        $userId = (int)($metadata['user_id'] ?? $postData['customer_id'] ?? 0);
        $plan = $metadata['plan'] ?? 'basic';
        $token = $metadata['token'] ?? '';

        if (!$userId) {
            http_response_code(400);
            echo 'Invalid transaction data';
            exit;
        }

        // Verify amount
        $planDetails = $this->plans[$plan] ?? null;
        if (!$planDetails) {
            http_response_code(400);
            echo 'Invalid plan';
            exit;
        }

        $paidAmount = (float)($postData['amount'] ?? 0);
        if (abs($paidAmount - $planDetails['price']) > 0.01) {
            http_response_code(400);
            echo 'Amount mismatch';
            exit;
        }

        // Check if subscription already activated
        if ($this->userHasActiveSubscription($userId)) {
            http_response_code(200);
            echo 'Subscription already active';
            exit;
        }

        // Activate subscription
        $transactionId = $postData['id'] ?? $postData['transaction_id'] ?? null;
        $this->activateSubscription($userId, $plan, 'bobpay', $transactionId);

        // Log successful activation
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Subscription activated for user {$userId}, plan {$plan}, transaction_id: {$transactionId}\n", FILE_APPEND);

        http_response_code(200);
        echo 'OK';
        exit;
    }

    private function activateSubscriptionEFT($userId, $plan, $reference, $amount, $paymentDate, $proofPath = null) {
        $db = Database::getInstance()->getConnection();

        try {
            // Create subscription with 'pending_eft' status
            $stmt = $db->prepare("
                INSERT INTO subscriptions (user_id, plan, price, status, current_period_start, current_period_end, created_at, payment_reference, payment_date, proof_path)
                VALUES (?, ?, ?, 'pending_eft', datetime('now'), datetime('now', '+7 days'), datetime('now'), ?, ?, ?)
            ");
            $stmt->execute([$userId, $plan, $amount, $reference, $paymentDate, $proofPath]);
        } catch (Exception $e) {
            // Table might not have these columns, try without proof_path
            try {
                $stmt = $db->prepare("
                    INSERT INTO subscriptions (user_id, plan, price, status, current_period_start, current_period_end, created_at, payment_reference, payment_date)
                    VALUES (?, ?, ?, 'pending_eft', datetime('now'), datetime('now', '+7 days'), datetime('now'), ?, ?)
                ");
                $stmt->execute([$userId, $plan, $amount, $reference, $paymentDate]);
            } catch (Exception $e2) {
                // Ignore if table doesn't exist
            }
        }
    }

    public function success() {
        requireStudent();
        
        $plan = $_GET['plan'] ?? 'basic';
        $user = getCurrentUser();
        $subscription = $this->getUserSubscription($user['id']);
        
        $pageTitle = 'Subscription Activated!';
        $currentPage = 'subscription';
        
        include __DIR__ . '/../templates/pages/subscription_success.php';
    }

    public function cancel() {
        requireStudent();

        $user = getCurrentUser();
        $this->cancelSubscription($user['id']);

        setFlashMessage('success', 'Your subscription has been cancelled. You will retain access until the end of your billing period.');
        header('Location: /subscription');
        exit;
    }

    /**
     * Downgrade to free plan
     */
    public function downgrade() {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /subscription');
            exit;
        }

        $user = getCurrentUser();
        $this->downgradeToFree($user['id']);

        setFlashMessage('success', 'You have been downgraded to the Free plan. You will lose access to premium features immediately.');
        header('Location: /subscription');
        exit;
    }

    public function getUserSubscription($userId) {
        $db = Database::getInstance()->getConnection();

        // Check if subscriptions table exists
        try {
            // Get active or trial subscriptions (trial users have access to basic features)
            $stmt = $db->prepare("
                SELECT * FROM subscriptions
                WHERE user_id = ?
                AND status IN ('active', 'trial')
                AND datetime(current_period_end) > datetime('now')
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $subscription = $stmt->fetch();

            // If user has an active trial, treat it as basic plan
            if ($subscription && $subscription['status'] === 'trial') {
                $subscription['plan'] = 'basic';
                $subscription['is_trial'] = true;
            }

            return $subscription;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check if user has any active or pending subscription
     */
    private function userHasActiveSubscription($userId) {
        $db = Database::getInstance()->getConnection();

        try {
            // Check for active paid subscriptions only (exclude free tier users)
            // A user can have a subscription record but still be on free tier if it's cancelled or expired
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM subscriptions
                WHERE user_id = ?
                AND status IN ('active', 'trial', 'pending_eft')
                AND datetime(current_period_end) > datetime('now')
            ");
            $stmt->execute([$userId]);
            $count = $stmt->fetchColumn();
            return $count > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function activateSubscription($userId, $plan, $paymentMethod = 'card', $transactionId = null) {
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("
                INSERT INTO subscriptions (user_id, plan, price, status, current_period_start, current_period_end, created_at, payment_method, transaction_id)
                VALUES (?, ?, ?, 'active', datetime('now'), datetime('now', '+1 month'), datetime('now'), ?, ?)
            ");
            $stmt->execute([$userId, $plan, $this->plans[$plan]['price'], $paymentMethod, $transactionId]);
        } catch (Exception $e) {
            // Table might not exist, create it
            $this->createSubscriptionsTable();

            $stmt = $db->prepare("
                INSERT INTO subscriptions (user_id, plan, price, status, current_period_start, current_period_end, created_at, payment_method, transaction_id)
                VALUES (?, ?, ?, 'active', datetime('now'), datetime('now', '+1 month'), datetime('now'), ?, ?)
            ");
            $stmt->execute([$userId, $plan, $this->plans[$plan]['price'], $paymentMethod, $transactionId]);
        }
    }

    private function cancelSubscription($userId) {
        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE user_id = ? AND status = 'active'");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            // Ignore if table doesn't exist
        }
    }

    private function downgradeToFree($userId) {
        $db = Database::getInstance()->getConnection();

        try {
            // Cancel any active or trial subscription
            $stmt = $db->prepare("
                UPDATE subscriptions 
                SET status = 'cancelled', 
                    current_period_end = datetime('now'),
                    updated_at = datetime('now')
                WHERE user_id = ? 
                AND status IN ('active', 'trial')
            ");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            // Ignore if table doesn't exist
        }
    }

    private function createSubscriptionsTable() {
        $db = Database::getInstance()->getConnection();

        $db->exec("
            CREATE TABLE IF NOT EXISTS subscriptions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                plan TEXT NOT NULL,
                price REAL NOT NULL,
                status TEXT DEFAULT 'active',
                current_period_start DATETIME,
                current_period_end DATETIME,
                cancelled_at DATETIME,
                payment_reference TEXT,
                payment_date DATETIME,
                proof_path TEXT,
                payment_method TEXT DEFAULT 'eft',
                transaction_id TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
    }

    public function getPlans() {
        return $this->plans;
    }
}
