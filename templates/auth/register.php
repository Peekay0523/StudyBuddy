<?php
$pageTitle = 'Register - StudySmart';
$currentPage = 'register';
$step = $step ?? '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .grid-wrapper {
            min-height: 100vh;
            width: 100%;
            background: linear-gradient(
                to bottom,
                #fff 0%,
                #fff 40%,
                rgba(255, 255, 255, 0) 100%
            ),
            linear-gradient(to right, #0ed2da, #5f29c7);
            position: relative;
            overflow: hidden;
        }
        .grid-wrapper::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: linear-gradient(90deg, #ccc 1px, transparent 1px);
            background-size: 50px 100%;
            pointer-events: none;
            mask-image: linear-gradient(
                to bottom,
                rgba(0, 0, 0, 1) 0%,
                rgba(0, 0, 0, 0) 70%
            );
            -webkit-mask-image: linear-gradient(
                to bottom,
                rgba(0, 0, 0, 1) 0%,
                rgba(0, 0, 0, 0) 70%
            );
        }
        .auth-container {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px 15px;
            background: transparent !important;
        }
        .auth-box {
            background: white;
            padding: 30px 25px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }
        .auth-box h2 {
            text-align: center;
            color: #1f2937;
            margin: 0 0 10px;
            font-size: 24px;
        }
        .auth-subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 25px;
        }
        .help-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 5px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }
        .step-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e5e7eb;
            transition: all 0.3s;
        }
        .step-dot.active {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            transform: scale(1.2);
        }
        .step-dot.completed {
            background: #16a34a;
        }
        .otp-input {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        .otp-input input {
            width: 45px;
            height: 55px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            outline: none;
            transition: all 0.2s;
        }
        .otp-input input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
        }
        .resend-container {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .resend-btn {
            background: none;
            border: none;
            color: #16a34a;
            cursor: pointer;
            font-size: 14px;
            padding: 5px 10px;
            text-decoration: underline;
        }
        .resend-btn:disabled {
            color: #9ca3af;
            cursor: not-allowed;
            text-decoration: none;
        }
        .resend-timer {
            color: #6b7280;
            font-size: 13px;
            margin-top: 8px;
        }
        .phone-display {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .phone-display i {
            color: #16a34a;
            font-size: 18px;
        }
        .phone-display span {
            color: #166534;
            font-size: 14px;
            font-weight: 500;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .alert-info {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .phone-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }
        .phone-success {
            border-color: #16a34a !important;
            background-color: #f0fdf4 !important;
        }
        .validation-message {
            font-size: 12px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .validation-message.error {
            color: #dc2626;
        }
        .validation-message.success {
            color: #16a34a;
        }
        .loading-spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #e5e7eb;
            border-top-color: #6b7280;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="grid-wrapper">
<div class="auth-container">
    <div class="auth-box">
        <?php if ($step === '1'): ?>
            <!-- Step 1: Registration Form -->
            <h2><i class="fas fa-user-plus" style="color: #16a34a;"></i> Create Account</h2>
            <p class="auth-subtitle">Join StudySmart today - it's free!</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/register" id="registerForm">
                <!-- Account Type Selector -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #1f2937; font-size: 14px;">I am registering as a:</label>
                    <div style="display: flex; gap: 12px;">
                        <label id="reg-student-card" style="flex: 1; text-align: center; padding: 14px 10px; border: 2px solid #3b82f6; border-radius: 12px; cursor: pointer; background: #eff6ff; transition: all 0.3s;">
                            <input type="radio" name="register_role" value="student" checked style="display: none;" onchange="updateRegisterRole()">
                            <i class="fas fa-user-graduate" style="font-size: 22px; color: #3b82f6; margin-bottom: 6px; display: block;"></i>
                            <div style="font-weight: 600; color: #1f2937; font-size: 14px;">Student</div>
                        </label>
                        <label id="reg-tutor-card" style="flex: 1; text-align: center; padding: 14px 10px; border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; background: white; transition: all 0.3s;">
                            <input type="radio" name="register_role" value="tutor" style="display: none;" onchange="updateRegisterRole()">
                            <i class="fas fa-chalkboard-teacher" style="font-size: 22px; color: #6b7280; margin-bottom: 6px; display: block;"></i>
                            <div style="font-weight: 600; color: #4b5563; font-size: 14px;">Tutor</div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email <span id="email-optional-label" style="color: #6b7280; font-weight: normal; font-size: 13px;">(Optional)</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" >
                    <div class="help-text" id="email-help-text">Used for account recovery and notifications</div>
                    
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="+27 12 345 6789" required>
                    <div class="help-text" id="phone-help-text">We'll send a verification code to this number</div>
                    <div id="phone-validation-message" class="validation-message" style="display: none;"></div>
                </div>

                <!-- OTP Delivery Method Selector -->
                <div class="form-group">
                    <label>Receive verification code via: <span style="color: #dc2626;">*</span></label>
                    <div style="display: flex; gap: 10px; margin-top: 6px;">
                        <label id="otp-sms-card" style="flex: 1; text-align: center; padding: 12px 8px; border: 2px solid #3b82f6; border-radius: 10px; cursor: pointer; background: #eff6ff; transition: all 0.2s;">
                            <input type="radio" name="otp_method" value="sms" checked style="display: none;" onchange="updateOtpMethod()">
                            <i class="fas fa-comment-sms" style="font-size: 20px; color: #3b82f6; margin-bottom: 4px; display: block;"></i>
                            <div style="font-weight: 600; color: #1f2937; font-size: 13px;">SMS</div>
                            <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">To your phone</div>
                        </label>
                        <label id="otp-email-card" style="flex: 1; text-align: center; padding: 12px 8px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; background: white; transition: all 0.2s;">
                            <input type="radio" name="otp_method" value="email" style="display: none;" onchange="updateOtpMethod()">
                            <i class="fas fa-envelope" style="font-size: 20px; color: #6b7280; margin-bottom: 4px; display: block;"></i>
                            <div style="font-weight: 600; color: #4b5563; font-size: 13px;">Email</div>
                            <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">To your inbox</div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8" >
                    <div class="help-text">Must be at least 8 characters</div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" >
                </div>

                <!-- Tutor-specific fields (shown only when Tutor is selected) -->
                <div id="tutor-fields" style="display: none; margin-top: 10px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                    <h3 style="font-size: 15px; color: #1f2937; margin: 0 0 15px;"><i class="fas fa-chalkboard-teacher" style="color: #f59e0b;"></i> Tutor Profile Details</h3>
                    <div class="form-group">
                        <label for="tutor_full_name">Full Name <span style="color: #dc2626;">*</span></label>
                        <input type="text" id="tutor_full_name" name="tutor_full_name" placeholder="e.g., John Smith">
                        <div class="help-text">This is how students will see your name.</div>
                    </div>
                    <div class="form-group">
                        <label for="tutor_subjects">Subjects You Teach <span style="color: #dc2626;">*</span></label>
                        <input type="text" id="tutor_subjects" name="tutor_subjects" placeholder="e.g., Mathematics, Physical Science, English">
                        <div class="help-text">Comma-separated list of subjects.</div>
                    </div>
                    <div class="form-group">
                        <label for="tutor_bio">Bio <span style="color: #6b7280; font-weight: normal; font-size: 13px;">(Optional)</span></label>
                        <textarea id="tutor_bio" name="tutor_bio" rows="3" placeholder="Tell students about your qualifications and teaching style..." style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; resize: vertical; font-family: inherit;"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tutor_company_name">Tutoring Company Name <span style="color: #6b7280; font-weight: normal; font-size: 13px;">(Optional)</span></label>
                        <input type="text" id="tutor_company_name" name="tutor_company_name" placeholder="e.g., Bright Minds Tutoring">
                        <div class="help-text">The name of your tutoring company or practice.</div>
                    </div>
                    <div class="form-group">
                        <label>How do you charge? <span style="color: #dc2626;">*</span></label>
                        <div style="display: flex; gap: 10px; margin-top: 4px;">
                            <label style="flex: 1; text-align: center; padding: 10px; border: 2px solid #3b82f6; border-radius: 10px; cursor: pointer; background: #eff6ff; transition: all 0.2s;" id="billing-hourly-card-reg">
                                <input type="radio" name="tutor_billing_type" value="hourly" checked style="display: none;" onchange="updateBillingType('reg')">
                                <i class="fas fa-clock" style="font-size: 18px; color: #3b82f6; margin-bottom: 4px; display: block;"></i>
                                <div style="font-weight: 600; color: #1f2937; font-size: 13px;">Hourly</div>
                            </label>
                            <label style="flex: 1; text-align: center; padding: 10px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; background: white; transition: all 0.2s;" id="billing-monthly-card-reg">
                                <input type="radio" name="tutor_billing_type" value="monthly" style="display: none;" onchange="updateBillingType('reg')">
                                <i class="fas fa-calendar-alt" style="font-size: 18px; color: #6b7280; margin-bottom: 4px; display: block;"></i>
                                <div style="font-weight: 600; color: #4b5563; font-size: 13px;">Monthly</div>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tutor_rate"><span id="rate-label-reg">Hourly Rate</span> <span style="color: #6b7280; font-weight: normal; font-size: 13px;">(Optional)</span></label>
                        <input type="text" id="tutor_rate" name="tutor_rate" placeholder="e.g., R150/hour">
                        <div class="help-text" id="rate-hint-reg">How much you charge per hour.</div>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #16a34a, #22c55e); width: 100%; padding: 14px; font-size: 16px; margin-top: 10px;">
                    <i class="fas fa-paper-plane"></i> Send Verification Code
                </button>
            </form>

        <?php elseif ($step === '2'): ?>
            <!-- Step 2: OTP Verification -->
            <?php
            $pendingReg = $_SESSION['pending_registration'] ?? null;
            if (!$pendingReg):
                header('Location: /register');
                exit;
            endif;
            ?>

            <?php
            $otpMethod = $pendingReg['otp_method'] ?? 'sms';
            $otpIdentifier = $pendingReg['otp_identifier'] ?? $pendingReg['phone'];
            $isEmail = ($otpMethod === 'email');
            $methodLabel = $isEmail ? 'Email' : 'SMS';
            $methodIcon = $isEmail ? 'fa-envelope' : 'fa-comment-sms';
            $methodColor = $isEmail ? '#7c3aed' : '#3b82f6';
            $badgeBg = $isEmail ? '#ede9fe' : '#dbeafe';
            $badgeColor = $isEmail ? '#5b21b6' : '#1e40af';
            $verifyTitle = $isEmail ? 'Verify Your Email' : 'Verify Your Phone';
            $verifySubtitle = $isEmail
                ? 'Enter the 6-digit code sent to your email'
                : 'Enter the 6-digit code sent to your phone via SMS';
            ?>
            <h2><i class="fas fa-shield-halved" style="color: #16a34a;"></i> <?php echo $verifyTitle; ?></h2>
            <p class="auth-subtitle"><?php echo $verifySubtitle; ?></p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="phone-display">
                <i class="fas <?php echo $methodIcon; ?>" style="color: <?php echo $methodColor; ?>;"></i>
                <span><?php echo htmlspecialchars($otpIdentifier); ?></span>
                <span style="margin-left: 10px; padding: 4px 8px; background: <?php echo $badgeBg; ?>; color: <?php echo $badgeColor; ?>; border-radius: 4px; font-size: 11px; font-weight: 600;">
                    <?php echo $methodLabel; ?>
                </span>
                <a href="/register" style="margin-left: auto; color: #16a34a; text-decoration: none; font-size: 12px;">
                    <i class="fas fa-edit"></i> Change
                </a>
            </div>

            <form method="post" action="/register?step=2" id="otpForm">
                <div class="otp-input">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="0" pattern="[0-9]*" inputmode="numeric">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="1" pattern="[0-9]*" inputmode="numeric">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="2" pattern="[0-9]*" inputmode="numeric">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="3" pattern="[0-9]*" inputmode="numeric">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="4" pattern="[0-9]*" inputmode="numeric">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="5" pattern="[0-9]*" inputmode="numeric">
                </div>
                <input type="hidden" name="otp_code" id="otp_code" value="">

                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #16a34a, #22c55e); width: 100%; padding: 14px; font-size: 16px; margin-top: 10px;">
                    <i class="fas fa-check"></i> Verify & Create Account
                </button>
            </form>

            <div class="resend-container">
                <p style="color: #6b7280; font-size: 14px; margin: 0;">Didn't receive the code?</p>
                <button type="button" id="resendBtn" class="resend-btn" onclick="resendOtp()">
                    <i class="fas fa-redo"></i> Resend via <?php echo $methodLabel; ?>
                </button>
                <div id="resendTimer" class="resend-timer" style="display: none;">
                    <i class="fas fa-clock"></i> Resend available in <span id="timerCount">60</span>s
                </div>
            </div>

            <?php
            // Development mode: show the OTP code on screen if mail()/SMS is unavailable
            $devOtp = $_SESSION['dev_otp_codes'][$otpIdentifier] ?? null;
            $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false;
            ?>
            <?php if ($devOtp || $isLocal): ?>
                <div class="alert alert-info" style="margin-top: 20px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 16px 20px;">
                    <i class="fas fa-info-circle" style="color: #2563eb; font-size: 18px;"></i>
                    <div style="margin-top: 8px;">
                        <strong style="color: #1e40af;">Development Mode</strong>
                        <p style="color: #1e40af; margin: 6px 0 0; font-size: 13.5px; line-height: 1.5;">
                            <?php if ($devOtp): ?>
                                Your verification code is: <span style="font-size: 22px; font-weight: 800; color: #2563eb; letter-spacing: 4px; background: #dbeafe; padding: 4px 14px; border-radius: 8px; display: inline-block; margin-top: 4px;"><?php echo htmlspecialchars($devOtp); ?></span>
                                <br><span style="font-size: 12px; color: #6b7280;">(Email/SMS delivery is not configured on this server. The code is shown here for testing.)</span>
                            <?php else: ?>
                                Check server error log for OTP code, or check your <?php echo $isEmail ? 'email' : 'phone'; ?>.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <div class="auth-link" style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <p style="color: #6b7280; font-size: 14px; margin: 0;">
                Already have an account? <a href="/login" style="color: #16a34a; text-decoration: none; font-weight: 500;">Login here</a>
            </p>
        </div>
    </div>
</div>
</div>

<?php if ($step === '2'): ?>
<script>
// OTP Input handling
const otpInputs = document.querySelectorAll('.otp-digit');
const otpCodeInput = document.getElementById('otp_code');

otpInputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        const value = e.target.value;
        
        // Only allow numbers
        if (!/^\d*$/.test(value)) {
            e.target.value = value.replace(/[^0-9]/g, '');
            return;
        }
        
        // Move to next input if value entered
        if (value && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
        }
        
        // Update hidden input with full OTP
        updateOtpCode();
    });

    input.addEventListener('keydown', (e) => {
        // Move to previous input on backspace if empty
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
            otpInputs[index - 1].focus();
        }
    });

    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasteData = e.clipboardData.getData('text').slice(0, 6);
        
        if (/^\d+$/.test(pasteData)) {
            const digits = pasteData.split('');
            otpInputs.forEach((inp, i) => {
                if (digits[i]) {
                    inp.value = digits[i];
                }
            });
            otpInputs[Math.min(digits.length, otpInputs.length - 1)].focus();
            updateOtpCode();
        }
    });
});

