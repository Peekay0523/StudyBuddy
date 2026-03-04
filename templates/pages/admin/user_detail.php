<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-user"></i> User Details
        </h1>
        <p style="color: #6b7280;">Viewing: <?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="/admin/users" class="btn-secondary" style="text-decoration: none; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
</div>

<!-- User Info Card -->
<div class="admin-section">
    <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-start;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 32px; font-weight: bold;">
            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
        </div>
        <div style="flex: 1;">
            <h2 style="margin-bottom: 10px;"><?php echo htmlspecialchars($user['username'] ?? ''); ?></h2>
            <p style="color: #6b7280; margin-bottom: 15px;"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <span class="badge <?php echo $user['role'] === 'admin' ? 'premium' : 'free'; ?>">
                    <i class="fas fa-shield"></i> <?php echo htmlspecialchars($user['role'] ?? 'student'); ?>
                </span>
                <span class="badge active">
                    <i class="fas fa-clock"></i> Joined <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                </span>
            </div>
        </div>
        <div>
            <form method="POST" action="/admin/users/toggle-role" onsubmit="return confirm('Toggle user role? This will <?php echo $user['role'] === 'admin' ? 'remove admin' : 'grant admin'; ?> privileges.');">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <button type="submit" class="btn-sm btn-sm-warning">
                    <i class="fas fa-exchange-alt"></i> Toggle Role
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon blue">
            <i class="fas fa-crown"></i>
        </div>
        <div class="value"><?php echo count($userSubscriptions); ?></div>
        <div class="label">Subscriptions</div>
    </div>
    
    <div class="stat-card">
        <div class="icon green">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="value"><?php echo count($userScripts); ?></div>
        <div class="label">Scripts Uploaded</div>
    </div>
    
    <div class="stat-card">
        <div class="icon orange">
            <i class="fas fa-file-upload"></i>
        </div>
        <div class="value"><?php echo count($userReportCards); ?></div>
        <div class="label">Report Cards</div>
    </div>
    
    <div class="stat-card">
        <div class="icon purple">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="value"><?php echo count($userStudyPlans); ?></div>
        <div class="label">Study Plans</div>
    </div>
</div>

<!-- Current Subscription -->
<div class="admin-section">
    <h3><i class="fas fa-crown"></i> Current Subscription</h3>
    <?php 
    $activeSub = array_filter($userSubscriptions, fn($s) => $s['status'] === 'active');
    $activeSub = reset($activeSub);
    ?>
    <?php if ($activeSub): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <span class="badge <?php echo htmlspecialchars($activeSub['plan'] ?? ''); ?>" style="font-size: 14px; padding: 6px 14px;">
                    <?php echo htmlspecialchars($activeSub['plan'] ?? ''); ?>
                </span>
                <span style="margin-left: 10px; color: #6b7280;">
                    R<?php echo number_format($activeSub['price'], 2); ?>/month
                </span>
            </div>
            <div>
                <span class="badge active">Active</span>
                <span style="margin-left: 10px; color: #6b7280;">
                    Renews: <?php echo date('M d, Y', strtotime($activeSub['current_period_end'])); ?>
                </span>
            </div>
        </div>
    <?php else: ?>
        <p style="color: #6b7280;"><i class="fas fa-info-circle"></i> No active subscription</p>
    <?php endif; ?>
</div>

<!-- Scripts -->
<div class="admin-section">
    <h3><i class="fas fa-file-alt"></i> Scripts (<?php echo count($userScripts); ?>)</h3>
    <?php if (empty($userScripts)): ?>
        <p style="color: #6b7280;">No scripts uploaded</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Subject</th>
                    <th>Memorandum</th>
                    <th>Uploaded</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($userScripts, 0, 10) as $script): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(basename($script['file_name'])); ?></td>
                        <td>
                            <?php if (!empty($script['subject'])): ?>
                                <span class="badge basic"><?php echo htmlspecialchars($script['subject'] ?? ''); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($script['memorandum_generated']): ?>
                                <span class="badge active">Generated</span>
                            <?php else: ?>
                                <span class="badge inactive">Not generated</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($script['uploaded_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if (count($userScripts) > 10): ?>
            <p style="margin-top: 15px; color: #6b7280;">+ <?php echo count($userScripts) - 10; ?> more scripts</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Report Cards -->
<div class="admin-section">
    <h3><i class="fas fa-file-upload"></i> Report Cards (<?php echo count($userReportCards); ?>)</h3>
    <?php if (empty($userReportCards)): ?>
        <p style="color: #6b7280;">No report cards uploaded</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Grade</th>
                    <th>Average</th>
                    <th>Career Recs</th>
                    <th>Uploaded</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($userReportCards, 0, 10) as $rc): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(basename($rc['file_name'])); ?></td>
                        <td>
                            <?php if (!empty($rc['grade'])): ?>
                                <span class="badge basic">Grade <?php echo htmlspecialchars($rc['grade'] ?? ''); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($rc['average']) && $rc['average'] !== null): ?>
                                <span class="badge <?php echo $rc['average'] >= 70 ? 'active' : 'basic'; ?>">
                                    <?php echo number_format($rc['average'], 1); ?>%
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($rc['career_recommendations_generated']): ?>
                                <span class="badge active">Generated</span>
                            <?php else: ?>
                                <span class="badge inactive">Not generated</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($rc['uploaded_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<!-- Study Plans -->
<div class="admin-section">
    <h3><i class="fas fa-calendar-alt"></i> Study Plans (<?php echo count($userStudyPlans); ?>)</h3>
    <?php if (empty($userStudyPlans)): ?>
        <p style="color: #6b7280;">No study plans created</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($userStudyPlans, 0, 10) as $plan): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($plan['title'] ?? ''); ?></td>
                        <td>
                            <span class="badge <?php echo $plan['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $plan['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($plan['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<!-- Subscription History -->
<div class="admin-section">
    <h3><i class="fas fa-history"></i> Subscription History</h3>
    <?php if (empty($userSubscriptions)): ?>
        <p style="color: #6b7280;">No subscription history</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Period</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userSubscriptions as $sub): ?>
                    <tr>
                        <td>
                            <span class="badge <?php echo htmlspecialchars($sub['plan'] ?? ''); ?>">
                                <?php echo htmlspecialchars($sub['plan'] ?? ''); ?>
                            </span>
                        </td>
                        <td>R<?php echo number_format($sub['price'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo $sub['status'] === 'active' ? 'active' : ($sub['status'] === 'cancelled' ? 'cancelled' : 'inactive'); ?>">
                                <?php echo htmlspecialchars($sub['status'] ?? ''); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo date('M d, Y', strtotime($sub['current_period_start'])); ?> -
                            <?php echo $sub['current_period_end'] ? date('M d, Y', strtotime($sub['current_period_end'])) : 'Ongoing'; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($sub['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
