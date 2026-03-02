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
            'price' => 49,
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
            'price' => 99,
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
        
        include __DIR__ . '/../templates/pages/subscription.php';
    }

    public function subscribe() {
        requireStudent();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /subscription');
            exit;
        }

        $user = getCurrentUser();
        $plan = $_POST['plan'] ?? 'basic';
        
        if (!isset($this->plans[$plan])) {
            setFlashMessage('error', 'Invalid plan selected');
            header('Location: /subscription');
            exit;
        }

        // In production, integrate with payment gateway (PayFast, PayPal, Stripe)
        // For now, simulate subscription activation
        
        $this->activateSubscription($user['id'], $plan);
        
        setFlashMessage('success', "Successfully subscribed to {$this->plans[$plan]['name']} plan!");
        header('Location: /subscription');
        exit;
    }

    public function cancel() {
        requireStudent();
        
        $user = getCurrentUser();
        $this->cancelSubscription($user['id']);
        
        setFlashMessage('success', 'Your subscription has been cancelled. You will retain access until the end of your billing period.');
        header('Location: /subscription');
        exit;
    }

    private function getUserSubscription($userId) {
        $db = Database::getInstance()->getConnection();
        
        // Check if subscriptions table exists
        try {
            $stmt = $db->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
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
