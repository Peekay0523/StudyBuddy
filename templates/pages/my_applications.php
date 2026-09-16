<?php
$pageTitle = $pageTitle ?? 'My Applications - StudySmart';
$currentPage = $currentPage ?? 'apply-for-me';
include __DIR__ . '/../layouts/header.php';

$statusColors = [
    'pending' => 'background: #f3f4f6; color: #374151;',
    'in_progress' => 'background: #dbeafe; color: #1d4ed8;',
    'submitted_to_institution' => 'background: #ede9fe; color: #7c3aed;',
    'completed' => 'background: #dcfce7; color: #16a34a;',
    'rejected' => 'background: #fee2e2; color: #dc2626;',
];
$statusLabels = [
    'pending' => 'Pending Review',
    'in_progress' => 'In Progress',
    'submitted_to_institution' => 'Submitted to Institution',
    'completed' => 'Completed',
    'rejected' => 'Rejected',
];
?>

<div class="page-header" style="margin-bottom: 30px;">
    <h1 style="font-size: 32px; margin-bottom: 10px;">My Applications</h1>
    <p style="color: #6b7280; max-width: 760px; line-height: 1.7em;">Track the progress of your university and bursary applications submitted through Apply For Me.</p>
</div>

<div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
    <a href="/apply-for-me" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border-radius: 10px; text-decoration: none; font-weight: 600; transition: transform 0.2s;">
        <i class="fas fa-plus"></i> Submit New Application
    </a>
</div>

<?php if (empty($submissions)): ?>
    <div style="background: white; border-radius: 16px; padding: 60px 30px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #ede9fe, #e0e7ff); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
            <i class="fas fa-paper-plane" style="font-size: 36px; color: #8b5cf6;"></i>
        </div>
        <h2 style="font-size: 22px; color: #1f2937; margin-bottom: 10px;">No Applications Yet</h2>
        <p style="color: #6b7280; max-width: 400px; margin: 0 auto 25px; line-height: 1.6;">
            You haven't submitted any applications yet. Use Apply For Me to let us submit university or bursary applications on your behalf.
        </p>
        <a href="/apply-for-me" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 30px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border-radius: 10px; text-decoration: none; font-weight: 600;">
            <i class="fas fa-paper-plane"></i> Apply Now
        </a>
    </div>
