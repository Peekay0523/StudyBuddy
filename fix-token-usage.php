<?php
/**
 * Fix Token Usage - Update NULL model_used to 'openai' and verify logging
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Fix Token Usage Logging</h1>";
echo "<pre>";

$db = Database::getInstance()->getConnection();

// Step 1: Check if column exists
echo "=== Step 1: Checking database schema ===\n";
$columns = $db->query("PRAGMA table_info(openai_usage_logs)")->fetchAll(PDO::FETCH_COLUMN);

if (in_array('model_used', $columns)) {
    echo "✓ model_used column exists\n";
} else {
    echo "✗ model_used column MISSING! Adding it now...\n";
    $db->exec("ALTER TABLE openai_usage_logs ADD COLUMN model_used TEXT DEFAULT 'openai'");
    echo "✓ Added model_used column\n";
}

// Step 2: Update NULL values to 'openai'
echo "\n=== Step 2: Updating NULL values ===\n";
$nullCount = $db->query("SELECT COUNT(*) FROM openai_usage_logs WHERE model_used IS NULL")->fetchColumn();
echo "Records with NULL model_used: $nullCount\n";

if ($nullCount > 0) {
    $db->exec("UPDATE openai_usage_logs SET model_used = 'openai' WHERE model_used IS NULL");
    echo "✓ Updated $nullCount records to 'openai'\n";
}

// Step 3: Show current distribution
echo "\n=== Step 3: Current model distribution ===\n";
$distribution = $db->query("
    SELECT 
        COALESCE(model_used, 'NULL') as model,
        COUNT(*) as count,
        SUM(total_tokens) as tokens
    FROM openai_usage_logs
    GROUP BY model_used
    ORDER BY count DESC
")->fetchAll();

foreach ($distribution as $row) {
    echo sprintf("%-10s: %5d requests | %8d tokens\n", 
        $row['model'], 
        $row['count'], 
        $row['tokens']
    );
}

// Step 4: Show recent logs with details
echo "\n=== Step 4: Recent 10 logs ===\n";
$logs = $db->query("
    SELECT model_used, prompt_tokens, completion_tokens, total_tokens, created_at 
    FROM openai_usage_logs 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetchAll();

foreach ($logs as $log) {
    $model = $log['model_used'] ?: 'NULL';
    echo sprintf("[%s] Model: %-8s | Tokens: %5d (P:%4d C:%4d)\n",
        $log['created_at'],
        $model,
        $log['total_tokens'],
        $log['prompt_tokens'],
        $log['completion_tokens']
    );
}

echo "\n=== DONE ===\n";
echo "\nNow visit: /admin/openai-settings to see updated stats\n";
echo "</pre>";
