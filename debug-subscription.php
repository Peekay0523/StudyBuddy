<?php
/**
 * Debug Subscription Status
 * Access: /debug-subscription
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Only allow logged in users
requireLogin();

$user = getCurrentUser();
$db = Database::getInstance()->getConnection();

echo "<h1>Subscription Debug Info</h1>";
echo "<p><strong>User ID:</strong> " . $user['id'] . "</p>";
echo "<p><strong>Username:</strong> " . htmlspecialchars($user['username']) . "</p>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email'] ?? 'N/A') . "</p>";
echo "<hr>";

// Check if subscriptions table exists
try {
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='subscriptions'")->fetch();
    if (!$result) {
        echo "<p style='color: red;'><strong>ERROR:</strong> Subscriptions table does not exist!</p>";
        exit;
    }
    echo "<p style='color: green;'><strong>OK:</strong> Subscriptions table exists</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    exit;
}

// Get all subscriptions for this user
echo "<h2>All Subscriptions for This User</h2>";
try {
    $stmt = $db->prepare("
        SELECT * FROM subscriptions
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $subscriptions = $stmt->fetchAll();

    if (empty($subscriptions)) {
        echo "<p><em>No subscription records found for this user.</em></p>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>
                <th>ID</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Price</th>
                <th>Period Start</th>
                <th>Period End</th>
                <th>Created At</th>
                <th>Is Expired?</th>
              </tr>";
        
        foreach ($subscriptions as $sub) {
            $isExpired = strtotime($sub['current_period_end']) < time();
            $isActive = in_array($sub['status'], ['active', 'trial', 'pending_eft']) && !$isExpired;
            
            echo "<tr style='" . ($isActive ? "background: #d1fae5;" : "") . "'>";
            echo "<td>" . $sub['id'] . "</td>";
            echo "<td>" . htmlspecialchars($sub['plan']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($sub['status']) . "</strong></td>";
            echo "<td>R" . number_format($sub['price'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($sub['current_period_start'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($sub['current_period_end'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($sub['created_at'] ?? 'N/A') . "</td>";
            echo "<td>" . ($isExpired ? "<span style='color: red;'><strong>YES (Expired)</strong></span>" : "<span style='color: green;'>NO (Active)</span>") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test the actual check
echo "<h2>Subscription Check Logic</h2>";

// Check 1: getUserSubscription
require_once __DIR__ . '/controllers/SubscriptionController.php';
$controller = new SubscriptionController();
$userSubscription = $controller->getUserSubscription($user['id']);

echo "<h3>getUserSubscription() Result:</h3>";
if ($userSubscription) {
    echo "<pre style='background: #f3f4f6; padding: 15px; border-radius: 8px;'>";
    print_r($userSubscription);
    echo "</pre>";
} else {
    echo "<p><em>Returns NULL (no active subscription found)</em></p>";
}

// Check 2: userHasActiveSubscription (using reflection to access private method)
echo "<h3>userHasActiveSubscription() Check:</h3>";
try {
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('userHasActiveSubscription');
    $method->setAccessible(true);
    $hasActive = $method->invoke($controller, $user['id']);
    
    if ($hasActive) {
        echo "<p style='color: red;'><strong>TRUE</strong> - User has an active/pending subscription (BLOCKING new subscription)</p>";
    } else {
        echo "<p style='color: green;'><strong>FALSE</strong> - User does NOT have an active subscription (CAN subscribe)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>SQL Query Being Executed</h2>";
echo "<pre style='background: #f3f4f6; padding: 15px; border-radius: 8px;'>";
echo "SELECT COUNT(*) FROM subscriptions
WHERE user_id = " . $user['id'] . "
AND status IN ('active', 'trial', 'pending_eft')
AND datetime(current_period_end) > datetime('now')";
echo "</pre>";

// Show current time for reference
echo "<p><strong>Current Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<hr>";
echo "<h2>Recommendations</h2>";
if ($userSubscription || $hasActive) {
    echo "<p><strong>Issue Found:</strong> You have an existing subscription record that is still active or pending.</p>";
    echo "<p><strong>Solutions:</strong></p>";
    echo "<ol>";
    echo "<li><strong>Option 1:</strong> Wait for the subscription period to expire</li>";
    echo "<li><strong>Option 2:</strong> Cancel the existing subscription from the admin panel</li>";
    echo "<li><strong>Option 3:</strong> Manually delete/update the subscription record in the database</li>";
    echo "</ol>";
    
    // Show button to clear old subscriptions
    echo "<form method='POST' action='/debug-subscription/clear' style='margin-top: 20px;' onsubmit=\"return confirm('This will delete ALL your subscription records. Are you sure?');\">";
    echo "<button type='submit' style='background: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;'><strong>⚠️ CLEAR ALL MY SUBSCRIPTION RECORDS</strong></button>";
    echo "</form>";
} else {
    echo "<p style='color: green;'><strong>No Issues Found:</strong> You should be able to subscribe to a plan.</p>";
    echo "<p>If you're still seeing the error, there might be a session/cache issue. Try logging out and back in.</p>";
}

echo "<hr>";
echo "<p><a href='/subscription'>← Back to Subscription Page</a></p>";
