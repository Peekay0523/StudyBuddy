<?php
$pageTitle = 'Dashboard - StudySmart';
$currentPage = 'dashboard';
include __DIR__ . '/../layouts/header.php';
?>

<!-- Subscription Plan Card - Clickable -->
<div id="plan-info-card" style="background: linear-gradient(135deg, <?php echo $planBadge === 'premium' ? '#fbbf24 0%, #f59e0b 100%' : ($planBadge === 'trial' ? '#10b981 0%, #059669 100%' : ($planBadge === 'basic' ? '#3b82f6 0%, #2563eb 100%' : '#6b7280 0%, #4b5563 100%')); ?>); color: white; padding: 20px 25px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); cursor: pointer; transition: all 0.3s ease; display: inline-block;"
     onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 14px 50px rgba(0,0,0,0.2)'"
     onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 40px rgba(0,0,0,0.15)'"
     onclick="showPlanDetailsModal()">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-crown" style="font-size: 28px;"></i>
        <div>
            <h2 style="margin: 0; font-size: 20px;"><?php echo htmlspecialchars($planName); ?> Plan</h2>
            <?php if ($planBadge === 'trial'): ?>
                <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 12px; font-size: 11px;">FREE TRIAL</span>
            <?php endif; ?>
        </div>
        <i class="fas fa-chevron-right" style="margin-left: auto; opacity: 0.8;"></i>
    </div>
</div>

<!-- Plan Details Modal -->
<div id="plan-details-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 16px; max-width: 500px; width: 90%; position: relative; max-height: 80vh; overflow-y: auto;">
        <button onclick="closePlanDetailsModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">
            &times;
        </button>

        <div style="background: linear-gradient(135deg, <?php echo $planBadge === 'premium' ? '#fbbf24 0%, #f59e0b 100%' : ($planBadge === 'trial' ? '#10b981 0%, #059669 100%' : ($planBadge === 'basic' ? '#3b82f6 0%, #2563eb 100%' : '#6b7280 0%, #4b5563 100%')); ?>); color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <i class="fas fa-crown" style="font-size: 24px;"></i>
                <h3 style="margin: 0; font-size: 20px;"><?php echo htmlspecialchars($planName); ?> Plan</h3>
                <?php if ($planBadge === 'trial'): ?>
                    <span style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 16px; font-size: 11px;">FREE TRIAL</span>
                <?php endif; ?>
            </div>
            <?php if ($trialEnds): ?>
                <p style="margin: 0; opacity: 0.9; font-size: 13px;">
                    <i class="fas fa-clock"></i> Trial ends: <?php echo date('M d, Y', strtotime($trialEnds)); ?>
                    <br><small>After trial, you'll be moved to Free plan. Upgrade now!</small>
                </p>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 20px;">
            <a href="/subscription" class="btn-primary" style="display: block; text-align: center; text-decoration: none;">
                <i class="fas fa-cog"></i> Manage Plan
            </a>
        </div>

        <?php if (!empty($planFeatures)): ?>
        <div>
            <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #1e293b;">
                <i class="fas fa-check-circle" style="color: #10b981;"></i> Your Plan Features:
            </h4>
            <div style="display: grid; gap: 10px;">
                <?php foreach ($planFeatures as $feature): ?>
                    <div style="font-size: 14px; color: #64748b; padding: 10px; background: #f8fafc; border-radius: 8px;">
                        <i class="fas fa-check" style="color: #10b981; margin-right: 8px;"></i><?php echo htmlspecialchars($feature); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showPlanDetailsModal() {
    document.getElementById('plan-details-modal').style.display = 'flex';
}

function closePlanDetailsModal() {
    document.getElementById('plan-details-modal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('plan-details-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePlanDetailsModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePlanDetailsModal();
    }
});
</script>



<!-- Main Header with Invite Friends - Inline Layout -->
<div class="dashboard-header-grid" style="display: grid; grid-template-columns: 1fr auto; gap: 30px; align-items: center; margin-bottom: 30px;">
    <!-- Left: Study Smart Heading -->
    <div style="min-width: 0;">
        <h1 class="title" style="margin-bottom: 10px;">
            Study <span>Smart</span>, Not <span class="hard">Hard</span>
        </h1>
        <p class="subtitle" style="margin: 0;">
            Upload your scripts, get instant memorandums, personalized study plans,
            and discover your ideal career path.
        </p>
    </div>
    
    <!-- Right: Invite Friends Card -->
    <div class="dashboard-invite-card" style="flex-shrink: 0;">
        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15); min-width: 320px;">
            <div style="display: flex; align-items: flex-start; gap: 15px;">
                
                <div style="flex: 1; min-width: 0;">
                    <h3 style="margin: 0 0 8px 0; color: #0369a1; font-size: 16px; line-height: 1.3;">
                        </i> Invite Friends to StudySmart
                    </h3>
                    
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button onclick="document.getElementById('inviteFriendsModal').style.display='flex'" class="btn-primary" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); border: none; padding: 8px 16px; font-size: 13px;">
                            <i class="fas fa-envelope"></i> Invite Friends
                        </button>
                        <a href="/invites" style="padding: 8px 16px; color: #0369a1; text-decoration: none; font-weight: 500; font-size: 13px; border: 1px solid #0ea5e9; border-radius: 6px; display: inline-block;">
                            View Sent Invites <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Responsive Styles -->
