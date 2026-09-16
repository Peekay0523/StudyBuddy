<?php
/**
 * Auth Controller - Login, Register, Logout
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/otp.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Tutor.php';
require_once __DIR__ . '/../models/UserActivity.php';

class AuthController {
    private $userModel;
    private $studentModel;
    private $tutorModel;
    private $userActivityModel;

    public function __construct() {
        $this->userModel = new User();
        $this->studentModel = new Student();
        $this->tutorModel = new Tutor();
        $this->userActivityModel = new UserActivity();
    }
    
    public function login() {
        if (isLoggedIn()) {
            if (isParent()) {
                header('Location: /parent-dashboard');
            } elseif (isTutor()) {
                header('Location: /tutor-dashboard');
            } else {
                header('Location: /dashboard');
            }
            exit;
        }
        
        $error = '';
        $username = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'student';
            $rememberMe = !empty($_POST['remember_me']);
            
            if (empty($username) || empty($password)) {
                $error = 'Please fill in all fields';
            } else {
                $user = $this->userModel->findByUsername($username);
                
                if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user'] = $user;

                    // Auto-detect tutor role from DB; otherwise use selected role
                    if ($user['role'] === 'tutor') {
                        $_SESSION['user_role'] = 'tutor';
                    } else {
                        $_SESSION['user_role'] = $role; // Selected role (parent or student)
                    }

                    // Track login activity and streak
                    $this->userActivityModel->updateActivity($user['id']);

                    // Handle "Stay Logged In" (Remember Me)
                    if ($rememberMe) {
                        createRememberToken($user['id']);
                    }

                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: /admin');
                    } elseif ($user['role'] === 'tutor') {
                        header('Location: /tutor-dashboard');
                    } elseif ($role === 'tutor') {
                        // Selected the Tutor card but account is not registered
                        // as a tutor yet - send them where they can become one
                        header('Location: /study-group');
                    } elseif ($role === 'parent') {
                        header('Location: /parent-dashboard');
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
        $email = '';
        $phone = '';
        $otpMethod = 'sms'; // Default to SMS
        $step = $_GET['step'] ?? '1'; // Step 1: Register form, Step 2: OTP verification

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($step === '1') {
                // Step 1: Initial registration - send OTP
                $username = $_POST['username'] ?? '';
                $email = $_POST['email'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $password = $_POST['password'] ?? '';
                $passwordConfirm = $_POST['password_confirm'] ?? '';
                $otpMethod = $_POST['otp_method'] ?? 'sms'; // sms, email, or whatsapp
                $registerRole = $_POST['register_role'] ?? 'student'; // student or tutor

                // Tutor-specific fields
                $tutorFullName = trim($_POST['tutor_full_name'] ?? '');
                $tutorSubjects = trim($_POST['tutor_subjects'] ?? '');
                $tutorBio = trim($_POST['tutor_bio'] ?? '');
                $tutorHourlyRate = trim($_POST['tutor_rate'] ?? '');
                $tutorBillingType = trim($_POST['tutor_billing_type'] ?? 'hourly');
                $tutorCompanyName = trim($_POST['tutor_company_name'] ?? '');

                // Validate tutor-required fields
                if ($registerRole === 'tutor' && (empty($tutorFullName) || empty($tutorSubjects))) {
                    $error = 'Please provide your full name and at least one subject to register as a tutor.';
                } elseif (empty($username) || empty($password) || empty($phone)) {
                    $error = 'Please fill in all fields';
                } elseif ($otpMethod === 'email' && empty($email)) {
                    $error = 'Please provide an email address to receive the verification code via email.';
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
                            // Validate phone format (always required for the account)
                            if (!$this->validatePhone($phone)) {
                                $error = 'Invalid phone number format. Please enter a valid phone number.';
                            } else {
                                // Determine the OTP identifier — email for email method, phone otherwise
                                $otpIdentifier = ($otpMethod === 'email' && !empty($email)) ? $email : $phone;

                                // Generate and send OTP
                                $otpCode = createOtp($otpIdentifier, 'registration');

                                // Send via user's preferred method
                                $sendSuccess = $this->sendOtpViaPreferredMethod($otpIdentifier, $otpCode, 'registration', $otpMethod);

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
                                    'email' => $email,
                                    'phone' => $phone,
                                    'password' => $password,
                                    'otp_method' => $otpMethod,
                                    'otp_identifier' => $otpIdentifier,
                                    'register_role' => $registerRole,
                                    'tutor_full_name' => $tutorFullName,
                                    'tutor_subjects' => $tutorSubjects,
                                    'tutor_bio' => $tutorBio,
                                    'tutor_hourly_rate' => $tutorHourlyRate,
                                    'tutor_billing_type' => $tutorBillingType,
                                    'tutor_company_name' => $tutorCompanyName
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
                    // Verify OTP — use otp_identifier (email or phone) if available
                    $otpIdentifier = $pendingReg['otp_identifier'] ?? $pendingReg['phone'];
                    $result = verifyOtp($otpIdentifier, $otpCode, 'registration');
                    
                    if ($result['success']) {
                        // OTP verified - create user account
                        try {
                            $registerRole = $pendingReg['register_role'] ?? 'student';

                            $userId = $this->userModel->create(
                                $pendingReg['username'],
                                $pendingReg['password'],
                                $pendingReg['email'] ?? null,
                                $pendingReg['phone'],
                                $registerRole
                            );

                            // Create student record for students; tutor profile for tutors
                            if ($registerRole === 'tutor') {
                                $this->tutorModel->register(
                                    $userId,
                                    $pendingReg['tutor_full_name'] ?? $pendingReg['username'],
                                    $pendingReg['tutor_subjects'] ?? '',
                                    $pendingReg['tutor_bio'] ?? '',
                                    $pendingReg['tutor_hourly_rate'] ?? '',
                                    $pendingReg['phone'],
                                    $pendingReg['email'] ?? '',
                                    $pendingReg['tutor_billing_type'] ?? 'hourly',
                                    $pendingReg['tutor_company_name'] ?? ''
                                );
                            } else {
                                $this->studentModel->create($userId);
                            }
                            
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
        // Clear remember-me tokens if user is logged in
        if (isLoggedIn()) {
            destroyRememberTokens($_SESSION['user_id']);
        }
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
        $message = "StudySmart: Your verification code for {$purpose} is: {$otpCode}. Valid for " . OTP_EXPIRY_MINUTES . " minutes.";

        if ($method === 'email') {
            // Send via Email — $phone param carries the email address in this case
            return sendOtpEmail($phone, $otpCode, $purpose);
        } elseif ($method === 'whatsapp') {
            // Send via WhatsApp
            return sendViaTwilio($phone, $message, true);
        } else {
            // Send via SMS
            return sendViaTwilio($phone, $message, false);
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
        $otpIdentifier = $pendingReg['otp_identifier'] ?? $pendingReg['phone'];
        $canResend = canResendOtp($otpIdentifier, 'registration');

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
        $otpCode = createOtp($otpIdentifier, 'registration');
        $this->sendOtpViaPreferredMethod($otpIdentifier, $otpCode, 'registration', $otpMethod);

        $methodLabel = ($otpMethod === 'email') ? 'email' : 'phone';
        echo json_encode(['success' => true, 'message' => "OTP resent successfully. Please check your {$methodLabel}."]);
        exit;
    }

    /**
     * Forgot Password - Multi-step process
     */
    public function forgotPassword() {
        if (isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }

        $error = '';
        $success = '';
        $phone = '';
        $step = $_GET['step'] ?? '1';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($step === '1') {
                // Step 1: Request password reset - send OTP
                $phone = $_POST['phone'] ?? '';

                if (empty($phone)) {
                    $error = 'Please enter your phone number';
                } else {
                    // Check if phone number exists
                    $user = $this->userModel->findByPhone($phone);
                    if (!$user) {
                        $error = 'No account found with this phone number';
                    } else {
                        try {
                            // Generate and send OTP
                            $otpCode = createOtp($phone, 'password_reset');
                            $this->sendOtpViaPreferredMethod($phone, $otpCode, 'password_reset', 'sms');
                            
                            $success = 'Verification code sent to your phone';
                            $step = '2';
                        } catch (Exception $e) {
                            $error = 'Failed to send verification code. Please try again.';
                        }
                    }
                }
            } elseif ($step === '2') {
                // Step 2: Verify OTP
                $phone = $_POST['phone'] ?? '';
                $otpCode = $_POST['otp_code'] ?? '';

                if (empty($phone) || empty($otpCode)) {
                    $error = 'Please enter the verification code';
                    $step = '2';
                } else {
                    $result = verifyOtp($phone, $otpCode, 'password_reset');

                    if ($result['success']) {
                        // OTP verified - proceed to password reset
                        $step = '3';
                    } else {
                        $error = 'Invalid or expired verification code';
                        incrementOtpAttempts($phone, $otpCode, 'password_reset');
                        $step = '2';
                    }
                }
            } elseif ($step === '3') {
                // Step 3: Set new password
                $phone = $_POST['phone'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (empty($phone) || empty($newPassword)) {
                    $error = 'Please fill in all fields';
                    $step = '3';
                } elseif (strlen($newPassword) < 8) {
                    $error = 'Password must be at least 8 characters';
                    $step = '3';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Passwords do not match';
                    $step = '3';
                } else {
                    try {
                        // Update password
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $user = $this->userModel->findByPhone($phone);
                        
                        if ($user) {
                            $this->userModel->update($user['id'], ['password' => $hashedPassword]);
                            
                            setFlashMessage('success', 'Password reset successfully! Please login with your new password.');
                            header('Location: /login');
                            exit;
                        } else {
                            $error = 'User not found';
                            $step = '3';
                        }
                    } catch (Exception $e) {
                        $error = 'Failed to reset password. Please try again.';
                        $step = '3';
                    }
                }
            }
        }

        include __DIR__ . '/../templates/auth/forgot_password.php';
        exit;
    }

    /**
     * Resend OTP for forgot password
     */
    public function resendOtpForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $phone = $input['phone'] ?? '';

        if (empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Phone number required']);
            exit;
        }

        // Check cooldown
        $canResend = canResendOtp($phone, 'password_reset');

        if (!$canResend['can_resend']) {
            echo json_encode([
                'success' => false,
                'message' => 'Please wait ' . $canResend['wait_time'] . ' seconds before requesting a new code.',
                'wait_time' => $canResend['wait_time']
            ]);
            exit;
        }

        // Generate and send new OTP
        $otpCode = createOtp($phone, 'password_reset');
        $this->sendOtpViaPreferredMethod($phone, $otpCode, 'password_reset', 'sms');

        echo json_encode(['success' => true, 'message' => 'Verification code resent successfully.']);
        exit;
    }
}
