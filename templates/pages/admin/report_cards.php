<?php
include __DIR__ . '/../../layouts/admin_header.php';

// Group report cards by user
$reportCardsByUser = [];
foreach ($reportCards as $rc) {
    $userId = $rc['user_id'];
    if (!isset($reportCardsByUser[$userId])) {
        $reportCardsByUser[$userId] = [
            'username' => $rc['username'],
            'email' => $rc['email'],
            'reportCards' => []
        ];
    }
    $reportCardsByUser[$userId]['reportCards'][] = $rc;
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-file-upload"></i> Manage Report Cards
        </h1>
        <p style="color: #6b7280;">View and manage all uploaded report cards grouped by user</p>
    </div>
    <a href="/admin" class="btn-secondary" style="text-decoration: none; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<?php if (empty($reportCardsByUser)): ?>
    <div class="admin-section">
        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            No report cards found
        </div>
    </div>
<?php else: ?>
    <div class="admin-section">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Total Report Cards</th>
                        <th>With Career Recs</th>
                        <th>Pending</th>
                        <th>Last Upload</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportCardsByUser as $userId => $userData): ?>
                        <?php
                        $generatedCount = 0;
                        $pendingCount = 0;
                        $lastUpload = null;
                        
                        foreach ($userData['reportCards'] as $rc) {
                            if ($rc['career_recommendations_generated']) {
                                $generatedCount++;
                            } else {
                                $pendingCount++;
                            }
                            if (!$lastUpload || strtotime($rc['uploaded_at']) > strtotime($lastUpload)) {
                                $lastUpload = $rc['uploaded_at'];
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <strong style="color: #667eea;">
                                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($userData['username'] ?? 'Unknown'); ?>
                                </strong>
                            </td>
                            <td>
                                <span style="color: #6b7280;"><?php echo htmlspecialchars($userData['email'] ?? ''); ?></span>
                            </td>
                            <td>
                                <span class="badge basic"><?php echo count($userData['reportCards']); ?></span>
                            </td>
                            <td>
                                <span style="color: #22c55e;"><i class="fas fa-check"></i> <?php echo $generatedCount; ?></span>
                            </td>
                            <td>
                                <span style="color: #f97316;"><i class="fas fa-clock"></i> <?php echo $pendingCount; ?></span>
                            </td>
                            <td>
                                <?php echo $lastUpload ? date('M d, Y H:i', strtotime($lastUpload)) : '-'; ?>
                            </td>
                            <td>
                                <button onclick="toggleUserReportCards(<?php echo $userId; ?>)" class="btn-sm btn-sm-primary" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i> View Report Cards
                                </button>
                            </td>
                        </tr>
                        <tr id="user-reportcards-<?php echo $userId; ?>" style="display: none;">
                            <td colspan="7" style="padding: 0; background: #f9fafb;">
                                <div style="padding: 20px;">
                                    <h4 style="margin: 0 0 15px 0; color: #667eea;">
                                        <i class="fas fa-folder-open"></i> Report Cards by <?php echo htmlspecialchars($userData['username']); ?>
                                    </h4>
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead style="background: #e5e7eb;">
                                            <tr>
                                                <th style="padding: 10px; text-align: left;">ID</th>
                                                <th style="padding: 10px; text-align: left;">File Name</th>
                                                <th style="padding: 10px; text-align: left;">Grade</th>
                                                <th style="padding: 10px; text-align: left;">Term</th>
                                                <th style="padding: 10px; text-align: left;">Average</th>
                                                <th style="padding: 10px; text-align: left;">Career Recs</th>
                                                <th style="padding: 10px; text-align: left;">Uploaded</th>
                                                <th style="padding: 10px; text-align: left;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userData['reportCards'] as $rc): ?>
                                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                                    <td style="padding: 10px; color: #6b7280;">#<?php echo $rc['id']; ?></td>
                                                    <td style="padding: 10px;">
                                                        <strong><?php echo htmlspecialchars(basename($rc['file_path']) ?? 'Unknown'); ?></strong>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php if (!empty($rc['grade'])): ?>
                                                            <span class="badge basic">Grade <?php echo htmlspecialchars($rc['grade']); ?></span>
                                                        <?php else: ?>
                                                            <span style="color: #9ca3af;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php if (!empty($rc['term'])): ?>
                                                            <span style="color: #6b7280; font-size: 13px;"><?php echo htmlspecialchars($rc['term']); ?></span>
                                                        <?php else: ?>
                                                            <span style="color: #9ca3af;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php if (isset($rc['average']) && $rc['average'] !== null): ?>
                                                            <span class="badge <?php echo $rc['average'] >= 70 ? 'active' : ($rc['average'] >= 50 ? 'basic' : 'cancelled'); ?>">
                                                                <?php echo number_format($rc['average'], 1); ?>%
                                                            </span>
                                                        <?php else: ?>
                                                            <span style="color: #9ca3af;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php if ($rc['career_recommendations_generated']): ?>
                                                            <span class="badge active"><i class="fas fa-check"></i> Generated</span>
                                                        <?php else: ?>
                                                            <span class="badge inactive">Not generated</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 10px;"><?php echo date('M d, Y H:i', strtotime($rc['uploaded_at'])); ?></td>
                                                    <td style="padding: 10px;">
                                                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                                            <a href="/view-career-recommendations/<?php echo $rc['id']; ?>" class="btn-sm btn-sm-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <form method="POST" action="/admin/report-cards/delete" style="display: inline;" onsubmit="return confirm('Delete this report card? This cannot be undone.');">
                                                                <input type="hidden" name="report_card_id" value="<?php echo $rc['id']; ?>">
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
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script>
function toggleUserReportCards(userId) {
    const row = document.getElementById('user-reportcards-' + userId);
    if (row.style.display === 'none') {
        row.style.display = '';
    } else {
        row.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
