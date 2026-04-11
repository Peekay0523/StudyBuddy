<?php
/**
 * Detailed Groq Token Diagnostic
 * Shows exact API response and token logging
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/GrokAI.php';

echo "<h1>🔍 Detailed Groq Token Diagnostic</h1>";
echo "<pre>";

// Get initial count
$db = Database::getInstance()->getConnection();
$beforeCount = $db->query("SELECT COUNT(*) FROM openai_usage_logs")->fetchColumn();
echo "Logs before test: $beforeCount\n\n";

// Make a direct Groq request with full logging
$apiKey = GROK_API_KEY;
$apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
$model = 'llama-3.3-70b-versatile';

echo "=== Making Direct Groq Request ===\n";
echo "URL: $apiUrl\n";
echo "Model: $model\n\n";

$data = [
    'model' => $model,
    'messages' => [['role' => 'user', 'content' => 'Say hello in 5 words.']],
    'max_tokens' => 50,
    'temperature' => 0.7
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n\n";

if ($response) {
    $result = json_decode($response, true);
    
    echo "=== Full Response Structure ===\n";
    print_r($result);
    
    echo "\n=== Token Usage Field ===\n";
    if (isset($result['usage'])) {
        echo "✓ 'usage' field exists!\n";
        echo json_encode($result['usage'], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "✗ 'usage' field NOT found\n";
        echo "Available keys: " . implode(', ', array_keys($result)) . "\n";
    }
    
    echo "\n=== Content ===\n";
    echo $result['choices'][0]['message']['content'] ?? 'No content';
}

// Now test GrokAI helper
echo "\n\n=== Testing GrokAI Helper ===\n";
$groqAI = new GrokAI();

echo "Provider: " . GROK_PROVIDER . "\n";
echo "API Key set: " . (defined('GROK_API_KEY') ? 'Yes' : 'No') . "\n";
echo "isValidApiKey: " . ($groqAI->isValidApiKey() ? 'Yes' : 'No') . "\n\n";

$response2 = $groqAI->chat('Reply with exactly: Groq is working');

echo "Response: " . ($response2 ?: 'NULL') . "\n";

$afterCount = $db->query("SELECT COUNT(*) FROM openai_usage_logs")->fetchColumn();
echo "\nLogs after test: $afterCount\n";
echo "New logs added: " . ($afterCount - $beforeCount) . "\n";

// Show latest log
if ($afterCount > $beforeCount) {
    $latest = $db->query("
        SELECT model_used, prompt_tokens, completion_tokens, total_tokens, created_at 
        FROM openai_usage_logs 
        ORDER BY created_at DESC 
        LIMIT 1
    ")->fetch();
    
    echo "\n=== Latest Log Entry ===\n";
    echo "Model: " . ($latest['model_used'] ?: 'NULL') . "\n";
    echo "Prompt tokens: " . $latest['prompt_tokens'] . "\n";
    echo "Completion tokens: " . $latest['completion_tokens'] . "\n";
    echo "Total tokens: " . $latest['total_tokens'] . "\n";
    echo "Time: " . $latest['created_at'] . "\n";
} else {
    echo "\n✗ No new logs added - token logging may be failing\n";
    echo "Check PHP error log for: 'Failed to log GrokAI usage'\n";
}

echo "\n=== DONE ===\n";
echo "</pre>";
