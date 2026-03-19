<?php
/**
 * Test SMS/WhatsApp OTP Sending
 *
 * Access: http://localhost:8000/test-sms-otp
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/otp.php';

// Only allow admin or in development
if (!DEBUG_MODE && (!isLoggedIn() || getCurrentUser()['role'] !== 'admin')) {
    die('Access denied');
}

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $testType = $_POST['test_type'] ?? 'sms';
    
    if (empty($phone)) {
        $message = 'Please enter a phone number';
        $messageType = 'error';
    } else {
        // Generate OTP
        $otpCode = createOtp($phone, 'test');
        
        // Send based on type
        if ($testType === 'whatsapp') {
            $success = sendViaTwilio($phone, "StudySmart Test: Your verification code is: {$otpCode}", true);
        } else {
            $success = sendOtpSms($phone, $otpCode, 'test');
        }
        
        if ($success) {
            $message = "✅ OTP sent successfully! Code: {$otpCode} (Check your phone AND error log)";
            $messageType = 'success';
        } else {
            $message = "❌ Failed to send. Check error log for details. Code would be: {$otpCode}";
            $messageType = 'error';
        }
    }
}

// Get current configuration
$configStatus = [];
$configStatus[] = "SMS_SERVICE: " . SMS_SERVICE;
$configStatus[] = "Twilio Configured: " . (TWILIO_ACCOUNT_SID !== 'YOUR_TWILIO_SID' ? '✅ Yes' : '❌ No');
$configStatus[] = "Africa's Talking Configured: " . (AT_API_KEY !== 'YOUR_AT_API_KEY' ? '✅ Yes' : '❌ No');
$configStatus[] = "ClickSend Configured: " . (CLICKSEND_API_KEY !== 'YOUR_CLICKSEND_KEY' ? '✅ Yes' : '❌ No');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SMS OTP - StudySmart</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .test-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .status-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .status-item {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .status-item:last-child {
            border-bottom: none;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1f2937;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-info {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .radio-group {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .radio-option:hover {
            border-color: #667eea;
            background: #f5f8ff;
        }
        .radio-option input[type="radio"]:checked + span {
            font-weight: 600;
            color: #667eea;
        }
        .radio-option:has(input:checked) {
            border-color: #667eea;
            background: #f5f8ff;
        }
    </style>
</head>
<body>

<div class="test-container">
    <h1 style="color: #1f2937; margin-bottom: 10px;">
        <i class="fas fa-flask" style="color: #667eea;"></i>
        Test SMS/WhatsApp OTP
    </h1>
    <p style="color: #6b7280; margin-bottom: 20px;">
        Send test OTP messages to verify your SMS configuration
    </p>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="status-box">
        <h3 style="margin-top: 0; color: #1f2937; font-size: 16px;">
            <i class="fas fa-cog"></i> Configuration Status
        </h3>
        <?php foreach ($configStatus as $status): ?>
            <div class="status-item"><?php echo $status; ?></div>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="">
        <div class="form-group">
            <label for="phone">
                <i class="fas fa-mobile-alt"></i> Phone Number
            </label>
            <input 
                type="tel" 
                id="phone" 
                name="phone" 
                placeholder="+27123456789" 
                required
                value="+27"
            >
            <small style="color: #6b7280; display: block; margin-top: 5px;">
                Include country code (e.g., +27 for South Africa)
            </small>
        </div>

        <div class="form-group">
            <label>
                <i class="fas fa-paper-plane"></i> Send Method
            </label>
            <div class="radio-group">
                <label class="radio-option">
                    <input type="radio" name="test_type" value="sms" checked>
                    <span><i class="fas fa-comment-sms"></i> SMS</span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="test_type" value="whatsapp">
                    <span><i class="fab fa-whatsapp"></i> WhatsApp</span>
                </label>
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">
            <i class="fas fa-paper-plane"></i> Send Test OTP
        </button>
    </form>

    <div class="status-box" style="margin-top: 30px; background: #fffbeb; border-color: #fde68a;">
        <h3 style="margin-top: 0; color: #92400e; font-size: 16px;">
            <i class="fas fa-info-circle"></i> Development Mode
        </h3>
        <p style="color: #78350f; font-size: 14px; margin: 0;">
            If no SMS service is configured, the OTP will be logged to the error log.
            Check: <code style="background: #fde68a; padding: 2px 6px; border-radius: 4px;">error_log</code> file
        </p>
        <p style="color: #78350f; font-size: 13px; margin: 10px 0 0 0;">
            <strong>Tip:</strong> During development, OTP codes are also stored in your browser session.
            Check the PHP error log to see the code.
        </p>
    </div>

    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <a href="/register" style="color: #667eea; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Registration
        </a>
    </div>
</div>

</body>
</html>
