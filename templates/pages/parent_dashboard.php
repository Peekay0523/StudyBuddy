<?php
$pageTitle = 'Parent Dashboard - StudySmart';
$currentPage = 'parent-dashboard';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-header-grid" style="display: grid; grid-template-columns: 1fr auto; gap: 30px; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 class="title" style="margin-bottom: 10px;">
            Parental <span>Support</span> Portal
        </h1>
        <p class="subtitle" style="margin: 0;">
            Monitor your child's learning journey, track their mastered topics, 
            and manage their subscription for uninterrupted access.
        </p>
    </div>

    <!-- Subscription Card - Consistent with Student Dashboard -->
    <div id="plan-info-card" style="background: linear-gradient(135deg, <?php echo $planBadge === 'premium' ? '#fbbf24 0%, #f59e0b 100%' : ($planBadge === 'trial' ? '#10b981 0%, #059669 100%' : ($planBadge === 'basic' ? '#3b82f6 0%, #2563eb 100%' : '#6b7280 0%, #4b5563 100%')); ?>); color: white; padding: 20px 25px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); cursor: pointer; transition: all 0.3s ease; display: inline-block;"
         onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 14px 50px rgba(0,0,0,0.2)'"
         onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 40px rgba(0,0,0,0.15)'"
         onclick="window.location.href='/subscription'">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-crown" style="font-size: 28px;"></i>
            <div>
                <h2 style="margin: 0; font-size: 20px; color: white;"><?php echo htmlspecialchars($planName); ?> Plan</h2>
                <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 12px; font-size: 11px;">CLICK TO MANAGE</span>
            </div>
            <i class="fas fa-chevron-right" style="margin-left: auto; opacity: 0.8;"></i>
        </div>
    </div>
</div>

<!-- STATS -->
<section class="stats">
    <div class="card blue">
        <i class="fas fa-file-alt bg-icon"></i>
        <p>Scripts Uploaded</p>
        <h2><?php echo $scriptsCount; ?></h2>
    </div>

    <div class="card orange">
        <i class="fas fa-calendar-check bg-icon"></i>
        <p>Study Plans</p>
        <h2><?php echo $plansCount; ?></h2>
    </div>

    <div class="card purple">
        <i class="fas fa-lightbulb bg-icon"></i>
        <p>Topics Mastered</p>
        <h2><?php echo $topicsCount; ?></h2>
    </div>

    <div class="card green">
        <i class="fas fa-chart-line bg-icon"></i>
        <p>Activity Status</p>
        <h2>Active</h2>
    </div>
</section>

<style>
/* Synchronized styles from dashboard.php */
.stats .card i.bg-icon {
    position: absolute !important;
    top: 15px !important;
    right: 15px !important;
    font-size: 50px !important;
    opacity: 0.49 !important;
    color: #ffffff !important;
    pointer-events: none !important;
    z-index: 0 !important;
}
.stats .card {
    position: relative !important;
    overflow: hidden !important;
    padding: 25px !important;
    border-radius: 16px !important;
}
.stats .card h2 {
    font-size: 32px !important;
    margin-top: 10px !important;
}
.stats .card p {
    font-size: 16px !important;
    font-weight: 500 !important;
}
</style>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 30px;">
    <!-- Parental Tips & Info -->
    <div>
        <h2 class="section-title"><i class="fas fa-info-circle"></i> Quick Overview</h2>
        <div style="background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 20px;">
                Welcome to the parent portal. This dashboard provides a simplified view of your child's academic engagement. 
                Instead of detailed logs, we provide subject-based summaries of their mastered topics and overall activity.
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="padding: 15px; background: #f0fdf4; border-radius: 10px; border-left: 4px solid #22c55e;">
                    <div style="font-weight: 600; color: #166534; font-size: 14px;">Total Mastery</div>
                    <div style="font-size: 20px; font-weight: 700; color: #15803d;"><?php echo $topicsCount; ?> Topics</div>
                </div>
                <div style="padding: 15px; background: #eff6ff; border-radius: 10px; border-left: 4px solid #3b82f6;">
                    <div style="font-weight: 600; color: #1e40af; font-size: 14px;">Study Assets</div>
                    <div style="font-size: 20px; font-weight: 700; color: #1d4ed8;"><?php echo $scriptsCount + $plansCount; ?> Files</div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <h2 class="section-title"><i class="fas fa-lightbulb"></i> Parenting Tip</h2>
        <div style="background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%); padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 4px solid #f59e0b;">
            <p style="font-size: 15px; color: #854d0e; line-height: 1.6; margin: 0;">
                <i class="fas fa-quote-left" style="opacity: 0.3; margin-right: 5px;"></i>
                Review the "Track Progress" section weekly to see which subjects your child is excelling in.
            </p>
        </div>
        
        <div style="margin-top: 30px; background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 4px solid #10b981;">
            <h3 style="font-size: 18px; color: #1e293b; margin-bottom: 15px;"><i class="fas fa-shield-alt"></i> Safety Tip</h3>
            <p style="font-size: 14px; color: #64748b; line-height: 1.5; margin: 0;">
                To see more detailed activity or chat with the AI yourself, use the same login but choose "Student" role.
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
