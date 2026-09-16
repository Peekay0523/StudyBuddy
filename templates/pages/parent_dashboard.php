<?php
$pageTitle = 'Parent Dashboard - StudySmart';
$currentPage = 'parent-dashboard';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-header-grid">
    <div class="parent-header-text">
        <h1 class="title" style="margin-bottom: 10px;">
            Parental <span>Support</span> Portal
        </h1>
        <p class="subtitle" style="margin: 0;">
            Monitor your child's learning journey, track their mastered topics, 
            and manage their subscription for uninterrupted access.
        </p>
    </div>

    <!-- Subscription Card - Consistent with Student Dashboard -->
    <div id="plan-info-card" class="parent-plan-card" style="background: linear-gradient(135deg, <?php echo $planBadge === 'premium' ? '#fbbf24 0%, #f59e0b 100%' : ($planBadge === 'trial' ? '#10b981 0%, #059669 100%' : ($planBadge === 'basic' ? '#3b82f6 0%, #2563eb 100%' : '#6b7280 0%, #4b5563 100%')); ?>); color: white; padding: 20px 25px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); cursor: pointer; transition: all 0.3s ease;"
         onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 14px 50px rgba(0,0,0,0.2)'"
         onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 40px rgba(0,0,0,0.15)'"
         onclick="window.location.href='/subscription'">
        <div class="parent-plan-inner">
            <i class="fas fa-crown" style="font-size: 28px;"></i>
            <div style="min-width: 0;">
                <h2 style="margin: 0; font-size: 20px; color: white;"><?php echo htmlspecialchars($planName); ?> Plan</h2>
                <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 12px; font-size: 11px;">CLICK TO MANAGE</span>
            </div>
            <i class="fas fa-chevron-right" style="margin-left: auto; opacity: 0.8; flex-shrink: 0;"></i>
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
        <i class="fas fa-scholarship bg-icon"></i>
        <p>Bursaries Applied</p>
        <h2><?php echo $bursaryCount; ?></h2>
    </div>

    <div class="card" style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white;">
        <i class="fas fa-university bg-icon"></i>
        <p>Institutions Applied</p>
        <h2><?php echo $institutionCount; ?></h2>
    </div>
</section>

<style>
/* Parent Dashboard Mobile Responsiveness */

/* --- Desktop Base Styles --- */
.dashboard-header-grid {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 30px;
    align-items: center;
    margin-bottom: 30px;
}

.parent-plan-inner {
    display: flex;
    align-items: center;
    gap: 12px;
}

.parent-plan-card {
    width: 100%;
    box-sizing: border-box;
}

.parent-plan-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 50px rgba(0,0,0,0.2);
}

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

.parent-content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-top: 30px;
}

.parent-inner-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.parent-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(320px, 100%), 1fr));
    gap: 16px;
}

.parent-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.parent-section-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

