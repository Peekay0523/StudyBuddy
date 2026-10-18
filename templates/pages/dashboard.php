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
<div id="plan-details-modal" class="sb-modal-overlay">
    <div class="sb-modal-box sb-modal-box--sm">
        <div style="position: relative; background: linear-gradient(135deg, <?php echo $planBadge === 'premium' ? '#fbbf24 0%, #f59e0b 100%' : ($planBadge === 'trial' ? '#10b981 0%, #059669 100%' : ($planBadge === 'basic' ? '#3b82f6 0%, #2563eb 100%' : '#6b7280 0%, #4b5563 100%')); ?>); color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
            <button onclick="closePlanDetailsModal()" class="sb-modal-close sb-modal-close--light" aria-label="Close">&times;</button>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding-right: 44px;">
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
    document.getElementById('plan-details-modal').classList.add('open');
}

function closePlanDetailsModal() {
    document.getElementById('plan-details-modal').classList.remove('open');
}

// Close any open modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.sb-modal-overlay.open').forEach(function(m) {
            m.classList.remove('open');
        });
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
                        <button onclick="openModal('inviteFriendsModal')" class="btn-primary" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); border: none; padding: 8px 16px; font-size: 13px;">
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

/* === Animated modals (plan details, invite friends, bursary / institution applications) === */
.sb-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(15, 23, 42, 0.5);
    -webkit-backdrop-filter: blur(0px);
    backdrop-filter: blur(0px);
    opacity: 0;
    visibility: hidden;
    transition: opacity .3s ease, backdrop-filter .3s ease, visibility 0s linear .3s;
}
.sb-modal-overlay.open {
    opacity: 1;
    visibility: visible;
    -webkit-backdrop-filter: blur(3px);
    backdrop-filter: blur(3px);
    transition: opacity .3s ease, backdrop-filter .3s ease, visibility 0s;
}
.sb-modal-box {
    position: relative;
    width: 100%;
    max-width: 600px;
    max-height: 85vh;
    overflow-y: auto;
    padding: 28px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
    transform: translateY(20px) scale(0.96);
    opacity: 0;
    transition: transform .38s cubic-bezier(.22, 1, .36, 1), opacity .3s ease;
}
.sb-modal-overlay.open .sb-modal-box {
    transform: translateY(0) scale(1);
    opacity: 1;
}
.sb-modal-box--sm {
    max-width: 500px;
}
.sb-modal-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 18px;
    padding-right: 48px;
    color: #1e293b;
    font-size: 20px;
}
.sb-modal-close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: #f1f5f9;
    color: #475569;
    font-size: 13px;
    line-height: 1;
    cursor: pointer;
    z-index: 3;
    transition: background .2s ease, color .2s ease, transform .2s ease;
}
.sb-modal-close:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: rotate(90deg);
}
.sb-modal-close:active {
    transform: scale(.92);
}
.sb-modal-close--light {
    background: rgba(255, 255, 255, .22);
    color: #fff;
}
.sb-modal-close--light:hover {
    background: rgba(255, 255, 255, .42);
    color: #fff;
}
.sb-modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 22px;
}
.sb-modal-btn-ghost {
    padding: 10px 20px;
    background: #f1f5f9;
    color: #64748b;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    font-family: inherit;
    transition: background .2s ease, color .2s ease;
}
.sb-modal-btn-ghost:hover {
    background: #e2e8f0;
    color: #334155;
}
@media (max-width: 576px) {
    .sb-modal-overlay { padding: 14px; }
    .sb-modal-box { padding: 22px 18px; max-height: 90vh; }
}
@media (prefers-reduced-motion: reduce) {
    .sb-modal-overlay, .sb-modal-box, .sb-modal-close { transition: none !important; }
}

