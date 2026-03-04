<?php
/**
 * Subscription Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class SubscriptionController {
    
    private $plans = [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'period' => 'Forever',
            'features' => [
                '5 script uploads per month',
                'Basic AI chat (limited messages)',
                'Simple study plans',
                'Career recommendations',
                'Ad-supported experience'
            ],
            'limitations' => [
                'No voice recitation',
                'No advanced AI features',
                'Limited storage (100MB)'
            ]
        ],
        'basic' => [
            'name' => 'Basic',
            'price' => 39,
            'period' => 'per month',
            'features' => [
                '50 script uploads per month',
                'Unlimited AI chat',
                'AI study plan recitation',
                'Priority email support',
                'Ad-free experience',
                'Advanced career guidance'
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
                'Voice recitation for all content',
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
        $plan = $_POST['plan'] ?? 'basic';
        $paymentMethod = $_POST['payment_method'] ?? 'card';

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
        } else if ($paymentMethod === 'paypal') {
            // PayPal - redirect to PayPal (in production)
            // For now, simulate successful payment
            $this->activateSubscription($user['id'], $plan);
            setFlashMessage('success', "Successfully subscribed to {$this->plans[$plan]['name']} plan via PayPal! Your subscription is now active.");
            header('Location: /subscription/success?plan=' . $plan);
            exit;
        } else {
            // Card Payment - simulate processing
            $cardNumber = $_POST['card_number'] ?? '';
            $expiryDate = $_POST['expiry_date'] ?? '';
            $cvv = $_POST['cvv'] ?? '';

            // Basic validation (in production, use payment gateway validation)
            if (empty($cardNumber) || empty($expiryDate) || empty($cvv)) {
                setFlashMessage('error', 'Please fill in all payment details');
                header('Location: /subscription/checkout?plan=' . $plan);
                exit;
            }

            // Process payment (in production, use PayFast/PayPal/Stripe API)
            $paymentSuccess = true; // Simulated

            if ($paymentSuccess) {
                $this->activateSubscription($user['id'], $plan);
                setFlashMessage('success', "Successfully subscribed to {$this->plans[$plan]['name']} plan! Your subscription is now active.");
                header('Location: /subscription/success?plan=' . $plan);
            } else {
                setFlashMessage('error', 'Payment failed. Please try again.');
                header('Location: /subscription/checkout?plan=' . $plan);
            }
        }
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
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM subscriptions
                WHERE user_id = ?
                AND status IN ('active', 'trial', 'pending_eft')
            ");
            $stmt->execute([$userId]);
            $count = $stmt->fetchColumn();
            return $count > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function activateSubscription($userId, $plan) {
        $db = Database::getInstance()->getConnection();
        
        try {
            $stmt = $db->prepare("
                INSERT INTO subscriptions (user_id, plan, price, status, current_period_start, current_period_end, created_at)
                VALUES (?, ?, ?, 'active', datetime('now'), datetime('now', '+1 month'), datetime('now'))
            ");
            $stmt->execute([$userId, $plan, $this->plans[$plan]['price']]);
        } catch (Exception $e) {
            // Table might not exist, create it
            $this->createSubscriptionsTable();
            
            $stmt = $db->prepare("
                INSERT INTO subscriptions (user_id, plan, price, status, current_period_start, current_period_end, created_at)
                VALUES (?, ?, ?, 'active', datetime('now'), datetime('now', '+1 month'), datetime('now'))
            ");
            $stmt->execute([$userId, $plan, $this->plans[$plan]['price']]);
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