function updateOtpCode() {
    let code = '';
    otpInputs.forEach(input => {
        code += input.value;
    });
    otpCodeInput.value = code;
}

// Auto-submit when all digits entered
otpInputs.forEach((input, index) => {
    input.addEventListener('input', () => {
        let allFilled = true;
        otpInputs.forEach(inp => {
            if (!inp.value) allFilled = false;
        });
        
        if (allFilled) {
            // Auto-submit after short delay
            setTimeout(() => {
                document.getElementById('otpForm').submit();
            }, 300);
        }
    });
});

// Focus first input on load
otpInputs[0].focus();

// Resend OTP function
let resendCooldown = 60;
let canResend = true;

async function resendOtp() {
    if (!canResend) return;

    const btn = document.getElementById('resendBtn');
    const timer = document.getElementById('resendTimer');
    const timerCount = document.getElementById('timerCount');

    btn.disabled = true;
    timer.style.display = 'block';

    try {
        const response = await fetch('/register/resend-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message || 'OTP resent successfully! Please check your phone.', 'success');
            startCooldown();
        } else {
            showNotification(data.message || 'Failed to resend OTP', 'error');
            if (data.wait_time) {
                resendCooldown = data.wait_time;
                startCooldown();
            } else {
                btn.disabled = false;
                timer.style.display = 'none';
            }
        }
    } catch (error) {
        showNotification('Failed to resend OTP. Please try again.', 'error');
        btn.disabled = false;
        timer.style.display = 'none';
    }
}

