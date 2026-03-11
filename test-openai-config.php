<?php
/**
 * Test OpenAI Configuration
 */

require_once __DIR__ . '/config/config.php';

echo "<h2>OpenAI Configuration Test</h2>";

echo "<h3>API Key Status</h3>";
if (defined('OPENAI_API_KEY')) {
    $key = OPENAI_API_KEY;
    if ($key === 'YOUR_OPENAI_API_KEY_HERE' || empty($key)) {
        echo "<p style='color: red;'><strong>❌ NOT CONFIGURED</strong></p>";
        echo "<p>You need to add your OpenAI API key in config/config.php</p>";
        echo "<p>Get your key from: <a href='https://platform.openai.com/api-keys' target='_blank'>https://platform.openai.com/api-keys</a></p>";
    } else {
        echo "<p style='color: green;'><strong>✅ CONFIGURED</strong></p>";
        echo "<p>API Key: " . substr($key, 0, 10) . "...</p>";
        
        // Test API connection
        echo "<h3>Testing API Connection...</h3>";
        
        $apiKey = OPENAI_API_KEY;
        $apiUrl = 'https://api.openai.com/v1/models';
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo "<p style='color: green;'><strong>✅ API Connection Successful!</strong></p>";
            echo "<p>HTTP Code: $httpCode</p>";
        } else {
            echo "<p style='color: orange;'><strong>⚠️ API Connection Failed</strong></p>";
            echo "<p>HTTP Code: $httpCode</p>";
            echo "<p>Error: " . htmlspecialchars($error) . "</p>";
            echo "<p>Response: " . htmlspecialchars($response) . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'><strong>❌ OPENAI_API_KEY constant not defined!</strong></p>";
}

echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>Get your API key from <a href='https://platform.openai.com/api-keys' target='_blank'>OpenAI</a></li>";
echo "<li>Edit <code>config/config.php</code></li>";
echo "<li>Replace 'YOUR_OPENAI_API_KEY_HERE' with your actual key</li>";
echo "<li>Re-upload your report card to get AI-powered recommendations</li>";
echo "</ol>";
