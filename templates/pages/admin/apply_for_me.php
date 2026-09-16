<?php
include __DIR__ . '/../../layouts/admin_header.php';

// Group submissions by student user_id
$groupedSubmissions = [];
foreach ($submissions as $sub) {
    $userId = $sub['user_id'] ?? 0;
    if (!isset($groupedSubmissions[$userId])) {
        $groupedSubmissions[$userId] = [
            'user_id' => $userId,
            'username' => $sub['username'] ?? 'Unknown',
            'user_email' => $sub['user_email'] ?? '',
            'applications' => [],
            'total_amount' => 0,
            'total_paid' => 0,
        ];
    }
    $groupedSubmissions[$userId]['applications'][] = $sub;
    $groupedSubmissions[$userId]['total_amount'] += $sub['payment_amount'] ?? 20;
    if (($sub['payment_status'] ?? '') === 'paid') {
        $groupedSubmissions[$userId]['total_paid'] += $sub['payment_amount'] ?? 20;
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 5px; color: #1f2937; letter-spacing: -0.025em;">
            <i class="fas fa-paper-plane" style="color: #8b5cf6;"></i> Apply For Me
        </h1>
        <p style="color: #6b7280; font-size: 15px;">Manage student application submissions</p>
    </div>
    <a href="/admin" class="btn-secondary-modern">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon purple">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="value"><?php echo number_format($stats['total']); ?></div>
        <div class="label">Total Applications</div>
    </div>
    <div class="stat-card">
        <div class="icon yellow">
            <i class="fas fa-clock"></i>
        </div>
        <div class="value"><?php echo number_format($stats['pending_review']); ?></div>
        <div class="label">Pending Review</div>
    </div>
    <div class="stat-card">
        <div class="icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="value"><?php echo number_format($stats['documents_valid']); ?></div>
        <div class="label">Documents Valid</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="background: #fee2e2; color: #dc2626;">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="value"><?php echo number_format($stats['documents_invalid']); ?></div>
        <div class="label">Documents Invalid</div>
    </div>
    <div class="stat-card">
        <div class="icon blue">
            <i class="fas fa-credit-card"></i>
        </div>
        <div class="value"><?php echo number_format($stats['paid']); ?></div>
        <div class="label">Paid</div>
    </div>
    <div class="stat-card">
        <div class="icon orange">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="value"><?php echo number_format($stats['unpaid']); ?></div>
        <div class="label">Unpaid</div>
    </div>
</div>

<!-- Filters -->
<div class="admin-section">
    <form method="GET" action="/admin/apply-for-me" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="search" placeholder="Search by name, email, ID number..."
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                   style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
        </div>
        <select name="status" style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
            <option value="">All Status</option>
            <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="in_progress" <?php echo ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
            <option value="submitted_to_institution" <?php echo ($_GET['status'] ?? '') === 'submitted_to_institution' ? 'selected' : ''; ?>>Submitted to Institution</option>
            <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="rejected" <?php echo ($_GET['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select>
        <select name="payment" style="padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
            <option value="">All Payments</option>
            <option value="pending" <?php echo ($_GET['payment'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="paid" <?php echo ($_GET['payment'] ?? '') === 'paid' ? 'selected' : ''; ?>>Paid</option>
            <option value="refunded" <?php echo ($_GET['payment'] ?? '') === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
        </select>
        <button type="submit" class="btn-sm btn-sm-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="/admin/apply-for-me" class="btn-sm" style="background: #f3f4f6; color: #374151;">
            <i class="fas fa-times"></i> Clear
        </a>
    </form>
</div>

<!-- Data Privacy Notice -->
<div class="admin-section" style="background: #fefce8; border: 1px solid #fde68a; border-radius: 10px;">
    <p style="margin: 0; color: #92400e; font-size: 13px;">
        <i class="fas fa-shield-halved"></i> <strong>Data Privacy:</strong> Student documents are handled in accordance with POPIA. 
        All document access is logged for audit purposes. Do not download or share student documents outside of this system.
    </p>
</div>

<!-- Applications Grouped by Student -->
<div class="admin-section">
    <h3><i class="fas fa-users"></i> Students (<?php echo count($groupedSubmissions); ?> students &middot; <?php echo count($submissions); ?> total applications)</h3>
    
    <?php if (empty($groupedSubmissions)): ?>
        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            <p>No applications found matching your criteria.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 12px;">
            <?php foreach ($groupedSubmissions as $userId => $group): 
                $appCount = count($group['applications']);
                // Determine overall status for the student
                $allPaid = true;
                $anyPaid = false;
                $allCompleted = true;
                $anyRejected = false;
                foreach ($group['applications'] as $app) {
                    if (($app['payment_status'] ?? '') !== 'paid') $allPaid = false;
                    if (($app['payment_status'] ?? '') === 'paid') $anyPaid = true;
                    if (($app['application_status'] ?? '') !== 'completed') $allCompleted = false;
                    if (($app['application_status'] ?? '') === 'rejected') $anyRejected = true;
                }
            ?>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
                <!-- Student Header -->
                <div style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; cursor: pointer; transition: background 0.2s;"
                     onclick="var detail = document.getElementById('student-detail-<?php echo $userId; ?>'); detail.style.display = detail.style.display === 'none' ? 'block' : 'none'; this.querySelector('.toggle-icon').style.transform = detail.style.display === 'none' ? 'rotate(0deg)' : 'rotate(180deg)';"
                     onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 42px; height: 42px; background: linear-gradient(135deg, #ede9fe, #e0e7ff); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #7c3aed; font-size: 18px; flex-shrink: 0;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; color: #1e293b; font-size: 15px;">
                                <?php echo htmlspecialchars($group['username']); ?>
                                <span style="font-weight: 400; color: #6b7280; font-size: 13px; margin-left: 6px;">@<?php echo htmlspecialchars($group['username']); ?></span>
                            </div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                                <?php echo htmlspecialchars($group['user_email']); ?>
                                &middot; <strong style="color: #8b5cf6;"><?php echo $appCount; ?> application<?php echo $appCount > 1 ? 's' : ''; ?></strong>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="text-align: right;">
                            <div style="font-size: 18px; font-weight: 700; color: #1e293b;">
                                R<?php echo number_format($group['total_amount'], 2); ?>
                            </div>
                            <div style="font-size: 12px;">
                                <?php if ($allPaid): ?>
                                    <span style="color: #16a34a;"><i class="fas fa-check-circle"></i> Fully Paid</span>
                                <?php elseif ($anyPaid): ?>
                                    <span style="color: #d97706;"><i class="fas fa-clock"></i> Partially Paid</span>
                                <?php else: ?>
                                    <span style="color: #dc2626;"><i class="fas fa-exclamation-circle"></i> Unpaid</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down toggle-icon" style="color: #9ca3af; transition: transform 0.2s; font-size: 14px;"></i>
                    </div>
                </div>

                <!-- Student's Applications (Expandable) -->
                <div id="student-detail-<?php echo $userId; ?>" style="display: none; border-top: 1px solid #f1f5f9;">
                    <?php foreach ($group['applications'] as $app): 
                        $appStatus = $app['application_status'] ?? 'pending';
                        $payStatus = $app['payment_status'] ?? 'pending';
                        $docStatus = $app['document_verification'] ?? 'pending';
                    ?>
                    <div style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                            <a href="/admin/apply-for-me/<?php echo $app['id']; ?>" style="font-weight: 700; color: #8b5cf6; text-decoration: none; font-size: 15px;">
                                #<?php echo $app['id']; ?>
                            </a>
                            <div>
                                <div style="font-size: 14px; color: #1e293b;">
                                    <?php if (!empty($app['university_preference_1'])): ?>
                                        <?php echo htmlspecialchars($app['university_preference_1']); ?>
                                        <?php if (!empty($app['university_preference_2'])): ?>
                                            + <?php echo htmlspecialchars($app['university_preference_2']); ?>
                                        <?php endif; ?>
                                        <?php if (!empty($app['university_preference_3'])): ?>
                                            + <?php echo htmlspecialchars($app['university_preference_3']); ?>
                                        <?php endif; ?>
                                    <?php elseif (!empty($app['bursary_name'])): ?>
                                        <i class="fas fa-award" style="color: #7c3aed; margin-right: 4px;"></i>
                                        <?php echo htmlspecialchars($app['bursary_name']); ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 12px; color: #6b7280;">
                                    Submitted <?php echo date('d M Y', strtotime($app['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <span class="badge" style="<?php echo $payStatus === 'paid' ? 'background: #dcfce7; color: #16a34a;' : ($payStatus === 'refunded' ? 'background: #fee2e2; color: #dc2626;' : 'background: #fef3c7; color: #92400e;'); ?>">
                                <?php echo ucfirst($payStatus); ?>
                            </span>
                            <span class="badge" style="<?php echo $docStatus === 'valid' ? 'background: #dcfce7; color: #16a34a;' : ($docStatus === 'invalid' ? 'background: #fee2e2; color: #dc2626;' : 'background: #fef3c7; color: #92400e;'); ?>">
                                Docs: <?php echo ucfirst($docStatus); ?>
                            </span>
                            <?php
                            $statusColors = [
                                'pending' => 'background: #f3f4f6; color: #374151;',
                                'in_progress' => 'background: #dbeafe; color: #1d4ed8;',
                                'submitted_to_institution' => 'background: #ede9fe; color: #7c3aed;',
                                'completed' => 'background: #dcfce7; color: #16a34a;',
                                'rejected' => 'background: #fee2e2; color: #dc2626;',
                            ];
                            ?>
                            <span class="badge" style="<?php echo $statusColors[$appStatus] ?? ''; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $appStatus)); ?>
                            </span>
                            <span style="font-weight: 600; color: #7c3aed; font-size: 13px;">
                                R<?php echo number_format($app['payment_amount'] ?? 20, 2); ?>
                            </span>
                            <a href="/admin/apply-for-me/<?php echo $app['id']; ?>" class="btn-sm btn-sm-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>