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
define('UPLOAD_DIR_SCRIPTS', __DIR__ . '/../public/uploads/scripts/');
define('UPLOAD_DIR_REPORT_CARDS', __DIR__ . '/../public/uploads/report_cards/');
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

function isParent() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'parent';
}

function isStudent() {
    return !isset($_SESSION['user_role']) || $_SESSION['user_role'] === 'student';
}

function isTutor() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'tutor';
}

function requireTutor() {
    requireLogin();
    if (!isTutor()) {
        header('Location: /study-group');
        exit;
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

function requireParent() {
    requireLogin();
    if (!isParent()) {
        header('Location: /dashboard');
        exit;
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        header('Location: /parent-dashboard');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return $_SESSION['user'] ?? null;
}

// Subscription helpers
function getUserSubscription($userId) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM subscriptions
            WHERE user_id = ?
            AND (
                status IN ('active', 'trial')
                OR (status = 'pending_eft' AND current_period_end > NOW())
            )
            AND current_period_end > NOW()
            ORDER BY current_period_end DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $subscription = $stmt->fetch();

        // Active trial users get basic-plan access
        if ($subscription && $subscription['status'] === 'trial') {
            $subscription['plan'] = 'basic';
            $subscription['is_trial'] = true;
        }

        return $subscription ?: null;
    } catch (Exception $e) {
        return null;
    }
}

function isFreeTierUser($userId) {
    $subscription = getUserSubscription($userId);
    return ($subscription['plan'] ?? 'free') === 'free';
}

// Scan limit helpers
if (!defined('FREE_TIER_SCAN_LIMIT')) {
    define('FREE_TIER_SCAN_LIMIT', 5);
}

function getScanLimitInfo($userId) {
    if (!isFreeTierUser($userId)) {
        return [
            'is_free_tier' => false,
            'limit' => PHP_INT_MAX,
            'used' => 0,
            'remaining' => PHP_INT_MAX,
            'period_end' => null,
        ];
    }

    $bonusFreeScans = 0;
    $used = 0;
    $periodEnd = null;

    try {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT free_scans FROM user_points WHERE user_id = ?");
        $stmt->execute([$userId]);
        $points = $stmt->fetch();
        $bonusFreeScans = (int)($points['free_scans'] ?? 0);

        $stmt = $db->prepare("
            SELECT scan_count, period_end FROM scan_usage
            WHERE user_id = ? AND (period_end IS NULL OR period_end > NOW())
            ORDER BY period_start DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $period = $stmt->fetch();
        if ($period) {
            $used = (int)$period['scan_count'];
            $periodEnd = $period['period_end'];
        }
    } catch (Exception $e) {
        error_log('getScanLimitInfo failed for user ' . $userId . ': ' . $e->getMessage());
    }

    $limit = FREE_TIER_SCAN_LIMIT + $bonusFreeScans;

    return [
        'is_free_tier' => true,
        'limit' => $limit,
        'used' => $used,
        'remaining' => max(0, $limit - $used),
        'period_end' => $periodEnd,
    ];
}

function canUserScan($userId) {
    if (!isFreeTierUser($userId)) {
        return true;
    }
    $scanInfo = getScanLimitInfo($userId);
    return $scanInfo['remaining'] > 0;
}

function recordScan($userId) {
    try {
        $db = Database::getInstance()->getConnection();

        if (isFreeTierUser($userId)) {
            // Purchased free scans are consumed first
            $stmt = $db->prepare("
                UPDATE user_points
                SET free_scans = free_scans - 1, updated_at = NOW()
                WHERE user_id = ? AND free_scans > 0
            ");
            $stmt->execute([$userId]);
            if ($stmt->rowCount() > 0) {
                return true;
            }
        }

        $stmt = $db->prepare("
            UPDATE scan_usage
            SET scan_count = scan_count + 1
            WHERE user_id = ? AND (period_end IS NULL OR period_end > NOW())
        ");
        $stmt->execute([$userId]);
        if ($stmt->rowCount() > 0) {
            return true;
        }

        $stmt = $db->prepare("
            INSERT INTO scan_usage (user_id, scan_count, period_start, period_end)
            VALUES (?, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))
        ");
        $stmt->execute([$userId]);
        return true;
    } catch (Exception $e) {
        error_log('recordScan failed for user ' . $userId . ': ' . $e->getMessage());
        return false;
    }
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
