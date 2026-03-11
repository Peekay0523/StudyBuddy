<?php
$pageTitle = 'My Invitations - StudySmart';
$currentPage = 'invites';
include __DIR__ . '/../layouts/header.php';

// Get user's points
require_once __DIR__ . '/../../models/UserPoints.php';
$pointsModel = new UserPoints();
$user = getCurrentUser();
$userPoints = $pointsModel->getUserPoints($user['id']);
$points = $userPoints['points'] ?? 0;
$freeScans = $userPoints['free_scans'] ?? 0;
?>

<h1 class="title">My Invitations</h1>
<p class="subtitle">Track invitations sent to your friends</p>

<a href="/dashboard" class="btn-primary" style="text-decoration: none; display: inline-block; margin-bottom: 20px;">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>

<!-- Points Summary Banner -->
<div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <i class="fas fa-coins" style="font-size: 24px; color: white;"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 5px 0; color: #92400e; font-size: 18px;">
                    <i class="fas fa-gift"></i> Your Rewards
                </h3>
                <p style="margin: 0; color: #78350f; font-size: 14px;">
                    <strong style="color: #f59e0b; font-size: 20px;"><?php echo $points; ?> Points</strong>
                    <?php if ($freeScans > 0): ?>
                        <span style="color: #059669; margin-left: 15px;">
                            <i class="fas fa-check-circle"></i> <?php echo $freeScans; ?> Free Scan(s)
                        </span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="/dashboard" class="btn-secondary" style="padding: 10px 16px; font-size: 14px; text-decoration: none; border: 2px solid #f59e0b; color: #f59e0b; background: white;">
                <i class="fas fa-users"></i> Invite More Friends
            </a>
            <?php if ($points >= 500): ?>
                <a href="/scan" class="btn-primary" style="padding: 10px 16px; font-size: 14px; background: linear-gradient(135deg, #10b981, #059669); border: none; text-decoration: none;">
                    <i class="fas fa-exchange-alt"></i> Convert to Free Scan
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Invite Stats -->
<?php if (!empty($stats)): ?>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="feature-card" style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 1px solid #0ea5e9;">
        <div style="font-size: 28px; color: #0369a1; font-weight: bold;"><?php echo $stats['total']; ?></div>
        <div style="font-size: 13px; color: #0c4a6e;">Total Sent</div>
    </div>
    <div class="feature-card" style="background: linear-gradient(135deg, #fef3c7, #fde68a); border: 1px solid #f59e0b;">
        <div style="font-size: 28px; color: #92400e; font-weight: bold;"><?php echo $stats['pending']; ?></div>
        <div style="font-size: 13px; color: #78350f;">Pending</div>
    </div>
    <div class="feature-card" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); border: 1px solid #10b981;">
        <div style="font-size: 28px; color: #065f46; font-weight: bold;"><?php echo $stats['accepted']; ?></div>
        <div style="font-size: 13px; color: #064e3b;">Accepted</div>
    </div>
    <div class="feature-card" style="background: linear-gradient(135deg, #fee2e2, #fecaca); border: 1px solid #ef4444;">
        <div style="font-size: 28px; color: #991b1b; font-weight: bold;"><?php echo $stats['expired_or_rejected']; ?></div>
        <div style="font-size: 13px; color: #7f1d1d;">Expired/Rejected</div>
    </div>
</div>
<?php endif; ?>

<!-- Invites List -->
<section class="feature-card">
    <h2 style="margin-bottom: 20px; color: #1e293b;">
        <i class="fas fa-paper-plane" style="color: #0ea5e9;"></i> Sent Invitations
    </h2>

    <?php if (empty($invites)): ?>
        <div style="text-align: center; padding: 40px; color: #64748b;">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
            <p>You haven't sent any invitations yet.</p>
            <button onclick="window.location.href='/dashboard'" class="btn-primary" style="margin-top: 15px;">
                <i class="fas fa-plus"></i> Send Your First Invite
            </button>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0;">
                        <th style="text-align: left; padding: 12px; color: #64748b; font-size: 13px;">Friend's Email</th>
                        <th style="text-align: left; padding: 12px; color: #64748b; font-size: 13px;">Group</th>
                        <th style="text-align: left; padding: 12px; color: #64748b; font-size: 13px;">Sent Date</th>
                        <th style="text-align: left; padding: 12px; color: #64748b; font-size: 13px;">Expires</th>
                        <th style="text-align: left; padding: 12px; color: #64748b; font-size: 13px;">Status</th>
                        <th style="text-align: left; padding: 12px; color: #64748b; font-size: 13px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invites as $invite): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px;">
                                <?php echo htmlspecialchars($invite['friend_email']); ?>
                                <?php if (!empty($invite['friend_name'])): ?>
                                    <div style="font-size: 12px; color: #94a3b8;"><?php echo htmlspecialchars($invite['friend_name']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if (!empty($invite['group_title'])): ?>
                                    <span style="background: #e0e7ff; color: #4f46e5; padding: 4px 10px; border-radius: 12px; font-size: 12px;">
                                        <?php echo htmlspecialchars($invite['group_title']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 12px;">General</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; font-size: 13px; color: #64748b;">
                                <?php echo date('M d, Y', strtotime($invite['created_at'])); ?>
                            </td>
                            <td style="padding: 12px; font-size: 13px; color: #64748b;">
                                <?php echo date('M d, Y', strtotime($invite['expires_at'])); ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php
                                $statusColors = [
                                    'pending' => 'background: #fef3c7; color: #92400e;',
                                    'accepted' => 'background: #d1fae5; color: #065f46;',
                                    'rejected' => 'background: #fee2e2; color: #991b1b;',
                                    'expired' => 'background: #f1f5f9; color: #64748b;'
                                ];
                                $status = $invite['status'];
                                if ($status === 'pending' && strtotime($invite['expires_at']) < time()) {
                                    $status = 'expired';
                                }
                                ?>
                                <span style="<?php echo $statusColors[$status] ?? 'background: #f1f5f9; color: #64748b;'; ?> padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    <?php echo strtoupper($status); ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <?php if ($invite['status'] === 'pending' && strtotime($invite['expires_at']) > time()): ?>
                                    <button onclick="if(confirm('Cancel this invitation?')) window.location.href='/study-group/cancel-invite/<?php echo $invite['id']; ?>'"
                                            class="btn-secondary" style="padding: 6px 12px; font-size: 12px;">
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
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
