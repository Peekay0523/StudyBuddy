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

    <!-- Right: Invite Friends Card 
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
    -->
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
    .stats {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 15px !important;
    }
}
/* === DECORATIONS ONLY (no layout changes) === */
.action.orange {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
}
.stats .card {
    position: relative !important;
    overflow: hidden !important;
}
.card h2,
.card h2::before,
.card h2::after {
    border-left: none !important;
    content: none !important;
}
/* Big soft circle (top right) */


/* Smaller shape (bottom right) */
.stats .card::before {
    content: "" !important;
    position: absolute !important;
    bottom: -30px !important;
    right: -20px !important;
    width: 100px !important;
    height: 100px !important;
    background: rgba(255, 255, 255, 0.1) !important;
    border-radius: 50% !important;
}

/* Optional glow line (like the vertical accent) */
.stats .card span.accent-line {
    position: absolute !important;
    left: 15px !important;
    top: 25px !important;
    width: 3px !important;
    height: 50px !important;
    background: rgba(255,255,255,0.6) !important;
    border-radius: 2px !important;
}
/* === BACKGROUND ICONS INSIDE CARDS === */

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

/* Make sure text stays above icon */
.stats .card p,
.stats .card h2,
.stats .card span {
    position: relative !important;
    z-index: 2 !important;
}
</style>

<!-- STATS -->
<section class="stats">
    <div class="card blue">
        <i class="fas fa-file-alt bg-icon"></i>
        <p>Scripts Uploaded</p>
        <h2><?php echo $scriptsCount ?? 0; ?></h2>
    </div>

    <div class="card orange">
        <i class="fas fa-layer-group bg-icon"></i>
        <p>Active Plans</p>
        <h2><?php echo $plansCount ?? 0; ?></h2>
    </div>

    <div class="card green">
        <i class="fas fa-chart-line bg-icon"></i>

        <p>Report Cards</p>
        <h2><?php echo $reportsCount ?? 0; ?></h2>
    </div>

    <div class="card purple">
        <i class="fas fa-brain bg-icon"></i>
        <p>Topics Mastered</p>
        <h2><?php echo $topicsCount ?? 0; ?></h2>
    </div>
</section>

<!-- Applications Section -->
<h2 class="section-title">My Applications</h2>

<section class="stats">
    <!-- Institution Applications Card -->
    <div class="card blue" style="cursor: pointer;" onclick="toggleApplicationsModal('institution')">
        <p><i class="fas fa-university"></i> Institution Applications</p>
        <h2><?php echo $institutionApplicationsCount ?? 0; ?></h2>
    </div>

    <!-- Bursary Applications Card -->
    <div class="card green" style="cursor: pointer;" onclick="toggleApplicationsModal('bursary')">
        <p><i class="fas fa-graduation-cap"></i> Bursary Applications</p>
        <h2><?php echo $bursaryApplicationsCount ?? 0; ?></h2>
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

    </div>
</section>

<!-- ACTIONS -->
<h2 class="section-title">What would you like to do?</h2>

<section class="actions actions-dock">
    <a href="/upload-script" class="action blue"><i class="fas fa-cloud-upload-alt icon-sm"></i><span class="action-label">Upload Script</span></a>
    <a href="/study-plan" class="action orange"><i class="fas fa-calendar-check icon-sm"></i><span class="action-label">Study Planner</span></a>
    <a href="/upload-report-card" class="action green"><i class="fas fa-compass icon-sm"></i><span class="action-label">Career Guide</span></a>
    <a href="/apply-for-me" class="action teal"><i class="fas fa-paper-plane icon-sm"></i><span class="action-label">Apply For Me</span></a>
    <a href="/ai-chat" class="action purple"><i class="fas fa-robot icon-sm"></i><span class="action-label">AI Assistant</span></a>
    <a href="/simulate" class="action yellow"><i class="fas fa-vial icon-sm"></i><span class="action-label">Simulate</span></a>
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

<!-- Bursary Applications Modal -->
<div id="bursaryApplicationsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 16px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; position: relative;">
        <button onclick="document.getElementById('bursaryApplicationsModal').style.display='none'" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">
            <i class="fas fa-times"></i>
        </button>
        <h2 style="margin-bottom: 20px; color: #1e293b;">
            <i class="fas fa-graduation-cap" style="color: #10b981;"></i> My Bursary Applications
        </h2>
        <div id="bursaryApplicationsList">
            <p style="text-align: center; color: #6b7280;">Loading...</p>
        </div>
        <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" onclick="document.getElementById('bursaryApplicationsModal').style.display='none'" style="padding: 10px 20px; background: #f1f5f9; color: #64748b; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Institution Applications Modal -->
