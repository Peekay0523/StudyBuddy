<?php
// Test API endpoint directly
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Script.php';

header('Content-Type: application/json');

// Test if we can reach the controller
echo json_encode([
    'success' => true,
    'message' => 'API is working',
    'test' => 'This is a test response from /test-api-endpoint.php'
]);
