<?php
/**
 * Test Subscription Controller BobPay
 * Access: http://localhost:8000/test-subscription-bobpay.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/SubscriptionController.php';

// Simulate being logged in
$_SESSION['user_id'] = 1;
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'Test User',
    'email' => 'test@example.com',
    'role' => 'student'
];
$_SESSION['student'] = [
    'id' => 1,
    'user_id' => 1,
    'first_name' => 'Test',
    'last_name' => 'User'
];

echo "<h1>Testing Subscription Controller BobPay</h1>";
echo "<pre>";

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['plan'] = 'basic';
$_POST['payment_method'] = 'bobpay';

echo "Simulating checkout with:\n";
echo "User: " . json_encode($_SESSION['user']) . "\n";
echo "Plan: {$_POST['plan']}\n";
echo "Payment Method: {$_POST['payment_method']}\n\n";

$controller = new SubscriptionController();

// Try to process payment (this will redirect or show error)
ob_start();
try {
    $controller->processPayment();
    $output = ob_get_clean();
    echo "Output: $output\n";
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nCheck logs for BobPay processing details.\n";
echo "</pre>";
?>
