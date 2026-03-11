<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    
    // Simulate being logged in
    $_SESSION['user_id'] = 1;
    $_SESSION['user'] = ['id' => 1, 'username' => 'test', 'role' => 'student'];
    
    $user = getCurrentUser();
    echo "User: " . json_encode($user) . "<br>";
    
    $isFreeTier = isFreeTierUser($user['id']);
    echo "Is Free Tier: " . ($isFreeTier ? 'true' : 'false') . "<br>";
    
    // Try including the page
    ob_start();
    include __DIR__ . '/templates/pages/upload_report_card.php';
    $content = ob_get_clean();
    
    echo "Page loaded successfully!<br>";
    echo "Content length: " . strlen($content) . " bytes<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
