<?php
/**
 * Quick BobPay Checkout Test
 * Access: http://localhost:8000/test-bobpay-checkout.php
 */

// Go up one directory from public to access config
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/bobpay.php';

echo "<h1>BobPay Checkout Test</h1>";
echo "<pre>";

// Simulate logged in user
$_SESSION['user_id'] = 1;
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'Test User',
    'email' => 'test@example.com',
    'role' => 'student'
];

$user = $_SESSION['user'];
$plan = 'basic';
$amount = 39;

echo "Testing BobPay payment for:\n";
echo "User: " . json_encode($user) . "\n";
echo "Plan: $plan\n";
echo "Amount: R$amount\n\n";

$bobPay = new BobPayHelper();
echo "BobPay Configured: " . ($bobPay->isConfigured() ? 'YES' : 'NO') . "\n\n";

$bobPayData = $bobPay->preparePaymentData([
    'amount' => $amount,
    'customer_email' => $user['email'],
    'customer_name' => $user['username'],
    'success_url' => APP_URL . '/subscription/bobpay/return',
    'cancel_url' => APP_URL . '/subscription/bobpay/cancel',
    'callback_url' => APP_URL . '/subscription/bobpay/webhook',
    'metadata' => [
        'user_id' => $user['id'],
        'plan' => $plan,
        'payment_id' => 'test_' . time()
    ]
]);

echo "Payment Data:\n";
echo json_encode($bobPayData, JSON_PRETTY_PRINT) . "\n\n";

echo "Creating payment link...\n";
$response = $bobPay->createPayment($bobPayData);

if ($response) {
    echo "✅ SUCCESS!\n";
    echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
    $paymentUrl = $response['short_url'] ?? $response['url'];
    echo "\n🔗 Payment URL: $paymentUrl\n";
    echo "<a href='$paymentUrl' target='_blank' style='font-size: 18px; display: inline-block; margin-top: 10px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Click here to test payment</a>\n";
} else {
    echo "❌ FAILED!\n";
    echo "Check logs for details.\n";
}

echo "</pre>";
?>
