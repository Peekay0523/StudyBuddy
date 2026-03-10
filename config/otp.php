<?php
/**
 * OTP (One-Time Password) Configuration and Helper Functions
 */

require_once __DIR__ . '/database.php';

// OTP Settings
define('OTP_LENGTH', 6);
define('OTP_EXPIRY_MINUTES', 10);
define('OTP_MAX_ATTEMPTS', 3);
define('OTP_RESEND_COOLDOWN_SECONDS', 60);

// SMS/WhatsApp Service Configuration
// Get these from: https://console.twilio.com/
define('TWILIO_ACCOUNT_SID', getenv('TWILIO_ACCOUNT_SID') ?: 'YOUR_TWILIO_SID');
define('TWILIO_AUTH_TOKEN', getenv('TWILIO_AUTH_TOKEN') ?: 'YOUR_TWILIO_TOKEN');
define('TWILIO_PHONE_NUMBER', getenv('TWILIO_PHONE_NUMBER') ?: '+1234567890'); // Your Twilio number
define('TWILIO_WHATSAPP_NUMBER', getenv('TWILIO_WHATSAPP_NUMBER') ?: 'whatsapp:+1234567890'); // WhatsApp-enabled number
define('SMS_SERVICE', 'twilio'); // Options: 'twilio', 'africastalking', 'clicksend'

// Alternative: Africa's Talking (Popular in Africa)
// Get from: https://africastalking.com/
define('AT_USERNAME', getenv('AT_USERNAME') ?: 'sandbox');
define('AT_API_KEY', getenv('AT_API_KEY') ?: 'YOUR_AT_API_KEY');
define('AT_SHORTCODE', getenv('AT_SHORTCODE') ?: 'YOUR_AT_SHORTCODE');

// Alternative: ClickSend (Global SMS)
// Get from: https://www.clicksend.com/
define('CLICKSEND_USERNAME', getenv('CLICKSEND_USERNAME') ?: 'YOUR_CLICKSEND_USERNAME');
define('CLICKSEND_API_KEY', getenv('CLICKSEND_API_KEY') ?: 'YOUR_CLICKSEND_KEY');

/**
 * Create OTP table if it doesn't exist
 */
function createOtpTableIfNotExists() {
    $db = Database::getInstance()->getConnection();
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS otp_codes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            phone TEXT NOT NULL,
            otp_code TEXT NOT NULL,
            purpose TEXT NOT NULL DEFAULT 'registration',
            attempts INTEGER DEFAULT 0,
            is_used INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL
        )
    ");
    
    $db->exec("CREATE INDEX IF NOT EXISTS idx_otp_phone ON otp_codes(phone)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_otp_code ON otp_codes(otp_code)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_otp_expires ON otp_codes(expires_at)");
}

/**
 * Generate a random OTP code
 */
function generateOtpCode() {
    $otp = '';
    for ($i = 0; $i < OTP_LENGTH; $i++) {
        $otp .= random_int(0, 9);
    }
    return $otp;
}

/**
 * Create and store a new OTP for a phone number
 */
function createOtp($phone, $purpose = 'registration') {
    $db = Database::getInstance()->getConnection();
    
    // Invalidate any existing OTPs for this phone
    $stmt = $db->prepare("UPDATE otp_codes SET is_used = 1 WHERE phone = ? AND purpose = ? AND is_used = 0");
    $stmt->execute([$phone, $purpose]);
    
    // Generate new OTP
    $otpCode = generateOtpCode();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    
    // Store OTP
    $stmt = $db->prepare("
        INSERT INTO otp_codes (phone, otp_code, purpose, expires_at)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$phone, $otpCode, $purpose, $expiresAt]);
    
    return $otpCode;
}

/**
 * Send OTP via SMS or WhatsApp
 * Supports multiple services: Twilio, Africa's Talking, ClickSend
 */
