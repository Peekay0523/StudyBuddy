<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div id="flash-alert" class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?>" style="margin-bottom: 20px; animation: slideIn 0.3s ease-out;">
        <?php echo $_SESSION['flash_message']; ?>
        <button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; cursor: pointer; font-size: 18px; color: currentColor;">&times;</button>
    </div>
    <?php
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    ?>
    <script>
        setTimeout(() => {
            const flash = document.getElementById('flash-alert');
            if (flash) {
                flash.style.animation = 'slideOut 0.3s ease-in forwards';
                setTimeout(() => flash.remove(), 300);
            }
        }, 5000);
    </script>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 5px; color: #1f2937; letter-spacing: -0.025em;">
            <i class="fas fa-crown" style="color: #fbbf24;"></i> Manage Subscriptions
        </h1>
        <p style="color: #6b7280; font-size: 15px;">View and manage all user subscriptions in real-time</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <!-- Filter Buttons -->
        <div class="filter-group">
            <a href="/admin/subscriptions?filter=all"
               class="filter-btn <?php echo ($filter ?? 'all') === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> All
            </a>
            <a href="/admin/subscriptions?filter=pending_eft"
               class="filter-btn <?php echo ($filter ?? '') === 'pending_eft' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Pending EFT
            </a>
            <a href="/admin/subscriptions?filter=bobpay"
               class="filter-btn <?php echo ($filter ?? '') === 'bobpay' ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i> BobPay
            </a>
            <a href="/admin/subscriptions?filter=active"
               class="filter-btn <?php echo ($filter ?? '') === 'active' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i> Active
            </a>
            <a href="/admin/subscriptions?filter=trial"
               class="filter-btn <?php echo ($filter ?? '') === 'trial' ? 'active' : ''; ?>">
                <i class="fas fa-star"></i> Trial
            </a>
            <a href="/admin/subscriptions?filter=expired"
               class="filter-btn <?php echo ($filter ?? '') === 'expired' ? 'active' : ''; ?>">
                <i class="fas fa-times-circle"></i> Expired
            </a>
            <a href="/admin/subscriptions?filter=cancelled"
               class="filter-btn <?php echo ($filter ?? '') === 'cancelled' ? 'active' : ''; ?>">
                <i class="fas fa-ban"></i> Cancelled
            </a>
        </div>
        <a href="/admin" class="btn-secondary-modern">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<div class="admin-section-modern">
    <div class="table-responsive">
        <table class="data-table-modern">
        <thead>
            <tr>
                <th>ID</th>
                <th>User Details</th>
                <th>Plan Type</th>
                <th>Amount</th>
                <th>Current Status</th>
                <th>Payment Info</th>
                <th>Period Validity</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($subscriptions)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 60px; color: #94a3b8;">
                        <div style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <p style="font-size: 18px; font-weight: 500;">No subscriptions found</p>
                        <p style="font-size: 14px;">Try adjusting your filters or search criteria</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($subscriptions as $sub): 
                    $isExpired = false;
                    if (!empty($sub['current_period_end'])) {
                        $isExpired = strtotime($sub['current_period_end']) < time();
                    }
                    
                    $displayStatus = $sub['status'];
                    if ($isExpired && $displayStatus === 'active') {
                        $displayStatus = 'expired';
                    }
                ?>
                    <tr class="<?php echo $isExpired ? 'row-expired' : ''; ?>">
                        <td data-label="ID" style="color: #94a3b8; font-weight: 500; font-size: 12px;">#<?php echo $sub['id']; ?></td>
                        <td data-label="User Details">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($sub['username'] ?? 'U', 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($sub['username'] ?? ''); ?></div>
                                    <div style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars($sub['email'] ?? ''); ?></div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Plan Type">
                            <div class="plan-badge <?php echo htmlspecialchars($sub['plan'] ?? 'free'); ?>">
                                <i class="fas <?php echo ($sub['plan'] ?? '') === 'premium' ? 'fa-gem' : 'fa-user'; ?>"></i>
                                <?php echo ucfirst(htmlspecialchars($sub['plan'] ?? 'Free')); ?>
                            </div>
                        </td>
                        <td data-label="Amount">
                            <div style="font-weight: 700; color: #1f2937;">R<?php echo number_format($sub['price'], 2); ?></div>
                        </td>
                        <td data-label="Current Status">
                            <?php if ($displayStatus === 'active'): ?>
                                <span class="status-chip status-active">
                                    <span class="dot"></span> Active
                                </span>
                            <?php elseif ($displayStatus === 'trial'): ?>
                                <span class="status-chip status-trial">
                                    <i class="fas fa-clock"></i> Free Trial
                                    <?php if (!empty($sub['current_period_end'])): ?>
                                        <small style="display: block; font-size: 10px; margin-top: 2px; opacity: 0.8;">
                                            <?php 
                                            $days = ceil((strtotime($sub['current_period_end']) - time()) / 86400);
                                            echo $days > 0 ? $days . ' days left' : 'Ending soon';
                                            ?>
                                        </small>
                                    <?php endif; ?>
                                </span>
                            <?php elseif ($displayStatus === 'expired' || $isExpired): ?>
                                <span class="status-chip status-expired">
                                    <i class="fas fa-exclamation-circle"></i> EXPIRED
                                </span>
                            <?php elseif ($displayStatus === 'pending_eft'): ?>
                                <span class="status-chip status-pending">
                                    <span class="pulse-dot"></span> Pending EFT
                                </span>
                            <?php elseif ($displayStatus === 'cancelled'): ?>
                                <span class="status-chip status-cancelled">
                                    <i class="fas fa-ban"></i> Cancelled
                                </span>
                            <?php else: ?>
                                <span class="status-chip status-inactive">
                                    <?php echo ucfirst(htmlspecialchars($displayStatus)); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Payment Info">
                            <?php if (!empty($sub['payment_method'])): ?>
                                <div class="payment-info">
                                    <?php
                                    $paymentMethod = $sub['payment_method'];
                                    $paymentIcon = 'fa-credit-card';
                                    $paymentLabel = ucfirst($paymentMethod);
                                    if ($paymentMethod === 'bobpay') {
                                        $paymentIcon = 'fa-shield-halved';
                                        $paymentLabel = 'BobPay';
                                    } else if ($paymentMethod === 'eft') {
                                        $paymentIcon = 'fa-university';
                                        $paymentLabel = 'EFT';
                                    }
                                    ?>
                                    <div class="method">
                                        <i class="fas <?php echo $paymentIcon; ?>"></i> <?php echo $paymentLabel; ?>
                                    </div>
                                    <?php if ($sub['status'] === 'pending_eft' && !empty($sub['payment_reference'])): ?>
                                        <div class="reference">Ref: <?php echo htmlspecialchars($sub['payment_reference']); ?></div>
                                    <?php elseif (!empty($sub['transaction_id'])): ?>
                                        <div class="transaction-id" title="<?php echo htmlspecialchars($sub['transaction_id']); ?>">
                                            TX: <?php echo htmlspecialchars(substr($sub['transaction_id'], 0, 12)); ?>...
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-size: 12px;">Manual/Internal</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Period Validity">
                            <div style="font-size: 13px;">
                                <div style="color: #6b7280;">Start: <?php echo date('M d, Y', strtotime($sub['current_period_start'] ?? 'now')); ?></div>
                                <div style="font-weight: 500; color: <?php echo $isExpired ? '#dc2626' : '#1f2937'; ?>;">
                                    End: <?php echo $sub['current_period_end'] ? date('M d, Y', strtotime($sub['current_period_end'])) : '-'; ?>
                                </div>
                            </div>
                        </td>
                        <td data-label="Actions" style="text-align: right;">
                            <div class="action-menu-container">
                                <?php if ($sub['status'] === 'pending_eft'): ?>
                                    <button type="button" onclick="toggleEFTDetails(<?php echo $sub['id']; ?>)"
                                            class="btn-action-primary" title="View Details">
                                        <i class="fas fa-eye"></i> Details
                                    </button>
                                <?php else: ?>
                                    <div class="dropdown-wrapper">
                                        <button type="button" class="btn-action-outline dropdown-toggle" onclick="toggleActionMenu(this)">
                                            Actions <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i>
                                        </button>
                                        <div class="dropdown-menu-modern">
                                            <a href="javascript:void(0)" onclick="confirmStatusChange(<?php echo $sub['id']; ?>, 'active', 'Activate')" class="<?php echo $sub['status'] === 'active' ? 'disabled' : ''; ?>">
                                                <i class="fas fa-check-circle" style="color: #16a34a;"></i> Activate
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmStatusChange(<?php echo $sub['id']; ?>, 'trial', 'Set to Trial')" class="<?php echo $sub['status'] === 'trial' ? 'disabled' : ''; ?>">
                                                <i class="fas fa-clock" style="color: #3b82f6;"></i> Start Trial
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmStatusChange(<?php echo $sub['id']; ?>, 'expired', 'Expire')" class="<?php echo $displayStatus === 'expired' ? 'disabled' : ''; ?>">
                                                <i class="fas fa-hourglass-end" style="color: #f59e0b;"></i> Expire
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmStatusChange(<?php echo $sub['id']; ?>, 'cancelled', 'Cancel')" class="<?php echo $sub['status'] === 'cancelled' ? 'disabled' : ''; ?>">
                                                <i class="fas fa-ban" style="color: #6b7280;"></i> Cancel
                                            </a>
                                            <div class="divider"></div>
                                            <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $sub['id']; ?>)" class="text-danger">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- EFT Details Modal (Keep existing logic but improve styling) -->
                            <?php if ($sub['status'] === 'pending_eft'): ?>
                                <div id="eft-details-<?php echo $sub['id']; ?>" class="modal-modern">
                                    <div class="modal-content-modern">
                                        <div class="modal-header-modern">
                                            <h3><i class="fas fa-university"></i> EFT Payment Details</h3>
                                            <button type="button" onclick="toggleEFTDetails(<?php echo $sub['id']; ?>)" class="btn-close">&times;</button>
                                        </div>
                                        <div class="modal-body-modern">
                                            <div class="details-grid">
                                                <div class="detail-item">
                                                    <label>User</label>
                                                    <p><?php echo htmlspecialchars($sub['username']); ?></p>
                                                </div>
                                                <div class="detail-item">
                                                    <label>Email</label>
                                                    <p><?php echo htmlspecialchars($sub['email'] ?? ''); ?></p>
                                                </div>
                                                <div class="detail-item">
                                                    <label>Amount</label>
                                                    <p class="text-success">R<?php echo number_format($sub['price'], 2); ?></p>
                                                </div>
                                                <div class="detail-item">
                                                    <label>Reference</label>
                                                    <p class="text-danger font-bold"><?php echo htmlspecialchars($sub['payment_reference'] ?? 'N/A'); ?></p>
                                                </div>
                                            </div>
                                            
                                            <div class="proof-container">
                                                <label>Proof of Payment</label>
                                                <?php if (!empty($sub['proof_path'])): ?>
                                                    <?php
                                                    $ext = strtolower(pathinfo($sub['proof_path'], PATHINFO_EXTENSION));
                                                    $fullPath = __DIR__ . '/../../../public/' . $sub['proof_path'];
                                                    $fileExists = file_exists($fullPath);
                                                    ?>
                                                    <div class="proof-preview">
                                                        <?php if (!$fileExists): ?>
                                                            <div class="file-error">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                <p>File not found on server</p>
                                                                <small style="display: block; margin-top: 5px; opacity: 0.7;">Path: <?php echo htmlspecialchars($sub['proof_path']); ?></small>
                                                            </div>
                                                        <?php elseif ($ext === 'pdf'): ?>
                                                            <iframe src="/<?php echo htmlspecialchars($sub['proof_path']); ?>#toolbar=0" class="pdf-iframe"></iframe>
                                                            <div class="pdf-overlay">
                                                                <a href="/admin/subscriptions/download-proof/<?php echo $sub['id']; ?>" class="btn-action-primary">
                                                                    <i class="fas fa-download"></i> Download PDF
                                                                </a>
                                                                <a href="/<?php echo htmlspecialchars($sub['proof_path']); ?>" target="_blank" class="btn-action-outline">
                                                                    <i class="fas fa-external-link-alt"></i> Open Full
                                                                </a>
                                                            </div>
                                                        <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                                            <img src="/<?php echo htmlspecialchars($sub['proof_path']); ?>" alt="Proof" onclick="viewProof('/<?php echo htmlspecialchars($sub['proof_path']); ?>')">
                                                            <div class="img-overlay">
                                                                <a href="/admin/subscriptions/download-proof/<?php echo $sub['id']; ?>" class="btn-action-primary btn-sm">
                                                                    <i class="fas fa-download"></i> Download
                                                                </a>
                                                                <span>Click to enlarge</span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="no-proof">
                                                        <i class="fas fa-info-circle"></i> No proof uploaded
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="modal-footer-modern">
                                            <div style="display: flex; gap: 10px; width: 100%;">
                                                <form method="POST" action="/admin/subscriptions/approve-eft" style="flex: 1;">
                                                    <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                    <button type="submit" class="btn-action-success w-full" onclick="return confirm('Approve this EFT payment?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <button type="button" onclick="showRejectModal(<?php echo $sub['id']; ?>)" class="btn-action-danger" style="flex: 1;">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                            <button type="button" onclick="toggleEFTDetails(<?php echo $sub['id']; ?>)" class="btn-action-outline w-full" style="margin-top: 10px;">Close</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div id="reject-modal-<?php echo $sub['id']; ?>" class="modal-modern small">
                                    <div class="modal-content-modern">
                                        <div class="modal-header-modern">
                                            <h3 class="text-danger">Reject Payment</h3>
                                        </div>
                                        <form method="POST" action="/admin/subscriptions/reject-eft">
                                            <div class="modal-body-modern">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <div class="form-group">
                                                    <label>Reason for rejection</label>
                                                    <textarea name="rejection_reason" rows="3" required placeholder="e.g., Incorrect amount, Invalid reference..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer-modern">
                                                <button type="button" onclick="hideRejectModal(<?php echo $sub['id']; ?>)" class="btn-action-outline">Cancel</button>
                                                <button type="submit" class="btn-action-danger">Reject Payment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div id="overlay-<?php echo $sub['id']; ?>" class="modal-overlay" onclick="toggleEFTDetails(<?php echo $sub['id']; ?>)"></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirm-modal" class="modal-modern small">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3 id="confirm-title">Confirm Action</h3>
        </div>
        <div class="modal-body-modern">
            <p id="confirm-message">Are you sure you want to proceed?</p>
        </div>
        <div class="modal-footer-modern">
            <button type="button" onclick="closeConfirmModal()" class="btn-action-outline">Cancel</button>
            <button type="button" id="confirm-btn" class="btn-action-primary">Proceed</button>
        </div>
    </div>
