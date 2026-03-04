<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-file-alt"></i> Manage Scripts
        </h1>
        <p style="color: #6b7280;">View and manage all uploaded scripts</p>
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
                <th>Subject</th>
                <th>Size</th>
                <th>Memorandum</th>
                <th>Uploaded</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($scripts)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        No scripts found
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($scripts as $script): ?>
                    <tr>
                        <td style="color: #6b7280;">#<?php echo $script['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars(basename($script['file_name'])); ?></strong>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($script['username'] ?? ''); ?><br>
                            <small style="color: #6b7280;"><?php echo htmlspecialchars($script['email'] ?? ''); ?></small>
                        </td>
                        <td>
                            <?php if (!empty($script['subject'])): ?>
                                <span class="badge basic"><?php echo htmlspecialchars($script['subject'] ?? ''); ?></span>
                            <?php else: ?>
                                <span style="color: #9ca3af;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($script['file_size'])): ?>
                                <?php echo round($script['file_size'] / 1024, 1); ?> KB
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($script['memorandum_generated']): ?>
                                <span class="badge active"><i class="fas fa-check"></i> Generated</span>
                            <?php else: ?>
                                <span class="badge inactive">Not generated</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($script['uploaded_at'])); ?></td>
                        <td>
                            <a href="/download-memorandum/<?php echo $script['id']; ?>" class="btn-sm btn-sm-primary" style="text-decoration: none;">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <form method="POST" action="/admin/scripts/delete" style="display: inline;" onsubmit="return confirm('Delete this script? This cannot be undone.');">
                                <input type="hidden" name="script_id" value="<?php echo $script['id']; ?>">
                                <button type="submit" class="btn-sm btn-sm-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