function sendOtpSms($phone, $otpCode, $purpose = 'registration') {
    $message = "StudySmart: Your verification code is: {$otpCode}. Valid for " . OTP_EXPIRY_MINUTES . " minutes. Do not share this code.";
    
    // Log OTP for development/testing fallback
    error_log("SMS to {$phone}: {$message}");
    if (!isset($_SESSION['dev_otp_codes'])) {
        $_SESSION['dev_otp_codes'] = [];
    }
    $_SESSION['dev_otp_codes'][$phone] = $otpCode;
    
    // Send via configured service
    switch (SMS_SERVICE) {
        case 'twilio':
            return sendViaTwilio($phone, $message);
        case 'africastalking':
            return sendViaAfricasTalking($phone, $message);
        case 'clicksend':
            return sendViaClickSend($phone, $message);
        default:
            // No service configured - just log for development
            error_log("No SMS service configured. OTP: {$otpCode}");
            return true;
    }
}

/**
 * Send SMS/WhatsApp via Twilio
 * Supports both SMS and WhatsApp
 */
function sendViaTwilio($phone, $message, $useWhatsApp = false) {
    // Check if Twilio is configured
    if (TWILIO_ACCOUNT_SID === 'YOUR_TWILIO_SID' || TWILIO_AUTH_TOKEN === 'YOUR_TWILIO_TOKEN') {
        error_log("Twilio not configured. OTP would be: " . substr($message, strpos($message, ': ') + 2));
        return true;
    }
    
    try {
        // Load Twilio SDK (if installed via composer)
        if (class_exists('Twilio\Rest\Client')) {
            $sid = TWILIO_ACCOUNT_SID;
            $token = TWILIO_AUTH_TOKEN;
            $client = new Twilio\Rest\Client($sid, $token);
            
            if ($useWhatsApp) {
                // Send via WhatsApp
                $message = $client->messages->create(
                    'whatsapp:' . $phone, // Format: +27123456789
                    [
                        'from' => TWILIO_WHATSAPP_NUMBER,
                        'body' => $message
                    ]
                );
                error_log("WhatsApp sent to {$phone}, SID: {$message->sid}");
            } else {
                // Send via SMS
                $message = $client->messages->create(
                    $phone, // Format: +27123456789
                    [
                        'from' => TWILIO_PHONE_NUMBER,
                        'body' => $message
                    ]
                );
                error_log("SMS sent to {$phone}, SID: {$message->sid}");
            }
            
            return true;
        } else {
            // Twilio SDK not installed - use cURL fallback
            return sendViaTwilioCurl($phone, $message, $useWhatsApp);
        }
    } catch (Exception $e) {
        error_log("Twilio error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send SMS via Twilio using cURL (no SDK required)
 */
function sendViaTwilioCurl($phone, $message, $useWhatsApp = false) {
    $sid = TWILIO_ACCOUNT_SID;
    $token = TWILIO_AUTH_TOKEN;
    $fromNumber = $useWhatsApp ? TWILIO_WHATSAPP_NUMBER : TWILIO_PHONE_NUMBER;
    $toNumber = $useWhatsApp ? 'whatsapp:' . $phone : $phone;
    
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
    
    $data = [
        'From' => $fromNumber,
        'To' => $toNumber,
        'Body' => $message
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$sid}:{$token}");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        error_log("Twilio SMS/WhatsApp sent successfully");
        return true;
    } else {
        error_log("Twilio error (HTTP {$httpCode}): {$error} - " . print_r($response, true));
        return false;
    }
}

/**
 * Send SMS via Africa's Talking
 * Popular and affordable in Africa
 */
function sendViaAfricasTalking($phone, $message) {
    if (AT_API_KEY === 'YOUR_AT_API_KEY') {
        error_log("Africa's Talking not configured. OTP would be: " . substr($message, strpos($message, ': ') + 2));
        return true;
    }
    
    try {
        $url = 'https://api.africastalking.com/version1/messaging';
        
        $data = [
            'username' => AT_USERNAME,
            'to' => $phone,
            'message' => $message,
            'from' => AT_SHORTCODE
        ];
        
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'ApiKey: ' . AT_API_KEY
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201 || $httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['SMSMessageData']['Recipients'][0]['statusCode']) && 
                $result['SMSMessageData']['Recipients'][0]['statusCode'] === 101) {
                error_log("Africa's Talking SMS sent successfully");
                return true;
            }
        }
        
        error_log("Africa's Talking error: " . $response);
        return false;
        
    } catch (Exception $e) {
        error_log("Africa's Talking error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send SMS via ClickSend
 * Global SMS service with good deliverability
 */
function sendViaClickSend($phone, $message) {
    if (CLICKSEND_API_KEY === 'YOUR_CLICKSEND_KEY') {
        error_log("ClickSend not configured. OTP would be: " . substr($message, strpos($message, ': ') + 2));
        return true;
    }
    
    try {
        $url = 'https://rest.clicksend.com/v3/sms/send';
        
        $data = [
            'messages' => [
                [
                    'source' => 'php',
                    'from' => 'StudySmart',
                    'to' => $phone,
                    'body' => $message
                ]
            ]
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(CLICKSEND_USERNAME . ':' . CLICKSEND_API_KEY)
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['response_code']) && $result['response_code'] === 'SUCCESS') {
            error_log("ClickSend SMS sent successfully");
            return true;
        }
        
        error_log("ClickSend error: " . $response);
        return false;
        
    } catch (Exception $e) {
        error_log("ClickSend error: " . $e->getMessage());
        return false;
    }
}

/**
 * Verify OTP code
 * Returns: ['success' => bool, 'message' => string]
 */
function verifyOtp($phone, $otpCode, $purpose = 'registration') {
    $db = Database::getInstance()->getConnection();
    
    // Get OTP from database
    $stmt = $db->prepare("
        SELECT * FROM otp_codes 
        WHERE phone = ? AND otp_code = ? AND purpose = ? AND is_used = 0
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$phone, $otpCode, $purpose]);
    $otp = $stmt->fetch();
    
    if (!$otp) {
        // Check if OTP exists but is used
        $stmt = $db->prepare("
            SELECT * FROM otp_codes 
            WHERE phone = ? AND otp_code = ? AND purpose = ?
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$phone, $otpCode, $purpose]);
        $usedOtp = $stmt->fetch();
        
        if ($usedOtp && $usedOtp['is_used']) {
            return ['success' => false, 'message' => 'This OTP has already been used. Please request a new one.'];
        }
        
        return ['success' => false, 'message' => 'Invalid OTP code.'];
    }
    
    // Check if OTP is expired
    if (strtotime($otp['expires_at']) < time()) {
        return ['success' => false, 'message' => 'OTP has expired. Please request a new one.'];
    }
    
    // Check max attempts
    if ($otp['attempts'] >= OTP_MAX_ATTEMPTS) {
        return ['success' => false, 'message' => 'Maximum attempts reached. Please request a new OTP.'];
    }
    
    // Mark OTP as used
    $stmt = $db->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
    $stmt->execute([$otp['id']]);
    
    return ['success' => true, 'message' => 'OTP verified successfully.'];
}

