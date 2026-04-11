<?php
/**
 * Grok API Direct Diagnostic Test
 * Tests the Grok API connection and shows exact errors
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Grok API Diagnostic Test</h1>";
echo "<pre>";

// Check configuration
echo "=== Configuration ===\n";
echo "GROK_PROVIDER: " . (defined('GROK_PROVIDER') ? GROK_PROVIDER : 'NOT DEFINED') . "\n";
echo "GROK_API_KEY: " . (defined('GROK_API_KEY') ? (substr(GROK_API_KEY, 0, 10) . '...') : 'NOT DEFINED') . "\n";
echo "GROK_API_URL: " . (defined('GROK_API_URL') ? GROK_API_URL : '(default)') . "\n";
echo "GROK_MODEL: " . (defined('GROK_MODEL') ? GROK_MODEL : '(default)') . "\n";

// Test direct API call
echo "\n=== Direct API Call Test ===\n";

$apiKey = defined('GROK_API_KEY') ? GROK_API_KEY : '';
$apiUrl = defined('GROK_API_URL') && !empty(GROK_API_URL) ? GROK_API_URL : 'https://api.x.ai/v1/chat/completions';
$model = defined('GROK_MODEL') && !empty(GROK_MODEL) ? GROK_MODEL : 'grok-beta';

echo "API URL: $apiUrl\n";
echo "Model: $model\n";
echo "API Key Length: " . strlen($apiKey) . "\n";

$data = [
    'model' => $model,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Say hello and confirm you are working.'
        ]
    ],
    'max_tokens' => 100,
    'temperature' => 0.7
];

echo "\n=== Making API Request ===\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_VERBOSE, false);

// Enable error output
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "cURL Error: " . ($curlError ?: 'None') . "\n";

if ($response) {
    echo "\n=== Response Headers ===\n";
    echo substr($response, 0, $headerSize);
    
    echo "\n=== Response Body ===\n";
    $body = substr($response, $headerSize);
    $decoded = json_decode($body, true);
    
    if ($decoded) {
        echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
        
        if (isset($decoded['error'])) {
            echo "\n❌ API ERROR DETECTED!\n";
            echo "Error Type: " . ($decoded['error']['type'] ?? 'Unknown') . "\n";
            echo "Error Message: " . ($decoded['error']['message'] ?? 'Unknown') . "\n";
            echo "Error Code: " . ($decoded['error']['code'] ?? 'Unknown') . "\n";
        } elseif (isset($decoded['choices'])) {
            echo "\n✅ SUCCESS! API is working!\n";
            echo "Response: " . $decoded['choices'][0]['message']['content'] . "\n";
        }
    } else {
        echo $body . "\n";
    }
} else {
    echo "\n❌ No response received\n";
    if ($curlError) {
        echo "cURL Error: $curlError\n";
    }
}

echo "\n=== Testing Alternative Grok Models ===\n";

$modelsToTry = ['grok-beta', 'grok-2', 'grok-2-latest'];

foreach ($modelsToTry as $testModel) {
    echo "\nTrying model: $testModel\n";
    
    $testData = [
        'model' => $testModel,
        'messages' => [['role' => 'user', 'content' => 'Say hi']],
        'max_tokens' => 50
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP: $code - ";
    if ($code === 200) {
        echo "✅ SUCCESS!\n";
        $decoded = json_decode($response, true);
        if (isset($decoded['choices'])) {
            echo "Response: " . $decoded['choices'][0]['message']['content'] . "\n";
        }
        break;
    } else {
        echo "Failed\n";
    }
}

echo "\n=== Done ===\n";
echo "</pre>";
