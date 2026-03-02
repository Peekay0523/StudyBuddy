<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-brain"></i> Topics Mastered
        </h1>
        <p style="color: #6b7280;">Analytics on user learning progress and topic mastery</p>
    </div>
    <a href="/admin" class="btn-secondary" style="text-decoration: none; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<!-- Topic Statistics -->
<div class="admin-section">
    <h3><i class="fas fa-chart-bar"></i> Subject Distribution</h3>
    <?php if (empty($topicStats)): ?>
        <p style="color: #6b7280; text-align: center; padding: 40px;">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            No topic data available yet
        </p>
    <?php else: ?>
        <div class="chart-container">
            <?php 
            $maxCount = max(array_column($topicStats, 'count'));
            $colors = ['#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#9333ea', '#0891b2', '#db2777', '#ea580c'];
            ?>
            <?php foreach ($topicStats as $index => $stat): 
                $percentage = $maxCount > 0 ? ($stat['count'] / $maxCount) * 100 : 0;
                $color = $colors[$index % count($colors)];
            ?>
                <div class="chart-box" style="flex: 1; min-width: 200px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-weight: 600;"><?php echo htmlspecialchars($stat['subject'] ?? ''); ?></span>
                        <span style="color: #6b7280;"><?php echo $stat['count']; ?> scripts</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $color; ?>;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- User Activity by Topics -->
<div class="admin-section">
    <h3><i class="fas fa-users"></i> User Learning Activity</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Subscription</th>
                <th>Study Plans</th>
                <th>Scripts</th>
                <th>Activity Score</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($topicsData)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        No user activity data available
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                $maxActivity = max(array_map(fn($u) => $u['study_plans_created'] + $u['scripts_uploaded'], $topicsData));
                ?>
                <?php foreach ($topicsData as $user): 
                    $activityScore = $user['study_plans_created'] + $user['scripts_uploaded'];
                    $progress = $maxActivity > 0 ? ($activityScore / $maxActivity) * 100 : 0;
                ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($user['username'] ?? ''); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                        <td>
                            <span class="badge <?php echo htmlspecialchars($user['subscription_plan'] ?? ''); ?>">
                                <?php echo htmlspecialchars($user['subscription_plan'] ?? ''); ?>
                            </span>
                        </td>
                        <td><?php echo $user['study_plans_created']; ?></td>
                        <td><?php echo $user['scripts_uploaded']; ?></td>
                        <td>
                            <span class="badge <?php echo $activityScore > 10 ? 'active' : ($activityScore > 5 ? 'basic' : 'free'); ?>">
                                <?php echo $activityScore; ?> points
                            </span>
                        </td>
                        <td style="width: 200px;">
                            <div class="progress-bar">
                                <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%; background: <?php echo $progress > 70 ? '#16a34a' : ($progress > 30 ? '#f59e0b' : '#6b7280'); ?>;"></div>
                            </div>
                            <small style="color: #9ca3af;"><?php echo round($progress); ?>% active</small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Learning Insights -->
<div class="stats-grid" style="margin-top: 20px;">
    <div class="stat-card">
        <div class="icon blue">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="value"><?php echo array_sum(array_column($topicsData, 'scripts_uploaded')); ?></div>
        <div class="label">Total Scripts Uploaded</div>
    </div>
    
    <div class="stat-card">
        <div class="icon green">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="value"><?php echo array_sum(array_column($topicsData, 'study_plans_created')); ?></div>
        <div class="label">Total Study Plans Created</div>
    </div>
    
    <div class="stat-card">
        <div class="icon purple">
            <i class="fas fa-users"></i>
        </div>
        <div class="value"><?php echo count($topicsData); ?></div>
        <div class="label">Active Learners</div>
    </div>
    
    <div class="stat-card">
        <div class="icon orange">
            <i class="fas fa-brain"></i>
        </div>
        <div class="value"><?php echo count($topicStats); ?></div>
        <div class="label">Unique Subjects</div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
