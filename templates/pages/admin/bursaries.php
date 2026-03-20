<?php
include __DIR__ . '/../../layouts/admin_header.php';

// Count statistics
$totalCount = count($bursaries);
$activeCount = 0;
$inactiveCount = 0;
$expiredCount = 0;
$today = date('Y-m-d');
foreach ($bursaries as $b) {
    if ($b['deadline'] < $today) {
        $expiredCount++;
    } elseif ($b['is_active']) {
        $activeCount++;
    } else {
        $inactiveCount++;
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-scholarship"></i> Manage Bursaries
        </h1>
        <p style="color: #6b7280;">Add and manage bursaries available for students</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="/admin/bursaries/add" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i> Add Bursary
        </a>
        <a href="/admin" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if (isset($_GET['created'])): ?>
<div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-left: 4px solid #10b981; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <i class="fas fa-check-circle" style="color: #059669; font-size: 20px;"></i>
    <div>
        <strong style="color: #065f46;">Success!</strong>
        <span style="color: #047857;">Bursary added successfully!</span>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
<div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-left: 4px solid #10b981; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <i class="fas fa-check-circle" style="color: #059669; font-size: 20px;"></i>
    <div>
        <strong style="color: #065f46;">Success!</strong>
        <span style="color: #047857;">Bursary updated successfully!</span>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
<div style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-left: 4px solid #ef4444; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <i class="fas fa-trash" style="color: #dc2626; font-size: 20px;"></i>
    <div>
        <strong style="color: #991b1b;">Deleted</strong>
        <span style="color: #b91c1c;">Bursary deleted successfully!</span>
    </div>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #667eea;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">Total Bursaries</p>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #667eea;"><?php echo $totalCount; ?></p>
            </div>
            <i class="fas fa-scholarship" style="font-size: 40px; color: #667eea; opacity: 0.3;"></i>
        </div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #10b981;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">Active</p>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #10b981;"><?php echo $activeCount; ?></p>
            </div>
            <i class="fas fa-check-circle" style="font-size: 40px; color: #10b981; opacity: 0.3;"></i>
        </div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #6b7280;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">Inactive</p>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #6b7280;"><?php echo $inactiveCount; ?></p>
            </div>
            <i class="fas fa-times-circle" style="font-size: 40px; color: #6b7280; opacity: 0.3;"></i>
        </div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #ef4444;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">Expired</p>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #ef4444;"><?php echo $expiredCount; ?></p>
            </div>
            <i class="fas fa-calendar-times" style="font-size: 40px; color: #ef4444; opacity: 0.3;"></i>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
    <a href="/admin/bursaries?filter=all" class="btn-sm <?php echo $filter === 'all' ? 'btn-sm-primary' : 'btn-sm-secondary'; ?>" style="text-decoration: none;">
        All (<?php echo $totalCount; ?>)
    </a>
    <a href="/admin/bursaries?filter=active" class="btn-sm <?php echo $filter === 'active' ? 'btn-sm-primary' : 'btn-sm-secondary'; ?>" style="text-decoration: none;">
        Active (<?php echo $activeCount; ?>)
    </a>
    <a href="/admin/bursaries?filter=inactive" class="btn-sm <?php echo $filter === 'inactive' ? 'btn-sm-primary' : 'btn-sm-secondary'; ?>" style="text-decoration: none;">
        Inactive (<?php echo $inactiveCount; ?>)
    </a>
    <a href="/admin/bursaries?filter=expired" class="btn-sm <?php echo $filter === 'expired' ? 'btn-sm-primary' : 'btn-sm-secondary'; ?>" style="text-decoration: none;">
        Expired (<?php echo $expiredCount; ?>)
    </a>
</div>

<?php if (empty($bursaries)): ?>
    <div class="admin-section">
        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            <p>No bursaries found. Click "Add Bursary" to create one.</p>
        </div>
    </div>
<?php else: ?>
    <div class="admin-section">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Provider</th>
                        <th>Deadline</th>
                        <th>Grade Range</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bursaries as $bursary): ?>
                        <tr>
                            <td style="color: #6b7280;">#<?php echo $bursary['id']; ?></td>
                            <td>
                                <strong style="color: #667eea;">
                                    <i class="fas fa-scholarship"></i> <?php echo htmlspecialchars($bursary['name']); ?>
                                </strong>
                            </td>
                            <td>
                                <span style="color: #6b7280;"><?php echo htmlspecialchars($bursary['provider']); ?></span>
                            </td>
                            <td>
                                <?php 
                                $deadline = new DateTime($bursary['deadline']);
                                $today = new DateTime();
                                $daysLeft = $today->diff($deadline)->days;
                                $isExpired = $deadline < $today;
                                ?>
                                <span style="color: <?php echo $isExpired ? '#ef4444' : ($daysLeft < 30 ? '#f59e0b' : '#10b981'); ?>;">
                                    <?php echo date('M d, Y', strtotime($bursary['deadline'])); ?>
                                    <?php if ($isExpired): ?>
                                        <span style="color: #ef4444; font-size: 12px;">(Expired)</span>
                                    <?php else: ?>
                                        <span style="color: #6b7280; font-size: 12px;">(<?php echo $daysLeft; ?> days left)</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 13px; color: #6b7280;">
                                    <?php echo $bursary['min_grade_average']; ?>% - <?php echo $bursary['max_grade_average']; ?>%
                                </span>
                            </td>
                            <td>
                                <?php if ($bursary['is_active'] && $deadline >= $today): ?>
                                    <span class="badge active"><i class="fas fa-check-circle"></i> Active</span>
                                <?php elseif ($deadline < $today): ?>
                                    <span class="badge cancelled"><i class="fas fa-calendar-times"></i> Expired</span>
                                <?php else: ?>
                                    <span class="badge inactive"><i class="fas fa-pause-circle"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: #6b7280; font-size: 13px;">
                                <?php echo date('M d, Y', strtotime($bursary['updated_at'])); ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <a href="/admin/bursaries/edit/<?php echo $bursary['id']; ?>" class="btn-sm btn-sm-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($deadline >= $today): ?>
                                        <form method="POST" action="/admin/bursaries/toggle-status" style="display: inline;" onsubmit="return confirm('Toggle bursary status?');">
                                            <input type="hidden" name="bursary_id" value="<?php echo $bursary['id']; ?>">
                                            <button type="submit" class="btn-sm <?php echo $bursary['is_active'] ? 'btn-sm-warning' : 'btn-sm-success'; ?>" style="cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                                <i class="fas fa-<?php echo $bursary['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                <?php echo $bursary['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: #9ca3af; font-style: italic;">Auto-deactivated</span>
                                    <?php endif; ?>
                                    <form method="POST" action="/admin/bursaries/delete" style="display: inline;" onsubmit="return confirm('Delete this bursary? This cannot be undone.');">
                                        <input type="hidden" name="bursary_id" value="<?php echo $bursary['id']; ?>">
                                        <button type="submit" class="btn-sm btn-sm-danger" style="cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
