<?php
$pageTitle = 'Dashboard - StudySmart';
$currentPage = 'dashboard';
include __DIR__ . '/../layouts/header.php';
?>

<!-- Subscription Plan Card -->
<div style="background: linear-gradient(135deg, <?php echo $planBadge === 'premium' ? '#fbbf24 0%, #f59e0b 100%' : ($planBadge === 'trial' ? '#10b981 0%, #059669 100%' : ($planBadge === 'basic' ? '#3b82f6 0%, #2563eb 100%' : '#6b7280 0%, #4b5563 100%')); ?>); color: white; padding: 25px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <i class="fas fa-crown" style="font-size: 28px;"></i>
                <h2 style="margin: 0; font-size: 24px;"><?php echo htmlspecialchars($planName); ?> Plan</h2>
                <?php if ($planBadge === 'trial'): ?>
                    <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 12px;">FREE TRIAL</span>
                <?php endif; ?>
            </div>
            <p style="margin: 0; opacity: 0.9; font-size: 14px;">
                <?php if ($trialEnds): ?>
                    <i class="fas fa-clock"></i> Trial ends: <?php echo date('M d, Y', strtotime($trialEnds)); ?>
                    <br><small>After trial, you'll be moved to Free plan. <a href="/subscription" style="color: white; text-decoration: underline;">Upgrade now</a></small>
                <?php elseif ($planBadge !== 'free'): ?>
                    <i class="fas fa-check-circle"></i> Active subscription
                    <br><small><a href="/subscription" style="color: white; text-decoration: underline;">Manage subscription</a></small>
                <?php else: ?>
                    <i class="fas fa-info-circle"></i> Free plan with limited features
                    <br><small><a href="/subscription" style="color: white; text-decoration: underline;">Upgrade to unlock more features</a></small>
                <?php endif; ?>
            </p>
        </div>
        <div>
            <a href="/subscription" style="background: white; color: <?php echo $planBadge === 'premium' ? '#f59e0b' : ($planBadge === 'trial' ? '#059669' : ($planBadge === 'basic' ? '#2563eb' : '#4b5563')); ?>; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">
                <i class="fas fa-arrow-right"></i> <?php echo $planBadge === 'free' ? 'Upgrade Now' : 'Manage Plan'; ?>
            </a>
        </div>
    </div>
    
    <?php if (!empty($planFeatures)): ?>
    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2);">
        <h4 style="margin: 0 0 10px 0; font-size: 14px; opacity: 0.9;"><i class="fas fa-check-circle"></i> Your Plan Features:</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            <?php foreach ($planFeatures as $feature): ?>
                <div style="font-size: 13px; opacity: 0.9;">
                    <i class="fas fa-check" style="margin-right: 6px;"></i><?php echo htmlspecialchars($feature); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="badge">✨ AI-Powered Learning</div>

<h1 class="title">
    Study <span>Smart</span>, Not <span class="hard">Hard</span>
</h1>

<p class="subtitle">
    Upload your scripts, get instant memorandums, personalized study plans,
    and discover your ideal career path.
</p>

<!-- STATS -->
<section class="stats">
    <div class="card blue">
        <p>Scripts Uploaded</p>
        <h2><?php echo $scriptsCount ?? 0; ?></h2>
    </div>

    <div class="card orange">
        <p>Active Plans</p>
        <h2><?php echo $plansCount ?? 0; ?></h2>
    </div>

    <div class="card green">
        <p>Report Cards</p>
        <h2><?php echo $reportsCount ?? 0; ?></h2>
    </div>

    <div class="card purple">
        <p>Topics Mastered</p>
        <h2><?php echo $topicsCount ?? 0; ?></h2>
    </div>
</section>

<!-- ACTIONS -->
<h2 class="section-title">What would you like to do?</h2>

<section class="actions">
    <a href="/upload-script" class="action blue"><i class="fas fa-cloud-upload-alt icon-sm"></i> Upload Script</a>
    <a href="/study-plan" class="action orange"><i class="fas fa-calendar-check icon-sm"></i> Study Planner</a>
    <a href="/upload-report-card" class="action green"><i class="fas fa-compass icon-sm"></i> Career Guide</a>
    <a href="/ai-chat" class="action purple"><i class="fas fa-robot icon-sm"></i> AI Assistant</a>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