<?php else: ?>
    <div style="display: grid; gap: 20px;">
        <?php foreach ($submissions as $sub):
            $currentStatus = $sub['application_status'] ?? 'pending';
        ?>
        <a href="/my-applications/<?php echo $sub['id']; ?>" style="display: block; background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s; border: 1px solid #e5e7eb;" 
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <span style="font-size: 12px; color: #8b5cf6; font-weight: 700;">#<?php echo $sub['id']; ?></span>
                        <span class="badge" style="<?php echo $statusColors[$currentStatus] ?? ''; ?>">
                            <?php echo $statusLabels[$currentStatus] ?? ucfirst($currentStatus); ?>
                        </span>
                        <?php if ($sub['payment_status'] === 'paid'): ?>
                            <span class="badge" style="background: #dcfce7; color: #16a34a;">Paid</span>
                        <?php else: ?>
                            <span class="badge" style="background: #fef3c7; color: #92400e;">Payment Pending</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($sub['bursary_name'])): ?>
                        <!-- Bursary Application -->
                        <h3 style="font-size: 18px; color: #1f2937; margin: 0 0 6px;">
                            <i class="fas fa-hand-holding-dollar" style="color: #7c3aed; font-size: 15px;"></i>
                            <?php echo htmlspecialchars($sub['bursary_name']); ?>
                        </h3>
                        <p style="color: #7c3aed; margin: 0; font-size: 13px; font-weight: 500;">
                            Bursary Application
                            <?php if (!empty($sub['bursary_provider'])): ?>
                                — Provider: <?php echo htmlspecialchars($sub['bursary_provider']); ?>
                            <?php endif; ?>
                        </p>
                    <?php elseif (!empty($sub['institution_name'])): ?>
                        <!-- Institution Application -->
                        <h3 style="font-size: 18px; color: #1f2937; margin: 0 0 6px;">
                            <i class="fas fa-university" style="color: #7c3aed; font-size: 15px;"></i>
                            <?php echo htmlspecialchars($sub['institution_name']); ?>
                        </h3>
                        <p style="color: #7c3aed; margin: 0; font-size: 13px; font-weight: 500;">
                            Institution Application
                            <?php if (!empty($sub['institution_type'])): ?>
                                — Type: <?php echo htmlspecialchars($sub['institution_type']); ?>
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <!-- University Application -->
                        <h3 style="font-size: 18px; color: #1f2937; margin: 0 0 6px;">
                            <?php echo htmlspecialchars($sub['university_preference_1'] ?? 'University'); ?>
                        </h3>
                        <p style="color: #6b7280; margin: 0; font-size: 14px;">
                            Course: <?php echo htmlspecialchars($sub['course_preference_1'] ?? 'N/A'); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 12px; color: #9ca3af;">
                        Submitted <?php echo date('d M Y', strtotime($sub['created_at'])); ?>
                    </div>
                    <div style="margin-top: 8px; color: #8b5cf6; font-size: 14px; font-weight: 600;">
                        View Details <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #f3f4f6;">
                <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
                    <?php
                    // Calculate progress based on per-institution statuses
                    // Only count institutions that actually have a university preference
                    $instStatuses = [];
                    if (!empty($sub['university_preference_1'])) {
                        $instStatuses[] = $sub['institution_1_status'] ?? 'pending';
                    }
                    if (!empty($sub['university_preference_2'])) {
                        $instStatuses[] = $sub['institution_2_status'] ?? 'pending';
                    }
                    if (!empty($sub['university_preference_3'])) {
                        $instStatuses[] = $sub['institution_3_status'] ?? 'pending';
                    }
                    
                    if (!empty($instStatuses)) {
                        $completedCount = count(array_filter($instStatuses, fn($s) => $s === 'completed'));
                        $totalInstitutions = count($instStatuses);
                        $progressPercent = ($completedCount / $totalInstitutions) * 100;
                    } else {
                        // Fallback to old method if no per-institution status
                        $progressSteps = ['submitted', 'in_progress', 'submitted_to_institution', 'completed'];
                        $progressIndex = array_search($currentStatus, $progressSteps);
                        if ($progressIndex === false) $progressIndex = 0;
                        $progressPercent = (($progressIndex + 1) / count($progressSteps)) * 100;
                    }
                    ?>
                    <div style="flex: 1; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; width: <?php echo $progressPercent; ?>%; background: linear-gradient(90deg, #8b5cf6, #7c3aed); border-radius: 3px; transition: width 0.5s;"></div>
                    </div>
                    <span style="font-size: 12px; color: #6b7280; white-space: nowrap;"><?php echo round($progressPercent); ?>%</span>
                </div>
                
                <!-- Per-institution status indicators -->
                <?php if (!empty($instStatuses) && count($instStatuses) > 1): ?>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;">
                    <?php
                    $instLabels = [
                        $sub['university_preference_1'] ?? null,
                        $sub['university_preference_2'] ?? null,
                        $sub['university_preference_3'] ?? null,
                    ];
                    $instStatusColors = [
                        'pending' => '#9ca3af',
                        'in_progress' => '#3b82f6',
                        'submitted_to_institution' => '#8b5cf6',
                        'completed' => '#16a34a',
                        'rejected' => '#dc2626',
                    ];
                    $instStatusLabels = [
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'submitted_to_institution' => 'Submitted',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ];
                    ?>
                    <?php for ($i = 0; $i < 3; $i++):
                        $instStatus = $sub['institution_' . ($i + 1) . '_status'] ?? null;
                        $instLabel = $instLabels[$i] ?? null;
                        if (empty($instStatus) || empty($instLabel)) continue;
                        $color = $instStatusColors[$instStatus] ?? '#9ca3af';
                        $label = $instStatusLabels[$instStatus] ?? ucfirst($instStatus);
                    ?>
                    <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: <?php echo $color; ?>; font-weight: 500;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: <?php echo $color; ?>;"></span>
                        <?php echo htmlspecialchars(substr($instLabel, 0, 15)); ?>: <?php echo $label; ?>
                    </span>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>