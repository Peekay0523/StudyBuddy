<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<h1 style="font-size: 28px; margin-bottom: 10px; color: #1f2937;">
    <i class="fas fa-chart-line"></i> Admin Dashboard
</h1>
<p style="color: #6b7280; margin-bottom: 30px;">Welcome back! Here's what's happening with StudySmart today.</p>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon blue">
            <i class="fas fa-users"></i>
        </div>
        <div class="value"><?php echo number_format($stats['total_users']); ?></div>
        <div class="label">Total Users</div>
        <div class="change positive">
            <i class="fas fa-arrow-up"></i> <?php echo $stats['new_users_this_month']; ?> new this month
        </div>
    </div>
    
    <div class="stat-card">
        <div class="icon green">
            <i class="fas fa-crown"></i>
        </div>
        <div class="value"><?php echo number_format($stats['total_subscriptions']); ?></div>
        <div class="label">Active Subscriptions</div>
    </div>
    
    <div class="stat-card">
        <div class="icon purple">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="value"><?php echo number_format($stats['total_scripts']); ?></div>
        <div class="label">Scripts Uploaded</div>
    </div>
    
    <div class="stat-card">
        <div class="icon orange">
            <i class="fas fa-file-upload"></i>
        </div>
        <div class="value"><?php echo number_format($stats['total_report_cards']); ?></div>
        <div class="label">Report Cards</div>
    </div>
    
    <div class="stat-card">
        <div class="icon pink">
            <i class="fas fa-coins"></i>
        </div>
        <div class="value">R<?php echo number_format($stats['monthly_revenue'], 2); ?></div>
        <div class="label">Monthly Revenue</div>
    </div>
    
    <div class="stat-card">
        <div class="icon yellow">
            <i class="fas fa-user-plus"></i>
        </div>
        <div class="value"><?php echo $stats['new_users_this_month']; ?></div>
        <div class="label">New Users (30 days)</div>
    </div>
</div>

<!-- Subscription Breakdown -->
<div class="admin-section">
    <h3><i class="fas fa-chart-pie"></i> Subscription Breakdown</h3>
    <div class="chart-container">
        <?php 
        $totalSubs = array_sum(array_column($subscriptionBreakdown, 'count'));
        foreach ($subscriptionBreakdown as $sub): 
            $percentage = $totalSubs > 0 ? ($sub['count'] / $totalSubs) * 100 : 0;
            $colors = ['free' => '#6b7280', 'basic' => '#2563eb', 'premium' => '#f59e0b'];
            $color = $colors[$sub['plan']] ?? '#6b7280';
        ?>
            <div class="chart-box">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 5px;">
                    <span style="font-weight: 600; text-transform: capitalize; white-space: nowrap;"><?php echo htmlspecialchars($sub['plan'] ?? ''); ?></span>
                    <span style="color: #6b7280; white-space: nowrap;"><?php echo $sub['count']; ?> users</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $color; ?>;"></div>
                </div>
                <div style="margin-top: 8px; font-size: 13px; color: #6b7280; white-space: nowrap;">
                    R<?php echo number_format($sub['revenue'], 2); ?> revenue
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Recent Users -->
<div class="admin-section">
    <h3><i class="fas fa-users"></i> Recent Users</h3>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Subscription</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($user['username'] ?? ''); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'premium' : 'free'; ?>">
                                <?php echo htmlspecialchars($user['role'] ?? 'student'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo htmlspecialchars($user['subscription_plan'] ?? 'free'); ?>">
                                <?php echo htmlspecialchars($user['subscription_plan'] ?? 'free'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $user['subscription_status'] === 'active' ? 'active' : 'inactive'; ?>">
                                <?php echo htmlspecialchars($user['subscription_status'] ?? 'inactive'); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="/admin/users/<?php echo $user['id']; ?>" class="btn-sm btn-sm-primary">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top: 20px; text-align: center;">
        <a href="/admin/users" class="btn-primary" style="text-decoration: none; display: inline-block;">
            View All Users <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<!-- Top Users by Activity -->
<div class="admin-section">
    <h3><i class="fas fa-trophy"></i> Top Users by Activity</h3>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Scripts</th>
                    <th>Report Cards</th>
                    <th>Total Activity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topUsers as $user): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($user['username'] ?? ''); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                        <td><?php echo $user['scripts_count']; ?></td>
                        <td><?php echo $user['report_cards_count']; ?></td>
                        <td>
                            <span class="badge basic">
                                <?php echo $user['scripts_count'] + $user['report_cards_count']; ?> actions
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
