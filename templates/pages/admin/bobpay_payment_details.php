<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-overview">
    <h1 class="title"><i class="fas fa-receipt"></i> Payment Details #<?php echo $payment['id']; ?></h1>
    <a href="/admin/bobpay" class="btn-secondary" style="margin-top: 10px; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Back to Payments
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
    <!-- Payment Information -->
    <div class="feature-card">
        <h3><i class="fas fa-info-circle"></i> Payment Information</h3>
        <table style="width: 100%; margin-top: 15px;">
            <tr>
                <td style="padding: 10px; font-weight: 600; color: #6b7280;">Payment ID:</td>
                <td style="padding: 10px;"><?php echo $payment['id']; ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600; color: #6b7280;">Reference:</td>
                <td style="padding: 10px;"><strong><?php echo htmlspecialchars($payment['reference'] ?? 'N/A'); ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600; color: #6b7280;">Custom ID:</td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($payment['custom_payment_id'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600; color: #6b7280;">Amount:</td>
                <td style="padding: 10px;"><strong style="color: #16a34a;">R<?php echo number_format($payment['amount'], 2); ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600; color: #6b7280;">Status:</td>
                <td style="padding: 10px;">
                    <span class="badge-<?php 
                        echo in_array($payment['status'] ?? '', ['paid', 'success']) ? 'success' : 'secondary'; 
                    ?>" style="padding: 5px 10px; border-radius: 15px; font-size: 14px;">
                        <?php echo htmlspecialchars(ucfirst($payment['status'] ?? 'unknown')); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600; color: #6b7280;">Payment Method:</td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600; color: #6b7280;">Created:</td>
                <td style="padding: 10px;"><?php echo date('Y-m-d H:i:s', strtotime($payment['time_created'] ?? 'now')); ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Customer Information -->
    <div class="feature-card">
        <h3><i class="fas fa-user"></i> Customer Information</h3>
        <table style="width: 100%; margin-top: 15px;">
            <tr>
                <td style="padding: 10px; font-weight: 600; color: #6b7280;">Email:</td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($payment['email'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600; color: #6b7280;">Account ID:</td>
                <td style="padding: 10px;"><?php echo $payment['account_id'] ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600; color: #6b7280;">Recipient Account:</td>
                <td style="padding: 10px;">
                    <?php echo htmlspecialchars($payment['recipient_account']['account_code'] ?? 'N/A'); ?>
                    (<?php echo htmlspecialchars($payment['recipient_account']['name'] ?? 'N/A'); ?>)
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- URLs -->
<div class="feature-card" style="margin-top: 30px;">
    <h3><i class="fas fa-link"></i> Payment URLs</h3>
    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td style="padding: 10px; font-weight: 600; color: #6b7280; vertical-align: top;">Success URL:</td>
            <td style="padding: 10px;">
                <a href="<?php echo htmlspecialchars($payment['success_url'] ?? '#'); ?>" target="_blank" style="color: #3b82f6;">
                    <?php echo htmlspecialchars($payment['success_url'] ?? 'N/A'); ?>
                </a>
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; font-weight: 600; color: #6b7280; vertical-align: top;">Cancel URL:</td>
            <td style="padding: 10px;">
                <a href="<?php echo htmlspecialchars($payment['cancel_url'] ?? '#'); ?>" target="_blank" style="color: #3b82f6;">
                    <?php echo htmlspecialchars($payment['cancel_url'] ?? 'N/A'); ?>
                </a>
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; font-weight: 600; color: #6b7280; vertical-align: top;">Notify URL:</td>
            <td style="padding: 10px;">
                <code><?php echo htmlspecialchars($payment['notify_url'] ?? 'N/A'); ?></code>
            </td>
        </tr>
    </table>
</div>

<!-- Raw Data -->
<div class="feature-card" style="margin-top: 30px;">
    <h3><i class="fas fa-code"></i> Raw Payment Data</h3>
    <pre style="background: #f3f4f6; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 10px;"><?php echo json_encode($payment, JSON_PRETTY_PRINT); ?></pre>
</div>

<style>
.badge-success { background: #dcfce7; color: #16a34a; }
.badge-secondary { background: #f3f4f6; color: #6b7280; }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