function startCooldown() {
    const btn = document.getElementById('resendBtn');
    const timer = document.getElementById('resendTimer');
    const timerCount = document.getElementById('timerCount');

    canResend = false;
    resendCooldown = 60;
    timerCount.textContent = resendCooldown;

    const interval = setInterval(() => {
        resendCooldown--;
        timerCount.textContent = resendCooldown;

        if (resendCooldown <= 0) {
            clearInterval(interval);
            canResend = true;
            btn.disabled = false;
            timer.style.display = 'none';
        }
    }, 1000);
}

function showNotification(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;

    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.replaceWith(alert);
    } else {
        document.querySelector('.auth-box h2').insertAdjacentElement('afterend', alert);
    }

    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>
<?php endif; ?>

<script>
// Toggle tutor fields visibility based on selected role
function updateRegisterRole() {
    const selected = document.querySelector('input[name="register_role"]:checked').value;
    const studentCard = document.getElementById('reg-student-card');
    const tutorCard = document.getElementById('reg-tutor-card');
    const tutorFields = document.getElementById('tutor-fields');

    if (selected === 'tutor') {
        studentCard.style.borderColor = '#e5e7eb';
        studentCard.style.background = 'white';
        studentCard.querySelector('i').style.color = '#6b7280';
        studentCard.querySelector('div').style.color = '#4b5563';

        tutorCard.style.borderColor = '#f59e0b';
        tutorCard.style.background = '#fffbeb';
        tutorCard.querySelector('i').style.color = '#f59e0b';
        tutorCard.querySelector('div').style.color = '#1f2937';

        tutorFields.style.display = 'block';
    } else {
        studentCard.style.borderColor = '#3b82f6';
        studentCard.style.background = '#eff6ff';
        studentCard.querySelector('i').style.color = '#3b82f6';
        studentCard.querySelector('div').style.color = '#1f2937';

        tutorCard.style.borderColor = '#e5e7eb';
        tutorCard.style.background = 'white';
        tutorCard.querySelector('i').style.color = '#6b7280';
        tutorCard.querySelector('div').style.color = '#4b5563';

        tutorFields.style.display = 'none';
    }
}

