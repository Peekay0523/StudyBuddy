<?php
requireAdmin();

$bobPay = new BobPayHelper();

// Get filters from request
$filters = [
    'include_retained_amount' => 'true',
    'limit' => 20,
    'order' => 'DESC',
    'order_by' => 'time_created'
];

if (!empty($_GET['status'])) {
    $filters['statuses'] = [$_GET['status']];
}

if (!empty($_GET['from_date'])) {
    $filters['start_date'] = $_GET['from_date'] . ' 00:00:00';
}

if (!empty($_GET['to_date'])) {
    $filters['end_date'] = $_GET['to_date'] . ' 23:59:59';
}

if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

// Get payment intents
$payments = $bobPay->getPaymentIntents($filters);

// Get payment methods
$paymentMethods = $bobPay->getPublicPaymentMethods('SAN001');

$pageTitle = 'BobPay Payment Management';
$currentPage = 'admin-bobpay';

include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-overview">
    <h1 class="title"><i class="fas fa-credit-card"></i> BobPay Payment Management</h1>
    <p class="subtitle">View and manage all BobPay payments</p>
</div>

<!-- Filters -->
<div class="feature-card" style="margin-top: 30px;">
    <h3><i class="fas fa-filter"></i> Filter Payments</h3>
    <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px;">
                <option value="">All Statuses</option>
                <option value="paid" <?php echo ($_GET['status'] ?? '') === 'paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="unpaid" <?php echo ($_GET['status'] ?? '') === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                <option value="refunded" <?php echo ($_GET['status'] ?? '') === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                <option value="canceled" <?php echo ($_GET['status'] ?? '') === 'canceled' ? 'selected' : ''; ?>>Canceled</option>
            </select>
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">From Date</label>
            <input type="date" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>" 
                   style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px;">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">To Date</label>
            <input type="date" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>" 
                   style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px;">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Search Reference</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                   placeholder="Search..." style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px;">
        </div>
        
        <div style="display: flex; align-items: flex-end; gap: 10px;">
            <button type="submit" class="btn-primary" style="flex: 1;">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="" class="btn-secondary" style="flex: 1; text-align: center;">
                <i class="fas fa-redo"></i> Reset
            </a>
        </div>
    </form>
</div>

<!-- Payment Methods -->
<div class="feature-card" style="margin-top: 30px;">
    <h3><i class="fas fa-list"></i> Available Payment Methods</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px;">
        <?php if (!empty($paymentMethods['payment_methods'])): ?>
            <?php foreach ($paymentMethods['payment_methods'] as $method): ?>
                <span class="badge-<?php echo $method['status'] === 'active' ? 'success' : 'secondary'; ?>" 
                      style="padding: 8px 15px; border-radius: 20px; font-size: 14px;">
                    <i class="fas fa-<?php echo $method['status'] === 'active' ? 'check-circle' : 'times-circle'; ?>"></i>
                    <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $method['name']))); ?>
                </span>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #6b7280;">No payment methods available</p>
        <?php endif; ?>
    </div>
</div>

<!-- Payments Table -->
<div class="feature-card" style="margin-top: 30px;">
    <h3><i class="fas fa-table"></i> Payment Records</h3>
    
    <?php if (!empty($payments['payment_intents'])): ?>
        <div style="overflow-x: auto; margin-top: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f3f4f6; border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px; text-align: left; font-weight: 600;">ID</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Reference</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Custom ID</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Amount</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Status</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Method</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Email</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Created</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments['payment_intents'] as $payment): ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 12px;">#<?php echo $payment['id']; ?></td>
                            <td style="padding: 12px;">
                                <strong><?php echo htmlspecialchars($payment['reference'] ?? 'N/A'); ?></strong>
                            </td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($payment['custom_payment_id'] ?? 'N/A'); ?></td>
                            <td style="padding: 12px;">
                                <strong style="color: #16a34a;">R<?php echo number_format($payment['amount'], 2); ?></strong>
                            </td>
                            <td style="padding: 12px;">
                                <span class="badge-<?php 
                                    echo in_array($payment['status'] ?? '', ['paid', 'success']) ? 'success' : 
                                        ($payment['status'] === 'unpaid' ? 'warning' : 'secondary'); 
                                ?>" style="padding: 5px 10px; border-radius: 15px; font-size: 12px;">
                                    <?php echo htmlspecialchars(ucfirst($payment['status'] ?? 'unknown')); ?>
                                </span>
                            </td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?></td>
                            <td style="padding: 12px; max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($payment['email'] ?? 'N/A'); ?>
                            </td>
                            <td style="padding: 12px; font-size: 13px;">
                                <?php echo date('Y-m-d H:i', strtotime($payment['time_created'] ?? 'now')); ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <a href="?action=view&id=<?php echo $payment['id']; ?>" 
                                   class="btn-sm btn-info" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (in_array($payment['status'] ?? '', ['paid', 'success'])): ?>
                                    <button onclick="confirmRefund(<?php echo $payment['id']; ?>)" 
                                            class="btn-sm btn-danger" 
                                            title="Refund">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px; text-align: center; color: #6b7280;">
            Showing <?php echo count($payments['payment_intents']); ?> of <?php echo $payments['count']; ?> payments
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
            <p>No payments found. Try adjusting your filters.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Refund Modal -->
<div id="refundModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; max-width: 500px; margin: 100px auto; padding: 30px; border-radius: 10px;">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-undo"></i> Process Refund</h3>
        <form method="POST" action="/admin/bobpay/refund">
            <input type="hidden" name="payment_id" id="refundPaymentId">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Reason for Refund</label>
                <textarea name="reason" rows="4" required 
                          style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px;"
                          placeholder="Please provide a reason for this refund..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('refundModal').style.display='none'" 
                        class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-danger">Process Refund</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmRefund(paymentId) {
    document.getElementById('refundPaymentId').value = paymentId;
    document.getElementById('refundModal').style.display = 'block';
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('refundModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<style>
.badge-success { background: #dcfce7; color: #16a34a; }
.badge-warning { background: #fef3c7; color: #d97706; }
.badge-secondary { background: #f3f4f6; color: #6b7280; }
.badge-info { background: #dbeafe; color: #2563eb; }

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}
.btn-info { background: #3b82f6; color: white; }
.btn-danger { background: #ef4444; color: white; }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