/**
 * Increment OTP attempt counter
 */
function incrementOtpAttempts($phone, $otpCode, $purpose = 'registration') {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        UPDATE otp_codes 
        SET attempts = attempts + 1 
        WHERE phone = ? AND otp_code = ? AND purpose = ? AND is_used = 0
    ");
    $stmt->execute([$phone, $otpCode, $purpose]);
}

/**
 * Check if OTP can be resent (cooldown period)
 */
function canResendOtp($phone, $purpose = 'registration') {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT * FROM otp_codes 
        WHERE phone = ? AND purpose = ? AND is_used = 0
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$phone, $purpose]);
    $otp = $stmt->fetch();
    
    if (!$otp) {
        return ['can_resend' => true, 'wait_time' => 0];
    }
    
    $createdAt = strtotime($otp['created_at']);
    $now = time();
    $elapsed = $now - $createdAt;
    
    if ($elapsed < OTP_RESEND_COOLDOWN_SECONDS) {
        $waitTime = OTP_RESEND_COOLDOWN_SECONDS - $elapsed;
        return ['can_resend' => false, 'wait_time' => $waitTime];
    }
    
    return ['can_resend' => true, 'wait_time' => 0];
}

/**
 * Clean up expired OTPs
 */
function cleanupExpiredOtps() {
    $db = Database::getInstance()->getConnection();
    
    $db->exec("DELETE FROM otp_codes WHERE expires_at < datetime('now') OR created_at < datetime('now', '-1 day')");
}

// Initialize OTP table on load
createOtpTableIfNotExists();

// Periodic cleanup (1% chance on each request)
if (random_int(1, 100) <= 1) {
    cleanupExpiredOtps();
}
