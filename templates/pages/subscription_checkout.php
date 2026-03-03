<?php
require_once __DIR__ . '/../../controllers/SubscriptionController.php';
include __DIR__ . '/../layouts/header.php';

$plan = $_GET['plan'] ?? 'basic';
$subscriptionController = new SubscriptionController();
$plans = $subscriptionController->getPlans();
$planDetails = $plans[$plan] ?? $plans['basic'];
?>

<div class="dashboard-overview">
    <h1 class="title">Checkout</h1>
    <p class="subtitle">Complete your subscription to <?php echo htmlspecialchars($planDetails['name']); ?></p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px; max-width: 1200px; margin-left: auto; margin-right: auto;">
    
    <!-- Order Summary -->
    <div class="feature-card">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-receipt"></i> Order Summary</h3>
        
        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-weight: 600;"><?php echo $planDetails['name']; ?> Plan</span>
                <span>R<?php echo $planDetails['price']; ?>.00</span>
            </div>
            <div style="display: flex; justify-content: space-between; color: #6b7280; font-size: 14px;">
                <span>Billing period</span>
                <span><?php echo $planDetails['period']; ?></span>
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
    <div class="feature-card">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-credit-card"></i> Payment Details</h3>
        
        <form method="POST" action="/subscription/process-payment" id="paymentForm" onsubmit="return validatePayment()" enctype="multipart/form-data">
            <input type="hidden" name="plan" value="<?php echo htmlspecialchars($plan); ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Payment Method</label>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <label style="flex: 1; min-width: 100px; padding: 12px; border: 2px solid #2563eb; border-radius: 8px; cursor: pointer; background: #eff6ff; text-align: center;">
                        <input type="radio" name="payment_method" value="card" checked style="margin-right: 8px;">
                        <i class="fas fa-credit-card"></i> Card
                    </label>
                    <label style="flex: 1; min-width: 100px; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; text-align: center;">
                        <input type="radio" name="payment_method" value="paypal" style="margin-right: 8px;">
                        <i class="fab fa-paypal"></i> PayPal
                    </label>
                    <label style="flex: 1; min-width: 100px; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; text-align: center;">
                        <input type="radio" name="payment_method" value="eft" style="margin-right: 8px;">
                        <i class="fas fa-university"></i> EFT
                    </label>
                </div>
            </div>

            <div id="cardPaymentFields">
                <div style="margin-bottom: 20px;">
                    <label for="card_number" style="display: block; margin-bottom: 8px; font-weight: 600;">Card Number</label>
                    <input
                        type="text"
                        id="card_number"
                        name="card_number"
                        placeholder="1234 5678 9012 3456"
                        maxlength="19"
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px;"
                        required
                    >
                    <div style="margin-top: 8px; display: flex; gap: 10px;">
                        <i class="fab fa-cc-visa" style="font-size: 24px; color: #1a1f71;"></i>
                        <i class="fab fa-cc-mastercard" style="font-size: 24px; color: #eb001b;"></i>
                        <i class="fab fa-cc-amex" style="font-size: 24px; color: #006fcf;"></i>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label for="expiry_date" style="display: block; margin-bottom: 8px; font-weight: 600;">Expiry Date</label>
                        <input
                            type="text"
                            id="expiry_date"
                            name="expiry_date"
                            placeholder="MM/YY"
                            maxlength="5"
                            style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px;"
                            required
                        >
                    </div>
                    <div>
                        <label for="cvv" style="display: block; margin-bottom: 8px; font-weight: 600;">CVV</label>
                        <input
                            type="text"
                            id="cvv"
                            name="cvv"
                            placeholder="123"
                            maxlength="4"
                            style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px;"
                            required
                        >
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="cardholder_name" style="display: block; margin-bottom: 8px; font-weight: 600;">Cardholder Name</label>
                    <input
                        type="text"
                        id="cardholder_name"
                        name="cardholder_name"
                        placeholder="John Doe"
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px;"
                        required
                    >
                </div>
            </div>

            <!-- EFT Payment Details -->
            <div id="eftPaymentFields" style="display: none;">
                <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #0284c7; margin-bottom: 20px;">
                    <h4 style="margin-bottom: 15px; color: #0369a1;">
                        <i class="fas fa-university"></i> Bank Details for EFT Transfer
                    </h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Bank Name</label>
                            <div style="font-weight: 600; color: #1f2937;">FNB</div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Account Type</label>
                            <div style="font-weight: 600; color: #1f2937;">Current Account</div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Account Number</label>
                            <div style="font-weight: 600; color: #1f2937;">62123456789</div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Branch Code</label>
                            <div style="font-weight: 600; color: #1f2937;">250655</div>
                        </div>
                    </div>

                    <div style="background: white; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                        <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Reference (IMPORTANT)</label>
                        <div style="font-weight: 700; color: #dc2626; font-size: 16px;">
                            <?php echo htmlspecialchars(getCurrentUser()['username']); ?>-<?php echo strtoupper($plan); ?>
                        </div>
                        <p style="font-size: 13px; color: #6b7280; margin-top: 8px; margin-bottom: 0;">
                            Use this exact reference when making the payment so we can identify your transaction.
                        </p>
                    </div>

                    <div style="background: #fef3c7; padding: 12px; border-radius: 6px; border-left: 3px solid #f59e0b;">
                        <p style="font-size: 13px; color: #92400e; margin: 0;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Important:</strong> Your subscription will be activated within 24-48 hours after payment is received. 
                            Please email proof of payment to <strong>billing@studysmart.co.za</strong>
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

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" required style="margin-top: 4px;">
                    <span style="font-size: 14px; color: #6b7280;">
                        I agree to the <a href="#" style="color: #2563eb;">Terms of Service</a> and <a href="#" style="color: #2563eb;">Privacy Policy</a>. 
                        I authorize the recurring charge of R<?php echo $planDetails['price']; ?>.00 <?php echo $planDetails['period']; ?>.
                    </span>
                </label>
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
// Format card number with spaces
document.getElementById('card_number')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '');
    let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formatted;
});

