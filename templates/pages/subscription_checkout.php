<?php
require_once __DIR__ . '/../../controllers/SubscriptionController.php';
$extraHead = '<link rel="stylesheet" href="/assets/css/seo-pages.css">';
include __DIR__ . '/../layouts/header.php';

$plan = $_GET['plan'] ?? 'basic';
$subscriptionController = new SubscriptionController();
$plans = $subscriptionController->getPlans();
$planDetails = $plans[$plan] ?? $plans['basic'];

// Get banking details from settings
$bankingDetails = [
    'bank_name' => 'FNB',
    'account_type' => 'Current Account',
    'account_number' => '62123456789',
    'branch_code' => '250655',
    'account_holder' => 'StudySmart',
    'reference_instruction' => 'Use your username and plan (e.g., john-basic)',
    'email_address' => 'billing@studysmart.co.za',
    'activation_time' => '24-48 hours',
];

try {
    $db = Database::getInstance()->getConnection();
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetch();
    if ($result) {
        $storedSettings = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'banking_%'")->fetchAll();
        foreach ($storedSettings as $setting) {
            $key = str_replace('banking_', '', $setting['setting_key']);
            if (!empty($setting['setting_value'])) {
                $bankingDetails[$key] = $setting['setting_value'];
            }
        }
    }
} catch (Exception $e) {
    // Use defaults
}
?>

<div class="dashboard-overview">
    <h1 class="title">Checkout</h1>
    <p class="subtitle">Complete your subscription to <?php echo htmlspecialchars($planDetails['name']); ?></p>
</div>

<div class="checkout-grid">

    <!-- Order Summary -->
    <div class="feature-card checkout-card">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-receipt"></i> Order Summary</h3>

        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-weight: 600;"><?php echo $planDetails['name']; ?> Plan</span>
                <span>R<?php echo $planDetails['price']; ?>.00</span>
            </div>
        </div>

        <div style="border-top: 2px dashed #e5e7eb; padding-top: 20px;">
            <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 700;">
                <span>Total Due Today</span>
                <span style="color: #16a34a;">R<?php echo $planDetails['price']; ?>.00</span>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #0284c7;">
            <h4 style="margin-bottom: 10px; color: #0369a1;"><i class="fas fa-info-circle"></i> What's Included:</h4>
            <ul style="margin: 0; padding-left: 20px; color: #0369a1;">
                <?php foreach (array_slice($planDetails['features'], 0, 4) as $feature): ?>
                    <li style="margin-bottom: 5px;"><?php echo $feature; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="feature-card checkout-card">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-credit-card"></i> Payment Details</h3>
        
        <form method="POST" action="/subscription/process-payment" id="paymentForm" onsubmit="return validatePayment()" enctype="multipart/form-data">
            <input type="hidden" name="plan" value="<?php echo htmlspecialchars($plan); ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Payment Method</label>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <label style="flex: 1; min-width: 100px; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; text-align: center;">
                        <input type="radio" name="payment_method" value="bobpay" checked style="margin-right: 8px;">
                        <i class="fas fa-credit-card"></i> BobPay (Card)
                    </label>
                    <label style="flex: 1; min-width: 100px; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; text-align: center;">
                        <input type="radio" name="payment_method" value="eft" style="margin-right: 8px;">
                        <i class="fas fa-university"></i> EFT
                    </label>
                </div>
            </div>

            <div id="bobPayFields">
                <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 15px; border-radius: 8px; border-left: 4px solid #16a34a; margin-bottom: 20px;">
                    <p style="font-size: 14px; color: #166534; margin: 0;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Secure Card Payment via BobPay:</strong> You will be redirected to BobPay's secure payment gateway to complete your card payment. All major credit and debit cards are accepted.
                    </p>
                </div>
            </div>

            <!-- EFT Payment Details -->
            <div id="eftPaymentFields" style="display: none;">
                <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #0284c7; margin-bottom: 20px;">
                    <h4 style="margin-bottom: 15px; color: #0369a1;">
                        <i class="fas fa-university"></i> Bank Details for EFT Transfer
                    </h4>

                    <div class="bank-details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Bank Name</label>
                            <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($bankingDetails['bank_name']); ?></div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Account Type</label>
                            <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($bankingDetails['account_type']); ?></div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Account Number</label>
                            <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($bankingDetails['account_number']); ?></div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Branch Code</label>
                            <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($bankingDetails['branch_code']); ?></div>
                        </div>
                    </div>

                    <?php if (!empty($bankingDetails['account_holder'])): ?>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Account Holder</label>
                        <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($bankingDetails['account_holder']); ?></div>
                    </div>
                    <?php endif; ?>

                    <div style="background: white; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                        <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Reference (IMPORTANT)</label>
                        <div style="font-weight: 700; color: #dc2626; font-size: 16px;">
                            <?php echo htmlspecialchars(getCurrentUser()['username']); ?>-<?php echo strtoupper($plan); ?>
                        </div>
                        <p style="font-size: 13px; color: #6b7280; margin-top: 8px; margin-bottom: 0;">
                            <?php echo htmlspecialchars($bankingDetails['reference_instruction']); ?>
                        </p>
                    </div>

                    <div style="background: #fef3c7; padding: 12px; border-radius: 6px; border-left: 3px solid #f59e0b;">
                        <p style="font-size: 13px; color: #92400e; margin: 0;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Important:</strong> Your subscription will be activated within <?php echo htmlspecialchars($bankingDetails['activation_time']); ?> after payment is received.
                            <?php if (!empty($bankingDetails['email_address'])): ?>
                                Please email proof of payment to <strong><?php echo htmlspecialchars($bankingDetails['email_address']); ?></strong>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="eft_reference" style="display: block; margin-bottom: 8px; font-weight: 600;">Payment Reference</label>
                    <input
                        type="text"
                        id="eft_reference"
                        name="eft_reference"
                        placeholder="<?php echo htmlspecialchars(getCurrentUser()['username']); ?>-<?php echo strtoupper($plan); ?>"
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px;"
                        value="<?php echo htmlspecialchars(getCurrentUser()['username']); ?>-<?php echo strtoupper($plan); ?>"
                        required
                    >
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="eft_amount" style="display: block; margin-bottom: 8px; font-weight: 600;">Amount Paid (R)</label>
                    <input
                        type="number"
                        id="eft_amount"
                        name="eft_amount"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px;"
                        required
                    >
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="eft_date" style="display: block; margin-bottom: 8px; font-weight: 600;">Date of Payment</label>
                    <input
                        type="date"
                        id="eft_date"
                        name="eft_date"
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px;"
                        required
                    >
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="proof_upload" style="display: block; margin-bottom: 8px; font-weight: 600;">Upload Proof of Payment <span style="color: #dc2626;">*</span></label>
                    <input
                        type="file"
                        id="proof_upload"
                        name="proof_upload"
                        accept=".pdf,.jpg,.jpeg,.png"
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;"
                        required
                    >
                    <p style="font-size: 13px; color: #6b7280; margin-top: 6px;">
                        Accepted formats: PDF, JPG, PNG. Max size: 5MB
                    </p>
                </div>
            </div>

            <button type="submit" class="btn-primary" id="submitBtn" style="width: 100%; padding: 15px; font-size: 16px; font-weight: 600;">
                <i class="fas fa-lock"></i> Pay R<?php echo $planDetails['price']; ?>.00 - Subscribe Now
            </button>

            <p style="text-align: center; margin-top: 15px; color: #6b7280; font-size: 14px;">
                <i class="fas fa-shield-alt"></i> Secure 256-bit SSL encrypted payment
            </p>
        </form>

        <div style="margin-top: 20px; text-align: center;">
            <a href="/subscription" style="color: #6b7280; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Plans
            </a>
        </div>
    </div>
