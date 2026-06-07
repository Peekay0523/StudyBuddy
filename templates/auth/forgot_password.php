<?php
$pageTitle = 'Forgot Password - StudySmart';
$currentPage = 'forgot-password';
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .otp-input input.filled {
            border-color: #667eea;
            background: #eff6ff;
            color: #667eea;
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
            color: #667eea;
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
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .phone-display i {
            color: #667eea;
            font-size: 18px;
        }
        .phone-display span {
            color: #1e40af;
            font-size: 14px;
            font-weight: 500;
        }
        .phone-display a {
            margin-left: auto;
            color: #667eea;
            text-decoration: none;
            font-size: 12px;
            padding: 4px 8px;
            background: #dbeafe;
            border-radius: 4px;
        }
        .phone-display a:hover {
            background: #bfdbfe;
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
            <!-- Step 1: Enter Phone Number -->
            <h2><i class="fas fa-key" style="color: #667eea;"></i> Reset Password</h2>
            <p class="auth-subtitle">Enter your phone number to receive a verification code</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/forgot-password">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="+27 12 345 6789" required>
                    <div class="help-text"><i class="fas fa-info-circle"></i> We'll send a verification code to this number</div>
                </div>

                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #667eea, #764ba2); width: 100%; padding: 14px; font-size: 16px; margin-top: 10px;">
                    <i class="fas fa-paper-plane"></i> Send Verification Code
                </button>
            </form>

        <?php elseif ($step === '2'): ?>
            <!-- Step 2: Verify OTP -->
            <h2><i class="fas fa-shield-halved" style="color: #667eea;"></i> Verify Code</h2>
            <p class="auth-subtitle">Enter the 6-digit code sent to your phone</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="phone-display">
                <i class="fas fa-comment-sms"></i>
                <span><?php echo htmlspecialchars($phone); ?></span>
                <a href="/forgot-password"><i class="fas fa-edit"></i> Change</a>
            </div>

            <form method="post" action="/forgot-password?step=2" id="otpForm">
                <div class="otp-input">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="0" pattern="[0-9]*" inputmode="numeric">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="1" pattern="[0-9]*" inputmode="numeric">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="2" pattern="[0-9]*" inputmode="numeric">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="3" pattern="[0-9]*" inputmode="numeric">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="4" pattern="[0-9]*" inputmode="numeric">
                    <input type="tel" maxlength="1" class="otp-digit" data-index="5" pattern="[0-9]*" inputmode="numeric">
                </div>
                <input type="hidden" name="otp_code" id="otp_code" value="">
                <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">

                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #667eea, #764ba2); width: 100%; padding: 14px; font-size: 16px; margin-top: 10px;">
                    <i class="fas fa-check"></i> Verify Code
                </button>
            </form>

            <div class="resend-container">
                <p style="color: #6b7280; font-size: 14px; margin: 0;">Didn't receive the code?</p>
                <button type="button" id="resendBtn" class="resend-btn" onclick="resendOtp()">
                    <i class="fas fa-redo"></i> Resend Code
                </button>
                <div id="resendTimer" class="resend-timer" style="display: none;">
                    <i class="fas fa-clock"></i> Resend available in <span id="timerCount">60</span>s
                </div>
            </div>

            <div class="alert alert-info" style="margin-top: 20px;">
                <i class="fas fa-info-circle"></i>
                <span><strong>Development Mode:</strong> Check server error log for OTP code, or check your phone.</span>
            </div>

        <?php elseif ($step === '3'): ?>
            <!-- Step 3: Set New Password -->
            <h2><i class="fas fa-lock" style="color: #667eea;"></i> Set New Password</h2>
            <p class="auth-subtitle">Create a new password for your account</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/forgot-password?step=3">
                <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8" placeholder="At least 8 characters">
                    <div class="help-text"><i class="fas fa-shield-halved"></i> Must be at least 8 characters</div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password">
                </div>

                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #667eea, #764ba2); width: 100%; padding: 14px; font-size: 16px; margin-top: 10px;">
                    <i class="fas fa-check"></i> Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="auth-link" style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <p style="color: #6b7280; font-size: 14px; margin: 0;">
                Remember your password? <a href="/login" style="color: #667eea; text-decoration: none; font-weight: 500;">Login here</a>
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

        // Add/remove filled class
        if (value) {
            input.classList.add('filled');
        } else {
            input.classList.remove('filled');
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
        // Update filled class on backspace
        if (e.key === 'Backspace') {
            input.classList.remove('filled');
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
                    inp.classList.add('filled');
                } else {
                    inp.classList.remove('filled');
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

// Focus first input on load
if (otpInputs.length > 0) {
    setTimeout(() => {
        otpInputs[0].focus();
    }, 300);
}

// Resend OTP function
async function resendOtp() {
    const btn = document.getElementById('resendBtn');
    const timer = document.getElementById('resendTimer');
    const timerCount = document.getElementById('timerCount');
    const phone = document.querySelector('input[name="phone"]')?.value;

    if (!phone) {
        alert('Phone number not found. Please start over.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    timer.style.display = 'block';

    try {
        const response = await fetch('/forgot-password/resend-otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ phone: phone })
        });

        const result = await response.json();

        if (result.success) {
            let count = 60;
            timerCount.textContent = count;

            const interval = setInterval(() => {
                count--;
                timerCount.textContent = count;
                if (count <= 0) {
                    clearInterval(interval);
                    timer.style.display = 'none';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-redo"></i> Resend Code';
                }
            }, 1000);

        } else {
            alert(result.message || 'Failed to resend code');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-redo"></i> Resend Code';
        }
    } catch (error) {
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-redo"></i> Resend Code';
    }
}
</script>
<?php endif; ?>

</body>
</html>
