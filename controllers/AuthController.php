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
                    
                    header('Location: /dashboard');
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
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            
            if (empty($username) || empty($password)) {
                $error = 'Please fill in all fields';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters';
            } elseif ($password !== $passwordConfirm) {
                $error = 'Passwords do not match';
            } else {
                try {
                    $userId = $this->userModel->create($username, $password);
                    $this->studentModel->create($userId);
                    
                    setFlashMessage('success', 'Account created successfully! Please login.');
                    header('Location: /login');
                    exit;
                } catch (Exception $e) {
                    $error = 'Username already exists';
                }
            }
        }
        
        include __DIR__ . '/../templates/auth/register.php';
    }
    
    public function logout() {
        session_destroy();
        header('Location: /');
        exit;
    }
}
