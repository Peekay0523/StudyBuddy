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

<!-- INVITE FRIENDS -->
<section style="margin: 30px 0;">
    <div class="feature-card" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: none; box-shadow: 0 2px 8px rgba(14, 165, 233, 0.15);">
        <div style="display: flex; align-items: flex-start; gap: 20px;">
            <div style="flex-shrink: 0;">
                <i class="fas fa-user-plus" style="font-size: 40px; color: #0ea5e9;"></i>
            </div>
            <div style="flex: 1;">
                <h3 style="margin: 0 0 10px 0; color: #0369a1; font-size: 20px;">
                    <i class="fas fa-gift"></i> Invite Friends to StudySmart
                </h3>
                <p style="margin: 0 0 15px 0; color: #0c4a6e; font-size: 14px; line-height: 1.6;">
                    Get your friends to join StudySmart and study together! Send them an invitation via email.
                </p>
                <button onclick="document.getElementById('inviteFriendsModal').style.display='flex'" class="btn-primary" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); border: none;">
                    <i class="fas fa-envelope"></i> Invite Friends
                </button>
                <a href="/invites" style="margin-left: 10px; color: #0369a1; text-decoration: none; font-weight: 500;">
                    View Sent Invites <i class="fas fa-arrow-right"></i>
                </a>
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
