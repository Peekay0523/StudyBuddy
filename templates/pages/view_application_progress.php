<?php
$pageTitle = $pageTitle ?? 'Application Progress - StudySmart';
$currentPage = $currentPage ?? 'apply-for-me';

// Get banking details from settings (same as subscription checkout)
$bankingDetails = [
    'bank_name' => 'FNB',
    'account_type' => 'Current Account',
    'account_number' => '62123456789',
    'branch_code' => '250655',
    'account_holder' => 'StudySmart',
    'reference_instruction' => 'Use your username and application ID (e.g., john-APP5)',
    'email_address' => 'billing@studysmart.co.za',
    'activation_time' => '24-48 hours',
];

try {
    $db = Database::getInstance()->getConnection();
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetch();
    if ($result) {
        $storedSettings = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'banking_%'")->fetchAll();
        foreach ($storedSettings as $setting) {
            $key = str_replace('banking_', '', $setting['setting_key']);
            if (!empty($setting['setting_value'])) {
                $bankingDetails[$key] = $setting['setting_value'];
            }
        }
    }
} catch (Exception $e) {
    // Use defaults
}

include __DIR__ . '/../layouts/header.php';

$currentStatus = $submission['application_status'] ?? 'pending';
$docVerification = $submission['document_verification'] ?? 'pending';

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

<style>
@media (max-width: 768px) {
    .app-details-grid { grid-template-columns: 1fr !important; }
    .banking-grid { grid-template-columns: 1fr !important; }
    .app-header-flex { flex-direction: column !important; align-items: flex-start !important; }
    .proof-upload-flex { flex-direction: column !important; align-items: stretch !important; }
    .proof-upload-flex > div { min-width: auto !important; }
    .proof-upload-flex button { width: 100%; justify-content: center; }
    .app-info-row { flex-direction: column !important; gap: 2px !important; }
    .payment-badge-row { flex-direction: column !important; align-items: flex-start !important; gap: 8px !important; }
    .banking-section-box { padding: 16px !important; }
}
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;" class="app-header-flex">
    <div>
        <a href="/my-applications" style="color: #8b5cf6; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i class="fas fa-arrow-left"></i> My Applications
        </a>
        <h1 style="font-size: 28px; margin-bottom: 5px;">
            Application #<?php echo $submission['id']; ?>
        </h1>
        <p style="color: #6b7280; font-size: 15px;">
            Submitted <?php echo date('d M Y \a\t H:i', strtotime($submission['created_at'])); ?>
            &middot;
            <span class="badge" style="<?php echo $statusColors[$currentStatus] ?? ''; ?>">
                <?php echo $statusLabels[$currentStatus] ?? ucfirst($currentStatus); ?>
            </span>
        </p>
    </div>
</div>

<!-- Status Banner for Rejected -->
<?php if ($currentStatus === 'rejected'): ?>
    <div style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
            <i class="fas fa-exclamation-triangle" style="color: #dc2626; font-size: 20px;"></i>
            <h3 style="margin: 0; color: #991b1b; font-size: 18px;">Application Rejected</h3>
        </div>
        <?php if (!empty($submission['rejection_reason'])): ?>
            <p style="margin: 0; color: #991b1b; font-size: 14px;">
                <strong>Reason:</strong> <?php echo htmlspecialchars($submission['rejection_reason']); ?>
            </p>
        <?php endif; ?>
        <p style="margin: 10px 0 0; color: #991b1b; font-size: 13px;">
            Please review the feedback above, address the issues, and submit a new application.
        </p>
    </div>
<?php endif; ?>

