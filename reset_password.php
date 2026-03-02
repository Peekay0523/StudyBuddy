<?php
/**
 * Script to reset admin password
 */

require_once __DIR__ . '/config/database.php';

$username = 'Peekay';
$newPassword = 'admin123'; // CHANGE THIS to your desired password

$db = Database::getInstance()->getConnection();

// Hash the password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the password
$update = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
$update->execute([$hashedPassword, $username]);

echo "✓ Password reset for user '{$username}'\n";
echo "New password: {$newPassword}\n";
echo "\nLogin at: http://localhost:8000/login\n";
?>
