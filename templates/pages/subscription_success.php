<?php
require_once __DIR__ . '/../../controllers/SubscriptionController.php';
include __DIR__ . '/../layouts/header.php';

$plan = $_GET['plan'] ?? 'basic';
$status = $_GET['status'] ?? 'active';
$subscriptionController = new SubscriptionController();
$plans = $subscriptionController->getPlans();
$planDetails = $plans[$plan] ?? $plans['basic'];
$user = getCurrentUser();

$isPending = $status === 'pending';
?>

<div style="text-align: center; padding: 60px 20px;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="width: 100px; height: 100px; background: linear-gradient(135deg, <?php echo $isPending ? '#f59e0b 0%, #d97706 100%' : '#16a34a 0%, #059669 100%'; ?>); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
            <i class="fas <?php echo $isPending ? 'fa-clock' : 'fa-check'; ?>" style="font-size: 50px; color: white;"></i>
        </div>

        <h1 class="title" style="color: <?php echo $isPending ? '#d97706' : '#16a34a'; ?>;">
            <?php echo $isPending ? 'Payment Reference Recorded!' : 'Payment Successful!'; ?>
        </h1>
        <p class="subtitle" style="font-size: 18px; color: #6b7280; margin-bottom: 30px;">
            <?php if ($isPending): ?>
                Your <strong><?php echo htmlspecialchars($planDetails['name']); ?></strong> subscription is pending verification
            <?php else: ?>
                Your subscription to <strong><?php echo htmlspecialchars($planDetails['name']); ?></strong> plan has been activated
            <?php endif; ?>
        </p>

        <div class="feature-card" style="text-align: left; margin-bottom: 30px;">
            <h3 style="margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 15px;">
                <i class="fas fa-receipt"></i> Subscription Details
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Plan</p>
                    <p style="font-weight: 600; font-size: 18px;"><?php echo htmlspecialchars($planDetails['name']); ?> Plan</p>
                </div>
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Amount</p>
                    <p style="font-weight: 600; font-size: 18px; color: #16a34a;">R<?php echo $planDetails['price']; ?>.00</p>
                </div>
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Billing Period</p>
                    <p style="font-weight: 600;"><?php echo $planDetails['period']; ?></p>
                </div>
                <div>
                    <p style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Status</p>
                    <p style="font-weight: 600; color: <?php echo $isPending ? '#d97706' : '#16a34a'; ?>;">
                        <?php echo $isPending ? 'Pending Verification' : 'Active'; ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if ($isPending): ?>
        <div class="feature-card" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b;">
            <h3 style="margin-bottom: 15px; color: #92400e;">
                <i class="fas fa-info-circle"></i> Important Next Steps
            </h3>
            <ul style="margin: 0; padding-left: 20px; color: #78350f; line-height: 2;">
                <li>Email your proof of payment to <strong>billing@studysmart.co.za</strong></li>
                <li>Use reference: <strong><?php echo htmlspecialchars($user['username']); ?>-<?php echo strtoupper($plan); ?></strong></li>
                <li>Your subscription will be activated within <strong>24-48 hours</strong></li>
                <li>You'll receive a confirmation email once verified</li>
            </ul>
        </div>
        <?php else: ?>
        <div class="feature-card" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #2563eb;">
            <h3 style="margin-bottom: 15px; color: #1d4ed8;">
                <i class="fas fa-gift"></i> What's Next?
            </h3>
            <ul style="margin: 0; padding-left: 20px; color: #1e40af; line-height: 2;">
                <li>You now have unlimited access to all <?php echo htmlspecialchars($planDetails['name']); ?> features</li>
                <li>Your next billing date is <strong><?php echo date('M d, Y', strtotime('+1 month')); ?></strong></li>
                <li>You can cancel anytime from your <a href="/subscription" style="color: #2563eb;">Subscription Page</a></li>
                <li>A confirmation email has been sent to <strong><?php echo htmlspecialchars($user['email'] ?? 'your email'); ?></strong></li>
            </ul>
        </div>
        <?php endif; ?>

        <div style="margin-top: 40px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="/dashboard" class="btn-primary" style="text-decoration: none; display: inline-block; padding: 15px 30px;">
                <i class="fas fa-chart-line"></i> Go to Dashboard
            </a>
            <a href="/subscription" class="btn-secondary" style="text-decoration: none; display: inline-block; padding: 15px 30px;">
                <i class="fas fa-crown"></i> View Subscription
            </a>
        </div>

        <p style="margin-top: 30px; color: #9ca3af; font-size: 14px;">
            <i class="fas fa-envelope"></i> 
            <?php echo $isPending ? 'A confirmation will be sent after verification' : 'A receipt has been sent to your email address'; ?>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