<div id="institutionApplicationsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 16px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; position: relative;">
        <button onclick="document.getElementById('institutionApplicationsModal').style.display='none'" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">
            <i class="fas fa-times"></i>
        </button>
        <h2 style="margin-bottom: 20px; color: #1e293b;">
            <i class="fas fa-university" style="color: #3b82f6;"></i> My Institution Applications
        </h2>
        <div id="institutionApplicationsList">
            <p style="text-align: center; color: #6b7280;">Loading...</p>
        </div>
        <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" onclick="document.getElementById('institutionApplicationsModal').style.display='none'" style="padding: 10px 20px; background: #f1f5f9; color: #64748b; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('inviteFriendsModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
        var bursaryModal = document.getElementById('bursaryApplicationsModal');
        if (event.target == bursaryModal) {
            bursaryModal.style.display = 'none';
        }
        var institutionModal = document.getElementById('institutionApplicationsModal');
        if (event.target == institutionModal) {
            institutionModal.style.display = 'none';
        }
    }

    // Toggle applications modal
    function toggleApplicationsModal(type) {
        if (type === 'bursary') {
            document.getElementById('bursaryApplicationsModal').style.display = 'flex';
            loadBursaryApplications();
        } else if (type === 'institution') {
            document.getElementById('institutionApplicationsModal').style.display = 'flex';
            loadInstitutionApplications();
        }
    }

    // Load bursary applications
    async function loadBursaryApplications() {
        try {
            const response = await fetch('/api/get-bursary-applications');
            const data = await response.json();
            const container = document.getElementById('bursaryApplicationsList');
            if (container) {
                if (data.applications && data.applications.length > 0) {
                    container.innerHTML = data.applications.map(app => `
                        <div style="padding: 15px; background: #f8fafc; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #10b981;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 5px 0; color: #1f2937;">${escapeHtml(app.bursary_name)}</h4>
                                    <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;"><i class="fas fa-building"></i> ${escapeHtml(app.bursary_provider)}</p>
                                    <span class="badge ${app.application_status === 'approved' ? 'green' : (app.application_status === 'submitted' ? 'blue' : 'yellow')}">${app.application_status}</span>
                                    ${app.deadline ? `<p style="margin: 8px 0 0 0; font-size: 13px; color: #6b7280;"><i class="fas fa-calendar"></i> Deadline: ${new Date(app.deadline).toLocaleDateString()}</p>` : ''}
                                </div>
                                <button onclick="deleteBursaryApplication(${app.id})" style="background: #fee2e2; color: #ef4444; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p style="text-align: center; color: #6b7280; padding: 20px;">No bursary applications yet.</p>';
                }
            }
        } catch (error) {
            console.error('Error loading bursary applications:', error);
        }
    }

    // Load institution applications
    async function loadInstitutionApplications() {
        try {
            const response = await fetch('/api/get-institution-applications');
            const data = await response.json();
            const container = document.getElementById('institutionApplicationsList');
            if (container) {
                if (data.applications && data.applications.length > 0) {
                    const statusColors = {
                        'pending': '#f59e0b',
                        'in_progress': '#3b82f6',
                        'submitted': '#8b5cf6',
                        'submitted_to_institution': '#8b5cf6',
                        'accepted': '#10b981',
                        'completed': '#10b981',
                        'rejected': '#ef4444'
                    };
                    const statusLabels = {
                        'pending': 'Pending',
                        'in_progress': 'In Progress',
                        'submitted': 'Submitted',
                        'submitted_to_institution': 'Submitted',
                        'accepted': 'Accepted',
                        'completed': 'Completed',
                        'rejected': 'Rejected'
                    };
                    container.innerHTML = data.applications.map(app => {
                        const status = app.application_status || 'pending';
                        const color = statusColors[status] || '#6b7280';
                        const label = statusLabels[status] || status;
                        const isApplyForMe = app.source === 'apply_for_me';
                        return `
                        <div style="padding: 15px; background: #f8fafc; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid ${color};">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                                        <h4 style="margin: 0; color: #1f2937;">${escapeHtml(app.institution_name)}</h4>
                                        ${isApplyForMe ? '<span style="font-size: 10px; background: #ede9fe; color: #7c3aed; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Apply For Me</span>' : ''}
                                    </div>
                                    <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;"><i class="fas fa-graduation-cap"></i> ${escapeHtml(app.course_name || 'Not specified')} • ${app.institution_type || 'university'}</p>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; color: ${color}; background: ${color}15; padding: 3px 10px; border-radius: 12px;">
                                        <i class="fas ${status === 'completed' || status === 'accepted' ? 'fa-check-circle' : (status === 'rejected' ? 'fa-times-circle' : 'fa-clock')}"></i>
                                        ${label}
                                    </span>
                                    ${app.notes ? `<p style="margin: 8px 0 0 0; font-size: 12px; color: #6b7280; font-style: italic;">${escapeHtml(app.notes.substring(0, 100))}${app.notes.length > 100 ? '...' : ''}</p>` : ''}
                                </div>
                                ${!isApplyForMe ? `<button onclick="deleteInstitutionApplication(${app.id})" style="background: #fee2e2; color: #ef4444; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                    <i class="fas fa-trash"></i>
                                </button>` : ''}
                            </div>
                        </div>
                    `}).join('');
                } else {
                    container.innerHTML = '<p style="text-align: center; color: #6b7280; padding: 20px;">No institution applications yet.</p>';
                }
            }
        } catch (error) {
            console.error('Error loading institution applications:', error);
        }
    }

    // Delete bursary application
    async function deleteBursaryApplication(id) {
        if (!confirm('Are you sure you want to delete this bursary application?')) return;
        try {
            const response = await fetch('/api/delete-bursary-application', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 'id': id })
            });
            const result = await response.json();
            if (result.success) {
                loadBursaryApplications();
                // Refresh page to update counts
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Error: ' + (result.error || 'Failed to delete'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    }

    // Delete institution application
    async function deleteInstitutionApplication(id) {
        if (!confirm('Are you sure you want to delete this institution application?')) return;
        try {
            const response = await fetch('/api/delete-institution-application', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 'id': id })
            });
            const result = await response.json();
            if (result.success) {
                loadInstitutionApplications();
                // Refresh page to update counts
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Error: ' + (result.error || 'Failed to delete'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Handle hash-based navigation from search (e.g., /dashboard#open-bursary)
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash.replace('#', '');
        if (hash === 'open-bursary') {
            setTimeout(function() {
                toggleApplicationsModal('bursary');
                // Clear the hash so it doesn't auto-open on refresh
                if (history.replaceState) {
                    history.replaceState(null, '', window.location.pathname);
                }
            }, 500);
        } else if (hash === 'open-institution') {
            setTimeout(function() {
                toggleApplicationsModal('institution');
                // Clear the hash so it doesn't auto-open on refresh
                if (history.replaceState) {
                    history.replaceState(null, '', window.location.pathname);
                }
            }, 500);
        }
    });

    // Dashboard action dock: on touch / narrow screens, tapping a tile smoothly flies it to the top slot and
    // expands it (FLIP animation); tapping it again opens the page
    (function () {
        var dock = document.querySelector('.actions-dock');
        if (!dock) return;

        var tiles = Array.prototype.slice.call(dock.querySelectorAll('.action'));
        tiles.forEach(function (t, i) { t.dataset.dockOrder = i; });

        function narrow() { return window.innerWidth <= 768; }
        function touchOnly() {
            return typeof window.matchMedia === 'function' && window.matchMedia('(hover: none)').matches;
        }
        function reducedMotion() {
            return typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        }
        function restoreOrder() {
            tiles.slice().sort(function (a, b) {
                return a.dataset.dockOrder - b.dataset.dockOrder;
            }).forEach(function (t) { dock.appendChild(t); });
        }
        function cancelAnimations() {
            tiles.forEach(function (t) {
                if (t.getAnimations) {
                    t.getAnimations().forEach(function (a) { a.cancel(); });
                }
            });
        }
        function flip(mutate) {
            if (reducedMotion() || typeof tiles[0].animate !== 'function') {
                mutate();
                return;
            }
            cancelAnimations();
            dock.classList.add('dock-flipping');
            var first = tiles.map(function (t) { return t.getBoundingClientRect(); });
            mutate();
            void dock.offsetWidth; // force layout so the measurements below are final
            tiles.forEach(function (t, i) {
                var b = first[i];
                var a = t.getBoundingClientRect();
                var dx = (b.left + b.width / 2) - (a.left + a.width / 2);
                var dy = (b.top + b.height / 2) - (a.top + a.height / 2);
                var sx = a.width ? b.width / a.width : 1;
                var sy = a.height ? b.height / a.height : 1;
                if (Math.abs(dx) < 0.5 && Math.abs(dy) < 0.5 && Math.abs(sx - 1) < 0.01 && Math.abs(sy - 1) < 0.01) return;
                t.animate([
                    { transform: 'translate(' + dx + 'px,' + dy + 'px) scale(' + sx + ',' + sy + ')' },
                    { transform: 'none' }
                ], { duration: 480, easing: 'cubic-bezier(.22, 1, .36, 1)' });
            });
            clearTimeout(dock._flipTimer);
            dock._flipTimer = setTimeout(function () {
                dock.classList.remove('dock-flipping');
                cancelAnimations();
            }, 520);
        }
        function openTile(tile) {
            flip(function () {
                var open = dock.querySelector('.action.open');
                if (open) open.classList.remove('open');
                restoreOrder();
                tile.classList.add('open');
                dock.insertBefore(tile, dock.firstChild);
            });
        }
        function closeAll() {
            var open = dock.querySelector('.action.open');
            if (!open) return;
            flip(function () {
                open.classList.remove('open');
                restoreOrder();
            });
        }

        dock.addEventListener('click', function (e) {
            var tile = e.target.closest ? e.target.closest('.action') : null;
            if (!tile || !dock.contains(tile)) return;
            if (!narrow() && !touchOnly()) return;
            if (tile.classList.contains('open')) return;
            e.preventDefault();
            openTile(tile);
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest || !e.target.closest('.actions-dock')) closeAll();
        });

        window.addEventListener('resize', function () {
            if (!narrow()) closeAll();
        });
    })();
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