/* === Hover: stat / score text grows smoothly and darkens a little === */
.stats .card p,
.stats .card h2,
.section-title,
.dash-grow-text {
    transform-origin: left center;
    transition: transform .3s cubic-bezier(.22, 1, .36, 1), color .3s ease, opacity .3s ease;
    will-change: transform;
}
.stats .card:hover p,
.stats .card:active p,
.stats .card.touch-grow p,
.stats .card:hover h2,
.stats .card:active h2,
.stats .card.touch-grow h2 {
    transform: scale(1.22);
    color: #dbe4f0;
}
.section-title:hover,
.section-title:active,
.section-title.touch-grow {
    transform: scale(1.12);
    color: #0f172a;
}
.dash-grow:hover .dash-grow-text,
.dash-grow:active .dash-grow-text,
.dash-grow.touch-grow .dash-grow-text {
    transform: scale(1.16);
    color: #dbe4f0;
}
.dash-grow:hover .dash-grow-text.dash-grow-strong,
.dash-grow:active .dash-grow-text.dash-grow-strong,
.dash-grow.touch-grow .dash-grow-text.dash-grow-strong {
    color: #cbd8ea;
}
@media (prefers-reduced-motion: reduce) {
    .stats .card p,
    .stats .card h2,
    .section-title,
    .dash-grow-text { transition: none !important; }
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
            <div class="dash-grow" style="flex: 1; min-width: 250px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                    <i class="fas fa-fire-alt" style="font-size: 32px; color: #fbbf24;"></i>
                    <h3 class="dash-grow-text" style="margin: 0; font-size: 22px;">Your Learning Score</h3>
                </div>
                <div style="display: flex; align-items: baseline; gap: 15px; margin-bottom: 10px;">
                    <span class="dash-grow-text dash-grow-strong" style="font-size: 48px; font-weight: 700; line-height: 1;"><?php echo $activityScore; ?></span>
                    <span class="dash-grow-text" style="font-size: 18px; opacity: 0.9;">points</span>
                </div>
                <p class="dash-grow-text" style="margin: 0; opacity: 0.9; font-size: 14px;">
                    <i class="fas fa-chart-line"></i> Keep learning to increase your score!
                </p>
            </div>

            <div class="dash-grow" style="flex: 1; min-width: 250px;">
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 12px; backdrop-filter: blur(10px);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span class="dash-grow-text" style="font-size: 14px; opacity: 0.9;">Activity Level</span>
                        <span class="dash-grow-text dash-grow-strong" style="font-size: 20px; font-weight: 700;"><?php echo $activityPercentage; ?>%</span>
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
<div id="inviteFriendsModal" class="sb-modal-overlay">
    <div class="sb-modal-box sb-modal-box--sm">
        <button onclick="closeModal('inviteFriendsModal')" class="sb-modal-close" aria-label="Close">&times;</button>

        <h2 class="sb-modal-title">
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

            <div class="sb-modal-actions">
                <button type="button" class="sb-modal-btn-ghost" onclick="closeModal('inviteFriendsModal')">
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
<div id="bursaryApplicationsModal" class="sb-modal-overlay">
    <div class="sb-modal-box">
        <button onclick="closeModal('bursaryApplicationsModal')" class="sb-modal-close" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
        <h2 class="sb-modal-title">
            <i class="fas fa-graduation-cap" style="color: #10b981;"></i> My Bursary Applications
        </h2>
        <div id="bursaryApplicationsList">
            <p style="text-align: center; color: #6b7280;">Loading...</p>
        </div>
        <div class="sb-modal-actions">
            <button type="button" class="sb-modal-btn-ghost" onclick="closeModal('bursaryApplicationsModal')">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Institution Applications Modal -->
<div id="institutionApplicationsModal" class="sb-modal-overlay">
    <div class="sb-modal-box">
        <button onclick="closeModal('institutionApplicationsModal')" class="sb-modal-close" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
        <h2 class="sb-modal-title">
            <i class="fas fa-university" style="color: #3b82f6;"></i> My Institution Applications
        </h2>
        <div id="institutionApplicationsList">
            <p style="text-align: center; color: #6b7280;">Loading...</p>
        </div>
        <div class="sb-modal-actions">
            <button type="button" class="sb-modal-btn-ghost" onclick="closeModal('institutionApplicationsModal')">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    // === Shared modal helpers: animate open/close ===
    function openModal(id) {
        var modal = document.getElementById(id);
        if (modal) modal.classList.add('open');
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        if (modal) modal.classList.remove('open');
    }

    // Close modal when clicking the backdrop (outside the dialog)
    document.querySelectorAll('.sb-modal-overlay').forEach(function(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('open');
            }
        });
    });

    // Toggle applications modal
    function toggleApplicationsModal(type) {
        if (type === 'bursary') {
            openModal('bursaryApplicationsModal');
            loadBursaryApplications();
        } else if (type === 'institution') {
            openModal('institutionApplicationsModal');
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

    // Touch devices: grow/darken the stat & score text while an item is pressed
    (function () {
        var touchOnly = typeof window.matchMedia === 'function' && window.matchMedia('(hover: none)').matches;
        if (!touchOnly) return;

        var targets = document.querySelectorAll('.stats .card, .section-title, .dash-grow');
        if (!targets.length) return;

        var timer = null;
        function clearAll() {
            Array.prototype.forEach.call(targets, function (el) { el.classList.remove('touch-grow'); });
        }
        Array.prototype.forEach.call(targets, function (el) {
            el.addEventListener('touchstart', function () {
                if (timer) { clearTimeout(timer); timer = null; }
                clearAll();
                el.classList.add('touch-grow');
            }, { passive: true });
            el.addEventListener('touchend', function () {
                timer = setTimeout(clearAll, 450);
            }, { passive: true });
            el.addEventListener('touchcancel', clearAll, { passive: true });
        });
    })();
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
