<?php
/**
 * BobPay Test Script
 * Run this to test BobPay API connection
 * Access: http://localhost:8000/test-bobpay.php
 */

// Load config - go up one directory from public
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/bobpay.php';

echo "<h1>BobPay Test</h1>";

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable logging to file
$logFile = __DIR__ . '/../logs/bobpay_test.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}
ini_set('error_log', $logFile);

echo "<pre>";

// Test 1: Check configuration
echo "=== Test 1: Configuration ===\n";
echo "BOBPAY_API_KEY defined: " . (defined('BOBPAY_API_KEY') ? 'YES' : 'NO') . "\n";
echo "BOBPAY_API_KEY value: " . (defined('BOBPAY_API_KEY') ? BOBPAY_API_KEY : 'NOT DEFINED') . "\n";
echo "APP_URL: " . APP_URL . "\n\n";

// Test 2: Initialize BobPay Helper
echo "=== Test 2: BobPay Helper ===\n";
$bobPay = new BobPayHelper();
echo "Is configured: " . ($bobPay->isConfigured() ? 'YES' : 'NO') . "\n\n";

// Test 3: Test API Call
echo "=== Test 3: Test Payment Creation ===\n";
$testData = $bobPay->preparePaymentData([
    'amount' => 39.00,
    'customer_email' => 'test@example.com',
    'customer_name' => 'Test User',
    'success_url' => APP_URL . '/subscription/bobpay/return',
    'cancel_url' => APP_URL . '/subscription/bobpay/cancel',
    'callback_url' => APP_URL . '/subscription/bobpay/webhook',
    'metadata' => [
        'test' => true,
        'plan' => 'basic'
    ]
]);

echo "Payment Data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

echo "=== Test 4: API Request ===\n";
echo "Sending request to BobPay API...\n";
echo "Base URL: " . (new ReflectionClass($bobPay))->getProperty('baseUrl')->getValue($bobPay) . "\n\n";

// Manually test cURL to see exact error (skip this - use BobPay helper instead)
echo "Skipping manual cURL test - using BobPay Helper with login...\n\n";

echo "=== Test 5: Testing BobPay Login ===\n";
echo "Attempting to login to BobPay...\n";
$token = $bobPay->login();
if ($token) {
    echo "✅ Login SUCCESS!\n";
    echo "Bearer Token: " . substr($token, 0, 20) . "...\n\n";
} else {
    echo "❌ Login FAILED!\n";
    echo "Check if sandbox credentials are correct.\n\n";
}

echo "=== Test 6: Creating Payment Link ===\n";
$response = $bobPay->createPayment($testData);

if ($response) {
    echo "✅ SUCCESS!\n";
    echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
    if (isset($response['payment_url'])) {
        echo "\n🔗 Payment URL: " . $response['payment_url'] . "\n";
        echo "<a href='" . $response['payment_url'] . "' target='_blank'>Click here to test payment</a>\n";
    }
} else {
    echo "❌ FAILED!\n";
}

echo "\n=== Test Complete ===\n";
echo "</pre>";

// Show error logs
echo "<h2>Recent Error Logs</h2>";
echo "<pre>";
$logFile = __DIR__ . '/../logs/bobpay_test.log';
if (file_exists($logFile)) {
    echo file_get_contents($logFile);
} else {
    echo "No log file found. Check PHP error log below.\n";
}
echo "</pre>";

// Also show PHP error log if available
echo "<h2>PHP Error Log (last 50 lines)</h2>";
echo "<pre>";
$phpErrorLog = 'C:/xampp/php/logs/php_error_log';
if (file_exists($phpErrorLog)) {
    $lines = file($phpErrorLog);
    $lastLines = array_slice($lines, -50);
    echo implode('', $lastLines);
} else {
    echo "PHP error log not found at: $phpErrorLog\n";
}
echo "</pre>";
?>
