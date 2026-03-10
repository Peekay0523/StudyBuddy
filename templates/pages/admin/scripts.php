<?php
include __DIR__ . '/../../layouts/admin_header.php';

// Group scripts by user
$scriptsByUser = [];
foreach ($scripts as $script) {
    $userId = $script['student_id'];
    if (!isset($scriptsByUser[$userId])) {
        $scriptsByUser[$userId] = [
            'username' => $script['username'],
            'email' => $script['email'],
            'scripts' => []
        ];
    }
    $scriptsByUser[$userId]['scripts'][] = $script;
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-file-alt"></i> Manage Scripts
        </h1>
        <p style="color: #6b7280;">View and manage all uploaded scripts grouped by user</p>
    </div>
    <a href="/admin" class="btn-secondary" style="text-decoration: none; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<?php if (empty($scriptsByUser)): ?>
    <div class="admin-section">
        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            No scripts found
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
                        <th>Total Scripts</th>
                        <th>Processed</th>
                        <th>Pending</th>
                        <th>Last Upload</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scriptsByUser as $userId => $userData): ?>
                        <?php
                        $processedCount = 0;
                        $pendingCount = 0;
                        $lastUpload = null;
                        
                        foreach ($userData['scripts'] as $script) {
                            if ($script['processed']) {
                                $processedCount++;
                            } else {
                                $pendingCount++;
                            }
                            if (!$lastUpload || strtotime($script['uploaded_at']) > strtotime($lastUpload)) {
                                $lastUpload = $script['uploaded_at'];
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
                                <span class="badge basic"><?php echo count($userData['scripts']); ?></span>
                            </td>
                            <td>
                                <span style="color: #22c55e;"><i class="fas fa-check"></i> <?php echo $processedCount; ?></span>
                            </td>
                            <td>
                                <span style="color: #f97316;"><i class="fas fa-clock"></i> <?php echo $pendingCount; ?></span>
                            </td>
                            <td>
                                <?php echo $lastUpload ? date('M d, Y H:i', strtotime($lastUpload)) : '-'; ?>
                            </td>
                            <td>
                                <button onclick="toggleUserScripts(<?php echo $userId; ?>)" class="btn-sm btn-sm-primary" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i> View Scripts
                                </button>
                            </td>
                        </tr>
                        <tr id="user-scripts-<?php echo $userId; ?>" style="display: none;">
                            <td colspan="7" style="padding: 0; background: #f9fafb;">
                                <div style="padding: 20px;">
                                    <h4 style="margin: 0 0 15px 0; color: #667eea;">
                                        <i class="fas fa-folder-open"></i> Scripts by <?php echo htmlspecialchars($userData['username']); ?>
                                    </h4>
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead style="background: #e5e7eb;">
                                            <tr>
                                                <th style="padding: 10px; text-align: left;">ID</th>
                                                <th style="padding: 10px; text-align: left;">File Name</th>
                                                <th style="padding: 10px; text-align: left;">Subject</th>
                                                <th style="padding: 10px; text-align: left;">Grade Level</th>
                                                <th style="padding: 10px; text-align: left;">Status</th>
                                                <th style="padding: 10px; text-align: left;">Uploaded</th>
                                                <th style="padding: 10px; text-align: left;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userData['scripts'] as $script): ?>
                                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                                    <td style="padding: 10px; color: #6b7280;">#<?php echo $script['id']; ?></td>
                                                    <td style="padding: 10px;">
                                                        <strong><?php echo htmlspecialchars(basename($script['file_path']) ?? 'Unknown'); ?></strong>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php if (!empty($script['subject'])): ?>
                                                            <span class="badge basic"><?php echo htmlspecialchars($script['subject']); ?></span>
                                                        <?php else: ?>
                                                            <span style="color: #9ca3af;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php if (!empty($script['grade_level'])): ?>
                                                            <span style="color: #6b7280; font-size: 13px;"><?php echo htmlspecialchars($script['grade_level']); ?></span>
                                                        <?php else: ?>
                                                            <span style="color: #9ca3af;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php if ($script['processed']): ?>
                                                            <span class="badge active"><i class="fas fa-check"></i> Processed</span>
                                                        <?php else: ?>
                                                            <span class="badge inactive">Not processed</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 10px;"><?php echo date('M d, Y H:i', strtotime($script['uploaded_at'])); ?></td>
                                                    <td style="padding: 10px;">
                                                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                                            <a href="/download-memorandum/<?php echo $script['id']; ?>" class="btn-sm btn-sm-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                            <form method="POST" action="/admin/scripts/delete" style="display: inline;" onsubmit="return confirm('Delete this script? This cannot be undone.');">
                                                                <input type="hidden" name="script_id" value="<?php echo $script['id']; ?>">
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
function toggleUserScripts(userId) {
    const row = document.getElementById('user-scripts-' + userId);
    if (row.style.display === 'none') {
        row.style.display = '';
    } else {
        row.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
