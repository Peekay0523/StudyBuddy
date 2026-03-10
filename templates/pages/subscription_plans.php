<?php
require_once __DIR__ . '/../../controllers/SubscriptionController.php';
include __DIR__ . '/../layouts/header.php';

$subscriptionController = new SubscriptionController();
$plans = $subscriptionController->getPlans();
$userSubscription = $subscriptionController->getUserSubscription(getCurrentUser()['id']);
$currentPlan = $userSubscription['plan'] ?? 'free';
$isTrial = $userSubscription['is_trial'] ?? false;
?>

<div class="dashboard-overview">
    <h1 class="title">Subscription Plans</h1>
    <p class="subtitle">Choose the perfect plan for your learning journey</p>
</div>

<?php if ($currentPlan !== 'free'): ?>
<div class="feature-card" style="margin-bottom: 30px; background: linear-gradient(135deg, <?php echo $isTrial ? '#059669 0%, #10b981 100%' : '#16a34a 0%, #059669 100%'; ?>); color: white;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h3 style="margin-bottom: 5px;">
                <i class="fas fa-check-circle"></i> 
                You're on the <?php echo htmlspecialchars($plans[$currentPlan]['name']); ?> Plan
                <?php if ($isTrial): ?>
                    <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 12px; font-size: 14px;">FREE TRIAL</span>
                <?php endif; ?>
            </h3>
            <p style="opacity: 0.9;">
                <?php if ($userSubscription['current_period_end']): ?>
                    <?php if ($isTrial): ?>
                        Trial ends: <?php echo date('M d, Y', strtotime($userSubscription['current_period_end'])); ?>
                        <br><small style="opacity: 0.8;">After trial, you'll be moved to the Free plan automatically</small>
                    <?php else: ?>
                        Next billing date: <?php echo date('M d, Y', strtotime($userSubscription['current_period_end'])); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </div>
        <?php if ($currentPlan !== 'free'): ?>
            <form method="POST" action="/subscription/cancel" onsubmit="return confirm('Are you sure you want to cancel your subscription? You will lose access to premium features at the end of your billing period.');">
                <button type="submit" class="btn-secondary" style="background: rgba(255,255,255,0.2); border: 1px solid white; color: white;">
                    <i class="fas fa-ban"></i> <?php echo $isTrial ? 'Cancel Trial' : 'Cancel Subscription'; ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="pricing-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 30px;">
    
    <!-- Free Plan -->
    <div class="pricing-card <?php echo $currentPlan === 'free' ? 'current-plan' : ''; ?>" style="background: white; border: 2px solid #e5e7eb; border-radius: 16px; padding: 30px; position: relative;">
        <?php if ($currentPlan === 'free'): ?>
            <span class="current-plan-badge" style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #6b7280; color: white; padding: 4px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;">CURRENT PLAN</span>
        <?php endif; ?>
        
        <h3 style="font-size: 24px; margin-bottom: 10px;"><?php echo $plans['free']['name']; ?></h3>
        <div class="price" style="font-size: 42px; font-weight: 700; color: #1f2937; margin-bottom: 5px;">
            R<?php echo $plans['free']['price']; ?>
        </div>
        <p style="color: #6b7280; margin-bottom: 25px;"><?php echo $plans['free']['period']; ?></p>
        
        <ul class="features-list" style="list-style: none; padding: 0; margin-bottom: 30px;">
            <?php foreach ($plans['free']['features'] as $feature): ?>
                <li style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; display: flex; align-items: flex-start;">
                    <i class="fas fa-check" style="color: #16a34a; margin-right: 12px; margin-top: 3px;"></i>
                    <span><?php echo $feature; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <ul class="limitations-list" style="list-style: none; padding: 0; margin-bottom: 30px;">
            <?php foreach ($plans['free']['limitations'] as $limitation): ?>
                <li style="padding: 10px 0; color: #9ca3af; display: flex; align-items: flex-start;">
                    <i class="fas fa-times" style="color: #9ca3af; margin-right: 12px; margin-top: 3px;"></i>
                    <span><?php echo $limitation; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <?php if ($currentPlan === 'free'): ?>
            <button class="btn-primary" disabled style="width: 100%; opacity: 0.6; cursor: not-allowed;">Current Plan</button>
        <?php else: ?>
            <form method="POST" action="/subscription/downgrade" onsubmit="return confirm('Are you sure you want to downgrade to Free? You will immediately lose access to all premium features.');">
                <button type="submit" class="btn-downgrade" style="display: block; width: 100%; padding: 12px 20px; background: #f1f5f9; color: #64748b; border: 2px solid #cbd5e1; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s ease;">
                    <i class="fas fa-arrow-down"></i> Downgrade to Free
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Basic Plan -->
    <div class="pricing-card <?php echo $currentPlan === 'basic' ? 'current-plan' : ''; ?>" style="background: white; border: 2px solid #3b82f6; border-radius: 16px; padding: 30px; position: relative; box-shadow: 0 10px 40px rgba(59, 130, 246, 0.15);">
        <?php if ($currentPlan === 'basic'): ?>
            <span class="current-plan-badge" style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: <?php echo $isTrial ? '#10b981' : '#3b82f6'; ?>; color: white; padding: 4px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                <?php echo $isTrial ? 'FREE TRIAL (7 DAYS)' : 'CURRENT PLAN'; ?>
            </span>
        <?php else: ?>
            <span class="popular-badge" style="position: absolute; top: 15px; right: 15px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">POPULAR</span>
        <?php endif; ?>

        <h3 style="font-size: 24px; margin-bottom: 10px;"><?php echo $plans['basic']['name']; ?></h3>
        <div class="price" style="font-size: 42px; font-weight: 700; color: #1f2937; margin-bottom: 5px;">
            R<?php echo $plans['basic']['price']; ?>
        </div>
        <p style="color: #6b7280; margin-bottom: 25px;"><?php echo $plans['basic']['period']; ?></p>

        <ul class="features-list" style="list-style: none; padding: 0; margin-bottom: 30px;">
            <?php foreach ($plans['basic']['features'] as $feature): ?>
                <li style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; display: flex; align-items: flex-start;">
                    <i class="fas fa-check" style="color: #16a34a; margin-right: 12px; margin-top: 3px;"></i>
                    <span><?php echo $feature; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <ul class="limitations-list" style="list-style: none; padding: 0; margin-bottom: 30px;">
            <?php foreach ($plans['basic']['limitations'] as $limitation): ?>
                <li style="padding: 10px 0; color: #9ca3af; display: flex; align-items: flex-start;">
                    <i class="fas fa-times" style="color: #9ca3af; margin-right: 12px; margin-top: 3px;"></i>
                    <span><?php echo $limitation; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($currentPlan === 'basic'): ?>
            <button class="btn-primary" disabled style="width: 100%; opacity: 0.6; cursor: not-allowed;">
                <?php echo $isTrial ? 'Trial Active' : 'Current Plan'; ?>
            </button>
        <?php else: ?>
            <a href="/subscription/checkout?plan=basic" class="btn-primary" style="display: block; text-align: center; text-decoration: none;">Subscribe to Basic</a>
        <?php endif; ?>
    </div>

    <!-- Premium Plan -->
    <div class="pricing-card <?php echo $currentPlan === 'premium' ? 'current-plan' : ''; ?>" style="background: white; border: 2px solid #fbbf24; border-radius: 16px; padding: 30px; position: relative; opacity: 0.75;">
        <?php if ($currentPlan === 'premium'): ?>
            <span class="current-plan-badge" style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; padding: 4px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;">CURRENT PLAN</span>
        <?php else: ?>
            <span class="coming-soon-badge" style="position: absolute; top: 15px; right: 15px; background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">COMING SOON</span>
        <?php endif; ?>

        <h3 style="font-size: 24px; margin-bottom: 10px;">
            <?php echo $plans['premium']['name']; ?>
            <span style="font-size: 14px; color: #f59e0b; display: block; margin-top: 5px;">
                <i class="fas fa-clock"></i> Coming Soon
            </span>
        </h3>
        <div class="price" style="font-size: 42px; font-weight: 700; color: #1f2937; margin-bottom: 5px;">
            R<?php echo $plans['premium']['price']; ?>
        </div>
        <p style="color: #6b7280; margin-bottom: 25px;"><?php echo $plans['premium']['period']; ?></p>

        <ul class="features-list" style="list-style: none; padding: 0; margin-bottom: 30px;">
            <?php foreach ($plans['premium']['features'] as $feature): ?>
                <li style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; display: flex; align-items: flex-start; color: #9ca3af;">
                    <i class="fas fa-lock" style="color: #f59e0b; margin-right: 12px; margin-top: 3px;"></i>
                    <span><?php echo $feature; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($currentPlan === 'premium'): ?>
            <button class="btn-primary" disabled style="width: 100%; opacity: 0.6; cursor: not-allowed; background: #9ca3af;">Current Plan</button>
        <?php else: ?>
            <button class="btn-primary" disabled style="width: 100%; text-align: center; text-decoration: none; background: #9ca3af; cursor: not-allowed; opacity: 0.6;">
                <i class="fas fa-hourglass-half"></i> Coming Soon
            </button>
        <?php endif; ?>
    </div>

