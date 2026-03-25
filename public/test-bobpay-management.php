<?php
/**
 * Test BobPay Payment Management Endpoints
 * Access: http://localhost:8000/test-bobpay-management.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/bobpay.php';

echo "<h1>BobPay Payment Management Test</h1>";
echo "<pre>";

$bobPay = new BobPayHelper();

echo "=== Test 1: Get Payment Intents ===\n\n";
$filters = [
    'include_retained_amount' => 'true',
    'limit' => 5,
    'order' => 'DESC',
    'order_by' => 'time_created'
];
$paymentIntents = $bobPay->getPaymentIntents($filters);

if ($paymentIntents) {
    echo "✅ Payment Intents Retrieved:\n";
    echo json_encode($paymentIntents, JSON_PRETTY_PRINT);
} else {
    echo "❌ Failed to retrieve payment intents";
}

echo "\n\n=== Test 2: Get Public Payment Methods ===\n\n";
$accountCode = 'SAN001'; // Sandbox account
$paymentMethods = $bobPay->getPublicPaymentMethods($accountCode);

if ($paymentMethods) {
    echo "✅ Payment Methods for $accountCode:\n";
    echo json_encode($paymentMethods, JSON_PRETTY_PRINT);
} else {
    echo "❌ Failed to retrieve payment methods";
}

echo "\n\n=== Test 3: Get Single Payment Intent ===\n\n";
// Use a sample payment ID (you would get this from your database or webhook)
$samplePaymentId = 75567; // Example from docs
echo "Testing with payment ID: $samplePaymentId\n";
$paymentIntent = $bobPay->getPaymentIntent($samplePaymentId);

if ($paymentIntent) {
    echo "✅ Payment Intent Retrieved:\n";
    echo json_encode($paymentIntent, JSON_PRETTY_PRINT);
} else {
    echo "❌ Failed to retrieve payment intent (ID may not exist)";
}

echo "\n\n=== Test 4: Create Payment Intent with Signature ===\n\n";
$intentData = [
    'recipient_account_code' => 'SAN001',
    'custom_payment_id' => 'test_' . time(),
    'email' => 'test@example.com',
    'phone_number' => '',
    'amount' => 499.99,
    'item_name' => 'Test Product',
    'item_description' => 'Test Description',
    'notify_url' => APP_URL . '/bobpay/webhook',
    'success_url' => APP_URL . '/bobpay/success',
    'pending_url' => APP_URL . '/bobpay/pending',
    'cancel_url' => APP_URL . '/bobpay/cancel',
    'signature' => bin2hex(random_bytes(16))
];
echo "Testing payment intent creation...\n";
$intent = $bobPay->createPaymentIntentWithSignature($intentData);

if ($intent) {
    echo "✅ Payment Intent Created:\n";
    echo json_encode($intent, JSON_PRETTY_PRINT);
} else {
    echo "❌ Failed to create payment intent";
}

echo "\n\n=== Test 5: Shorten Payment URL ===\n\n";
$longUrl = 'https://sandbox.bobpay.co.za/pay?amount=499.99&custom_payment_id=test';
echo "Testing URL shortening...\n";
$shortUrl = $bobPay->shortenPaymentUrl($longUrl);

if ($shortUrl) {
    echo "✅ Shortened URL: $shortUrl\n";
} else {
    echo "❌ Failed to shorten URL";
}

echo "\n\n=== Test 6: Get Payout Requests ===\n\n";
$payoutFilters = [
    'account_id' => 4718,
    'order' => 'DESC',
    'offset' => 0,
    'limit' => 20,
    'order_by' => 'time_created',
    'status' => 'pending'
];
echo "Testing payout requests retrieval...\n";
$payouts = $bobPay->getPayoutRequests($payoutFilters);

if ($payouts) {
    echo "✅ Payout Requests Retrieved:\n";
    echo json_encode($payouts, JSON_PRETTY_PRINT);
} else {
    echo "❌ Failed to retrieve payout requests";
}

echo "\n\n=== Test 7: Get Payout Schedule ===\n\n";
$scheduleFilters = [
    'account_id' => 665,
    'payout_frequency' => 'monthly'
];
echo "Testing payout schedule retrieval...\n";
$schedule = $bobPay->getPayoutSchedule($scheduleFilters);

if ($schedule) {
    echo "✅ Payout Schedule Retrieved:\n";
    echo json_encode($schedule, JSON_PRETTY_PRINT);
} else {
    echo "❌ Failed to retrieve payout schedule";
}

echo "\n\n=== Test 8: Refund Payment ===\n\n";
echo "⚠️  Skipping refund test (requires valid payment ID)\n";
echo "To test refunds, call:\n";
echo "\$bobPay->refundPayment(\$paymentId);\n";

echo "\n\n=== Usage Examples ===\n\n";
?>

<h3>Get Payment Intents with Filters</h3>
<pre>
$bobPay = new BobPayHelper();
$filters = [
    'include_retained_amount' => 'true',
    'start_date' => '2024-04-18 00:00:00',
    'end_date' => '2024-06-18 23:59:59',
    'limit' => 10,
    'order' => 'DESC',
    'order_by' => 'status'
];
$payments = $bobPay->getPaymentIntents($filters);
</pre>

<h3>Get Payment Methods for Account</h3>
<pre>
$bobPay = new BobPayHelper();
$methods = $bobPay->getPublicPaymentMethods('SAN001');
print_r($methods);
</pre>

<h3>Refund a Payment</h3>
<pre>
$bobPay = new BobPayHelper();
$result = $bobPay->refundPayment(72052); // Payment ID
if ($result) {
    echo "Refund successful!";
    print_r($result);
} else {
    echo "Refund failed";
}
</pre>

<h3>Get Single Payment Details</h3>
<pre>
$bobPay = new BobPayHelper();
$payment = $bobPay->getPaymentIntent(12345);
print_r($payment);
</pre>

<?php
echo "</pre>";
?>

<style>
    h3 {
        background: #667eea;
        color: white;
        padding: 10px;
        border-radius: 5px;
        margin-top: 20px;
    }
    pre {
        background: #f4f4f4;
        padding: 15px;
        border-radius: 5px;
        overflow-x: auto;
    }
</style>
