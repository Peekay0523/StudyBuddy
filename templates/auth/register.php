<?php
$pageTitle = 'Register - StudySmart';
$currentPage = 'register';
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
            max-width: 400px;
        }
        .auth-box h2 {
            text-align: center;
            color: #1f2937;
            margin: 0 0 30px;
            font-size: 24px;
        }
        .help-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-box">
        <h2><i class="fas fa-user-plus icon"></i> Create Account</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="/register">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="+27 12 345 6789" required>
                <div class="help-text">Enter your phone number with country code (e.g., +27 for South Africa).</div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
                <div class="help-text">Your password must be at least 8 characters.</div>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>

            <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #16a34a, #22c55e);">Register</button>
        </form>

        <div class="auth-link">
            <p>Already have an account? <a href="/login">Login here</a></p>
        </div>
    </div>
</div>

</body>
</html>
