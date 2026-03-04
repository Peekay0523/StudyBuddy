<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?>" style="margin-bottom: 20px;">
        <?php echo $_SESSION['flash_message']; ?>
        <button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; cursor: pointer; font-size: 18px;">&times;</button>
    </div>
    <?php
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    ?>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-crown"></i> Manage Subscriptions
        </h1>
        <p style="color: #6b7280;">View and manage all user subscriptions</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <!-- Filter Buttons -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="/admin/subscriptions?filter=all" 
               class="btn-sm <?php echo ($filter ?? 'all') === 'all' ? 'btn-sm-info' : 'btn-sm-secondary'; ?>"
               style="text-decoration: none;">
                <i class="fas fa-list"></i> All
            </a>
            <a href="/admin/subscriptions?filter=pending_eft" 
               class="btn-sm <?php echo ($filter ?? '') === 'pending_eft' ? 'btn-sm-info' : 'btn-sm-secondary'; ?>"
               style="text-decoration: none;">
                <i class="fas fa-clock"></i> Pending EFT
            </a>
            <a href="/admin/subscriptions?filter=active" 
               class="btn-sm <?php echo ($filter ?? '') === 'active' ? 'btn-sm-info' : 'btn-sm-secondary'; ?>"
               style="text-decoration: none;">
                <i class="fas fa-check-circle"></i> Active
            </a>
            <a href="/admin/subscriptions?filter=trial" 
               class="btn-sm <?php echo ($filter ?? '') === 'trial' ? 'btn-sm-info' : 'btn-sm-secondary'; ?>"
               style="text-decoration: none;">
                <i class="fas fa-star"></i> Trial
            </a>
            <a href="/admin/subscriptions?filter=expired" 
               class="btn-sm <?php echo ($filter ?? '') === 'expired' ? 'btn-sm-info' : 'btn-sm-secondary'; ?>"
               style="text-decoration: none;">
                <i class="fas fa-times-circle"></i> Expired
            </a>
            <a href="/admin/subscriptions?filter=cancelled" 
               class="btn-sm <?php echo ($filter ?? '') === 'cancelled' ? 'btn-sm-info' : 'btn-sm-secondary'; ?>"
               style="text-decoration: none;">
                <i class="fas fa-ban"></i> Cancelled
            </a>
        </div>
        <a href="/admin" class="btn-secondary" style="text-decoration: none; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="admin-section">
    <div class="table-responsive">
        <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Plan</th>
                <th>Price</th>
                <th>Status</th>
                <th>Payment Reference</th>
                <th>Period Start</th>
                <th>Period End</th>
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
                            <span class="badge <?php echo $sub['status'] === 'active' ? 'active' : ($sub['status'] === 'cancelled' ? 'cancelled' : ($sub['status'] === 'trial' ? 'basic' : ($sub['status'] === 'pending_eft' ? 'premium' : 'inactive'))) ?>">
                                <?php 
                                    $statusDisplay = $sub['status'] === 'pending_eft' ? 'Pending EFT' : htmlspecialchars($sub['status'] ?? '');
                                    echo $statusDisplay;
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($sub['status'] === 'pending_eft' && !empty($sub['payment_reference'])): ?>
                                <span style="color: #dc2626; font-weight: 600; font-size: 13px;">
                                    <?php echo htmlspecialchars($sub['payment_reference']); ?>
                                </span>
                                <br>
                                <small style="color: #6b7280;">
                                    Paid: R<?php echo number_format($sub['price'], 2); ?>
                                </small>
                            <?php else: ?>
                                <span style="color: #6b7280; font-size: 13px;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($sub['current_period_start'] ?? 'now')); ?></td>
                        <td>
                            <?php if ($sub['current_period_end']): ?>
                                <?php echo date('M d, Y', strtotime($sub['current_period_end'])); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <?php if ($sub['status'] === 'pending_eft'): ?>
                                    <!-- View EFT Button -->
                                    <button type="button" onclick="toggleEFTDetails(<?php echo $sub['id']; ?>)"
                                            class="btn-sm btn-sm-info">
                                        <i class="fas fa-eye"></i> View EFT
                                    </button>
                                    
                                    <!-- EFT Details Modal -->
                                    <div id="eft-details-<?php echo $sub['id']; ?>" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); z-index: 1000; max-width: 500px; width: 90%; max-height: 85vh; overflow-y: auto;">
                                        <h3 style="margin-bottom: 20px; color: #1f2937;">
                                            <i class="fas fa-university"></i> EFT Payment Details
                                        </h3>
                                        
                                        <div style="text-align: left;">
                                            <p style="margin-bottom: 10px;"><strong>User:</strong> <?php echo htmlspecialchars($sub['username']); ?></p>
                                            <p style="margin-bottom: 10px;"><strong>Email:</strong> <?php echo htmlspecialchars($sub['email'] ?? ''); ?></p>
                                            <p style="margin-bottom: 10px;"><strong>Phone:</strong> <?php echo htmlspecialchars($sub['phone'] ?? 'N/A'); ?></p>
                                            <hr style="margin: 15px 0; border: none; border-top: 1px solid #e5e7eb;">
                                            <p style="margin-bottom: 10px;"><strong>Plan:</strong> <?php echo htmlspecialchars($sub['plan']); ?></p>
                                            <p style="margin-bottom: 10px;"><strong>Reference:</strong> <span style="color: #dc2626; font-weight: 600;"><?php echo htmlspecialchars($sub['payment_reference'] ?? 'N/A'); ?></span></p>
                                            <p style="margin-bottom: 10px;"><strong>Amount Paid:</strong> <span style="color: #16a34a; font-weight: 600;">R<?php echo number_format($sub['price'], 2); ?></span></p>
                                            <p style="margin-bottom: 10px;"><strong>Payment Date:</strong> <?php echo date('M d, Y', strtotime($sub['payment_date'] ?? 'now')); ?></p>
                                            <p style="margin-bottom: 10px;"><strong>Submitted:</strong> <?php echo date('M d, Y H:i', strtotime($sub['created_at'])); ?></p>
                                            
                                            <?php if (!empty($sub['proof_path'])): ?>
                                                <?php
                                                $ext = strtolower(pathinfo($sub['proof_path'], PATHINFO_EXTENSION));
                                                $fullPath = __DIR__ . '/../../' . $sub['proof_path'];
                                                $fileExists = file_exists($fullPath);
                                                ?>
                                                <div style="margin-top: 15px; padding: 10px; background: #f0f9ff; border-radius: 6px;">
                                                    <p style="margin-bottom: 8px;"><strong>Proof of Payment:</strong></p>

                                                    <!-- PDF/Image Preview -->
                                                    <div style="margin-bottom: 10px; border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden; background: #f9fafb;">
                                                        <?php if (!$fileExists): ?>
                                                            <div style="padding: 40px; text-align: center; color: #dc2626;">
                                                                <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px;"></i>
                                                                <p><strong>File not found on server</strong></p>
                                                                <p style="font-size: 12px; margin-top: 8px;">Path: <?php echo htmlspecialchars($sub['proof_path']); ?></p>
                                                            </div>
                                                        <?php elseif ($ext === 'pdf'): ?>
                                                            <iframe src="/<?php echo htmlspecialchars($sub['proof_path']); ?>#toolbar=1"
                                                                    style="width: 100%; height: 500px; border: none;"
                                                                    title="Proof of Payment PDF"
                                                                    onload="this.style.opacity='1'"
                                                                    onerror="this.outerHTML='<div style=\'padding:40px;text-align:center;color:#dc2626;\'><i class=\'fas fa-exclamation-triangle\' style=\'font-size:48px;margin-bottom:15px;\'></i><p>PDF failed to load</p></div>'">
                                                            </iframe>
                                                            <p style="padding: 10px; background: #fef3c7; color: #92400e; font-size: 13px; margin: 0; border-top: 1px solid #fcd34d;">
                                                                <i class="fas fa-info-circle"></i> If PDF doesn't display above, click "Open in New Tab" or "Download Proof" below.
                                                            </p>
                                                        <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                                            <img src="/<?php echo htmlspecialchars($sub['proof_path']); ?>"
                                                                 alt="Proof of Payment"
                                                                 style="width: 100%; height: auto; display: block; max-height: 500px; object-fit: contain;">
                                                        <?php else: ?>
                                                            <div style="padding: 40px; text-align: center; color: #6b7280;">
                                                                <i class="fas fa-file" style="font-size: 48px; margin-bottom: 15px;"></i>
                                                                <p>Preview not available for this file type</p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                                        <a href="/admin/subscriptions/download-proof/<?php echo $sub['id']; ?>"
                                                           style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: linear-gradient(135deg, #16a34a 0%, #059669 100%); color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                                                            <i class="fas fa-download"></i> Download Proof
                                                        </a>
                                                        <?php if ($ext === 'pdf' && $fileExists): ?>
                                                            <a href="/<?php echo htmlspecialchars($sub['proof_path']); ?>"
                                                               target="_blank"
                                                               style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: white; text-decoration: none; border-radius: 6px; font-size: 13px; transition: all 0.2s;">
                                                                <i class="fas fa-external-link-alt"></i> Open in New Tab
                                                            </a>
                                                        <?php endif; ?>
                                                        <span style="font-size: 12px; color: #6b7280;">
                                                            <i class="fas fa-file"></i> <?php echo htmlspecialchars(strtoupper($ext)); ?> - <?php echo htmlspecialchars(basename($sub['proof_path'])); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div style="margin-top: 15px; padding: 10px; background: #fef3c7; border-radius: 6px; border-left: 4px solid #f59e0b;">
                                                    <p style="margin: 0; color: #92400e;">
                                                        <i class="fas fa-exclamation-triangle"></i> No proof of payment uploaded
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;">
                                            <form method="POST" action="/admin/subscriptions/approve-eft" style="display: inline;">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <button type="submit" class="btn-sm btn-sm-success" onclick="return confirm('Approve this EFT payment and activate subscription?');">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            
                                            <button type="button" onclick="showRejectModal(<?php echo $sub['id']; ?>)" 
                                                    class="btn-sm btn-sm-danger">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                            
                                            <form method="POST" action="/admin/subscriptions/delete" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this subscription? This action cannot be undone.');">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <button type="submit" class="btn-sm btn-sm-secondary">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                            
                                            <button type="button" onclick="toggleEFTDetails(<?php echo $sub['id']; ?>)" 
                                                    class="btn-sm btn-sm-secondary">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Reject Modal -->
                                    <div id="reject-modal-<?php echo $sub['id']; ?>" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); z-index: 1001; max-width: 400px; width: 90%; max-height: 85vh; overflow-y: auto;">
                                        <h3 style="margin-bottom: 15px; color: #dc2626;">
                                            <i class="fas fa-exclamation-triangle"></i> Reject EFT Payment
                                        </h3>
                                        <form method="POST" action="/admin/subscriptions/reject-eft">
                                            <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                            <div style="margin-bottom: 20px;">
                                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Rejection Reason</label>
                                                <textarea name="rejection_reason" rows="3" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;" placeholder="e.g., Payment amount incorrect, Invalid reference, etc."><?php echo htmlspecialchars($_POST['rejection_reason'] ?? 'Payment verification failed'); ?></textarea>
                                            </div>
                                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                                <button type="button" onclick="hideRejectModal(<?php echo $sub['id']; ?>)" class="btn-sm btn-sm-secondary">Cancel</button>
                                                <button type="submit" class="btn-sm btn-sm-danger">
                                                    <i class="fas fa-times"></i> Reject Payment
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <div id="overlay-<?php echo $sub['id']; ?>" onclick="toggleEFTDetails(<?php echo $sub['id']; ?>)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999;"></div>
                                    
                                <?php else: ?>
                                    <!-- Status Dropdown for non-pending subscriptions -->
                                    <select onchange="if(this.value) changeStatus(<?php echo $sub['id']; ?>, this.value)"
                                            style="padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; background: white; cursor: pointer;">
                                        <option value="">Change Status</option>
                                        <option value="active" <?php echo $sub['status'] === 'active' ? 'selected' : ''; ?>>Activate</option>
                                        <option value="trial" <?php echo $sub['status'] === 'trial' ? 'selected' : ''; ?>>Trial</option>
                                        <option value="expired" <?php echo $sub['status'] === 'expired' ? 'selected' : ''; ?>>Expire</option>
                                        <option value="cancelled" <?php echo $sub['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancel</option>
                                    </select>
                                    
                                    <?php if ($sub['status'] === 'active' || $sub['status'] === 'trial'): ?>
                                        <form method="POST" action="/admin/subscriptions/cancel" style="display: inline;" onsubmit="return confirm('Cancel this subscription?');">
                                            <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                            <button type="submit" class="btn-sm btn-sm-warning" title="Cancel Subscription">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <!-- Delete Button -->
                                    <form method="POST" action="/admin/subscriptions/delete" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this subscription? This action cannot be undone.');">
                                        <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                        <button type="submit" class="btn-sm btn-sm-danger" title="Delete Subscription">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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

<script>
function changeStatus(subscriptionId, newStatus) {
    if (!confirm('Are you sure you want to change this subscription status to "' + newStatus + '"?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/subscriptions/change-status';
    
    const subIdInput = document.createElement('input');
    subIdInput.type = 'hidden';
    subIdInput.name = 'subscription_id';
    subIdInput.value = subscriptionId;
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'new_status';
    statusInput.value = newStatus;
    
    form.appendChild(subIdInput);
    form.appendChild(statusInput);
    document.body.appendChild(form);
    form.submit();
}

function toggleEFTDetails(subscriptionId) {
    const details = document.getElementById('eft-details-' + subscriptionId);
    const overlay = document.getElementById('overlay-' + subscriptionId);
    const isHidden = details.style.display === 'none';
    details.style.display = isHidden ? 'block' : 'none';
    overlay.style.display = isHidden ? 'block' : 'none';
}

function showRejectModal(subscriptionId) {
    const modal = document.getElementById('reject-modal-' + subscriptionId);
    const overlay = document.getElementById('overlay-' + subscriptionId);
    modal.style.display = 'block';
    overlay.style.display = 'block';
}

function hideRejectModal(subscriptionId) {
    const modal = document.getElementById('reject-modal-' + subscriptionId);
    const overlay = document.getElementById('overlay-' + subscriptionId);
    modal.style.display = 'none';
    overlay.style.display = 'none';
}

function viewProof(proofPath) {
    const modal = document.getElementById('proof-view-modal');
    const overlay = document.getElementById('proof-view-overlay');
    const img = document.getElementById('proof-image');
    const downloadLink = document.getElementById('proof-download-link');
    
    img.src = '/' + proofPath;
    downloadLink.href = '/' + proofPath;
    modal.style.display = 'block';
    overlay.style.display = 'block';
}

function closeProofView() {
    const modal = document.getElementById('proof-view-modal');
    const overlay = document.getElementById('proof-view-overlay');
    modal.style.display = 'none';
    overlay.style.display = 'none';
}
</script>

<style>
.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-sm:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.btn-sm-success {
    background: linear-gradient(135deg, #16a34a 0%, #059669 100%);
    color: white;
}

.btn-sm-danger {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.btn-sm-info {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: white;
}

.btn-sm-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
}

.btn-sm-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

/* Proof View Modal */
.proof-view-modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    z-index: 1002;
    max-width: 90%;
    max-height: 85vh;
    overflow: auto;
}

.proof-view-modal img {
    max-width: 100%;
    height: auto;
    display: block;
}

.proof-view-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 1000;
}
</style>

<!-- Proof View Modal (shared for all images) -->
<div id="proof-view-overlay" class="proof-view-overlay" onclick="closeProofView()"></div>
<div id="proof-view-modal" class="proof-view-modal">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="margin: 0; color: #1f2937;">
            <i class="fas fa-image"></i> Proof of Payment Preview
        </h3>
        <button type="button" onclick="closeProofView()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">
            &times;
        </button>
    </div>
    <img id="proof-image" src="" alt="Proof of Payment" style="max-width: 100%;">
    <div style="margin-top: 15px; text-align: center;">
        <a id="proof-download-link" href="" download class="btn-primary" style="text-decoration: none; display: inline-block;">
            <i class="fas fa-download"></i> Download Image
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
