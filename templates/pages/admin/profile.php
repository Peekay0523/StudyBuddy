<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div style="margin-bottom: 30px;">
    <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
        <i class="fas fa-user-circle"></i> My Profile
    </h1>
    <p style="color: #6b7280;">Manage your administrator account details</p>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="admin-section">
            <h3 style="border-bottom: 1px solid #f3f4f6; padding-bottom: 15px; margin-bottom: 25px;">
                <i class="fas fa-id-card" style="color: #6366f1;"></i> Account Information
            </h3>
            
            <form method="POST" action="/admin/profile/update">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Username</label>
                    <input 
                        type="text" 
                        name="username" 
                        value="<?php echo htmlspecialchars($user['username']); ?>" 
                        required
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 15px;"
                    >
                    <small style="color: #6b7280; margin-top: 5px; display: block;">This is the name you use to login.</small>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Email Address</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 15px;"
                    >
                </div>

                <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px dashed #e2e8f0; margin-top: 30px; margin-bottom: 25px;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #1e293b;">
                        <i class="fas fa-lock" style="color: #f59e0b;"></i> Change Password
                    </h4>
                    <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">Leave these fields blank if you don't want to change your password.</p>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #4b5563;">New Password</label>
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="Min. 8 characters"
                            style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 15px;"
                        >
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #4b5563;">Confirm New Password</label>
                        <input 
                            type="password" 
                            name="confirm_password" 
                            placeholder="Repeat new password"
                            style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 15px;"
                        >
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" class="btn-primary" style="padding: 12px 25px; border-radius: 10px; font-weight: 700; background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; color: white; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="/admin" class="btn-secondary" style="padding: 12px 25px; border-radius: 10px; font-weight: 600; text-decoration: none; color: #4b5563; background: #f1f5f9; text-align: center;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-6">
        <div class="admin-section" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white;">
            <h3 style="color: white; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
                <i class="fas fa-shield-halved" style="color: #fbbf24;"></i> Security Tips
            </h3>
            <ul style="list-style: none; padding: 0; margin-top: 20px;">
                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                    <i class="fas fa-check-circle" style="color: #22c55e; margin-top: 4px;"></i>
                    <span>Use a password that is unique to this application.</span>
                </li>
                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                    <i class="fas fa-check-circle" style="color: #22c55e; margin-top: 4px;"></i>
                    <span>A strong password should contain a mix of letters, numbers, and symbols.</span>
                </li>
                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                    <i class="fas fa-check-circle" style="color: #22c55e; margin-top: 4px;"></i>
                    <span>Never share your administrator credentials with anyone.</span>
                </li>
                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                    <i class="fas fa-check-circle" style="color: #22c55e; margin-top: 4px;"></i>
                    <span>Regularly update your password to maintain account integrity.</span>
                </li>
            </ul>
            
            <div style="margin-top: 30px; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 10px; border-left: 4px solid #fbbf24;">
                <p style="margin: 0; font-size: 14px; opacity: 0.9;">
                    <strong>Current Role:</strong> Full Administrator<br>
                    <strong>Access Level:</strong> All features unlocked
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
