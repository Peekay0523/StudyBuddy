<?php
/**
 * Script to set a user as admin
 * Usage: php set_admin.php <username>
 * Or edit the $username variable below
 */

require_once __DIR__ . '/config/database.php';

// Set the username you want to make admin
$username = 'Peekay'; // CHANGE THIS to your username

$db = Database::getInstance()->getConnection();

// Check if user exists
$stmt = $db->prepare("SELECT id, username, role FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "User '{$username}' not found!\n";
    echo "Existing users:\n";
    $users = $db->query("SELECT id, username, role FROM users")->fetchAll();
    foreach ($users as $u) {
        echo "  - ID: {$u['id']}, Username: {$u['username']}, Role: {$u['role']}\n";
    }
    exit(1);
}

echo "Found user: {$user['username']} (Current role: {$user['role']})\n";

// Update to admin
$update = $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
$update->execute([$user['id']]);

echo "✓ User '{$username}' is now an ADMIN!\n";
echo "Login and visit /admin to access the admin panel.\n";
?>
