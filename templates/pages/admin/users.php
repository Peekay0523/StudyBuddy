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
                                <a href="/admin/users/<?php echo $user['id']; ?>" class="btn-sm btn-sm-primary" style="text-decoration: none;">
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

<div style="text-align: center; margin-top: 20px; color: #6b7280;">
    Showing <?php echo count($users); ?> user(s)
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
