<?php
/**
 * Script to downgrade expired trial subscriptions to free plan
 * Run this script daily via cron job or Windows Task Scheduler
 * 
 * Usage: php downgrade_trials.php
 */

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "Checking for expired trial subscriptions...\n\n";

try {
    // Find all trial subscriptions that have expired
    $stmt = $db->prepare("
        SELECT s.id, s.user_id, u.username, u.phone, s.current_period_end
        FROM subscriptions s
        JOIN users u ON s.user_id = u.id
        WHERE s.status = 'trial' 
        AND datetime(s.current_period_end) < datetime('now')
    ");
    $stmt->execute();
    $expiredTrials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($expiredTrials)) {
        echo "No expired trial subscriptions found.\n";
    } else {
        echo "Found " . count($expiredTrials) . " expired trial subscription(s):\n\n";

        foreach ($expiredTrials as $trial) {
            echo "- User: {$trial['username']} (Phone: {$trial['phone']})\n";
            echo "  Trial ended: {$trial['current_period_end']}\n";

            // Downgrade to free plan by marking trial as expired
            $update = $db->prepare("
                UPDATE subscriptions 
                SET status = 'expired' 
                WHERE id = ?
            ");
            $update->execute([$trial['id']]);

            echo "  Status: Downgraded to expired (free plan)\n\n";
        }

        echo count($expiredTrials) . " subscription(s) downgraded successfully.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "The subscriptions table may not exist yet.\n";
    exit(1);
}

echo "\nDone!\n";
?>
