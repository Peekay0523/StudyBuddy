<?php
include __DIR__ . '/../../layouts/admin_header.php';

$statusColors = [
    'pending' => 'background: #f3f4f6; color: #374151;',
    'in_progress' => 'background: #dbeafe; color: #1d4ed8;',
    'submitted_to_institution' => 'background: #ede9fe; color: #7c3aed;',
    'completed' => 'background: #dcfce7; color: #16a34a;',
    'rejected' => 'background: #fee2e2; color: #dc2626;',
];
$statusLabels = [
    'pending' => 'Pending',
    'in_progress' => 'In Progress',
    'submitted_to_institution' => 'Submitted to Institution',
    'completed' => 'Completed',
    'rejected' => 'Rejected',
];

$verificationColors = [
    'pending' => 'background: #fef3c7; color: #92400e;',
    'valid' => 'background: #dcfce7; color: #16a34a;',
    'invalid' => 'background: #fee2e2; color: #dc2626;',
];

$currentStatus = $submission['application_status'] ?? 'pending';
$docVerification = $submission['document_verification'] ?? 'pending';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-paper-plane" style="color: #8b5cf6;"></i> Application #<?php echo $submission['id']; ?>
        </h1>
        <p style="color: #6b7280; font-size: 15px;">
            Submitted <?php echo date('d M Y \a\t H:i', strtotime($submission['created_at'])); ?>
            &middot; 
            <span class="badge" style="<?php echo $statusColors[$currentStatus] ?? ''; ?>">
                <?php echo $statusLabels[$currentStatus] ?? ucfirst($currentStatus); ?>
            </span>
        </p>
    </div>
    <a href="/admin/apply-for-me" class="btn-secondary-modern">
        <i class="fas fa-arrow-left"></i> Back to Applications
    </a>
</div>

<!-- Data Privacy Notice -->
<div style="background: #fefce8; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 16px; margin-bottom: 24px;">
    <p style="margin: 0; color: #92400e; font-size: 13px;">
        <i class="fas fa-shield-halved"></i> <strong>Data Privacy (POPIA):</strong> All document access is logged for audit purposes. 
        Student documents must not be downloaded, copied, or shared outside of this system.
    </p>
</div>

<style>
    @media (max-width: 900px) {
        .admin-detail-grid { grid-template-columns: 1fr !important; }
    }
</style>

