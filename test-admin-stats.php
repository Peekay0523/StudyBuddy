<?php
/**
 * Test AdminController Stats Calculation
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>🔍 AdminController Stats Test</h1>";
echo "<pre>";

$db = Database::getInstance()->getConnection();

echo "=== Testing AdminController Logic ===\n\n";

$stats = [
    'total_tokens_used' => 0,
    'tokens_this_month' => 0,
    'total_api_calls' => 0,
    'estimated_cost' => 0,
    'openai_tokens' => 0,
    'grok_tokens' => 0,
    'openai_calls' => 0,
    'grok_calls' => 0,
];

try {
    // Check if model_used column exists
    $columns = $db->query("PRAGMA table_info(openai_usage_logs)")->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    $hasModelUsed = in_array('model_used', $columnNames);
    
    echo "model_used column exists: " . ($hasModelUsed ? 'YES' : 'NO') . "\n";
    echo "Columns found: " . implode(', ', $columnNames) . "\n\n";

    // Total stats
    $stats['total_tokens_used'] = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs")->fetchColumn();
    $stats['tokens_this_month'] = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs WHERE DATE(created_at) >= DATE('now', 'start of month')")->fetchColumn();
    $stats['total_api_calls'] = $db->query("SELECT COUNT(*) FROM openai_usage_logs")->fetchColumn();

    echo "Total tokens: {$stats['total_tokens_used']}\n";
    echo "Tokens this month: {$stats['tokens_this_month']}\n";
    echo "Total API calls: {$stats['total_api_calls']}\n\n";

    // Model-specific stats
    if ($hasModelUsed) {
        echo "✓ Executing model-specific queries...\n";
        
        $stats['openai_tokens'] = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs WHERE model_used = 'openai' OR model_used IS NULL")->fetchColumn();
        $stats['grok_tokens'] = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs WHERE model_used = 'grok'")->fetchColumn();
        $stats['openai_calls'] = $db->query("SELECT COUNT(*) FROM openai_usage_logs WHERE model_used = 'openai' OR model_used IS NULL")->fetchColumn();
        $stats['grok_calls'] = $db->query("SELECT COUNT(*) FROM openai_usage_logs WHERE model_used = 'grok'")->fetchColumn();
        
        echo "OpenAI tokens query result: {$stats['openai_tokens']}\n";
        echo "Grok tokens query result: {$stats['grok_tokens']}\n";
        echo "OpenAI calls: {$stats['openai_calls']}\n";
        echo "Grok calls: {$stats['grok_calls']}\n";
    } else {
        echo "✗ model_used column NOT found - using fallback\n";
        $stats['openai_tokens'] = $stats['total_tokens_used'];
        $stats['openai_calls'] = $stats['total_api_calls'];
    }

    $stats['estimated_cost'] = $stats['openai_tokens'] * 0.0000006;
    $stats['estimated_savings'] = $stats['grok_tokens'] * 0.0000004;

} catch (Exception $e) {
    echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\n=== Final Stats Array ===\n";
print_r($stats);

echo "\n=== What the page will show ===\n";
echo "Total Tokens: " . number_format($stats['total_tokens_used']) . "\n";
echo "OpenAI Tokens: " . number_format($stats['openai_tokens']) . "\n";
echo "Grok/LLaMA Tokens: " . number_format($stats['grok_tokens']) . "\n";
echo "Estimated Cost: \$" . number_format($stats['estimated_cost'], 4) . "\n";
echo "Estimated Savings: \$" . number_format($stats['estimated_savings'], 4) . "\n";

echo "\n=== DONE ===\n";
echo "</pre>";