</div>
<div id="confirm-overlay" class="modal-overlay" onclick="closeConfirmModal()"></div>

<!-- Global Loading Spinner -->
<div id="loading-spinner" class="loading-overlay">
    <div class="spinner"></div>
    <p>Processing action...</p>
</div>

<script>
let currentAction = null;

function toggleActionMenu(btn) {
    const menu = btn.nextElementSibling;
    const allMenus = document.querySelectorAll('.dropdown-menu-modern');
    
    allMenus.forEach(m => {
        if (m !== menu) m.classList.remove('show');
    });
    
    menu.classList.toggle('show');
    
    // Close when clicking outside
    const closeMenu = (e) => {
        if (!btn.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('show');
            document.removeEventListener('click', closeMenu);
        }
    };
    document.addEventListener('click', closeMenu);
}

function confirmStatusChange(subscriptionId, status, label) {
    const title = `Confirm ${label}`;
    const message = `Are you sure you want to change this subscription status to <strong>${status}</strong>?`;
    
    showConfirmModal(title, message, () => {
        executeAction('/admin/subscriptions/change-status', {
            subscription_id: subscriptionId,
            new_status: status
        });
    });
}

function confirmDelete(subscriptionId) {
    showConfirmModal(
        'Delete Subscription', 
        '<span class="text-danger">Warning: This action cannot be undone.</span> Are you sure you want to delete this subscription records?',
        () => {
            executeAction('/admin/subscriptions/delete', {
                subscription_id: subscriptionId
            });
        },
        'btn-action-danger'
    );
}

