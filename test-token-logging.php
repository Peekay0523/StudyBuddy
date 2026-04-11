<?php
/**
 * Test Token Logging
 * Verifies that both OpenAI and Groq token usage is being logged
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/AIHelper.php';
require_once __DIR__ . '/helpers/GrokAI.php';

$openAI = new AIHelper();
$groqAI = new GrokAI();

echo "<h1>Token Logging Test</h1>";
echo "<pre>";

// Get initial count
$db = Database::getInstance()->getConnection();
$initialCount = $db->query("SELECT COUNT(*) FROM openai_usage_logs")->fetchColumn();
echo "Initial log count: $initialCount\n\n";

// Test OpenAI
echo "=== Testing OpenAI ===\n";
$response1 = $openAI->chat('Say hello briefly.');
echo "OpenAI response: " . ($response1 ? 'SUCCESS' : 'FAILED') . "\n";

$count2 = $db->query("SELECT COUNT(*) FROM openai_usage_logs")->fetchColumn();
echo "Log count after OpenAI: $count2\n";

// Test Groq
echo "\n=== Testing Groq ===\n";
$response2 = $groqAI->chat('Say hello briefly.');
echo "Groq response: " . ($response2 ? 'SUCCESS' : 'FAILED') . "\n";

$count3 = $db->query("SELECT COUNT(*) FROM openai_usage_logs")->fetchColumn();
echo "Log count after Groq: $count3\n";

// Show recent logs
echo "\n=== Recent Token Logs ===\n";
$logs = $db->query("
    SELECT model_used, prompt_tokens, completion_tokens, total_tokens, created_at 
    FROM openai_usage_logs 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetchAll();

foreach ($logs as $log) {
    echo sprintf(
        "%s - Model: %s | Prompt: %d | Completion: %d | Total: %d | Time: %s\n",
        $log['model_used'] ?: 'NULL',
        $log['prompt_tokens'],
        $log['completion_tokens'],
        $log['total_tokens'],
        $log['created_at']
    );
}

// Summary
echo "\n=== Summary ===\n";
$summary = $db->query("
    SELECT 
        COALESCE(model_used, 'not_set') as model,
        COUNT(*) as count,
        SUM(total_tokens) as tokens
    FROM openai_usage_logs
    GROUP BY model_used
")->fetchAll();

foreach ($summary as $row) {
    echo "{$row['model']}: {$row['count']} requests, {$row['tokens']} total tokens\n";
}

echo "\n=== Done ===\n";
echo "</pre>";
