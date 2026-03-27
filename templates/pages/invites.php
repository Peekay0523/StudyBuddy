<?php
$pageTitle = 'My Invitations - StudySmart';
$currentPage = 'invites';
include __DIR__ . '/../layouts/header.php';

// Get user's points and subscription status
require_once __DIR__ . '/../../models/UserPoints.php';
require_once __DIR__ . '/../../config/config.php';
$pointsModel = new UserPoints();
$user = getCurrentUser();
$userPoints = $pointsModel->getUserPoints($user['id']);
$points = $userPoints['points'] ?? 0;
$freeScans = $userPoints['free_scans'] ?? 0;

// Check if user is on free tier
$scanInfo = getScanLimitInfo($user['id']);
$isFreeTier = $scanInfo['is_free_tier'];
?>

<style>
.invites-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.invites-header {
    margin-bottom: 30px;
}

.invites-title {
    font-size: 28px;
    color: #1e293b;
    margin: 0 0 8px 0;
}

.invites-subtitle {
    color: #64748b;
    margin: 0;
    font-size: 14px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: transform 0.2s;
    margin-bottom: 20px;
}

.back-btn:hover {
    transform: translateY(-2px);
}

/* Points Banner */
.points-banner {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 2px solid #f59e0b;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}

.points-banner-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.points-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.points-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.points-icon i {
    font-size: 24px;
    color: white;
}

.points-text h3 {
    margin: 0 0 5px 0;
    color: #92400e;
    font-size: 18px;
}

.points-text p {
    margin: 0;
    color: #78350f;
    font-size: 14px;
}

.points-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-left: 4px solid;
}

