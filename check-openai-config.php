<?php
/**
 * Check OpenAI Configuration and Test API
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>OpenAI Configuration Check</h1>";

echo "<h2>1. API Key Status</h2>";
if (defined('OPENAI_API_KEY')) {
    $key = OPENAI_API_KEY;
    echo "<p><strong>OPENAI_API_KEY is defined</strong></p>";
    echo "<p>Key starts with: " . substr($key, 0, 10) . "...</p>";
    
    if ($key === 'YOUR_OPENAI_API_KEY_HERE' || empty($key) || strlen($key) < 20) {
        echo "<p style='color: red;'><strong>⚠️ INVALID API KEY!</strong></p>";
        echo "<p>You need to set a valid OpenAI API key in your .env file.</p>";
        echo "<p>Get your API key from: <a href='https://platform.openai.com/api-keys' target='_blank'>https://platform.openai.com/api-keys</a></p>";
        echo "<h3>How to fix:</h3>";
        echo "<ol>";
        echo "<li>Copy <code>.env.example</code> to <code>.env</code></li>";
        echo "<li>Edit <code>.env</code> and replace <code>YOUR_OPENAI_API_KEY_HERE</code> with your actual API key</li>";
        echo "<li>Format: <code>OPENAI_API_KEY=sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxx</code></li>";
        echo "</ol>";
        
        echo "<h3>Or run this command:</h3>";
        echo "<pre>echo OPENAI_API_KEY=sk-proj-YOUR_ACTUAL_KEY_HERE > .env</pre>";
    } else {
        echo "<p style='color: green;'><strong>✓ API key looks valid</strong></p>";
        
        // Test the API
        echo "<h2>2. Testing OpenAI API</h2>";
        
        $apiKey = $key;
        $apiUrl = 'https://api.openai.com/v1/chat/completions';
        
        $messages = [
            ['role' => 'user', 'content' => 'Say "Hello, the API is working!"']
        ];
        
        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
            'max_tokens' => 50
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        echo "<p><strong>HTTP Response Code:</strong> $httpCode</p>";
        
        if ($curlError) {
            echo "<p style='color: red;'><strong>cURL Error:</strong> $curlError</p>";
        }
        
        if ($response) {
            $result = json_decode($response, true);
            
            if (isset($result['error'])) {
                echo "<p style='color: red;'><strong>API Error:</strong> " . htmlspecialchars(json_encode($result['error'], JSON_PRETTY_PRINT)) . "</p>";
            } elseif (isset($result['choices'][0]['message']['content'])) {
                echo "<p style='color: green;'><strong>✓ API Test Successful!</strong></p>";
                echo "<p><strong>Response:</strong> " . htmlspecialchars($result['choices'][0]['message']['content']) . "</p>";
            } else {
                echo "<p style='color: orange;'><strong>Unexpected Response:</strong></p>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }
        }
    }
} else {
    echo "<p style='color: red;'><strong>OPENAI_API_KEY is NOT defined!</strong></p>";
}

echo "<h2>3. ImageMagick Status</h2>";
$magickPath = 'C:\Program Files\ImageMagick-7.1.2-Q16\magick.exe';
if (file_exists($magickPath)) {
    echo "<p style='color: green;'><strong>✓ ImageMagick found at:</strong> $magickPath</p>";
    
    // Test ImageMagick
    $versionCmd = "\"$magickPath\" -version";
    $versionOutput = shell_exec($versionCmd);
    echo "<pre>" . htmlspecialchars($versionOutput) . "</pre>";
} else {
    echo "<p style='color: red;'><strong>⚠️ ImageMagick NOT found at:</strong> $magickPath</p>";
    echo "<p>ImageMagick is required to convert PDFs to images for OCR.</p>";
    echo "<p>Download from: <a href='https://imagemagick.org/script/download.php#windows' target='_blank'>https://imagemagick.org/script/download.php#windows</a></p>";
}

echo "<h2>4. Error Log</h2>";
$logFile = __DIR__ . '/error.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = substr($logs, -5000); // Last 5000 characters
    echo "<pre>" . htmlspecialchars($recentLogs) . "</pre>";
} else {
    echo "<p>No error log file found.</p>";
}

echo "<h2>5. Test Report Card Processing</h2>";
echo "<p><a href='/upload-report-card' class='btn-primary'>Upload a Report Card</a></p>";

?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h1 { color: #667eea; }
h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; margin-top: 30px; }
pre { background: #f5f5f5; padding: 15px; border-radius: 8px; overflow-x: auto; }
.btn-primary { display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 8px; }
</style>
