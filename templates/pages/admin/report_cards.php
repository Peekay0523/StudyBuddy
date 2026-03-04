<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-file-upload"></i> Manage Report Cards
        </h1>
        <p style="color: #6b7280;">View and manage all uploaded report cards</p>
    </div>
    <a href="/admin" class="btn-secondary" style="text-decoration: none; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="admin-section">
    <div class="table-responsive">
        <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>File Name</th>
                <th>User</th>
                <th>Grade</th>
                <th>Average</th>
                <th>Career Recs</th>
                <th>Uploaded</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reportCards)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        No report cards found
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($reportCards as $rc): ?>
                    <tr>
                        <td style="color: #6b7280;">#<?php echo $rc['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars(basename($rc['file_name'])); ?></strong>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($rc['username'] ?? ''); ?><br>
                            <small style="color: #6b7280;"><?php echo htmlspecialchars($rc['email'] ?? ''); ?></small>
                        </td>
                        <td>
                            <?php if (!empty($rc['grade'])): ?>
                                <span class="badge basic">Grade <?php echo htmlspecialchars($rc['grade'] ?? ''); ?></span>
                            <?php else: ?>
                                <span style="color: #9ca3af;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($rc['average']) && $rc['average'] !== null): ?>
                                <span class="badge <?php echo $rc['average'] >= 70 ? 'active' : ($rc['average'] >= 50 ? 'basic' : 'cancelled'); ?>">
                                    <?php echo number_format($rc['average'], 1); ?>%
                                </span>
                            <?php else: ?>
                                <span style="color: #9ca3af;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($rc['career_recommendations_generated']): ?>
                                <span class="badge active"><i class="fas fa-check"></i> Generated</span>
                            <?php else: ?>
                                <span class="badge inactive">Not generated</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($rc['uploaded_at'])); ?></td>
                        <td>
                            <a href="/view-career-recommendations/<?php echo $rc['id']; ?>" class="btn-sm btn-sm-primary" style="text-decoration: none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
