<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-crown"></i> Manage Subscriptions
        </h1>
        <p style="color: #6b7280;">View and manage all user subscriptions</p>
    </div>
    <a href="/admin" class="btn-secondary" style="text-decoration: none; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="admin-section">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Plan</th>
                <th>Price</th>
                <th>Status</th>
                <th>Period Start</th>
                <th>Period End</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($subscriptions)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        No subscriptions found
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($subscriptions as $sub): ?>
                    <tr>
                        <td style="color: #6b7280;">#<?php echo $sub['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($sub['username'] ?? ''); ?></strong><br>
                            <small style="color: #6b7280;"><?php echo htmlspecialchars($sub['email'] ?? ''); ?></small>
                        </td>
                        <td>
                            <span class="badge <?php echo htmlspecialchars($sub['plan'] ?? ''); ?>">
                                <?php echo htmlspecialchars($sub['plan'] ?? ''); ?>
                            </span>
                        </td>
                        <td>R<?php echo number_format($sub['price'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo $sub['status'] === 'active' ? 'active' : ($sub['status'] === 'cancelled' ? 'cancelled' : 'inactive'); ?>">
                                <?php echo htmlspecialchars($sub['status'] ?? ''); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($sub['current_period_start'] ?? 'now')); ?></td>
                        <td>
                            <?php if ($sub['current_period_end']): ?>
                                <?php echo date('M d, Y', strtotime($sub['current_period_end'])); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($sub['created_at'])); ?></td>
                        <td>
                            <?php if ($sub['status'] === 'active'): ?>
                                <form method="POST" action="/admin/subscriptions/cancel" style="display: inline;" onsubmit="return confirm('Cancel this subscription?');">
                                    <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                    <button type="submit" class="btn-sm btn-sm-warning">
                                        <i class="fas fa-ban"></i> Cancel
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
