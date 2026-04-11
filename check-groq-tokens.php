<?php
/**
 * Check why Groq tokens aren't showing in dashboard
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>🔍 Groq Token Dashboard Diagnostic</h1>";
echo "<pre>";

$db = Database::getInstance()->getConnection();

echo "=== All Logs in openai_usage_logs ===\n\n";

// Total count
$totalLogs = $db->query("SELECT COUNT(*) FROM openai_usage_logs")->fetchColumn();
echo "Total logs: $totalLogs\n\n";

// Show all logs with model_used
echo "=== All Logs (Last 20) ===\n";
$logs = $db->query("
    SELECT id, model_used, prompt_tokens, completion_tokens, total_tokens, created_at
    FROM openai_usage_logs
    ORDER BY created_at DESC
    LIMIT 20
")->fetchAll();

foreach ($logs as $log) {
    $model = $log['model_used'] === null ? 'NULL' : "'{$log['model_used']}'";
    echo sprintf(
        "ID: %d | Model: %-10s | Tokens: %5d | Date: %s\n",
        $log['id'],
        $model,
        $log['total_tokens'],
        $log['created_at']
    );
}

// Group by model_used
echo "\n=== Summary by Model ===\n";
$summary = $db->query("
    SELECT 
        CASE 
            WHEN model_used IS NULL THEN 'NULL'
            WHEN model_used = '' THEN '(empty string)'
            ELSE model_used 
        END as model_display,
        COUNT(*) as count,
        COALESCE(SUM(total_tokens), 0) as total_tokens
    FROM openai_usage_logs
    GROUP BY model_used
")->fetchAll();

foreach ($summary as $row) {
    echo "{$row['model_display']}: {$row['count']} logs, {$row['total_tokens']} tokens\n";
}

// What the dashboard query sees
echo "\n=== Dashboard Stats (What AdminController Calculates) ===\n";
$openaiTokens = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs WHERE model_used = 'openai' OR model_used IS NULL")->fetchColumn();
$grokTokens = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs WHERE model_used = 'grok'")->fetchColumn();

echo "OpenAI tokens (model_used = 'openai' OR NULL): $openaiTokens\n";
echo "Grok tokens (model_used = 'grok'): $grokTokens\n";

// Check what GrokAI is actually saving
echo "\n=== Checking GrokAI log method ===\n";
$grokLogs = $db->query("
    SELECT COUNT(*) 
    FROM openai_usage_logs 
    WHERE model_used = 'grok'
")->fetchColumn();

echo "Logs with model_used = 'grok': $grokLogs\n";

$nonGrokLogs = $db->query("
    SELECT COUNT(*) 
    FROM openai_usage_logs 
    WHERE model_used != 'grok' OR model_used IS NULL
")->fetchColumn();

echo "Logs with model_used != 'grok' or NULL: $nonGrokLogs\n";

echo "\n=== DONE ===\n";
echo "</pre>";
