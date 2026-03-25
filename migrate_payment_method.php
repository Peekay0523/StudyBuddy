<?php
/**
 * Migration: Add payment_method and transaction_id columns to subscriptions table
 *
 * Run this once to add tracking for payment methods (bobpay, eft)
 */

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "Adding payment_method and transaction_id columns to subscriptions table...\n";

try {
    // Check if columns already exist
    $columns = $db->query("PRAGMA table_info(subscriptions)")->fetchAll(PDO::FETCH_COLUMN);
    
    $hasPaymentMethod = in_array('payment_method', $columns);
    $hasTransactionId = in_array('transaction_id', $columns);
    
    if ($hasPaymentMethod && $hasTransactionId) {
        echo "✓ Columns already exist. Migration complete.\n";
        exit(0);
    }
    
    // Add payment_method column if it doesn't exist
    if (!$hasPaymentMethod) {
        $db->exec("ALTER TABLE subscriptions ADD COLUMN payment_method TEXT DEFAULT 'eft'");
        echo "✓ Added payment_method column\n";
    }
    
    // Add transaction_id column if it doesn't exist
    if (!$hasTransactionId) {
        $db->exec("ALTER TABLE subscriptions ADD COLUMN transaction_id TEXT");
        echo "✓ Added transaction_id column\n";
    }
    
    // Update existing EFT subscriptions
    $db->exec("UPDATE subscriptions SET payment_method = 'eft' WHERE status = 'pending_eft'");
    echo "✓ Updated pending_eft subscriptions to payment_method = 'eft'\n";
    
    // Update existing active subscriptions
    $db->exec("UPDATE subscriptions SET payment_method = 'card' WHERE status = 'active' AND (payment_method IS NULL OR payment_method = 'eft')");
    echo "✓ Updated active subscriptions to payment_method = 'card'\n";
    
    // Create index for faster filtering
    try {
        $db->exec("CREATE INDEX IF NOT EXISTS idx_subscriptions_payment_method ON subscriptions(payment_method)");
        echo "✓ Created index on payment_method\n";
    } catch (Exception $e) {
        echo "⚠ Index creation skipped (may already exist)\n";
    }
    
    echo "\n✓ Migration complete!\n";
    
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
