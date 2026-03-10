<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-users"></i> Manage Users
        </h1>
        <p style="color: #6b7280;">View and manage all registered users</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="/admin" class="btn-secondary" style="text-decoration: none; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Filters -->
<div class="admin-section" style="padding: 20px;">
    <form method="GET" action="/admin/users" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 250px;">
            <input 
                type="text" 
                name="search" 
                placeholder="Search by username or email..." 
                value="<?php echo htmlspecialchars($search); ?>"
                style="width: 100%; padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;"
            >
        </div>
        <select 
            name="filter" 
            style="padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; min-width: 150px;"
        >
            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Plans</option>
            <option value="free" <?php echo $filter === 'free' ? 'selected' : ''; ?>>Free</option>
            <option value="basic" <?php echo $filter === 'basic' ? 'selected' : ''; ?>>Basic</option>
            <option value="premium" <?php echo $filter === 'premium' ? 'selected' : ''; ?>>Premium</option>
        </select>
        <button type="submit" class="btn-primary" style="padding: 10px 20px;">
            <i class="fas fa-search"></i> Filter
        </button>
        <?php if ($search || $filter !== 'all'): ?>
            <a href="/admin/users" class="btn-secondary" style="padding: 10px 20px; text-decoration: none;">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Users Table -->
<div class="admin-section">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Subscription</th>
                    <th>Scripts</th>
                    <th>Report Cards</th>
                    <th>Study Plans</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 40px; color: #6b7280;">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            No users found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td style="color: #6b7280;">#<?php echo $user['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username'] ?? ''); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                            <td>
                                <span class="badge <?php echo $user['role'] === 'admin' ? 'premium' : 'free'; ?>">
                                    <?php echo htmlspecialchars($user['role'] ?? 'student'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo htmlspecialchars($user['subscription_plan'] ?? 'free'); ?>">
                                    <?php echo htmlspecialchars($user['subscription_plan'] ?? 'free'); ?>
                                </span>
                                <?php if ($user['subscription_status'] === 'active'): ?>
                                    <span style="font-size: 11px; color: #6b7280;">
                                        until <?php echo date('M d', strtotime($user['subscription_end'] ?? 'now')); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['scripts_count']; ?></td>
                            <td><?php echo $user['report_cards_count']; ?></td>
                            <td><?php echo $user['study_plans_count']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <a href="/admin/users/<?php echo $user['id']; ?>" class="btn-sm btn-sm-primary" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($user['id'] != getCurrentUser()['id']): ?>
                                        <button type="button" class="btn-sm btn-sm-danger" onclick="openDeleteModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" style="padding: 6px 12px; font-size: 13px; border-radius: 6px; border: none; background: #ef4444; color: white; cursor: pointer;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div style="text-align: center; margin-top: 20px; color: #6b7280;">
    Showing <?php echo count($users); ?> user(s)
</div>

<!-- Delete User Modal -->
<div id="deleteUserModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; max-width: 450px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #ef4444, #dc2626); display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                <i class="fas fa-exclamation-triangle" style="font-size: 24px;"></i>
            </div>
            <div>
                <h3 style="margin: 0; color: #1f2937; font-size: 18px;">Delete User</h3>
                <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">This action cannot be undone</p>
            </div>
        </div>

        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <p style="margin: 0; color: #991b1b; font-size: 14px;">
                <strong>Warning:</strong> This will permanently delete the user <strong id="delete-username" style="color: #7f1d1d;"></strong> and ALL associated data including:
            </p>
            <ul style="margin: 10px 0 0 20px; color: #991b1b; font-size: 13px; line-height: 1.8;">
                <li>All subscriptions (active and expired)</li>
                <li>All uploaded scripts and memorandums</li>
                <li>All report cards</li>
                <li>All study plans</li>
                <li>All scan history</li>
                <li>All study group memberships and created groups</li>
                <li>All chat messages</li>
            </ul>
        </div>

        <form method="POST" action="/admin/users/delete" id="deleteUserForm">
            <input type="hidden" name="user_id" id="delete-user-id">
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="closeDeleteModal()" style="padding: 10px 20px; background: #f1f5f9; color: #64748b; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 14px;">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #ef4444, #dc2626); padding: 10px 20px;">
                    <i class="fas fa-trash"></i> Yes, Delete User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(userId, username) {
    document.getElementById('delete-user-id').value = userId;
    document.getElementById('delete-username').textContent = username;
    document.getElementById('deleteUserModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteUserModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('deleteUserModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
