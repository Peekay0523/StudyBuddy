<?php
/**
 * Debug script to check user roles
 */
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "=== All Users and Their Roles ===\n\n";

$users = $db->query("SELECT id, username, email, role FROM users")->fetchAll();

foreach ($users as $user) {
    echo "ID: {$user['id']}\n";
    echo "Username: {$user['username']}\n";
    echo "Email: " . ($user['email'] ?? 'N/A') . "\n";
    echo "Role: " . ($user['role'] ?? 'NULL') . "\n";
    echo str_repeat('-', 30) . "\n";
}

echo "\n=== Current Session ===\n";
echo "Session user_id: " . ($_SESSION['user_id'] ?? 'Not logged in') . "\n";
echo "Session user role: " . ($_SESSION['user']['role'] ?? 'Not set') . "\n";
?>
