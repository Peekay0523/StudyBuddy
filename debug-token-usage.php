<?php
/**
 * Debug token usage tracking
 */

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "=== Token Usage Debug Report ===\n\n";

// Check if table exists
$tableExists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='openai_usage_logs'")->fetch();
if (!$tableExists) {
    echo "ERROR: openai_usage_logs table does not exist!\n";
    echo "Run: php add_openai_usage_table.php\n";
    exit;
}

echo "✓ Table exists\n\n";

// Total tokens
$totalTokens = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs")->fetchColumn();
echo "Total tokens (all time): " . number_format($totalTokens) . "\n\n";

// Current month query
$startOfMonth = $db->query("SELECT DATE('now', 'start of month')")->fetchColumn();
echo "Start of month (UTC): " . $startOfMonth . "\n";

$tokensThisMonth = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs WHERE DATE(created_at) >= DATE('now', 'start of month')")->fetchColumn();
echo "Tokens this month (UTC query): " . number_format($tokensThisMonth) . "\n\n";

// Show all records
echo "=== All Usage Records ===\n";
$records = $db->query("SELECT * FROM openai_usage_logs ORDER BY created_at DESC LIMIT 20")->fetchAll();

if (empty($records)) {
    echo "No records found.\n";
} else {
    foreach ($records as $record) {
        $createdDate = date('Y-m-d H:i:s', strtotime($record['created_at']));
        $isThisMonth = strtotime($record['created_at']) >= strtotime($startOfMonth) ? 'YES' : 'NO';
        echo "ID: {$record['id']} | Tokens: {$record['total_tokens']} | Created: {$createdDate} | Is This Month: {$isThisMonth}\n";
    }
}

echo "\n=== Debug Complete ===\n";