function showConfirmModal(title, message, onConfirm, btnClass = 'btn-action-primary') {
    document.getElementById('confirm-title').innerText = title;
    document.getElementById('confirm-message').innerHTML = message;
    
    const confirmBtn = document.getElementById('confirm-btn');
    confirmBtn.className = 'btn-action-primary ' + btnClass;
    
    // Clone to remove old listeners
    const newBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
    
    newBtn.addEventListener('click', () => {
        closeConfirmModal();
        onConfirm();
    });
    
    document.getElementById('confirm-modal').classList.add('show');
    document.getElementById('confirm-overlay').classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.remove('show');
    document.getElementById('confirm-overlay').classList.remove('show');
}

function executeAction(url, data) {
    document.getElementById('loading-spinner').classList.add('show');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    
    for (const key in data) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = data[key];
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
}

function toggleEFTDetails(id) {
    const modal = document.getElementById('eft-details-' + id);
    const overlay = document.getElementById('overlay-' + id);
    modal.classList.toggle('show');
    overlay.classList.toggle('show');
}

function showRejectModal(id) {
    document.getElementById('reject-modal-' + id).classList.add('show');
}

function hideRejectModal(id) {
    document.getElementById('reject-modal-' + id).classList.remove('show');
}

function viewProof(url) {
    const previewModal = document.createElement('div');
    previewModal.className = 'image-fullscreen-preview';
    previewModal.innerHTML = `
        <div class="preview-content">
            <img src="${url}">
            <button onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    document.body.appendChild(previewModal);
}
</script>

<style>
/* Modern UI Variables & Resets */
:root {
    --primary: #2563eb;
    --success: #16a34a;
    --danger: #dc2626;
    --warning: #f59e0b;
    --gray: #6b7280;
    --light-bg: #f8fafc;
    --border: #e2e8f0;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes slideOut {
    from { transform: translateY(0); opacity: 1; }
    to { transform: translateY(-20px); opacity: 0; }
}

/* Header & Filters */
.filter-group {
    display: flex;
    background: #e2e8f0;
    padding: 4px;
    border-radius: 10px;
    gap: 2px;
}

.filter-btn {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-btn:hover {
    color: #1e293b;
    background: rgba(255,255,255,0.5);
}

.filter-btn.active {
    background: white;
    color: #2563eb;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.btn-secondary-modern {
    background: white;
    border: 1px solid #d1d5db;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    color: #374151;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-secondary-modern:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

/* Table Modern Styling */
.admin-section-modern {
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow);
    padding: 0;
    overflow: hidden;
    border: 1px solid var(--border);
}

.data-table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.data-table-modern th {
    background: #f8fafc;
    padding: 16px 20px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    font-weight: 700;
    border-bottom: 1px solid #f1f5f9;
}

.data-table-modern td {
    padding: 18px 20px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.data-table-modern tr:last-child td {
    border-bottom: none;
}

.data-table-modern tr:hover {
    background: #f1f5f9/50;
}

.row-expired {
    background: #fff5f5;
}

.row-expired td {
    border-bottom-color: #fee2e2 !important;
}

/* User Avatar */
.user-avatar {
    width: 36px;
    height: 36px;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}

/* Plan Badges */
.plan-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.plan-badge.premium { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.plan-badge.free { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

/* Status Chips */
.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.status-active {
    background: #dcfce7;
    color: #166534;
    box-shadow: 0 0 10px rgba(34, 197, 94, 0.2);
}

.status-active .dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
}

.status-trial {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

.status-expired {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-cancelled {
    background: #f1f5f9;
    color: #475569;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background: #f59e0b;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}

/* Payment Info */
.payment-info .method {
    font-weight: 600;
    color: #334155;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.payment-info .reference, .payment-info .transaction-id {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
    font-family: monospace;
}

/* Action Buttons & Dropdown */
.btn-action-primary {
    background: var(--primary);
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-action-outline {
    background: white;
    border: 1px solid #cbd5e1;
    color: #334155;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-action-outline:hover {
    background: #f8fafc;
    border-color: #94a3b8;
}

.dropdown-wrapper {
    position: relative;
    display: inline-block;
}

.dropdown-menu-modern {
    position: absolute;
    right: 0;
    top: calc(100% + 5px);
    background: white;
    min-width: 160px;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    z-index: 100;
    padding: 8px;
    border: 1px solid #e2e8f0;
    display: none;
    transform-origin: top right;
    animation: dropdownGrow 0.2s ease-out;
}

@keyframes dropdownGrow {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

.dropdown-menu-modern.show {
    display: block;
}

.dropdown-menu-modern a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    color: #475569;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    transition: background 0.2s;
}

.dropdown-menu-modern a:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.dropdown-menu-modern a.disabled {
    opacity: 0.5;
    pointer-events: none;
    background: none !important;
}

.dropdown-menu-modern .divider {
    height: 1px;
    background: #f1f5f9;
    margin: 6px 0;
}

.text-danger { color: #dc2626 !important; }
.font-bold { font-weight: 700; }

/* Modal Modern Styling */
.modal-modern {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    z-index: 1001;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-modern.small { max-width: 400px; }

.modal-modern.show {
    display: block;
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.show {
    display: block;
    opacity: 1;
}

.modal-header-modern {
    padding: 24px 30px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header-modern h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
}

.btn-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #94a3b8;
    cursor: pointer;
}

.modal-body-modern {
    padding: 30px;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

.detail-item label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    color: #64748b;
    font-weight: 700;
    margin-bottom: 4px;
}

.detail-item p {
    margin: 0;
    font-weight: 600;
    color: #1e293b;
}

.proof-container {
    margin-top: 20px;
}

.proof-container label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 10px;
}

.proof-preview {
    border: 2px dashed #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    background: #f8fafc;
}

.proof-preview img {
    width: 100%;
    display: block;
    max-height: 300px;
    object-fit: contain;
    cursor: zoom-in;
}

.pdf-iframe {
    width: 100%;
    height: 300px;
    border: none;
}

.pdf-overlay, .img-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.5);
    padding: 15px;
    display: flex;
    gap: 10px;
    justify-content: center;
    color: white;
    font-size: 12px;
}

.modal-footer-modern {
    padding: 20px 30px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}

/* Forms in Modal */
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-family: inherit;
    resize: vertical;
}

/* Loading Overlay */
.loading-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(255,255,255,0.8);
    z-index: 9999;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.loading-overlay.show { display: flex; }

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #2563eb;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Fullscreen Preview */
.image-fullscreen-preview {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.preview-content img {
    max-width: 100%;
    max-height: 90vh;
}

.preview-content button {
    position: absolute;
    top: -40px; right: -40px;
    background: none; border: none;
    color: white; font-size: 40px;
    cursor: pointer;
}

.w-full { width: 100%; }

.btn-action-success { background: var(--success); color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-action-danger { background: var(--danger); color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; }

/* Mobile Optimizations */
@media (max-width: 768px) {
    .data-table-modern thead { display: none; }
    .data-table-modern tr { display: block; margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px; }
    .data-table-modern td { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #f1f5f9; }
    .data-table-modern td:last-child { border-bottom: none; }
    .data-table-modern td::before { content: attr(data-label); font-weight: 700; color: #64748b; font-size: 12px; }
    
    .filter-group { overflow-x: auto; max-width: 100%; }
    .details-grid { grid-template-columns: 1fr; }
}
</style>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
