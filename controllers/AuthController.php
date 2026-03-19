<?php
/**
 * Auth Controller - Login, Register, Logout
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/otp.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/UserActivity.php';

class AuthController {
    private $userModel;
    private $studentModel;
    private $userActivityModel;

    public function __construct() {
        $this->userModel = new User();
        $this->studentModel = new Student();
        $this->userActivityModel = new UserActivity();
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

                    // Track login activity and streak
                    $this->userActivityModel->updateActivity($user['id']);

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
        $otpMethod = 'sms'; // Default to SMS
        $step = $_GET['step'] ?? '1'; // Step 1: Register form, Step 2: OTP verification

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($step === '1') {
                // Step 1: Initial registration - send OTP
                $username = $_POST['username'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $password = $_POST['password'] ?? '';
                $passwordConfirm = $_POST['password_confirm'] ?? '';
                $otpMethod = $_POST['otp_method'] ?? 'sms'; // Default to SMS

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
                            // Validate phone format
                            if (!$this->validatePhone($phone)) {
                                $error = 'Invalid phone number format. Please enter a valid phone number.';
                            } else {
                                // Generate and send OTP
                                $otpCode = createOtp($phone, 'registration');
                                
                                // Send via user's preferred method
                                $sendSuccess = $this->sendOtpViaPreferredMethod($phone, $otpCode, 'registration', $otpMethod);
                                
                                if (!$sendSuccess && $otpMethod === 'whatsapp' && defined('FALLBACK_TO_SMS') && FALLBACK_TO_SMS) {
                                    // Fallback to SMS if WhatsApp fails
                                    $sendSuccess = sendViaTwilio($phone, "StudySmart: Your verification code is: {$otpCode}. Valid for " . OTP_EXPIRY_MINUTES . " minutes.", false);
                                    if ($sendSuccess) {
                                        $otpMethod = 'sms'; // Switch to SMS
                                    }
                                }

                                // Store registration data in session for step 2
                                $_SESSION['pending_registration'] = [
                                    'username' => $username,
                                    'phone' => $phone,
                                    'password' => $password,
                                    'otp_method' => $otpMethod
                                ];

                                // Redirect to OTP verification step
                                header('Location: /register?step=2');
                                exit;
                            }
                        } catch (Exception $e) {
                            $error = 'Username or phone number already exists';
                        }
                    }
                }
            } elseif ($step === '2') {
                // Step 2: Verify OTP
                $otpCode = $_POST['otp_code'] ?? '';
                $pendingReg = $_SESSION['pending_registration'] ?? null;
                
                if (!$pendingReg) {
                    $error = 'Registration session expired. Please start again.';
                    $step = '1';
                } elseif (empty($otpCode)) {
                    $error = 'Please enter the OTP code';
                } else {
                    // Verify OTP
                    $result = verifyOtp($pendingReg['phone'], $otpCode, 'registration');
                    
                    if ($result['success']) {
                        // OTP verified - create user account
                        try {
                            $userId = $this->userModel->create(
                                $pendingReg['username'],
                                $pendingReg['password'],
                                null,
                                $pendingReg['phone']
                            );
                            $this->studentModel->create($userId);
                            
                            // Create 7-day free trial subscription
                            $this->createFreeTrialSubscription($userId);
                            
                            // Clear pending registration
                            unset($_SESSION['pending_registration']);
                            
                            setFlashMessage('success', 'Account created successfully! You have been enrolled in a 7-day free trial of the Basic plan. Please login.');
                            header('Location: /login');
                            exit;
                        } catch (Exception $e) {
                            $error = 'Failed to create account. Please try again.';
                            $step = '1';
                        }
                    } else {
                        $error = $result['message'];
                        // Increment attempt counter
                        incrementOtpAttempts($pendingReg['phone'], $otpCode, 'registration');
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

    /**
     * Validate phone number format
     */
    private function validatePhone($phone) {
        // Remove spaces, dashes, and parentheses
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Check if it starts with + followed by digits (international format)
        // Or just digits (local format)
        if (preg_match('/^\+?[0-9]{7,15}$/', $cleaned)) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if phone number already exists (AJAX endpoint)
     */
    public function checkPhone() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit;
        }

        $phone = $_POST['phone'] ?? '';

        if (empty($phone)) {
            echo json_encode(['exists' => false, 'message' => '']);
            exit;
        }

        // Check if phone number exists
        $existingUser = $this->userModel->findByPhone($phone);

        if ($existingUser) {
            echo json_encode([
                'exists' => true,
                'message' => 'This phone number is already registered. Please use a different number or login.'
            ]);
        } else {
            echo json_encode(['exists' => false, 'message' => '']);
        }
        exit;
    }

    /**
     * Send OTP via user's preferred method (WhatsApp or SMS)
     */
    private function sendOtpViaPreferredMethod($phone, $otpCode, $purpose, $method) {
        if ($method === 'whatsapp') {
            // Send via WhatsApp
            $message = "StudySmart: Your verification code is: {$otpCode}. Valid for " . OTP_EXPIRY_MINUTES . " minutes. Do not share this code.";
            return sendViaTwilio($phone, $message, true); // true = use WhatsApp
        } else {
            // Send via SMS
            return sendOtpSms($phone, $otpCode, $purpose);
        }
    }

    /**
     * Resend OTP via original method
     */
    public function resendOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit;
        }

        $pendingReg = $_SESSION['pending_registration'] ?? null;

        if (!$pendingReg) {
            echo json_encode(['success' => false, 'message' => 'Registration session expired. Please start again.']);
            exit;
        }

        // Check cooldown
        $canResend = canResendOtp($pendingReg['phone'], 'registration');

        if (!$canResend['can_resend']) {
            echo json_encode([
                'success' => false,
                'message' => 'Please wait ' . $canResend['wait_time'] . ' seconds before requesting a new OTP.',
                'wait_time' => $canResend['wait_time']
            ]);
            exit;
        }

        // Generate and send new OTP via original method
        $otpMethod = $pendingReg['otp_method'] ?? 'sms';
        $otpCode = createOtp($pendingReg['phone'], 'registration');
        $this->sendOtpViaPreferredMethod($pendingReg['phone'], $otpCode, 'registration', $otpMethod);

        echo json_encode(['success' => true, 'message' => 'OTP resent successfully. Please check your phone.']);
        exit;
    }
}