<div class="admin-detail-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    
    <!-- Left Column: Student Info + Documents -->
    <div>
        <!-- Student Information -->
        <div class="admin-section">
            <h3><i class="fas fa-user"></i> Student Information</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Full Name</label>
                    <p style="margin: 4px 0 0; font-weight: 600;"><?php echo htmlspecialchars($submission['full_name'] . ' ' . $submission['surname']); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">ID Number</label>
                    <p style="margin: 4px 0 0; font-weight: 600;"><?php echo htmlspecialchars($submission['id_number']); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Email</label>
                    <p style="margin: 4px 0 0;"><?php echo htmlspecialchars($submission['email'] ?? ''); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Phone</label>
                    <p style="margin: 4px 0 0;"><?php echo htmlspecialchars($submission['phone'] ?? ''); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Date of Birth</label>
                    <p style="margin: 4px 0 0;"><?php echo htmlspecialchars($submission['date_of_birth'] ?? ''); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Gender</label>
                    <p style="margin: 4px 0 0;"><?php echo htmlspecialchars($submission['gender'] ?? ''); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Citizenship</label>
                    <p style="margin: 4px 0 0;"><?php echo htmlspecialchars($submission['citizenship'] ?? ''); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">School</label>
                    <p style="margin: 4px 0 0;"><?php echo htmlspecialchars($submission['school_name'] ?? ''); ?></p>
                </div>
                <div style="grid-column: span 2;">
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Home Address</label>
                    <p style="margin: 4px 0 0;"><?php echo htmlspecialchars($submission['home_address'] ?? ''); ?></p>
                </div>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="admin-section">
            <h3><i class="fas fa-graduation-cap"></i> Academic Information</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Matric Year</label>
                    <p style="margin: 4px 0 0; font-weight: 600;"><?php echo htmlspecialchars($submission['matric_year'] ?? ''); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">APS Score</label>
                    <p style="margin: 4px 0 0; font-weight: 600;"><?php echo htmlspecialchars($submission['aps_score'] ?? 'N/A'); ?></p>
                </div>
                <div style="grid-column: span 2;">
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Grade 12 Results</label>
                    <p style="margin: 4px 0 0; white-space: pre-wrap; background: #f9fafb; padding: 10px; border-radius: 6px; font-size: 13px;"><?php echo htmlspecialchars($submission['grade_12_results'] ?? 'N/A'); ?></p>
                </div>
                <div style="grid-column: span 2;">
                    <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Subjects</label>
                    <p style="margin: 4px 0 0; white-space: pre-wrap; background: #f9fafb; padding: 10px; border-radius: 6px; font-size: 13px;"><?php echo htmlspecialchars($submission['subjects'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>

        <!-- University/Course Preferences with Per-Institution Status -->
        <div class="admin-section">
            <h3><i class="fas fa-university"></i> Preferences & Institution Status</h3>
            
            <?php
            // Define institution statuses
            $institutionStatusColors = [
                'pending' => 'background: #f3f4f6; color: #374151;',
                'in_progress' => 'background: #dbeafe; color: #1d4ed8;',
                'submitted_to_institution' => 'background: #ede9fe; color: #7c3aed;',
                'completed' => 'background: #dcfce7; color: #16a34a;',
                'rejected' => 'background: #fee2e2; color: #dc2626;',
            ];
            $institutionStatusLabels = [
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'submitted_to_institution' => 'Submitted',
                'completed' => 'Completed',
                'rejected' => 'Rejected',
            ];
            
            // Build preferences array
            $preferences = [
                1 => [
                    'university' => $submission['university_preference_1'] ?? null,
                    'course' => $submission['course_preference_1'] ?? null,
                    'status' => $submission['institution_1_status'] ?? 'pending',
                    'notes' => $submission['institution_1_notes'] ?? null,
                    'response' => $submission['institution_1_response'] ?? null,
                    'submitted_at' => $submission['institution_1_submitted_at'] ?? null,
                    'completed_at' => $submission['institution_1_completed_at'] ?? null,
                ],
                2 => [
                    'university' => $submission['university_preference_2'] ?? null,
                    'course' => $submission['course_preference_2'] ?? null,
                    'status' => $submission['institution_2_status'] ?? 'pending',
                    'notes' => $submission['institution_2_notes'] ?? null,
                    'response' => $submission['institution_2_response'] ?? null,
                    'submitted_at' => $submission['institution_2_submitted_at'] ?? null,
                    'completed_at' => $submission['institution_2_completed_at'] ?? null,
                ],
                3 => [
                    'university' => $submission['university_preference_3'] ?? null,
                    'course' => $submission['course_preference_3'] ?? null,
                    'status' => $submission['institution_3_status'] ?? 'pending',
                    'notes' => $submission['institution_3_notes'] ?? null,
                    'response' => $submission['institution_3_response'] ?? null,
                    'submitted_at' => $submission['institution_3_submitted_at'] ?? null,
                    'completed_at' => $submission['institution_3_completed_at'] ?? null,
                ],
            ];
            ?>
            
            <?php foreach ($preferences as $num => $pref): ?>
                <?php if (!empty($pref['university'])): ?>
                <div style="background: <?php echo $num === 1 ? '#f8fafc' : 'white'; ?>; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; margin-bottom: 16px; <?php echo $num === 1 ? 'border-left: 4px solid #6366f1;' : ($num === 2 ? 'border-left: 4px solid #8b5cf6;' : 'border-left: 4px solid #a78bfa;'); ?>">
                    
                    <!-- Institution Header -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div>
                            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.05em;">Preference <?php echo $num; ?></span>
                            <h4 style="margin: 2px 0 0; font-size: 16px; font-weight: 700; color: #1f2937;">
                                <i class="fas fa-university" style="color: #6366f1; margin-right: 6px;"></i>
                                <?php echo htmlspecialchars($pref['university']); ?>
                            </h4>
                            <?php if (!empty($pref['course'])): ?>
                                <p style="margin: 2px 0 0; font-size: 13px; color: #6b7280;">
                                    <i class="fas fa-book" style="margin-right: 4px;"></i> <?php echo htmlspecialchars($pref['course']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <span class="badge" style="<?php echo $institutionStatusColors[$pref['status']] ?? ''; ?> padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            <?php echo $institutionStatusLabels[$pref['status']] ?? ucfirst($pref['status']); ?>
                        </span>
                    </div>
                    
                    <!-- Timestamps -->
                    <?php if (!empty($pref['submitted_at']) || !empty($pref['completed_at'])): ?>
                    <div style="display: flex; gap: 16px; margin-bottom: 12px; font-size: 12px; color: #6b7280;">
                        <?php if (!empty($pref['submitted_at'])): ?>
                            <span><i class="fas fa-paper-plane"></i> Submitted: <?php echo date('d M Y H:i', strtotime($pref['submitted_at'])); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($pref['completed_at'])): ?>
                            <span><i class="fas fa-check-circle" style="color: #16a34a;"></i> Completed: <?php echo date('d M Y H:i', strtotime($pref['completed_at'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Status Notes/Response Display -->
                    <?php if (!empty($pref['notes'])): ?>
                    <div style="background: #f9fafb; border-radius: 6px; padding: 10px; margin-bottom: 12px; font-size: 13px;">
                        <strong style="color: #374151;">Notes:</strong> <?php echo htmlspecialchars($pref['notes']); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($pref['response'])): ?>
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px; margin-bottom: 12px; font-size: 13px;">
                        <strong style="color: #166534;">Institution Response:</strong> <?php echo htmlspecialchars($pref['response']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Status Update Form -->
                    <form method="POST" action="/admin/apply-for-me/update-institution-status" style="background: #f9fafb; border-radius: 8px; padding: 12px;">
                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                        <input type="hidden" name="institution_number" value="<?php echo $num; ?>">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                            <div>
                                <label style="font-size: 12px; font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Status</label>
                                <select name="institution_status" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;">
                                    <option value="pending" <?php echo $pref['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in_progress" <?php echo $pref['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="submitted_to_institution" <?php echo $pref['status'] === 'submitted_to_institution' ? 'selected' : ''; ?>>Submitted to Institution</option>
                                    <option value="completed" <?php echo $pref['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="rejected" <?php echo $pref['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 12px; font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Institution Response</label>
                                <input type="text" name="institution_response" value="<?php echo htmlspecialchars($pref['response'] ?? ''); ?>" 
                                       placeholder="Response from institution..." 
                                       style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;">
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 10px;">
                            <label style="font-size: 12px; font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Admin Notes for this Institution</label>
                            <textarea name="institution_notes" rows="2" placeholder="Notes specific to this institution application..." 
                                      style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; resize: vertical;"><?php echo htmlspecialchars($pref['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-sm btn-sm-primary" style="font-size: 13px;">
                            <i class="fas fa-save"></i> Update Institution <?php echo $num; ?> Status
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <?php if (empty($preferences[1]['university']) && empty($preferences[2]['university']) && empty($preferences[3]['university'])): ?>
                <p style="color: #6b7280; text-align: center; padding: 20px;">No university preferences submitted yet.</p>
            <?php endif; ?>
            
            <?php if (!empty($submission['bursary_name'])): ?>
            <div style="padding-top: 12px; border-top: 1px solid #e5e7eb; margin-top: 8px;">
                <label style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Bursary Name</label>
                <p style="margin: 4px 0 0; font-weight: 600; color: #7c3aed;"><?php echo htmlspecialchars($submission['bursary_name'] ?? ''); ?></p>
                <?php if (!empty($submission['bursary_provider'])): ?>
                    <p style="margin: 2px 0 0; font-size: 13px; color: #6b7280;">Provider: <?php echo htmlspecialchars($submission['bursary_provider']); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Documents -->
        <div class="admin-section">
            <h3><i class="fas fa-file-alt"></i> Student Documents</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <?php
                $documents = [
                    'id_copy' => ['label' => 'ID Copy', 'path' => 'id_copy_path', 'icon' => 'fa-id-card'],
                    'matric_cert' => ['label' => 'Matric Certificate', 'path' => 'matric_cert_path', 'icon' => 'fa-certificate'],
                    'proof_of_residence' => ['label' => 'Proof of Residence', 'path' => 'proof_res_path', 'icon' => 'fa-home'],
                    'cv' => ['label' => 'CV', 'path' => 'cv_path', 'icon' => 'fa-file-lines'],
                    'transcript' => ['label' => 'Transcript', 'path' => 'transcript_path', 'icon' => 'fa-file-alt'],
                ];
                foreach ($documents as $docKey => $doc):
                    $docPath = $submission[$doc['path']] ?? null;
                    $hasFile = !empty($docPath);
                ?>
                <div style="background: <?php echo $hasFile ? '#f0fdf4' : '#f9fafb'; ?>; border: 1px solid <?php echo $hasFile ? '#bbf7d0' : '#e5e7eb'; ?>; border-radius: 8px; padding: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <i class="fas <?php echo $doc['icon']; ?>" style="color: <?php echo $hasFile ? '#16a34a' : '#9ca3af'; ?>;"></i>
                        <span style="font-weight: 600; font-size: 14px;"><?php echo $doc['label']; ?></span>
                    </div>
                    <?php if ($hasFile): ?>
                        <div style="display: flex; gap: 8px;">
                            <a href="/admin/apply-for-me/<?php echo $submission['id']; ?>/view-document/<?php echo $docKey; ?>" 
                               target="_blank" class="btn-sm btn-sm-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="/admin/apply-for-me/<?php echo $submission['id']; ?>/download-document/<?php echo $docKey; ?>" 
                               class="btn-sm" style="background: #f3f4f6; color: #374151;">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    <?php else: ?>
                        <p style="margin: 0; font-size: 12px; color: #9ca3af;">Not uploaded</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Actions + Status -->
    <div>
        <!-- Quick Actions -->
        <div class="admin-section">
            <h3><i class="fas fa-cogs"></i> Actions</h3>
            
            <!-- Payment Status -->
            <div style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 600; color: #374151; display: block; margin-bottom: 8px;">
                    <i class="fas fa-credit-card"></i> Payment Status
                </label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span class="badge" style="<?php echo $submission['payment_status'] === 'paid' ? 'background: #dcfce7; color: #16a34a;' : ($submission['payment_status'] === 'refunded' ? 'background: #fee2e2; color: #dc2626;' : 'background: #fef3c7; color: #92400e;'); ?>">
                        R<?php echo number_format($submission['payment_amount'] ?? 20, 2); ?> &middot; 
                        <?php echo ucfirst($submission['payment_status'] ?? 'pending'); ?>
                    </span>
                </div>
                <form method="POST" action="/admin/apply-for-me/update-payment" style="margin-top: 8px;">
                    <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                    <div style="display: flex; gap: 8px;">
                        <select name="payment_status" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;">
                            <option value="pending" <?php echo ($submission['payment_status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo ($submission['payment_status'] ?? '') === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="refunded" <?php echo ($submission['payment_status'] ?? '') === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                        <button type="submit" class="btn-sm btn-sm-primary">Update</button>
                    </div>
                </form>
            </div>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 16px 0;">

            <!-- Document Verification -->
            <div style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 600; color: #374151; display: block; margin-bottom: 8px;">
                    <i class="fas fa-file-circle-check"></i> Document Verification
                </label>
                <div style="margin-bottom: 10px;">
                    <span class="badge" style="<?php echo $verificationColors[$docVerification] ?? ''; ?>">
                        <?php echo ucfirst($docVerification); ?>
                    </span>
                    <?php if (!empty($submission['verified_at'])): ?>
                        <span style="font-size: 12px; color: #6b7280; margin-left: 8px;">
                            by <?php echo htmlspecialchars($submission['verified_by_name'] ?? 'Admin'); ?> 
                            on <?php echo date('d M Y H:i', strtotime($submission['verified_at'])); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($submission['rejection_reason'])): ?>
                    <div style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px; padding: 10px; margin-bottom: 10px; font-size: 13px; color: #991b1b;">
                        <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($submission['rejection_reason']); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="/admin/apply-for-me/verify-documents">
                    <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                    <textarea name="verification_notes" placeholder="Verification notes (optional)..." rows="2" 
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; margin-bottom: 8px; resize: vertical;"><?php echo htmlspecialchars($submission['document_verification_notes'] ?? ''); ?></textarea>
                    <div id="rejectionReasonGroup" style="display: <?php echo $docVerification === 'invalid' ? 'block' : 'none'; ?>; margin-bottom: 8px;">
                        <textarea name="rejection_reason" placeholder="Reason for rejection (required if invalid)..." rows="2" 
                                  style="width: 100%; padding: 8px 12px; border: 1px solid #fca5a5; border-radius: 6px; font-size: 13px; resize: vertical;"><?php echo htmlspecialchars($submission['rejection_reason'] ?? ''); ?></textarea>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" name="action" value="valid" class="btn-sm" style="background: #16a34a; color: white;">
                            <i class="fas fa-check"></i> Valid
                        </button>
                        <button type="submit" name="action" value="invalid" class="btn-sm" style="background: #dc2626; color: white;" 
                                onclick="document.getElementById('rejectionReasonGroup').style.display='block';">
                            <i class="fas fa-times"></i> Invalid
                        </button>
                        <button type="submit" name="action" value="pending" class="btn-sm" style="background: #f59e0b; color: white;">
                            <i class="fas fa-clock"></i> Reset
                        </button>
                    </div>
                </form>
            </div>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 16px 0;">

            <!-- Application Status -->
            <div style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 600; color: #374151; display: block; margin-bottom: 8px;">
                    <i class="fas fa-tasks"></i> Application Status
                </label>
                <form method="POST" action="/admin/apply-for-me/update-status">
                    <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                    <select name="new_status" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; margin-bottom: 8px;">
                        <option value="pending" <?php echo $currentStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $currentStatus === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="submitted_to_institution" <?php echo $currentStatus === 'submitted_to_institution' ? 'selected' : ''; ?>>Submitted to Institution</option>
                        <option value="completed" <?php echo $currentStatus === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="rejected" <?php echo $currentStatus === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <textarea name="institution_response" placeholder="Institution response (if applicable)..." rows="2" 
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; margin-bottom: 8px; resize: vertical;"><?php echo htmlspecialchars($submission['institution_response'] ?? ''); ?></textarea>
                    <textarea name="admin_notes" placeholder="Admin notes (internal)..." rows="2" 
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; margin-bottom: 8px; resize: vertical;"><?php echo htmlspecialchars($submission['admin_notes'] ?? ''); ?></textarea>
                    <button type="submit" class="btn-sm btn-sm-primary">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Per-Institution Progress Steps -->
        <?php
        $stepKeys = ['submitted', 'payment', 'document_verification', 'in_progress', 'submitted_to_institution', 'completed'];
        $stepLabels = ['Application Submitted', 'Payment Confirmed', 'Documents Verified', 'Application Being Processed', 'Submitted to Institution', 'Application Completed'];
        $stepIcons = ['fa-paper-plane', 'fa-credit-card', 'fa-file-circle-check', 'fa-cogs', 'fa-university', 'fa-check-circle'];
        $stepDescs = ['Student submitted the application', 'Service fee received', 'All documents checked and valid', 'Preparing application for institution', 'Application sent to this institution', 'Final response received'];
        
        // Institution data
        $institutionData = [
            1 => ['university' => $submission['university_preference_1'] ?? null, 'color' => '#6366f1'],
            2 => ['university' => $submission['university_preference_2'] ?? null, 'color' => '#8b5cf6'],
            3 => ['university' => $submission['university_preference_3'] ?? null, 'color' => '#a78bfa'],
        ];
        ?>
        
        <?php foreach ($institutionData as $instNum => $instInfo): ?>
            <?php if (!empty($instInfo['university'])): ?>
            <div class="admin-section" style="border-left: 4px solid <?php echo $instInfo['color']; ?>;">
                <h3 style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-university" style="color: <?php echo $instInfo['color']; ?>;"></i> 
                    Institution <?php echo $instNum; ?> Progress: <?php echo htmlspecialchars($instInfo['university']); ?>
                </h3>
                <p style="font-size: 12px; color: #6b7280; margin: -10px 0 16px;">Click a step to mark it as completed for this specific institution.</p>
                
                <?php
                $instStepsField = "institution_{$instNum}_steps_completed";
                $instCompletedSteps = json_decode($submission[$instStepsField] ?? '[]', true);
                if (!is_array($instCompletedSteps)) {
                    $instCompletedSteps = [];
                }
                ?>
                
                <div style="display: grid; gap: 8px;">
                    <?php for ($si = 0; $si < count($stepKeys); $si++):
                        $sk = $stepKeys[$si];
                        $sl = $stepLabels[$si];
                        $sicon = $stepIcons[$si];
                        $sd = $stepDescs[$si];
                        $isStepDone = in_array($sk, $instCompletedSteps);
                    ?>
                    <form method="POST" action="/admin/apply-for-me/toggle-institution-step" style="display: contents;">
                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                        <input type="hidden" name="institution_number" value="<?php echo $instNum; ?>">
                        <input type="hidden" name="step_key" value="<?php echo $sk; ?>">
                        <button type="submit" style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; border: 2px solid <?php echo $isStepDone ? '#bbf7d0' : '#e5e7eb'; ?>; border-radius: 8px; background: <?php echo $isStepDone ? '#f0fdf4' : 'white'; ?>; cursor: pointer; text-align: left; transition: all 0.2s; width: 100%;"
                                onmouseover="this.style.borderColor='<?php echo $instInfo['color']; ?>'"
                                onmouseout="this.style.borderColor='<?php echo $isStepDone ? '#bbf7d0' : '#e5e7eb'; ?>'">
                            <div style="width: 26px; height: 26px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;
                                        background: <?php echo $isStepDone ? '#16a34a' : '#f3f4f6'; ?>; color: <?php echo $isStepDone ? 'white' : '#9ca3af'; ?>;">
                                <i class="fas <?php echo $isStepDone ? 'fa-check' : $sicon; ?>"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; font-size: 13px; color: <?php echo $isStepDone ? '#166534' : '#374151'; ?>;"><?php echo $sl; ?></div>
                                <div style="font-size: 11px; color: #6b7280;"><?php echo $sd; ?></div>
                            </div>
                            <div style="font-size: 11px; color: <?php echo $isStepDone ? '#16a34a' : '#9ca3af'; ?>; font-weight: 500;">
                                <?php echo $isStepDone ? '<i class="fas fa-check-circle"></i> Done' : '<i class="fas fa-circle" style="font-size: 8px;"></i> Pending'; ?>
                            </div>
                        </button>
                    </form>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Institution Response -->
        <?php if (!empty($submission['institution_response'])): ?>
        <div class="admin-section">
            <h3><i class="fas fa-building"></i> Institution Response</h3>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px;">
                <p style="margin: 0; font-size: 14px; white-space: pre-wrap;"><?php echo htmlspecialchars($submission['institution_response']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Admin Notes -->
        <?php if (!empty($submission['admin_notes'])): ?>
        <div class="admin-section">
            <h3><i class="fas fa-sticky-note"></i> Admin Notes</h3>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px;">
                <p style="margin: 0; font-size: 14px; white-space: pre-wrap;"><?php echo htmlspecialchars($submission['admin_notes']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Proof of Payment -->
        <div class="admin-section" style="border: 2px solid #fde68a;">
            <h3><i class="fas fa-receipt"></i> Proof of Payment</h3>
            
            <?php if (!empty($submission['proof_of_payment_path'])): ?>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 40px; height: 40px; background: #fef3c7; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-invoice" style="color: #d97706; font-size: 18px;"></i>
                    </div>
                    <div>
                        <p style="margin: 0; font-weight: 600; color: #1e293b; font-size: 14px;">Proof of Payment Uploaded</p>
                        <?php if (!empty($submission['proof_of_payment_uploaded_at'])): ?>
                            <p style="margin: 2px 0 0; font-size: 12px; color: #6b7280;">Uploaded: <?php echo date('d M Y H:i', strtotime($submission['proof_of_payment_uploaded_at'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Inline Preview -->
                <?php 
                $proofPath = $submission['proof_of_payment_path'];
                $proofFullPath = __DIR__ . '/../../public/uploads/apply_for_me/' . $proofPath;
                $proofExt = strtolower(pathinfo($proofFullPath, PATHINFO_EXTENSION));
                ?>
                <div style="margin-bottom: 16px; background: #f9fafb; border-radius: 8px; padding: 10px; text-align: center;">
                    <?php if (in_array($proofExt, ['jpg', 'jpeg', 'png'])): ?>
                        <img src="/uploads/apply_for_me/<?php echo htmlspecialchars($proofPath); ?>" 
                             alt="Proof of Payment" 
                             style="max-width: 100%; max-height: 400px; border-radius: 8px; border: 1px solid #e5e7eb;" />
                    <?php elseif ($proofExt === 'pdf'): ?>
                        <iframe src="/uploads/apply_for_me/<?php echo htmlspecialchars($proofPath); ?>" 
                                style="width: 100%; height: 400px; border: none; border-radius: 8px;"></iframe>
                    <?php else: ?>
                        <p style="color: #6b7280; padding: 20px;">Preview not available for this file type.</p>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                    <a href="/admin/apply-for-me/<?php echo $submission['id']; ?>/view-proof-payment" target="_blank" class="btn-sm btn-sm-primary">
                        <i class="fas fa-external-link-alt"></i> Open Full Size
                    </a>
                </div>

                <!-- Approve / Reject Payment -->
                <?php if (($submission['payment_status'] ?? 'pending') === 'pending'): ?>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                    <form method="POST" action="/admin/apply-for-me/approve-payment" onsubmit="return confirm('Approve this payment? This will advance the application to In Progress status.');">
                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                        <button type="submit" class="btn-sm" style="background: #16a34a; color: white; padding: 10px 20px; font-size: 14px;">
                            <i class="fas fa-check"></i> Approve Payment
                        </button>
                    </form>
                    <form method="POST" action="/admin/apply-for-me/reject-payment" style="display: flex; gap: 8px; align-items: flex-end;" onsubmit="return confirm('Reject this payment?');">
                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                        <input type="text" name="rejection_reason" placeholder="Rejection reason..." 
                               style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; width: 220px;" />
                        <button type="submit" class="btn-sm" style="background: #dc2626; color: white; padding: 10px 20px; font-size: 14px;">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </form>
                </div>
                <?php elseif (($submission['payment_status'] ?? '') === 'paid'): ?>
                    <div style="background: #dcfce7; border-radius: 8px; padding: 12px; font-size: 13px; color: #166534; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-check-circle" style="font-size: 18px;"></i>
                        <div>
                            <strong>Payment Approved</strong>
                            <?php if (!empty($submission['verified_at'])): ?>
                                on <?php echo date('d M Y H:i', strtotime($submission['verified_at'])); ?>
                            <?php endif; ?>
                            <?php if (!empty($submission['verified_by_name'])): ?>
                                by <?php echo htmlspecialchars($submission['verified_by_name']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="background: #fee2e2; border-radius: 8px; padding: 12px; font-size: 13px; color: #991b1b;">
                        <i class="fas fa-times-circle"></i> Payment was rejected. Student needs to re-upload.
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- No proof uploaded yet -->
                <div style="text-align: center; padding: 30px; background: #f9fafb; border-radius: 8px; border: 2px dashed #d1d5db;">
                    <i class="fas fa-clock" style="font-size: 36px; color: #d1d5db; margin-bottom: 10px; display: block;"></i>
                    <p style="margin: 0; color: #6b7280; font-size: 14px;">No proof of payment uploaded yet</p>
                    <p style="margin: 4px 0 0; color: #9ca3af; font-size: 12px;">Waiting for student to upload their proof of payment</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Application Metadata -->
        <div class="admin-section">
            <h3><i class="fas fa-info-circle"></i> Application Details</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                <div>
                    <span style="color: #6b7280;">Application ID:</span>
                    <strong>#<?php echo $submission['id']; ?></strong>
                </div>
                <div>
                    <span style="color: #6b7280;">Student Number:</span>
                    <strong><?php echo htmlspecialchars($submission['student_number'] ?? 'N/A'); ?></strong>
                </div>
                <div>
                    <span style="color: #6b7280;">Submitted:</span>
                    <?php echo date('d M Y H:i', strtotime($submission['created_at'])); ?>
                </div>
                <div>
                    <span style="color: #6b7280;">Last Updated:</span>
                    <?php echo date('d M Y H:i', strtotime($submission['updated_at'])); ?>
                </div>
                <?php if (!empty($submission['submitted_to_institution_at'])): ?>
                <div>
                    <span style="color: #6b7280;">Submitted to Institution:</span>
                    <?php echo date('d M Y H:i', strtotime($submission['submitted_to_institution_at'])); ?>
                </div>
                <?php endif; ?>
                <div>
                    <span style="color: #6b7280;">Payment Reference:</span>
                    <?php echo htmlspecialchars($submission['payment_reference'] ?? 'N/A'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide rejection reason when clicking "Invalid"
document.querySelectorAll('button[value="invalid"]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        setTimeout(function() {
            var group = document.getElementById('rejectionReasonGroup');
            if (group) group.style.display = 'block';
        }, 10);
    });
});
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>