// Format expiry date
document.getElementById('expiry_date')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }
    e.target.value = value;
});

// Toggle payment fields
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const cardFields = document.getElementById('cardPaymentFields');
        const eftFields = document.getElementById('eftPaymentFields');
        const submitBtn = document.getElementById('submitBtn');
        
        // Hide all fields first
        cardFields.style.display = 'none';
        eftFields.style.display = 'none';
        
        // Remove required from all fields
        cardFields.querySelectorAll('input').forEach(input => input.required = false);
        eftFields.querySelectorAll('input').forEach(input => input.required = false);
        
        // Show and set required based on selection
        if (this.value === 'card') {
            cardFields.style.display = 'block';
            cardFields.querySelectorAll('input').forEach(input => input.required = true);
            submitBtn.innerHTML = '<i class="fas fa-lock"></i> Pay R<?php echo $planDetails['price']; ?>.00 - Subscribe Now';
        } else if (this.value === 'eft') {
            eftFields.style.display = 'block';
            eftFields.querySelectorAll('input').forEach(input => input.required = true);
            submitBtn.innerHTML = '<i class="fas fa-university"></i> I Have Made the EFT Payment';
        } else if (this.value === 'paypal') {
            submitBtn.innerHTML = '<i class="fab fa-paypal"></i> Continue with PayPal';
        }
    });
});

function validatePayment() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (paymentMethod === 'card') {
        const cardNumber = document.getElementById('card_number').value;
        const expiryDate = document.getElementById('expiry_date').value;
        const cvv = document.getElementById('cvv').value;

        if (cardNumber.replace(/\s/g, '').length < 15) {
            alert('Please enter a valid card number');
            return false;
        }

        if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
            alert('Please enter a valid expiry date (MM/YY)');
            return false;
        }

        if (cvv.length < 3) {
            alert('Please enter a valid CVV');
            return false;
        }
    } else if (paymentMethod === 'eft') {
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
    // PayPal requires no additional validation
    
    return true;
}
</script>

<style>
#cardPaymentFields {
    transition: all 0.3s ease;
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
