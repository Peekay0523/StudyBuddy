<?php
include __DIR__ . '/../layouts/header.php';
?>

<style>
.profile-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.profile-header {
    margin-bottom: 30px;
}

.profile-title {
    font-size: 28px;
    color: #1e293b;
    margin: 0 0 8px 0;
}

.profile-subtitle {
    color: #64748b;
    margin: 0;
    font-size: 14px;
}

.profile-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    margin-bottom: 24px;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f1f5f9;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #4b5563;
    font-size: 14px;
}

.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.2s;
    background: #f8fafc;
}

.form-group input:focus {
    outline: none;
    border-color: #6c63ff;
    background: white;
    box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
}

.password-reset-box {
    background: #fcfaff;
    padding: 20px;
    border-radius: 12px;
    border: 1px dashed #dcd7ff;
    margin-top: 30px;
}

.btn-save {
    background: linear-gradient(135deg, #6c63ff 0%, #5a52e6 100%);
    color: white;
    border: none;
    padding: 14px 28px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 12px rgba(108, 99, 255, 0.2);
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(108, 99, 255, 0.3);
}

.btn-save:active {
    transform: translateY(0);
}

.security-tip {
    display: flex;
    gap: 12px;
    margin-bottom: 15px;
    font-size: 14px;
    color: #4b5563;
    line-height: 1.5;
}

.security-tip i {
    color: #10b981;
    margin-top: 3px;
}

@media (max-width: 768px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="profile-page">
    <div class="profile-header">
        <h1 class="profile-title"><i class="fas fa-user-circle"></i> Account Settings</h1>
        <p class="profile-subtitle">Update your personal information and security preferences</p>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="profile-card">
                <h3 class="section-title"><i class="fas fa-id-card" style="color: #6c63ff;"></i> Personal Details</h3>
                
                <form method="POST" action="/profile/update">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        <small style="color: #64748b; margin-top: 5px; display: block;">This is your display name and login ID.</small>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Enter your email">
                    </div>

                    <div class="password-reset-box">
                        <h4 style="margin: 0 0 15px 0; font-size: 15px; color: #4f46e5;">
                            <i class="fas fa-lock"></i> Security Update
                        </h4>
                        <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">To change your password, fill in the fields below. Otherwise, leave them blank.</p>
                        
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="password" placeholder="At least 8 characters">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" placeholder="Repeat new password">
                        </div>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Save My Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="profile-card" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border: none;">
                <h3 class="section-title" style="color: white; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-shield-halved" style="color: #fbbf24;"></i> Security Standards
                </h3>
                
                <div class="security-tip" style="color: #cbd5e1;">
                    <i class="fas fa-check-circle"></i>
                    <span>Use a unique password for StudyBuddie to keep your data safe.</span>
                </div>
                <div class="security-tip" style="color: #cbd5e1;">
                    <i class="fas fa-check-circle"></i>
                    <span>Enable a mix of uppercase, numbers, and symbols in your password.</span>
                </div>
                <div class="security-tip" style="color: #cbd5e1;">
                    <i class="fas fa-check-circle"></i>
                    <span>Your account details are private and never shared with other users.</span>
                </div>
                
                <div style="margin-top: 30px; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px; border-left: 4px solid #6c63ff;">
                    <p style="margin: 0; font-size: 13px; line-height: 1.6;">
                        <strong>Account Role:</strong> <?php echo ucfirst($user['role'] ?? 'Student'); ?><br>
                        <strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($user['joined_date'])); ?>
                    </p>
                </div>
            </div>

            <div class="profile-card" style="text-align: center; padding: 30px;">
                <i class="fas fa-circle-question" style="font-size: 40px; color: #e2e8f0; margin-bottom: 15px;"></i>
                <h4 style="margin: 0 0 10px 0; color: #1e293b;">Need more help?</h4>
                <p style="font-size: 14px; color: #64748b; margin-bottom: 20px;">If you're having trouble with your account, our support team is here to help.</p>
                <a href="/ai-chat" class="btn-sm btn-sm-secondary" style="text-decoration: none; width: 100%; justify-content: center; height: 45px;">
                    <i class="fas fa-comments"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