</div>

<script>
// Toggle payment fields
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const bobPayFields = document.getElementById('bobPayFields');
        const eftFields = document.getElementById('eftPaymentFields');
        const submitBtn = document.getElementById('submitBtn');

        // Hide all fields first
        bobPayFields.style.display = 'none';
        eftFields.style.display = 'none';

        // Remove required from all fields
        bobPayFields.querySelectorAll('input').forEach(input => input.required = false);
        eftFields.querySelectorAll('input').forEach(input => input.required = false);

        // Show and set required based on selection
        if (this.value === 'bobpay') {
            // BobPay - card details will be entered on BobPay gateway
            bobPayFields.style.display = 'block';
            bobPayFields.querySelectorAll('input').forEach(input => input.required = false);
            submitBtn.innerHTML = '<i class="fas fa-credit-card"></i> Pay with Card via BobPay';
        } else if (this.value === 'eft') {
            eftFields.style.display = 'block';
            eftFields.querySelectorAll('input').forEach(input => input.required = true);
            submitBtn.innerHTML = '<i class="fas fa-university"></i> I Have Made the EFT Payment';
        }
    });
});

function validatePayment() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

    if (paymentMethod === 'eft') {
        const eftReference = document.getElementById('eft_reference').value;
        const eftAmount = document.getElementById('eft_amount').value;
        const eftDate = document.getElementById('eft_date').value;
        const proofUpload = document.getElementById('proof_upload');

        if (!eftReference || eftReference.trim() === '') {
            alert('Please enter a payment reference');
            return false;
        }

        if (!eftAmount || parseFloat(eftAmount) <= 0) {
            alert('Please enter a valid payment amount');
            return false;
        }

        if (!eftDate) {
            alert('Please enter the date of payment');
            return false;
        }

        if (!proofUpload || !proofUpload.files || proofUpload.files.length === 0) {
            alert('Please upload proof of payment (PDF, JPG, or PNG)');
            return false;
        }
    }
    // BobPay and EFT require no additional validation

    return true;
}

// Initialize with BobPay selected
document.addEventListener('DOMContentLoaded', function() {
    const bobPayRadio = document.querySelector('input[name="payment_method"][value="bobpay"]');
    if (bobPayRadio) {
        bobPayRadio.checked = true;
        // Trigger change event to show BobPay fields
        bobPayRadio.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
