<?php
/**
 * Debug Session
 * Access: http://localhost:8000/debug-session.php
 */
require_once __DIR__ . '/../config/config.php';

echo "<h1>Session Debug</h1>";
echo "<pre>";

echo "Session ID: " . session_id() . "\n\n";

echo "All Session Data:\n";
print_r($_SESSION);

echo "\n\nUser Data:\n";
$user = getCurrentUser();
print_r($user);

echo "\n\nStudent Data:\n";
$student = getCurrentStudent();
print_r($student);

echo "\n\nIs Logged In: " . (isLoggedIn() ? 'YES' : 'NO') . "\n";

if ($user) {
    echo "\nUser Email: " . ($user['email'] ?? 'MISSING') . "\n";
    echo "User Username: " . ($user['username'] ?? 'MISSING') . "\n";
    echo "User ID: " . ($user['id'] ?? 'MISSING') . "\n";
}

echo "</pre>";

// If user has no email, show form to add it
if ($user && empty($user['email'])) {
    echo "<h2>Add Email to Account</h2>";
    echo "<form method='POST' action='debug-session.php'>";
    echo "<input type='email' name='email' placeholder='Enter your email' required style='padding: 10px; width: 300px; font-size: 16px;'>";
    echo "<button type='submit' style='padding: 10px 20px; font-size: 16px; background: #667eea; color: white; border: none; cursor: pointer;'>Save Email</button>";
    echo "</form>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->execute([$_POST['email'], $user['id']]);
    
    $_SESSION['user']['email'] = $_POST['email'];
    
    echo "<p style='color: green; font-size: 18px; margin-top: 20px;'>✅ Email updated successfully!</p>";
    echo "<a href='debug-session.php' style='display: inline-block; margin-top: 10px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Refresh</a>";
}
?>