// Toggle OTP delivery method (SMS/Email) visual state + field hints
function updateOtpMethod() {
    const selected = document.querySelector('input[name="otp_method"]:checked').value;
    const smsCard = document.getElementById('otp-sms-card');
    const emailCard = document.getElementById('otp-email-card');
    const emailOptionalLabel = document.getElementById('email-optional-label');
    const emailHelpText = document.getElementById('email-help-text');
    const phoneHelpText = document.getElementById('phone-help-text');
    const emailInput = document.getElementById('email');

    if (selected === 'email') {
        smsCard.style.borderColor = '#e5e7eb';
        smsCard.style.background = 'white';
        smsCard.querySelector('i').style.color = '#6b7280';
        smsCard.querySelector('div').style.color = '#4b5563';

        emailCard.style.borderColor = '#3b82f6';
        emailCard.style.background = '#eff6ff';
        emailCard.querySelector('i').style.color = '#3b82f6';
        emailCard.querySelector('div').style.color = '#1f2937';

        if (emailOptionalLabel) {
            emailOptionalLabel.innerHTML = '<span style="color: #dc2626;">*</span>';
            emailOptionalLabel.style.fontWeight = '600';
        }
        if (emailHelpText) emailHelpText.innerHTML = 'Your verification code will be sent to this email';
        if (emailInput) emailInput.required = true;
        if (phoneHelpText) phoneHelpText.textContent = 'Required for your account profile';
    } else {
        emailCard.style.borderColor = '#e5e7eb';
        emailCard.style.background = 'white';
        emailCard.querySelector('i').style.color = '#6b7280';
        emailCard.querySelector('div').style.color = '#4b5563';

        smsCard.style.borderColor = '#3b82f6';
        smsCard.style.background = '#eff6ff';
        smsCard.querySelector('i').style.color = '#3b82f6';
        smsCard.querySelector('div').style.color = '#1f2937';

        if (emailOptionalLabel) {
            emailOptionalLabel.textContent = '(Optional)';
            emailOptionalLabel.style.fontWeight = 'normal';
        }
        if (emailHelpText) emailHelpText.textContent = 'Used for account recovery and notifications';
        if (emailInput) emailInput.required = false;
        if (phoneHelpText) phoneHelpText.textContent = "We'll send a verification code to this number";
    }
}

