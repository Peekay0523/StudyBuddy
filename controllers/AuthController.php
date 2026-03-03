<?php
/**
 * Auth Controller - Login, Register, Logout
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';

class AuthController {
    private $userModel;
    private $studentModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->studentModel = new Student();
    }
    
    public function login() {
        if (isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
        
        $error = '';
        $username = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $error = 'Please fill in all fields';
            } else {
                $user = $this->userModel->findByUsername($username);
                
                if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user'] = $user;

                    // Redirect admin users to admin panel
                    if ($user['role'] === 'admin') {
                        header('Location: /admin');
                    } else {
                        header('Location: /dashboard');
                    }
                    exit;
                } else {
                    $error = 'Invalid username or password';
                }
            }
        }
        
        include __DIR__ . '/../templates/auth/login.php';
    }
    
    public function register() {
        if (isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }

        $error = '';
        $username = '';
        $phone = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            if (empty($username) || empty($password) || empty($phone)) {
                $error = 'Please fill in all fields';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters';
            } elseif ($password !== $passwordConfirm) {
                $error = 'Passwords do not match';
            } else {
                // Check if phone number already exists
                $existingUser = $this->userModel->findByPhone($phone);
                if ($existingUser) {
                    $error = 'Phone number already registered. Please use a different number or login.';
                } else {
                    try {
                        $userId = $this->userModel->create($username, $password, null, $phone);
                        $this->studentModel->create($userId);

                        // Create 7-day free trial subscription for Basic plan
                        $this->createFreeTrialSubscription($userId);

                        setFlashMessage('success', 'Account created successfully! You have been enrolled in a 7-day free trial of the Basic plan. Please login.');
                        header('Location: /login');
                        exit;
                    } catch (Exception $e) {
                        $error = 'Username or phone number already exists';
                    }
                }
            }
        }

        include __DIR__ . '/../templates/auth/register.php';
    }

    private function createFreeTrialSubscription($userId) {
        $db = Database::getInstance()->getConnection();

        try {
            // Create a 7-day free trial subscription for Basic plan
            $stmt = $db->prepare("
                INSERT INTO subscriptions (user_id, plan, price, status, current_period_start, current_period_end, created_at)
                VALUES (?, 'basic', 0, 'trial', datetime('now'), datetime('now', '+7 days'), datetime('now'))
            ");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            // Table might not exist, create it first
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
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");

            $stmt = $db->prepare("
                INSERT INTO subscriptions (user_id, plan, price, status, current_period_start, current_period_end, created_at)
                VALUES (?, 'basic', 0, 'trial', datetime('now'), datetime('now', '+7 days'), datetime('now'))
            ");
            $stmt->execute([$userId]);
        }
    }
    
    public function logout() {
        session_destroy();
        header('Location: /');
        exit;
    }
}