/* --- Tablet & Mobile: 768px and below --- */
@media (max-width: 768px) {
    .dashboard-header-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .parent-plan-card {
        width: 100%;
    }

    .parent-plan-inner {
        width: 100%;
    }

    .stats {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .stats .card {
        padding: 15px !important;
    }

    .stats .card h2 {
        font-size: 24px !important;
    }

    .stats .card p {
        font-size: 13px !important;
    }

    .stats .card i.bg-icon {
        font-size: 35px !important;
    }

    .parent-content-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .parent-inner-grid {
        grid-template-columns: 1fr;
    }

    .parent-cards-grid {
        grid-template-columns: 1fr;
    }

    .parent-section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .parent-section-header .section-title {
        margin-bottom: 0;
    }

    .parent-section-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    /* Reduce section margins on mobile */
    .parent-content-grid > div > .section-title {
        font-size: 20px;
    }
}

/* --- Small Mobile: 480px and below --- */
@media (max-width: 480px) {
    .dashboard-header-grid .title {
        font-size: 22px;
    }

    .dashboard-header-grid .subtitle {
        font-size: 13px;
    }

    .parent-plan-card {
        padding: 15px 18px !important;
    }

    .parent-plan-card h2 {
        font-size: 17px !important;
    }

    .parent-plan-card .fa-crown {
        font-size: 22px !important;
    }

    .stats {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .stats .card {
        padding: 12px !important;
    }

    .stats .card h2 {
        font-size: 20px !important;
    }

    .stats .card p {
        font-size: 12px !important;
    }

    .stats .card i.bg-icon {
        font-size: 28px !important;
        top: 10px !important;
        right: 10px !important;
    }

    .section-title {
        font-size: 18px !important;
    }

    .parent-cards-grid {
        gap: 12px;
    }

    .parent-cards-grid > div {
        padding: 15px !important;
    }
}
</style>

<div class="parent-content-grid">
    <!-- Parental Tips & Info -->
    <div>
        <h2 class="section-title"><i class="fas fa-info-circle"></i> Quick Overview</h2>
        <div style="background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 20px;">
                Welcome to the parent portal. This dashboard provides a simplified view of your child's academic engagement. 
                Instead of detailed logs, we provide subject-based summaries of their mastered topics and overall activity.
            </p>
            <div class="parent-inner-grid">
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

<!-- Bursary Applications Section -->
<div style="margin-top: 30px;">
    <div class="parent-section-row">
        <h2 class="section-title" style="margin: 0;"><i class="fas fa-scholarship" style="color: #f59e0b;"></i> Bursary Applications</h2>
        <?php if ($bursaryCount > 0): ?>
            <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                <?php echo $bursaryCount; ?> Total
            </span>
        <?php endif; ?>
    </div>

    <?php if (empty($bursaryApplications)): ?>
        <div style="background: white; border-radius: 16px; padding: 40px 30px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="fas fa-scholarship" style="font-size: 28px; color: #f59e0b;"></i>
            </div>
            <h3 style="font-size: 18px; color: #1f2937; margin-bottom: 8px;">No Bursary Applications</h3>
            <p style="color: #6b7280; font-size: 14px; margin: 0;">Your child hasn't tracked any bursary applications yet.</p>
        </div>
    <?php else: ?>
        <div class="parent-cards-grid">
            <?php foreach ($bursaryApplications as $bursary):
                $statusColorMap = [
                    'pending' => ['bg' => '#f3f4f6', 'text' => '#374151'],
                    'approved' => ['bg' => '#dcfce7', 'text' => '#16a34a'],
                    'rejected' => ['bg' => '#fee2e2', 'text' => '#dc2626'],
                    'submitted' => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
                    'awarded' => ['bg' => '#dcfce7', 'text' => '#15803d'],
                    'declined' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                    'expired' => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
                ];
                $bStatus = $statusColorMap[$bursary['application_status']] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
            ?>
            <div style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; border-left: 4px solid #f59e0b; transition: transform 0.2s, box-shadow 0.2s;"
                 onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
                 onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                    <div>
                        <h3 style="font-size: 16px; color: #1f2937; margin: 0 0 4px;"><?php echo htmlspecialchars($bursary['bursary_name']); ?></h3>
                        <p style="font-size: 13px; color: #6b7280; margin: 0;"><?php echo htmlspecialchars($bursary['bursary_provider']); ?></p>
                    </div>
                    <span style="background: <?php echo $bStatus['bg']; ?>; color: <?php echo $bStatus['text']; ?>; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; white-space: nowrap;">
                        <?php echo ucfirst(str_replace('_', ' ', $bursary['application_status'])); ?>
                    </span>
                </div>
                <?php if (!empty($bursary['deadline'])): ?>
                    <div style="font-size: 12px; color: #9ca3af; margin-top: 8px;">
                        <i class="fas fa-calendar-alt"></i> Deadline: <?php echo date('d M Y', strtotime($bursary['deadline'])); ?>
                    </div>
                <?php endif; ?>
                <div style="font-size: 12px; color: #9ca3af; margin-top: 4px;">
                    <i class="fas fa-clock"></i> Applied: <?php echo date('d M Y', strtotime($bursary['applied_date'])); ?>
                </div>
                <?php if (!empty($bursary['notes'])): ?>
                    <p style="font-size: 13px; color: #64748b; margin: 10px 0 0; padding-top: 10px; border-top: 1px solid #f3f4f6;">
                        <?php echo htmlspecialchars($bursary['notes']); ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Institution Applications Section -->
<div style="margin-top: 30px;">
    <div class="parent-section-row">
        <h2 class="section-title" style="margin: 0;"><i class="fas fa-university" style="color: #6366f1;"></i> Institution Applications</h2>
        <?php if ($institutionCount > 0): ?>
            <span style="background: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                <?php echo $institutionCount; ?> Total
            </span>
        <?php endif; ?>
    </div>

    <?php if (empty($institutionApplications)): ?>
        <div style="background: white; border-radius: 16px; padding: 40px 30px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #e0e7ff, #c7d2fe); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="fas fa-university" style="font-size: 28px; color: #6366f1;"></i>
            </div>
            <h3 style="font-size: 18px; color: #1f2937; margin-bottom: 8px;">No Institution Applications</h3>
            <p style="color: #6b7280; font-size: 14px; margin: 0;">Your child hasn't tracked any institution applications yet.</p>
        </div>
    <?php else: ?>
        <div class="parent-cards-grid">
            <?php foreach ($institutionApplications as $inst):
                $statusColorMap2 = [
                    'pending' => ['bg' => '#f3f4f6', 'text' => '#374151'],
                    'accepted' => ['bg' => '#dcfce7', 'text' => '#16a34a'],
                    'rejected' => ['bg' => '#fee2e2', 'text' => '#dc2626'],
                    'submitted' => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
                    'waitlisted' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                    'deferred' => ['bg' => '#ede9fe', 'text' => '#7c3aed'],
                    'enrolled' => ['bg' => '#dcfce7', 'text' => '#15803d'],
                    'declined' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                ];
                $iStatus = $statusColorMap2[$inst['application_status']] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
            ?>
            <div style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; border-left: 4px solid #6366f1; transition: transform 0.2s, box-shadow 0.2s;"
                 onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
                 onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                    <div>
                        <h3 style="font-size: 16px; color: #1f2937; margin: 0 0 4px;"><?php echo htmlspecialchars($inst['institution_name']); ?></h3>
                        <?php if (!empty($inst['course_name'])): ?>
                            <p style="font-size: 13px; color: #6b7280; margin: 0;"><i class="fas fa-book"></i> <?php echo htmlspecialchars($inst['course_name']); ?></p>
                        <?php endif; ?>
                    </div>
                    <span style="background: <?php echo $iStatus['bg']; ?>; color: <?php echo $iStatus['text']; ?>; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; white-space: nowrap;">
                        <?php echo ucfirst(str_replace('_', ' ', $inst['application_status'])); ?>
                    </span>
                </div>
                <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px;">
                    <?php if (!empty($inst['institution_type'])): ?>
                        <span style="font-size: 12px; color: #6b7280; background: #f3f4f6; padding: 2px 8px; border-radius: 8px;">
                            <i class="fas fa-building"></i> <?php echo ucfirst(htmlspecialchars($inst['institution_type'])); ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($inst['deadline'])): ?>
                        <span style="font-size: 12px; color: #9ca3af;">
                            <i class="fas fa-calendar-alt"></i> Deadline: <?php echo date('d M Y', strtotime($inst['deadline'])); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div style="font-size: 12px; color: #9ca3af; margin-top: 8px;">
                    <i class="fas fa-clock"></i> Applied: <?php echo date('d M Y', strtotime($inst['applied_date'])); ?>
                </div>
                <?php if (!empty($inst['notes'])): ?>
                    <p style="font-size: 13px; color: #64748b; margin: 10px 0 0; padding-top: 10px; border-top: 1px solid #f3f4f6;">
                        <?php echo htmlspecialchars($inst['notes']); ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Apply For Me Submissions Section -->
<?php if ($applyForMeCount > 0): ?>
<div style="margin-top: 30px;">
    <div class="parent-section-row">
        <h2 class="section-title" style="margin: 0;"><i class="fas fa-paper-plane" style="color: #8b5cf6;"></i> Platform Applications</h2>
        <span style="background: #ede9fe; color: #7c3aed; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
            <?php echo $applyForMeCount; ?> Total
        </span>
    </div>

    <div style="display: grid; gap: 16px;">
        <?php
        $statusLabels2 = [
            'pending' => 'Pending Review',
            'in_progress' => 'In Progress',
            'submitted_to_institution' => 'Submitted to Institution',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            'submitted' => 'Submitted',
        ];
        $statusColors2 = [
            'pending' => ['#f3f4f6', '#374151'],
            'in_progress' => ['#dbeafe', '#1d4ed8'],
            'submitted_to_institution' => ['#ede9fe', '#7c3aed'],
            'completed' => ['#dcfce7', '#16a34a'],
            'rejected' => ['#fee2e2', '#dc2626'],
            'submitted' => ['#dbeafe', '#2563eb'],
        ];
        foreach ($applyForMeSubmissions as $sub):
            $subStatus = $sub['application_status'] ?? 'pending';
            $subColors = $statusColors2[$subStatus] ?? ['#f3f4f6', '#374151'];
        ?>
        <div style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; transition: transform 0.2s, box-shadow 0.2s;"
             onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
             onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="font-size: 12px; color: #8b5cf6; font-weight: 700;">#<?php echo $sub['id']; ?></span>
                        <span style="background: <?php echo $subColors[0]; ?>; color: <?php echo $subColors[1]; ?>; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                            <?php echo $statusLabels2[$subStatus] ?? ucfirst(str_replace('_', ' ', $subStatus)); ?>
                        </span>
                        <?php if (($sub['payment_status'] ?? '') === 'paid'): ?>
                            <span style="background: #dcfce7; color: #16a34a; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">Paid</span>
                        <?php endif; ?>
                    </div>
                    <h3 style="font-size: 16px; color: #1f2937; margin: 0 0 4px;">
                        <?php echo htmlspecialchars($sub['university_preference_1'] ?? 'University'); ?>
                    </h3>
                    <p style="color: #6b7280; margin: 0; font-size: 13px;">
                        Course: <?php echo htmlspecialchars($sub['course_preference_1'] ?? 'N/A'); ?>
                    </p>
                    <?php if (!empty($sub['bursary_name'])): ?>
                        <p style="color: #7c3aed; margin: 4px 0 0; font-size: 12px; font-weight: 500;">
                            <i class="fas fa-scholarship"></i> <?php echo htmlspecialchars($sub['bursary_name']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 12px; color: #9ca3af;">
                        Submitted <?php echo date('d M Y', strtotime($sub['created_at'])); ?>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                <div style="display: flex; gap: 8px; align-items: center;">
                    <?php
                    $progressSteps = ['submitted', 'in_progress', 'submitted_to_institution', 'completed'];
                    $progressIndex = array_search($subStatus, $progressSteps);
                    if ($progressIndex === false) $progressIndex = 0;
                    $progressPercent = (($progressIndex + 1) / count($progressSteps)) * 100;
                    ?>
                    <div style="flex: 1; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; width: <?php echo $progressPercent; ?>%; background: linear-gradient(90deg, #8b5cf6, #7c3aed); border-radius: 3px;"></div>
                    </div>
                    <span style="font-size: 12px; color: #6b7280; white-space: nowrap;"><?php echo round($progressPercent); ?>%</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