<?php if (!empty($submission['bursary_name'])): ?>
<!-- Bursary Application Timeline -->
<div style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; border-left: 4px solid #7c3aed;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
        <div style="width: 42px; height: 42px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; flex-shrink: 0;">
            <i class="fas fa-hand-holding-dollar"></i>
        </div>
        <div>
            <h2 style="font-size: 18px; color: #1f2937; margin: 0;">
                <?php echo htmlspecialchars($submission['bursary_name']); ?>
            </h2>
            <?php if (!empty($submission['bursary_provider'])): ?>
                <p style="margin: 2px 0 0; font-size: 13px; color: #6b7280;">Provider: <?php echo htmlspecialchars($submission['bursary_provider']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $bursaryTimelineSteps = [
        ['key' => 'submitted', 'label' => 'Application Submitted', 'description' => 'Your bursary application has been received', 'icon' => 'fa-paper-plane'],
        ['key' => 'payment', 'label' => 'Payment Confirmed', 'description' => 'Service fee received', 'icon' => 'fa-credit-card'],
        ['key' => 'document_verification', 'label' => 'Documents Verified', 'description' => 'Your documents have been checked', 'icon' => 'fa-file-circle-check'],
        ['key' => 'in_progress', 'label' => 'Application Being Processed', 'description' => 'We are preparing your bursary application', 'icon' => 'fa-cogs'],
        ['key' => 'submitted_to_institution', 'label' => 'Submitted to Bursary Provider', 'description' => 'Your application has been sent to ' . htmlspecialchars($submission['bursary_name']), 'icon' => 'fa-building-columns'],
        ['key' => 'completed', 'label' => 'Application Completed', 'description' => 'Final response received from the bursary provider', 'icon' => 'fa-check-circle'],
    ];

    // Use overall application status for bursary timeline
    $bursaryCompletedSteps = [];
    if ($currentStatus === 'in_progress' || $currentStatus === 'submitted_to_institution' || $currentStatus === 'completed') {
        $bursaryCompletedSteps[] = 'submitted';
    }
    if ($currentStatus === 'in_progress' || $currentStatus === 'submitted_to_institution' || $currentStatus === 'completed') {
        $bursaryCompletedSteps[] = 'payment';
    }
    if ($currentStatus === 'in_progress' || $currentStatus === 'submitted_to_institution' || $currentStatus === 'completed') {
        $bursaryCompletedSteps[] = 'document_verification';
    }
    if ($currentStatus === 'submitted_to_institution' || $currentStatus === 'completed') {
        $bursaryCompletedSteps[] = 'in_progress';
    }
    if ($currentStatus === 'submitted_to_institution' || $currentStatus === 'completed') {
        $bursaryCompletedSteps[] = 'submitted_to_institution';
    }
    if ($currentStatus === 'completed') {
        $bursaryCompletedSteps[] = 'completed';
    }
    ?>

    <div style="position: relative; padding-left: 40px;">
        <?php foreach ($bursaryTimelineSteps as $index => $step):
            $isStepDone = in_array($step['key'], $bursaryCompletedSteps);
            $isRejected = $currentStatus === 'rejected' && !$isStepDone;
        ?>
        <div style="position: relative; padding-bottom: 30px; <?php echo (!$isStepDone && !$isRejected) ? 'opacity: 0.4;' : ''; ?>">
            <div style="position: absolute; left: -40px; top: 0; width: 28px; height: 28px; border-radius: 50%; 
                        background: <?php 
                            if ($isRejected) echo '#fee2e2';
                            elseif ($isStepDone) echo '#16a34a';
                            else echo '#e5e7eb';
                        ?>; 
                        display: flex; align-items: center; justify-content: center; color: <?php echo ($isStepDone && !$isRejected) ? 'white' : '#9ca3af'; ?>; font-size: 12px; z-index: 1;">
                <i class="fas <?php echo $isStepDone ? 'fa-check' : $step['icon']; ?>"></i>
            </div>
            <?php if ($index < count($bursaryTimelineSteps) - 1): ?>
                <div style="position: absolute; left: -27px; top: 30px; width: 2px; height: calc(100% - 10px); 
                            background: <?php echo ($isStepDone && !$isRejected) ? '#16a34a' : '#e5e7eb'; ?>;"></div>
            <?php endif; ?>
            <div>
                <div style="font-weight: 600; font-size: 15px; color: <?php echo $isStepDone ? '#1f2937' : '#374151'; ?>;">
                    <?php echo $step['label']; ?>
                </div>
                <div style="font-size: 13px; color: #6b7280; margin-top: 2px;">
                    <?php echo $step['description']; ?>
                </div>
                <?php if ($isStepDone): ?>
                    <div style="margin-top: 4px; font-size: 12px; color: #16a34a; font-weight: 500;">
                        <i class="fas fa-check-circle"></i> Completed
                    </div>
                <?php elseif ($isRejected): ?>
                    <div style="margin-top: 4px; font-size: 12px; color: #dc2626; font-weight: 500;">
                        <i class="fas fa-times-circle"></i> Waiting
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Required Documents for Bursary -->
<div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px;">
    <h3 style="font-size: 16px; color: #1f2937; margin-bottom: 16px;">
        <i class="fas fa-file-circle-check" style="color: #7c3aed;"></i> Required Documents
    </h3>
    <div style="display: grid; gap: 10px;">
        <?php
        $documents = [
            ['key' => 'id_copy_path', 'label' => 'ID Copy', 'icon' => 'fa-id-card'],
            ['key' => 'matric_cert_path', 'label' => 'Matric Certificate', 'icon' => 'fa-certificate'],
            ['key' => 'proof_res_path', 'label' => 'Proof of Residence', 'icon' => 'fa-home'],
            ['key' => 'cv_path', 'label' => 'CV', 'icon' => 'fa-file-lines', 'optional' => true],
        ];
        foreach ($documents as $doc):
            $hasDoc = !empty($submission[$doc['key']]);
            $isOptional = !empty($doc['optional']);
        ?>
        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; border: 1px solid <?php echo $hasDoc ? '#dcfce7' : ($isOptional ? '#f3f4f6' : '#fef3c7'); ?>; background: <?php echo $hasDoc ? '#f0fdf4' : ($isOptional ? '#fafafa' : '#fffbeb'); ?>;">
            <div style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: <?php echo $hasDoc ? '#dcfce7' : ($isOptional ? '#f3f4f6' : '#fef3c7'); ?>; color: <?php echo $hasDoc ? '#16a34a' : ($isOptional ? '#9ca3af' : '#f59e0b'); ?>; font-size: 14px; flex-shrink: 0;">
                <i class="fas <?php echo $hasDoc ? 'fa-check' : ($doc['icon']); ?>"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 14px; color: #1f2937;">
                    <?php echo $doc['label']; ?>
                    <?php if ($isOptional): ?>
                        <span style="font-weight: 400; color: #9ca3af; font-size: 12px;">(optional)</span>
                    <?php endif; ?>
                </div>
                <div style="font-size: 12px; color: <?php echo $hasDoc ? '#16a34a' : ($isOptional ? '#9ca3af' : '#f59e0b'); ?>;">
                    <?php echo $hasDoc ? 'Uploaded ✓' : ($isOptional ? 'Not uploaded' : 'Required — pending upload'); ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php else: ?>
<!-- Per-Institution Progress Timelines (for university applications) -->
<?php
$institutionTimelines = [
    1 => ['university' => $submission['university_preference_1'] ?? null, 'color' => '#6366f1', 'stepsField' => 'institution_1_steps_completed'],
    2 => ['university' => $submission['university_preference_2'] ?? null, 'color' => '#8b5cf6', 'stepsField' => 'institution_2_steps_completed'],
    3 => ['university' => $submission['university_preference_3'] ?? null, 'color' => '#a78bfa', 'stepsField' => 'institution_3_steps_completed'],
];

$timelineSteps = [
    ['key' => 'submitted', 'label' => 'Application Submitted', 'description' => 'Your application has been received', 'icon' => 'fa-paper-plane'],
    ['key' => 'payment', 'label' => 'Payment Confirmed', 'description' => 'Service fee received', 'icon' => 'fa-credit-card'],
    ['key' => 'document_verification', 'label' => 'Documents Verified', 'description' => 'Your documents have been checked', 'icon' => 'fa-file-circle-check'],
    ['key' => 'in_progress', 'label' => 'Application Being Processed', 'description' => 'We are preparing your application for this institution', 'icon' => 'fa-cogs'],
    ['key' => 'submitted_to_institution', 'label' => 'Submitted to Institution', 'description' => 'Your application has been sent to this university', 'icon' => 'fa-university'],
    ['key' => 'completed', 'label' => 'Application Completed', 'description' => 'Final response received from this university', 'icon' => 'fa-check-circle'],
];
?>

<?php foreach ($institutionTimelines as $instNum => $instInfo): ?>
    <?php if (!empty($instInfo['university'])): ?>
    <div style="background: white; border-radius: 16px; padding: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; border-left: 4px solid <?php echo $instInfo['color']; ?>;">
        <h2 style="font-size: 18px; color: #1f2937; margin-bottom: 24px;">
            <i class="fas fa-university" style="color: <?php echo $instInfo['color']; ?>;"></i> 
            Institution <?php echo $instNum; ?>: <?php echo htmlspecialchars($instInfo['university']); ?>
        </h2>

        <?php
        $instCompletedSteps = json_decode($submission[$instInfo['stepsField']] ?? '[]', true) ?? [];
        $instStatus = $submission["institution_{$instNum}_status"] ?? 'pending';
        ?>
        
        <!-- Institution Status Badge -->
        <div style="margin-bottom: 16px;">
            <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; padding: 4px 12px; border-radius: 20px; 
                         background: <?php 
                            $statusBgMap = ['pending' => '#f3f4f6', 'in_progress' => '#dbeafe', 'submitted_to_institution' => '#ede9fe', 'completed' => '#dcfce7', 'rejected' => '#fee2e2'];
                            echo $statusBgMap[$instStatus] ?? '#f3f4f6';
                         ?>; color: <?php 
                            $statusColorMap = ['pending' => '#374151', 'in_progress' => '#1d4ed8', 'submitted_to_institution' => '#7c3aed', 'completed' => '#16a34a', 'rejected' => '#dc2626'];
                            echo $statusColorMap[$instStatus] ?? '#374151';
                         ?>;">
                <?php 
                $statusIconMap = ['pending' => 'fa-clock', 'in_progress' => 'fa-spinner', 'submitted_to_institution' => 'fa-paper-plane', 'completed' => 'fa-check-circle', 'rejected' => 'fa-times-circle'];
                $statusLabelMap = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'submitted_to_institution' => 'Submitted', 'completed' => 'Completed', 'rejected' => 'Rejected'];
                ?>
                <i class="fas <?php echo $statusIconMap[$instStatus] ?? 'fa-clock'; ?>"></i>
                <?php echo $statusLabelMap[$instStatus] ?? ucfirst($instStatus); ?>
            </span>
        </div>

        <div style="position: relative; padding-left: 40px;">
            <?php foreach ($timelineSteps as $index => $step):
                $isStepDone = in_array($step['key'], $instCompletedSteps);
                $isRejected = $instStatus === 'rejected' && !$isStepDone;
            ?>
            <div style="position: relative; padding-bottom: 30px; <?php echo (!$isStepDone && !$isRejected) ? 'opacity: 0.4;' : ''; ?>">
                <div style="position: absolute; left: -40px; top: 0; width: 28px; height: 28px; border-radius: 50%; 
                            background: <?php 
                                if ($isRejected) echo '#fee2e2';
                                elseif ($isStepDone) echo '#16a34a';
                                else echo '#e5e7eb';
                            ?>; 
                            display: flex; align-items: center; justify-content: center; color: <?php echo ($isStepDone && !$isRejected) ? 'white' : '#9ca3af'; ?>; font-size: 12px; z-index: 1;">
                    <i class="fas <?php echo $isStepDone ? 'fa-check' : $step['icon']; ?>"></i>
                </div>
                <?php if ($index < count($timelineSteps) - 1): ?>
                    <div style="position: absolute; left: -27px; top: 30px; width: 2px; height: calc(100% - 10px); 
                                background: <?php echo ($isStepDone && !$isRejected) ? '#16a34a' : '#e5e7eb'; ?>;"></div>
                <?php endif; ?>
                <div>
                    <div style="font-weight: 600; font-size: 15px; color: <?php echo $isStepDone ? '#1f2937' : '#374151'; ?>;">
                        <?php echo $step['label']; ?>
                    </div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 2px;">
                        <?php echo $step['description']; ?>
                    </div>
                    <?php if ($isStepDone): ?>
                        <div style="margin-top: 4px; font-size: 12px; color: #16a34a; font-weight: 500;">
                            <i class="fas fa-check-circle"></i> Completed
                        </div>
                    <?php elseif ($isRejected): ?>
                        <div style="margin-top: 4px; font-size: 12px; color: #dc2626; font-weight: 500;">
                            <i class="fas fa-times-circle"></i> Waiting
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>