// Toggle billing type (hourly/monthly) visual state + label text
function updateBillingType(prefix) {
    var selected = document.querySelector('input[name="tutor_billing_type"]:checked');
    if (!selected) selected = document.querySelector('input[name="billing_type"]:checked');
    if (!selected) return;
    var isHourly = selected.value === 'hourly';

    var hourlyCard = document.getElementById('billing-hourly-card-' + prefix);
    var monthlyCard = document.getElementById('billing-monthly-card-' + prefix);
    var rateLabel = document.getElementById('rate-label-' + prefix);
    var rateHint = document.getElementById('rate-hint-' + prefix);
    var rateInput = document.getElementById(prefix === 'reg' ? 'tutor_rate' : 'modal_rate');

    if (hourlyCard && monthlyCard) {
        if (isHourly) {
            hourlyCard.style.borderColor = '#3b82f6'; hourlyCard.style.background = '#eff6ff';
            hourlyCard.querySelector('i').style.color = '#3b82f6'; hourlyCard.querySelector('div').style.color = '#1f2937';
            monthlyCard.style.borderColor = '#e5e7eb'; monthlyCard.style.background = 'white';
            monthlyCard.querySelector('i').style.color = '#6b7280'; monthlyCard.querySelector('div').style.color = '#4b5563';
        } else {
            monthlyCard.style.borderColor = '#3b82f6'; monthlyCard.style.background = '#eff6ff';
            monthlyCard.querySelector('i').style.color = '#3b82f6'; monthlyCard.querySelector('div').style.color = '#1f2937';
            hourlyCard.style.borderColor = '#e5e7eb'; hourlyCard.style.background = 'white';
            hourlyCard.querySelector('i').style.color = '#6b7280'; hourlyCard.querySelector('div').style.color = '#4b5563';
        }
    }
    if (rateLabel) rateLabel.textContent = isHourly ? 'Hourly Rate' : 'Monthly Rate';
    if (rateHint) rateHint.textContent = isHourly ? 'How much you charge per hour.' : 'How much you charge per month.';
    if (rateInput) rateInput.placeholder = isHourly ? 'e.g., R150/hour' : 'e.g., R2000/month';
}
</script>

