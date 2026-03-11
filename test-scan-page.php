<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config/config.php';
    
    echo "Config loaded successfully<br>";
    
    // Test getScanLimitInfo
    $db = Database::getInstance()->getConnection();
    echo "Database connected<br>";
    
    // Test with a dummy user ID
    $testUserId = 1;
    $scanInfo = getScanLimitInfo($testUserId);
    echo "getScanLimitInfo works: " . json_encode($scanInfo) . "<br>";
    
    echo "All tests passed!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