<style>
@media (max-width: 900px) {
    .dashboard-header-grid {
        grid-template-columns: 1fr !important;
    }
    .dashboard-invite-card {
        min-width: 100% !important;
    }
}
</style>

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

<!-- Activity Score Card -->
<section style="margin: 30px 0;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 16px; box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div style="flex: 1; min-width: 250px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                    <i class="fas fa-fire-alt" style="font-size: 32px; color: #fbbf24;"></i>
                    <h3 style="margin: 0; font-size: 22px;">Your Learning Score</h3>
                </div>
                <div style="display: flex; align-items: baseline; gap: 15px; margin-bottom: 10px;">
                    <span style="font-size: 48px; font-weight: 700; line-height: 1;"><?php echo $activityScore; ?></span>
                    <span style="font-size: 18px; opacity: 0.9;">points</span>
                </div>
                <p style="margin: 0; opacity: 0.9; font-size: 14px;">
                    <i class="fas fa-chart-line"></i> Keep learning to increase your score!
                </p>
            </div>
            
            <div style="flex: 1; min-width: 250px;">
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 12px; backdrop-filter: blur(10px);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-size: 14px; opacity: 0.9;">Activity Level</span>
                        <span style="font-size: 20px; font-weight: 700;"><?php echo $activityPercentage; ?>%</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); height: 12px; border-radius: 6px; overflow: hidden; margin-bottom: 12px;">
                        <div style="background: linear-gradient(90deg, #fbbf24 0%, #f59e0b 100%); height: 100%; width: <?php echo $activityPercentage; ?>%; transition: width 0.5s ease; border-radius: 6px;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; opacity: 0.8;">
                        <span><i class="fas fa-seedling"></i> Getting Started</span>
                        <span><i class="fas fa-trophy"></i> Learning Champion</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Score Breakdown -->
        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2);">
            <h4 style="margin: 0 0 15px 0; font-size: 14px; opacity: 0.9;"><i class="fas fa-calculator"></i> Score Breakdown:</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                <div style="background: rgba(255,255,255,0.1); padding: 12px; border-radius: 8px;">
                    <div style="font-size: 12px; opacity: 0.8; margin-bottom: 5px;">
                        <i class="fas fa-file-upload"></i> Scripts
                    </div>
                    <div style="font-size: 20px; font-weight: 700;"><?php echo $scriptsCount; ?></div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 12px; border-radius: 8px;">
                    <div style="font-size: 12px; opacity: 0.8; margin-bottom: 5px;">
                        <i class="fas fa-calendar-check"></i> Study Plans
                    </div>
                    <div style="font-size: 20px; font-weight: 700;"><?php echo $plansCount; ?></div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; position: relative; overflow: hidden;">
                    <div style="font-size: 12px; opacity: 0.8; margin-bottom: 5px;">
                        <i class="fas fa-fire-alt"></i> Login Streak
                    </div>
                    <div style="font-size: 20px; font-weight: 700;"><?php echo $loginStreakPoints; ?></div>
                    <?php if ($loginStreak > 0): ?>
                        <div style="font-size: 10px; opacity: 0.7; margin-top: 4px;">
                            <i class="fas fa-chart-line"></i> <?php echo $loginStreak; ?> day streak
                        </div>
                        <?php if ($loginStreak % 3 !== 0): ?>
                            <div style="font-size: 10px; opacity: 0.7; margin-top: 2px;">
                                <i class="fas fa-gift"></i> <?php echo $nextRewardAt; ?> more to earn point
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 12px; border-radius: 8px;">
                    <div style="font-size: 12px; opacity: 0.8; margin-bottom: 5px;">
                        <i class="fas fa-plus-circle"></i> Total
                    </div>
                    <div style="font-size: 20px; font-weight: 700;"><?php echo $activityScore; ?></div>
                </div>
            </div>
        </div>
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

<!-- Invite Friends Modal -->
<div id="inviteFriendsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; position: relative; max-height: 90vh; overflow-y: auto;">
        <button onclick="document.getElementById('inviteFriendsModal').style.display='none'"
                style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">
            &times;
        </button>

        <h2 style="margin-bottom: 20px; color: #1e293b;">
            <i class="fas fa-envelope-open-text" style="color: #0ea5e9;"></i> Invite Friends
        </h2>

        <form method="POST" action="/study-group/send-invite">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Friend's Email <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="friend_emails" required rows="3"
                          placeholder="Enter email addresses (separate multiple emails with commas)"
                          style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
                <small style="color: #64748b; display: block; margin-top: 5px;">Example: friend@example.com, buddy@example.com</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Friend's Name (Optional)
                </label>
                <input type="text" name="friend_name" maxlength="100"
                       placeholder="e.g., John"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Personal Message (Optional)
                </label>
                <textarea name="invite_message" rows="3"
                          placeholder="Add a personal message to your invitation..."
                          style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #0ea5e9;">
                <p style="margin: 0; color: #0369a1; font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Your friends will receive an email with a link to join StudySmart. Invites expire after 7 days.
                </p>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('inviteFriendsModal').style.display='none'"
                        style="padding: 10px 20px; background: #f1f5f9; color: #64748b; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); border: none;">
                    <i class="fas fa-paper-plane"></i> Send Invitation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('inviteFriendsModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
