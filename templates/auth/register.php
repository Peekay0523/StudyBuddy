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
        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f5f8ff;
            padding: 20px 15px;
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
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email <span style="color: #6b7280; font-weight: normal; font-size: 13px;">(Optional)</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" >
                    
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="+27 12 345 6789" required>
                    <div class="help-text">We'll send a verification code to this number</div>
                    <div id="phone-validation-message" class="validation-message" style="display: none;"></div>
                </div>

                <!-- OTP method is now SMS by default, no selection shown to user -->
                <input type="hidden" name="otp_method" value="sms">

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8" >
                    <div class="help-text">Must be at least 8 characters</div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" >
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

            <h2><i class="fas fa-shield-halved" style="color: #16a34a;"></i> Verify Your Phone</h2>
            <p class="auth-subtitle">
                Enter the 6-digit code sent to your phone via SMS
            </p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="phone-display">
                <i class="fas fa-comment-sms" style="color: #3b82f6;"></i>
                <span><?php echo htmlspecialchars($pendingReg['phone']); ?></span>
                <span style="margin-left: 10px; padding: 4px 8px; background: #dbeafe; color: #1e40af; border-radius: 4px; font-size: 11px; font-weight: 600;">
                    SMS
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
                    <i class="fas fa-redo"></i> Resend via SMS
                </button>
                <div id="resendTimer" class="resend-timer" style="display: none;">
                    <i class="fas fa-clock"></i> Resend available in <span id="timerCount">60</span>s
                </div>
            </div>

            <div class="alert alert-info" style="margin-top: 20px;">
                <i class="fas fa-info-circle"></i>
                <span><strong>Development Mode:</strong> Check server error log for OTP code, or check your phone.</span>
            </div>

        <?php endif; ?>

        <div class="auth-link" style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <p style="color: #6b7280; font-size: 14px; margin: 0;">
                Already have an account? <a href="/login" style="color: #16a34a; text-decoration: none; font-weight: 500;">Login here</a>
            </p>
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
            showNotification('OTP resent successfully! Please check your phone.', 'success');
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