.stat-card.total { border-color: #0ea5e9; background: linear-gradient(135deg, #f0f9ff, #e0f2fe); }
.stat-card.pending { border-color: #f59e0b; background: linear-gradient(135deg, #fef3c7, #fde68a); }
.stat-card.accepted { border-color: #10b981; background: linear-gradient(135deg, #d1fae5, #a7f3d0); }
.stat-card.expired { border-color: #ef4444; background: linear-gradient(135deg, #fee2e2, #fecaca); }

.stat-value {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    color: #64748b;
}

.stat-card.total .stat-value { color: #0369a1; }
.stat-card.pending .stat-value { color: #92400e; }
.stat-card.accepted .stat-value { color: #065f46; }
.stat-card.expired .stat-value { color: #991b1b; }

/* Invites Card */
.invites-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.invites-card h2 {
    margin: 0 0 20px 0;
    color: #1e293b;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Table Styles */
.invites-table-container {
    overflow-x: auto;
}

.invites-table {
    width: 100%;
    border-collapse: collapse;
}

.invites-table thead {
    border-bottom: 2px solid #e2e8f0;
}

.invites-table th {
    text-align: left;
    padding: 12px;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

.invites-table td {
    padding: 16px 12px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.invites-table tbody tr:hover {
    background: #f8fafc;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.pending { background: #fef3c7; color: #92400e; }
.status-badge.accepted { background: #d1fae5; color: #065f46; }
.status-badge.rejected { background: #fee2e2; color: #991b1b; }
.status-badge.expired { background: #f1f5f9; color: #64748b; }

/* Buttons */
.btn-cancel {
    padding: 6px 12px;
    font-size: 12px;
    background: white;
    border: 1px solid #e2e8f0;
    color: #64748b;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state p {
    margin: 0 0 20px 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .invites-page {
        padding: 15px;
    }

    .invites-title {
        font-size: 22px;
    }

    .points-banner {
        padding: 15px;
    }

    .points-banner-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .points-actions {
        width: 100%;
    }

    .points-actions a {
        flex: 1;
        text-align: center;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .stat-card {
        padding: 15px;
    }

    .stat-value {
        font-size: 24px;
    }

    .invites-card {
        padding: 15px;
    }

    /* Convert table to cards on mobile */
    .invites-table thead {
        display: none;
    }

    .invites-table tbody tr {
        display: block;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .invites-table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
    }

    .invites-table td:last-child {
        border-bottom: none;
    }

    .invites-table td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #64748b;
        font-size: 13px;
        margin-right: 15px;
    }

    .invites-table td .status-badge {
        margin-left: auto;
    }

    .invites-table td .btn-cancel {
        margin-left: auto;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .points-info {
        flex-direction: column;
        align-items: flex-start;
    }

    .invites-table td {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }

    .invites-table td::before {
        margin-right: 0;
    }

    .invites-table td .status-badge,
    .invites-table td .btn-cancel {
        margin-left: 0;
        width: 100%;
        text-align: center;
    }
}
</style>

<div class="invites-page">
    <div class="invites-header">
        <a href="/dashboard" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <h1 class="invites-title">
            <i class="fas fa-paper-plane" style="color: #0ea5e9;"></i> My Invitations
        </h1>
        <p class="invites-subtitle">Track invitations sent to your friends</p>
    </div>

    <!-- Points Summary Banner (Free Tier Only) -->
    <?php if ($isFreeTier): ?>
    <div class="points-banner">
        <div class="points-banner-content">
            <div class="points-info">
                <div class="points-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="points-text">
                    <h3><i class="fas fa-gift"></i> Your Rewards</h3>
                    <p>
                        <strong style="color: #f59e0b; font-size: 20px;"><?php echo $points; ?> Points</strong>
                        <?php if ($freeScans > 0): ?>
                            <span style="color: #059669; margin-left: 15px;">
                                <i class="fas fa-check-circle"></i> <?php echo $freeScans; ?> Free Scan(s)
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="points-actions">
                <a href="/dashboard" class="btn-secondary" style="padding: 10px 16px; font-size: 14px; border: 2px solid #f59e0b; color: #f59e0b; background: white; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-users"></i> Invite More
                </a>
                <?php if ($points >= 500): ?>
                    <a href="/scan" class="btn-primary" style="padding: 10px 16px; font-size: 14px; background: linear-gradient(135deg, #10b981, #059669); border: none; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-exchange-alt"></i> Convert to Scan
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Invite Stats -->
    <?php if (!empty($stats)): ?>
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-value"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Sent</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-value"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card accepted">
            <div class="stat-value"><?php echo $stats['accepted']; ?></div>
            <div class="stat-label">Accepted</div>
        </div>
        <div class="stat-card expired">
            <div class="stat-value"><?php echo $stats['expired_or_rejected']; ?></div>
            <div class="stat-label">Expired/Rejected</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Invites List -->
    <div class="invites-card">
        <h2>
            <i class="fas fa-paper-plane" style="color: #0ea5e9;"></i> Sent Invitations
        </h2>

        <?php if (empty($invites)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>You haven't sent any invitations yet.</p>
                <button onclick="window.location.href='/dashboard'" class="btn-primary" style="padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-plus"></i> Send Your First Invite
                </button>
            </div>
        <?php else: ?>
            <div class="invites-table-container">
                <table class="invites-table">
                    <thead>
                        <tr>
                            <th>Friend's Email</th>
                            <th>Group</th>
                            <th>Sent Date</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invites as $invite): ?>
                            <tr>
                                <td data-label="Friend's Email">
                                    <div style="font-weight: 500; color: #1e293b;">
                                        <?php echo htmlspecialchars($invite['friend_email']); ?>
                                    </div>
                                    <?php if (!empty($invite['friend_name'])): ?>
                                        <div style="font-size: 12px; color: #94a3b8;"><?php echo htmlspecialchars($invite['friend_name']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Group">
                                    <?php if (!empty($invite['group_title'])): ?>
                                        <span style="background: #e0e7ff; color: #4f46e5; padding: 4px 10px; border-radius: 12px; font-size: 12px;">
                                            <?php echo htmlspecialchars($invite['group_title']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 12px;">General</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Sent Date">
                                    <span style="font-size: 13px; color: #64748b;">
                                        <?php echo date('M d, Y', strtotime($invite['created_at'])); ?>
                                    </span>
                                </td>
                                <td data-label="Expires">
                                    <span style="font-size: 13px; color: #64748b;">
                                        <?php echo date('M d, Y', strtotime($invite['expires_at'])); ?>
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $status = $invite['status'];
                                    if ($status === 'pending' && strtotime($invite['expires_at']) < time()) {
                                        $status = 'expired';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status; ?>">
                                        <?php echo strtoupper($status); ?>
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <?php if ($invite['status'] === 'pending' && strtotime($invite['expires_at']) > time()): ?>
                                        <button onclick="if(confirm('Cancel this invitation?')) window.location.href='/study-group/cancel-invite/<?php echo $invite['id']; ?>'"
                                                class="btn-cancel">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1; font-size: 12px;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
