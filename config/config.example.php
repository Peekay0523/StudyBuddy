<?php
/**
 * Application Configuration
 * 
 * Copy this file to config.php and fill in your actual values.
 * NEVER commit config.php with real secrets to version control.
 */

// Application settings
define('APP_NAME', 'StudySmart - AI Learning Assistant');
define('APP_URL', 'http://localhost:8000');
define('DEBUG_MODE', true);

// Database
define('DB_PATH', __DIR__ . '/../database.sqlite3');

// File uploads
define('UPLOAD_DIR_SCRIPTS', __DIR__ . '/../uploads/scripts/');
define('UPLOAD_DIR_REPORT_CARDS', __DIR__ . '/../uploads/report_cards/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Allowed file extensions
define('ALLOWED_SCRIPT_EXTENSIONS', ['pdf', 'docx', 'txt']);
define('ALLOWED_REPORT_CARD_EXTENSIONS', ['pdf', 'docx', 'jpg', 'jpeg', 'png']);

// Session settings
ini_set('session.cookie_httponly', 1);
session_start();

// OpenAI Configuration (for AI features)
// Get your API key from: https://platform.openai.com/api-keys
define('OPENAI_API_KEY', 'YOUR_OPENAI_API_KEY_HERE');

// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Flash messages helper
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Authentication helpers
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return $_SESSION['user'] ?? null;
}

function getCurrentStudent() {
    if (!isLoggedIn()) {
        return null;
    }
    if (!isset($_SESSION['student'])) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM students WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['student'] = $stmt->fetch();
    }
    return $_SESSION['student'] ?? null;
}

// URL helper
function url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

// Asset helper
function asset($path) {
    return APP_URL . '/public/' . ltrim($path, '/');
}
