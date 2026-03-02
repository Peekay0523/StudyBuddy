<?php
$pageTitle = 'Login - StudySmart';
$currentPage = 'login';
$extraHead = '<style>
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
        max-width: 400px;
    }
    .auth-box h2 {
        text-align: center;
        color: #1f2937;
        margin: 0 0 30px;
        font-size: 24px;
    }
</style>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-box">
        <h2><i class="fas fa-sign-in-alt icon"></i> Welcome Back</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="/login">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>

        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <button type="button" onclick="fillAdminCredentials()" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); border: none; border-radius: 8px; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-shield-halved"></i> Login as Admin
            </button>
        </div>

        <div class="auth-link">
            <p>Don't have an account? <a href="/register">Register here</a></p>
        </div>
    </div>
</div>

<script>
function fillAdminCredentials() {
    document.getElementById('username').value = 'Peekay';
    document.getElementById('password').value = 'admin123';
    // Optional: auto-submit
    // document.querySelector('form').submit();
}
</script>

</body>
</html>