<script>
// Real-time phone number validation
const phoneInput = document.getElementById('phone');
const phoneValidationMessage = document.getElementById('phone-validation-message');
let checkPhoneTimeout;
let isCheckingPhone = false;

if (phoneInput) {
    phoneInput.addEventListener('blur', function() {
        const phone = this.value.trim();
        
        if (phone) {
            checkPhoneAvailability(phone);
        } else {
            hidePhoneValidation();
        }
    });

    phoneInput.addEventListener('input', function() {
        // Clear previous timeout
        clearTimeout(checkPhoneTimeout);
        
        // Hide validation message while typing
        hidePhoneValidation();
        
        // Set new timeout to check after user stops typing
        checkPhoneTimeout = setTimeout(() => {
            const phone = this.value.trim();
            if (phone) {
                checkPhoneAvailability(phone);
            }
        }, 1000); // Check 1 second after user stops typing
    });
}

async function checkPhoneAvailability(phone) {
    if (isCheckingPhone) return;
    
    isCheckingPhone = true;
    showPhoneLoading();
    
    try {
        const formData = new FormData();
        formData.append('phone', phone);
        
        const response = await fetch('/register/check-phone', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.exists) {
            showPhoneError(data.message);
        } else {
            showPhoneSuccess();
        }
    } catch (error) {
        console.error('Phone check failed:', error);
        hidePhoneValidation();
    } finally {
        isCheckingPhone = false;
    }
}

function showPhoneLoading() {
    phoneValidationMessage.style.display = 'flex';
    phoneValidationMessage.className = 'validation-message';
    phoneValidationMessage.innerHTML = '<span class="loading-spinner"></span> Checking phone number...';
    phoneInput.classList.remove('phone-error', 'phone-success');
}

function showPhoneError(message) {
    phoneValidationMessage.style.display = 'flex';
    phoneValidationMessage.className = 'validation-message error';
    phoneValidationMessage.innerHTML = '<i class="fas fa-times-circle"></i> ' + message;
    phoneInput.classList.add('phone-error');
    phoneInput.classList.remove('phone-success');
}

function showPhoneSuccess() {
    phoneValidationMessage.style.display = 'flex';
    phoneValidationMessage.className = 'validation-message success';
    phoneValidationMessage.innerHTML = '<i class="fas fa-check-circle"></i> Phone number is available';
    phoneInput.classList.add('phone-success');
    phoneInput.classList.remove('phone-error');
}

function hidePhoneValidation() {
    phoneValidationMessage.style.display = 'none';
    phoneInput.classList.remove('phone-error', 'phone-success');
}

// Prevent form submission if phone number exists
document.getElementById('registerForm')?.addEventListener('submit', function(e) {
    if (phoneInput.classList.contains('phone-error')) {
        e.preventDefault();
        alert('Please use a different phone number. This phone number is already registered.');
        phoneInput.focus();
    }
});
</script>

</body>
</html>