</div>

<!-- Payment Methods Info -->
<div class="feature-card" style="margin-top: 40px; text-align: center;">
    <h4 style="margin-bottom: 20px;"><i class="fas fa-credit-card"></i> Secure Payment Methods</h4>
    <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; align-items: center;">
        <div style="display: flex; align-items: center; gap: 8px; color: #6b7280;">
            <i class="fas fa-credit-card" style="font-size: 24px;"></i>
            <span>Credit/Debit Card</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; color: #6b7280;">
            <i class="fab fa-paypal" style="font-size: 24px;"></i>
            <span>PayPal</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; color: #6b7280;">
            <i class="fas fa-university" style="font-size: 24px;"></i>
            <span>EFT</span>
        </div>
    </div>
    <p style="margin-top: 20px; color: #9ca3af; font-size: 14px;">
        <i class="fas fa-lock"></i> All payments are secured with 256-bit SSL encryption
    </p>
</div>

<style>
.pricing-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.pricing-card.current-plan {
    border-color: #16a34a;
}

.price {
    font-family: 'Inter', sans-serif;
}

.features-list li:last-child,
.limitations-list li:last-child {
    border-bottom: none;
}

.btn-downgrade:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
    color: #475569;
}

@media (max-width: 768px) {
    .pricing-container {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