<!-- Payment / Banking Details Section -->
<?php if (($submission['payment_status'] ?? 'pending') === 'pending'): ?>
<div style="background: white; border-radius: 16px; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; border: 2px solid #fde68a;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
        <div style="width: 42px; height: 42px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; flex-shrink: 0;">
            <i class="fas fa-university"></i>
        </div>
        <div>
            <h2 style="font-size: 18px; color: #1f2937; margin: 0;">Service Fee Payment</h2>
            <p style="font-size: 13px; color: #92400e; margin: 2px 0 0;">Amount due: <strong>R<?php echo number_format($submission['payment_amount'] ?? 20, 2); ?></strong></p>
        </div>
    </div>

    <!-- Banking Details -->
    <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 22px; border-radius: 12px; border-left: 4px solid #0284c7; margin-bottom: 20px;" class="banking-section-box">
        <h4 style="margin-bottom: 16px; color: #0369a1; font-size: 15px;">
            <i class="fas fa-building-columns"></i> Bank Details for EFT Transfer
        </h4>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;" class="banking-grid">
            <div>
                <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 3px;">Bank Name</label>
                <div style="font-weight: 600; color: #1f2937; font-size: 15px;"><?php echo htmlspecialchars($bankingDetails['bank_name']); ?></div>
            </div>
            <div>
                <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 3px;">Account Type</label>
                <div style="font-weight: 600; color: #1f2937; font-size: 15px;"><?php echo htmlspecialchars($bankingDetails['account_type']); ?></div>
            </div>
            <div>
                <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 3px;">Account Number</label>
                <div style="font-weight: 600; color: #1f2937; font-size: 15px;"><?php echo htmlspecialchars($bankingDetails['account_number']); ?></div>
            </div>
            <div>
                <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 3px;">Branch Code</label>
                <div style="font-weight: 600; color: #1f2937; font-size: 15px;"><?php echo htmlspecialchars($bankingDetails['branch_code']); ?></div>
            </div>
        </div>

        <?php if (!empty($bankingDetails['account_holder'])): ?>
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 3px;">Account Holder</label>
            <div style="font-weight: 600; color: #1f2937; font-size: 15px;"><?php echo htmlspecialchars($bankingDetails['account_holder']); ?></div>
        </div>
        <?php endif; ?>

        <!-- Reference -->
        <div style="background: white; padding: 14px; border-radius: 8px; margin-bottom: 14px;">
            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Payment Reference (IMPORTANT)</label>
            <div style="font-weight: 700; color: #dc2626; font-size: 18px;">
                <?php echo htmlspecialchars(getCurrentUser()['username']); ?>-APP<?php echo $submission['id']; ?>
            </div>
            <p style="font-size: 13px; color: #6b7280; margin: 6px 0 0;">
                <?php echo htmlspecialchars($bankingDetails['reference_instruction']); ?>
            </p>
        </div>

        <!-- Amount -->
        <div style="background: white; padding: 14px; border-radius: 8px; margin-bottom: 14px;">
            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Amount to Pay</label>
            <div style="font-weight: 700; color: #16a34a; font-size: 22px;">
                R<?php echo number_format($submission['payment_amount'] ?? 20, 2); ?>
            </div>
            <p style="font-size: 13px; color: #6b7280; margin: 6px 0 0;">
                This is a <strong>service fee</strong> — not an application fee.
            </p>
        </div>

        <!-- Important Notes -->
        <div style="background: #fef3c7; padding: 14px; border-radius: 8px; border-left: 3px solid #f59e0b;">
            <p style="font-size: 13px; color: #92400e; margin: 0; line-height: 1.6;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important:</strong> After making payment, please email proof of payment to
                <strong><?php echo htmlspecialchars($bankingDetails['email_address']); ?></strong>.
                Your application will be processed within <?php echo htmlspecialchars($bankingDetails['activation_time']); ?> after payment is confirmed.
            </p>
        </div>
    </div>

    <!-- Proof of Payment Upload -->
    <?php if (empty($submission['proof_of_payment_path'])): ?>
    <div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 22px; margin-top: 16px;">
        <h4 style="margin: 0 0 8px 0; font-size: 15px; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-file-arrow-up" style="color: #8b5cf6;"></i> Upload Proof of Payment
        </h4>
        <p style="font-size: 13px; color: #64748b; margin: 0 0 14px;">Once you've made the EFT transfer, upload your proof of payment here for verification.</p>
        <form method="POST" action="/my-applications/<?php echo $submission['id']; ?>/upload-proof" enctype="multipart/form-data">
            <div style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;" class="proof-upload-flex">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">Proof of Payment (PDF, JPG, PNG - max 10MB)</label>
                    <input type="file" name="proof_of_payment" accept=".pdf,.jpg,.jpeg,.png" required
                           style="width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; background: #f8fafc;" />
                </div>
                <button type="submit" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; border: none; border-radius: 10px; padding: 12px 24px; font-weight: 600; cursor: pointer; white-space: nowrap; min-height: 44px;">
                    <i class="fas fa-upload"></i> Upload
                </button>
            </div>
        </form>
    </div>
    <?php elseif ($submission['payment_status'] === 'pending'): ?>
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-top: 16px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-clock" style="color: #16a34a; font-size: 18px;"></i>
            <div>
                <p style="margin: 0; font-weight: 600; color: #166534; font-size: 14px;">Proof of Payment Submitted</p>
                <p style="margin: 2px 0 0; font-size: 12px; color: #166534;">
                    Uploaded on <?php echo date('d M Y \a\t H:i', strtotime($submission['proof_of_payment_uploaded_at'])); ?>
                    &middot; Awaiting verification by our team.
                </p>
            </div>
        </div>
        <div style="margin-top: 10px; display: flex; gap: 8px;">
            <a href="/my-applications/<?php echo $submission['id']; ?>/view-proof" target="_blank"
               style="padding: 6px 12px; font-size: 12px; border: 1px solid #bbf7d0; border-radius: 6px; color: #166534; text-decoration: none; font-weight: 500;">
                <i class="fas fa-eye"></i> View
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Application Details -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;" class="app-details-grid">
    <!-- Left: Preferences -->
    <div>
        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 16px; color: #1f2937; margin-bottom: 16px;">
                <i class="fas fa-university" style="color: #8b5cf6;"></i> Your Preferences
            </h3>
            
            <?php
            // Institution status styling
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
            $instStatusBgs = [
                'pending' => '#f3f4f6',
                'in_progress' => '#dbeafe',
                'submitted_to_institution' => '#ede9fe',
                'completed' => '#dcfce7',
                'rejected' => '#fee2e2',
            ];
            ?>
            
            <div style="display: grid; gap: 14px;">
                <!-- Institution 1 -->
                <?php if (!empty($submission['university_preference_1'])): ?>
                <div style="padding: 12px; border-radius: 10px; border: 1px solid #e5e7eb; border-left: 4px solid <?php echo $instStatusColors[$submission['institution_1_status'] ?? 'pending']; ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label style="font-size: 11px; color: #6b7280; text-transform: uppercase; font-weight: 700;">1st Choice</label>
                        <?php
                        $i1Status = $submission['institution_1_status'] ?? 'pending';
                        ?>
                        <span style="font-size: 11px; font-weight: 600; color: <?php echo $instStatusColors[$i1Status]; ?>; background: <?php echo $instStatusBgs[$i1Status]; ?>; padding: 2px 8px; border-radius: 10px;">
                            <?php echo $instStatusLabels[$i1Status] ?? ucfirst($i1Status); ?>
                        </span>
                    </div>
                    <p style="margin: 2px 0 0; font-weight: 600; color: #1f2937; word-break: break-word; font-size: 14px;"><?php echo htmlspecialchars($submission['university_preference_1']); ?></p>
                    <?php if (!empty($submission['course_preference_1'])): ?>
                        <p style="margin: 2px 0 0; font-size: 13px; color: #6b7280;">Course: <?php echo htmlspecialchars($submission['course_preference_1']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($submission['institution_1_notes'])): ?>
                        <p style="margin: 6px 0 0; font-size: 12px; color: #6b7280; font-style: italic;">Note: <?php echo htmlspecialchars($submission['institution_1_notes']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($submission['institution_1_response'])): ?>
                        <p style="margin: 4px 0 0; font-size: 12px; color: #16a34a; font-weight: 500;">Response: <?php echo htmlspecialchars($submission['institution_1_response']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Institution 2 -->
                <?php if (!empty($submission['university_preference_2'])): ?>
                <div style="padding: 12px; border-radius: 10px; border: 1px solid #e5e7eb; border-left: 4px solid <?php echo $instStatusColors[$submission['institution_2_status'] ?? 'pending']; ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label style="font-size: 11px; color: #6b7280; text-transform: uppercase; font-weight: 700;">2nd Choice</label>
                        <?php
                        $i2Status = $submission['institution_2_status'] ?? 'pending';
                        ?>
                        <span style="font-size: 11px; font-weight: 600; color: <?php echo $instStatusColors[$i2Status]; ?>; background: <?php echo $instStatusBgs[$i2Status]; ?>; padding: 2px 8px; border-radius: 10px;">
                            <?php echo $instStatusLabels[$i2Status] ?? ucfirst($i2Status); ?>
                        </span>
                    </div>
                    <p style="margin: 2px 0 0; font-weight: 600; color: #1f2937; word-break: break-word; font-size: 14px;"><?php echo htmlspecialchars($submission['university_preference_2']); ?></p>
                    <?php if (!empty($submission['course_preference_2'])): ?>
                        <p style="margin: 2px 0 0; font-size: 13px; color: #6b7280;">Course: <?php echo htmlspecialchars($submission['course_preference_2']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($submission['institution_2_notes'])): ?>
                        <p style="margin: 6px 0 0; font-size: 12px; color: #6b7280; font-style: italic;">Note: <?php echo htmlspecialchars($submission['institution_2_notes']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($submission['institution_2_response'])): ?>
                        <p style="margin: 4px 0 0; font-size: 12px; color: #16a34a; font-weight: 500;">Response: <?php echo htmlspecialchars($submission['institution_2_response']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Institution 3 -->
                <?php if (!empty($submission['university_preference_3'])): ?>
                <div style="padding: 12px; border-radius: 10px; border: 1px solid #e5e7eb; border-left: 4px solid <?php echo $instStatusColors[$submission['institution_3_status'] ?? 'pending']; ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label style="font-size: 11px; color: #6b7280; text-transform: uppercase; font-weight: 700;">3rd Choice</label>
                        <?php
                        $i3Status = $submission['institution_3_status'] ?? 'pending';
                        ?>
                        <span style="font-size: 11px; font-weight: 600; color: <?php echo $instStatusColors[$i3Status]; ?>; background: <?php echo $instStatusBgs[$i3Status]; ?>; padding: 2px 8px; border-radius: 10px;">
                            <?php echo $instStatusLabels[$i3Status] ?? ucfirst($i3Status); ?>
                        </span>
                    </div>
                    <p style="margin: 2px 0 0; font-weight: 600; color: #1f2937; word-break: break-word; font-size: 14px;"><?php echo htmlspecialchars($submission['university_preference_3']); ?></p>
                    <?php if (!empty($submission['course_preference_3'])): ?>
                        <p style="margin: 2px 0 0; font-size: 13px; color: #6b7280;">Course: <?php echo htmlspecialchars($submission['course_preference_3']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($submission['institution_3_notes'])): ?>
                        <p style="margin: 6px 0 0; font-size: 12px; color: #6b7280; font-style: italic;">Note: <?php echo htmlspecialchars($submission['institution_3_notes']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($submission['institution_3_response'])): ?>
                        <p style="margin: 4px 0 0; font-size: 12px; color: #16a34a; font-weight: 500;">Response: <?php echo htmlspecialchars($submission['institution_3_response']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($submission['bursary_name'])): ?>
                <div style="padding-top: 10px; border-top: 1px solid #f3f4f6;">
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Bursary</label>
                    <p style="margin: 4px 0 0; font-weight: 600; color: #7c3aed;">
                        <i class="fas fa-scholarship"></i> <?php echo htmlspecialchars($submission['bursary_name']); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment Info -->
        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 24px;">
            <h3 style="font-size: 16px; color: #1f2937; margin-bottom: 16px;">
                <i class="fas fa-credit-card" style="color: #8b5cf6;"></i> Payment
            </h3>
            <div style="display: flex; align-items: center; gap: 12px;" class="payment-badge-row">
                <div style="font-size: 24px; font-weight: 700; color: #1f2937;">
                    R<?php echo number_format($submission['payment_amount'] ?? 20, 2); ?>
                </div>
                <span class="badge" style="<?php 
                    echo $submission['payment_status'] === 'paid' ? 'background: #dcfce7; color: #16a34a;' : 
                        ($submission['payment_status'] === 'refunded' ? 'background: #fee2e2; color: #dc2626;' : 'background: #fef3c7; color: #92400e;'); 
                ?>">
                    <?php echo ucfirst($submission['payment_status'] ?? 'pending'); ?>
                </span>
            </div>
            <?php if (($submission['payment_status'] ?? 'pending') === 'pending'): ?>
                <p style="margin: 12px 0 0; font-size: 13px; color: #6b7280;">
                    <i class="fas fa-info-circle"></i> Please complete payment for your application to be processed.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Document Status + Institution Response -->
    <div>
        <!-- Document Verification Status -->
        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 16px; color: #1f2937; margin-bottom: 16px;">
                <i class="fas fa-file-circle-check" style="color: #8b5cf6;"></i> Document Status
            </h3>
            <?php
            $docStatusColor = $docVerification === 'valid' ? '#dcfce7' : ($docVerification === 'invalid' ? '#fee2e2' : '#fef3c7');
            $docStatusTextColor = $docVerification === 'valid' ? '#16a34a' : ($docVerification === 'invalid' ? '#dc2626' : '#92400e');
            $docStatusLabel = $docVerification === 'valid' ? 'All Documents Verified' : ($docVerification === 'invalid' ? 'Documents Need Attention' : 'Pending Verification');
            ?>
            <div style="background: <?php echo $docStatusColor; ?>; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas <?php echo $docVerification === 'valid' ? 'fa-check-circle' : ($docVerification === 'invalid' ? 'fa-exclamation-circle' : 'fa-clock'); ?>" 
                       style="color: <?php echo $docStatusTextColor; ?>; font-size: 18px;"></i>
                    <span style="font-weight: 600; color: <?php echo $docStatusTextColor; ?>;"><?php echo $docStatusLabel; ?></span>
                </div>
            </div>

            <?php if ($docVerification === 'valid'): ?>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">
                    <i class="fas fa-shield-check" style="color: #16a34a;"></i> Your documents have been verified and are valid for submission.
                </p>
            <?php elseif ($docVerification === 'invalid'): ?>
                <div>
                    <?php if (!empty($submission['rejection_reason'])): ?>
                        <div style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                            <p style="margin: 0; font-size: 13px; color: #991b1b;">
                                <strong>Issue:</strong> <?php echo htmlspecialchars($submission['rejection_reason']); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <p style="font-size: 13px; color: #6b7280; margin: 0;">
                        <i class="fas fa-info-circle" style="color: #dc2626;"></i> Please review the feedback and submit a new application with corrected documents.
                    </p>
                </div>
            <?php else: ?>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">
                    <i class="fas fa-hourglass-half" style="color: #92400e;"></i> Your documents are being reviewed by our team. This typically takes 1-2 business days.
                </p>
            <?php endif; ?>
        </div>

        <!-- Institution Response -->
        <?php if (!empty($submission['institution_response'])): ?>
        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 24px;">
            <h3 style="font-size: 16px; color: #1f2937; margin-bottom: 16px;">
                <i class="fas fa-building" style="color: #8b5cf6;"></i> Institution Response
            </h3>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px;">
                <p style="margin: 0; font-size: 14px; white-space: pre-wrap; word-break: break-word; color: #1f2937;"><?php echo htmlspecialchars($submission['institution_response']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Application Info -->
        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 24px;">
            <h3 style="font-size: 16px; color: #1f2937; margin-bottom: 16px;">
                <i class="fas fa-info-circle" style="color: #8b5cf6;"></i> Application Info
            </h3>
            <div style="display: grid; gap: 10px; font-size: 14px;">
                <div style="display: flex; justify-content: space-between;" class="app-info-row">
                    <span style="color: #6b7280;">Application ID</span>
                    <strong>#<?php echo $submission['id']; ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between;" class="app-info-row">
                    <span style="color: #6b7280;">Submitted</span>
                    <span><?php echo date('d M Y', strtotime($submission['created_at'])); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;" class="app-info-row">
                    <span style="color: #6b7280;">Last Updated</span>
                    <span><?php echo date('d M Y', strtotime($submission['updated_at'])); ?></span>
                </div>
                <?php if (!empty($submission['submitted_to_institution_at'])): ?>
                <div style="display: flex; justify-content: space-between;" class="app-info-row">
                    <span style="color: #6b7280;">Sent to Institution</span>
                    <span><?php echo date('d M Y', strtotime($submission['submitted_to_institution_at'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>