<?php
/**
 * Direct API Test - Bypass Router
 */
header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/CareerController.php';

$action = $_GET['action'] ?? 'search';

try {
    $controller = new CareerController();
    
    if ($action === 'categories') {
        $controller->categories();
    } elseif ($action === 'search') {
        $controller->search();
    } elseif ($action === 'institutions') {
        $controller->institutions();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
