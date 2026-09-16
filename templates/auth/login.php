<?php
$pageTitle = 'Login - StudySmart';
$currentPage = 'login';
$extraHead = '<style>

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
        max-width: 400px;
    }
    .auth-box h2 {
        text-align: center;
        color: #1f2937;
        margin: 0 0 30px;
        font-size: 24px;
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
    .btn-primary {
        border: none;
        outline: none;
        background-color: #6c5ce7;
        padding: 10px 20px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        border-radius: 5px;
        transition: all ease 0.1s;
        box-shadow: 0px 5px 0px 0px #a29bfe;
        cursor: pointer;
        margin-top: 10px;
    }
    .btn-primary:active {
        transform: translateY(5px);
        box-shadow: 0px 0px 0px 0px #a29bfe;
    }
    .btn-admin {
        border: none;
        outline: none;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        padding: 12px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        border-radius: 5px;
        transition: all ease 0.1s;
        box-shadow: 0px 5px 0px 0px #d97706;
        width: 100%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-admin:active {
        transform: translateY(5px);
        box-shadow: 0px 0px 0px 0px #d97706;
    }
</style>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php echo $extraHead; ?>
</head>
<body>

<div class="grid-wrapper">
    <div class="auth-container">
        <div class="auth-box">
        <h2><i class="fas fa-sign-in-alt icon"></i> Welcome Back</h2>

        <?php
        $flash = getFlashMessage();
        if ($flash):
            $alertClass = 'alert-info';
            if ($flash['type'] === 'success') $alertClass = 'alert-success';
            if ($flash['type'] === 'error') $alertClass = 'alert-error';
        ?>
            <div class="alert <?php echo $alertClass; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="/login">
            <div class="role-selection" style="display: flex; gap: 12px; margin-bottom: 25px;">
                <label class="role-card" id="student-card" style="flex: 1; text-align: center; padding: 15px 10px; border: 2px solid #3b82f6; border-radius: 12px; cursor: pointer; background: #eff6ff; transition: all 0.3s;">
                    <input type="radio" name="role" value="student" checked style="display: none;" onchange="updateRoleSelection()">
                    <i class="fas fa-user-graduate" style="font-size: 22px; color: #3b82f6; margin-bottom: 6px; display: block;"></i>
                    <div style="font-weight: 600; color: #1f2937; font-size: 14px;">Student</div>
                </label>
                <label class="role-card" id="tutor-card" style="flex: 1; text-align: center; padding: 15px 10px; border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; background: white; transition: all 0.3s;">
                    <input type="radio" name="role" value="tutor" style="display: none;" onchange="updateRoleSelection()">
                    <i class="fas fa-chalkboard-teacher" style="font-size: 22px; color: #6b7280; margin-bottom: 6px; display: block;"></i>
                    <div style="font-weight: 600; color: #4b5563; font-size: 14px;">Tutor</div>
                </label>
                <label class="role-card" id="parent-card" style="flex: 1; text-align: center; padding: 15px 10px; border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; background: white; transition: all 0.3s;">
                    <input type="radio" name="role" value="parent" style="display: none;" onchange="updateRoleSelection()">
                    <i class="fas fa-user-friends" style="font-size: 22px; color: #6b7280; margin-bottom: 6px; display: block;"></i>
                    <div style="font-weight: 600; color: #4b5563; font-size: 14px;">Parent</div>
                </label>
            </div>

            <!-- Parent Login Info Message -->
            <div id="parent-info" style="display: none; margin-bottom: 20px; padding: 12px 15px; background: #fefce8; border: 1px solid #fef08a; border-radius: 12px; color: #854d0e; font-size: 13px; line-height: 1.5; animation: fadeIn 0.3s ease-in;">
                <i class="fas fa-circle-info" style="margin-right: 5px;"></i>
                <strong>Note:</strong> Parent/Guardian login details are the same as the child's login details.
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: #4b5563;">
                    <input type="checkbox" name="remember_me" value="1" style="width: 16px; height: 16px; accent-color: #6c5ce7; cursor: pointer;" />
                    <i class="fas fa-clock" style="color: #6c5ce7; font-size: 12px;"></i> Stay logged in
                </label>
                <a href="/forgot-password" style="color: #3b82f6; text-decoration: none; font-size: 14px;">
                    <i class="fas fa-key"></i> Forgot Password?
                </a>
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>

        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <button type="button" onclick="fillAdminCredentials()" class="btn-admin" title="Fills default credentials (Peekay / admin123)">
                <i class="fas fa-shield-halved"></i> Default Admin Login
            </button>
        </div>

        <div class="auth-link">
            <p>Don't have an account? <a href="/register">Register here</a></p>
        </div>
    </div>
</div>
</div>

<script>
function updateRoleSelection() {
    const cards = document.querySelectorAll('.role-card');
    const selected = document.querySelector('input[name="role"]:checked').value;
    const parentInfo = document.getElementById('parent-info');

    cards.forEach(function(card) {
        const input = card.querySelector('input[name="role"]');
        const icon = card.querySelector('i');
        const label = card.querySelector('div');
        if (input.value === selected) {
            card.style.borderColor = '#3b82f6';
            card.style.background = '#eff6ff';
            icon.style.color = '#3b82f6';
            label.style.color = '#1f2937';
        } else {
            card.style.borderColor = '#e5e7eb';
            card.style.background = 'white';
            icon.style.color = '#6b7280';
            label.style.color = '#4b5563';
        }
    });

    if (parentInfo) {
        parentInfo.style.display = (selected === 'parent') ? 'block' : 'none';
    }
}
function fillAdminCredentials() {
    document.getElementById('username').value = 'Pontsho';
    document.getElementById('password').value = 'admin123';
    // Optional: auto-submit
    // document.querySelector('form').submit();
}
</script>

</body>
</html>
