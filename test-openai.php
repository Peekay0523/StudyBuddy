<?php
/**
 * Test OpenAI Connection
 * Access: http://localhost:8000/test-openai
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/AIHelper.php';

// Only allow logged in users or in debug mode
if (!DEBUG_MODE && !isLoggedIn()) {
    die('Please login first');
}

$testResults = [];
$apiKeyConfigured = !empty(OPENAI_API_KEY) && OPENAI_API_KEY !== 'YOUR_OPENAI_API_KEY_HERE';

// Test API connection if key is configured
if ($apiKeyConfigured && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $testModel = $_POST['model'] ?? 'gpt-4o-mini';
    $testMessage = $_POST['test_message'] ?? 'Say hello in one short sentence';
    
    $startTime = microtime(true);
    
    $aiHelper = new AIHelper();
    
    // Temporarily override the model for testing
    $reflection = new ReflectionClass($aiHelper);
    $property = $reflection->getProperty('apiUrl');
    $property->setAccessible(true);
    
    // Make direct API call to test
    $apiKey = OPENAI_API_KEY;
    $apiUrl = 'https://api.openai.com/v1/chat/completions';
    
    $data = [
        'model' => $testModel,
        'messages' => [
            ['role' => 'system', 'content' => 'You are a test assistant. Keep responses very brief.'],
            ['role' => 'user', 'content' => $testMessage]
        ],
        'max_tokens' => 100,
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
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $endTime = microtime(true);
    curl_close($ch);
    
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    if ($response) {
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            $testResults = [
                'success' => false,
                'error' => $result['error']['message'] ?? 'Unknown API error',
                'http_code' => $httpCode,
                'response_time' => $responseTime,
                'model' => $testModel
            ];
        } elseif (isset($result['choices'][0]['message']['content'])) {
            $testResults = [
                'success' => true,
                'response' => $result['choices'][0]['message']['content'],
                'model' => $testModel,
                'http_code' => $httpCode,
                'response_time' => $responseTime,
                'tokens_used' => $result['usage']['total_tokens'] ?? 'N/A',
                'full_response' => $result
            ];
        } else {
            $testResults = [
                'success' => false,
                'error' => 'Unexpected response format',
                'http_code' => $httpCode,
                'response_time' => $responseTime,
                'raw_response' => substr($response, 0, 500)
            ];
        }
    } else {
        $testResults = [
            'success' => false,
            'error' => $curlError ?: 'No response from API',
            'http_code' => $httpCode,
            'response_time' => $responseTime
        ];
    }
}

// Get current model from AIHelper
$aiHelper = new AIHelper();
$reflection = new ReflectionClass($aiHelper);
$property = $reflection->getProperty('apiUrl');
$property->setAccessible(true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test OpenAI - StudySmart</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .status-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .status-item {
            padding: 12px;
            margin: 8px 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .status-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .status-info {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1f2937;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .result-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .result-box h4 {
            margin-top: 0;
            color: #1f2937;
        }
        .result-content {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .model-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .model-option {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .model-option:hover {
            border-color: #667eea;
            background: #f5f8ff;
        }
        .model-option input[type="radio"]:checked + div {
            font-weight: 600;
            color: #667eea;
        }
        .model-option:has(input:checked) {
            border-color: #667eea;
            background: #f5f8ff;
        }
        .code {
            background: #1e293b;
            color: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="test-container">
    <h1 style="color: #1f2937; margin-bottom: 10px;">
        <i class="fas fa-robot" style="color: #667eea;"></i>
        OpenAI Connection Test
    </h1>
    <p style="color: #6b7280; margin-bottom: 20px;">
        Test your OpenAI API configuration and try different models
    </p>

    <!-- Configuration Status -->
    <div class="status-box">
        <h3 style="margin-top: 0; color: #1f2937; font-size: 16px;">
            <i class="fas fa-cog"></i> Configuration Status
        </h3>
        
        <div class="status-item <?php echo $apiKeyConfigured ? 'status-success' : 'status-error'; ?>">
            <i class="fas fa-key"></i>
            <strong>API Key:</strong>&nbsp;
            <?php if ($apiKeyConfigured): ?>
                <span>✅ Configured</span>
                <span style="margin-left: 10px; opacity: 0.7;"><?php echo substr(OPENAI_API_KEY, 0, 15); ?>...</span>
            <?php else: ?>
                <span>❌ Not configured</span>
            <?php endif; ?>
        </div>
        
        <div class="status-item status-info">
            <i class="fas fa-microchip"></i>
            <strong>Current Model:</strong>&nbsp;
            <span class="code">gpt-4o-mini</span>
        </div>
        
        <div class="status-item status-info">
            <i class="fas fa-link"></i>
            <strong>API Endpoint:</strong>&nbsp;
            <span style="font-size: 12px;">https://api.openai.com/v1/chat/completions</span>
        </div>
    </div>

    <?php if (!$apiKeyConfigured): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>API Key Not Configured!</strong><br><br>
            <strong>Steps to fix:</strong>
            <ol style="margin: 10px 0 0 20px;">
                <li>Get your API key from: <a href="https://platform.openai.com/api-keys" target="_blank">https://platform.openai.com/api-keys</a></li>
                <li>Create a <code class="code">.env</code> file in project root with:<br>
                    <code class="code">OPENAI_API_KEY=sk-proj-your-key-here</code></li>
                <li>Or edit <code class="code">config/config.php</code> and add your key</li>
            </ol>
        </div>
    <?php endif; ?>

    <?php if ($testResults): ?>
        <div class="alert alert-<?php echo $testResults['success'] ? 'success' : 'error'; ?>">
            <i class="fas fa-<?php echo $testResults['success'] ? 'check-circle' : 'times-circle'; ?>"></i>
            <strong><?php echo $testResults['success'] ? '✅ Test Successful!' : '❌ Test Failed!' ?></strong><br>
            <?php if (!$testResults['success']): ?>
                Error: <?php echo htmlspecialchars($testResults['error']); ?>
            <?php endif; ?>
        </div>

        <?php if ($testResults['success']): ?>
            <div class="result-box">
                <h4><i class="fas fa-comment-dots"></i> AI Response</h4>
                <div class="result-content"><?php echo htmlspecialchars($testResults['response']); ?></div>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 15px;">
                    <div style="background: #f0fdf4; padding: 10px; border-radius: 6px;">
                        <div style="font-size: 12px; color: #6b7280;">Response Time</div>
                        <div style="font-size: 18px; font-weight: 600; color: #16a34a;"><?php echo $testResults['response_time']; ?> ms</div>
                    </div>
                    <div style="background: #eff6ff; padding: 10px; border-radius: 6px;">
                        <div style="font-size: 12px; color: #6b7280;">HTTP Code</div>
                        <div style="font-size: 18px; font-weight: 600; color: #2563eb;"><?php echo $testResults['http_code']; ?></div>
                    </div>
                    <div style="background: #fef3c7; padding: 10px; border-radius: 6px;">
                        <div style="font-size: 12px; color: #6b7280;">Tokens Used</div>
                        <div style="font-size: 18px; font-weight: 600; color: #d97706;"><?php echo $testResults['tokens_used']; ?></div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php if (isset($testResults['raw_response'])): ?>
                <div class="result-box">
                    <h4><i class="fas fa-code"></i> Raw Response</h4>
                    <div class="result-content"><?php echo htmlspecialchars($testResults['raw_response']); ?></div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Test Form -->
    <form method="POST" action="" style="margin-top: 30px;">
        <div class="form-group">
            <label><i class="fas fa-microchip"></i> Select Model</label>
            <div class="model-grid">
                <label class="model-option">
                    <input type="radio" name="model" value="gpt-4o-mini" checked>
                    <div>
                        <strong>gpt-4o-mini</strong><br>
                        <small style="color: #6b7280;">Fast & Affordable ⭐</small>
                    </div>
                </label>
                <label class="model-option">
                    <input type="radio" name="model" value="gpt-3.5-turbo">
                    <div>
                        <strong>gpt-3.5-turbo</strong><br>
                        <small style="color: #6b7280;">Legacy Model</small>
                    </div>
                </label>
                <label class="model-option">
                    <input type="radio" name="model" value="gpt-4o">
                    <div>
                        <strong>gpt-4o</strong><br>
                        <small style="color: #6b7280;">More Powerful</small>
                    </div>
                </label>
                <label class="model-option">
                    <input type="radio" name="model" value="gpt-4-turbo">
                    <div>
                        <strong>gpt-4-turbo</strong><br>
                        <small style="color: #6b7280;">Most Advanced</small>
                    </div>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="test_message"><i class="fas fa-comment"></i> Test Message</label>
            <textarea 
                id="test_message" 
                name="test_message" 
                placeholder="Enter a test message... (e.g., 'Explain what photosynthesis is in one sentence')"
                required
            >Say hello in one short sentence</textarea>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 16px;" <?php echo !$apiKeyConfigured ? 'disabled' : ''; ?>>
            <i class="fas fa-paper-plane"></i> Test OpenAI Connection
        </button>
    </form>

    <div class="status-box" style="margin-top: 30px; background: #fffbeb; border-color: #fde68a;">
        <h4 style="margin-top: 0; color: #92400e;">
            <i class="fas fa-info-circle"></i> Quick Tips
        </h4>
        <ul style="color: #78350f; font-size: 14px; margin: 10px 0 0 20px; line-height: 1.8;">
            <li>Monitor usage: <a href="https://platform.openai.com/usage" target="_blank">platform.openai.com/usage</a></li>
            <li>Set spending limits: <a href="https://platform.openai.com/account/billing/limits" target="_blank">Billing Limits</a></li>
            <li>Current model (gpt-4o-mini) costs ~$0.00015 per 1K tokens</li>
            <li>Average chat uses 100-200 tokens (~$0.00003 per message)</li>
        </ul>
    </div>

    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <a href="/ai-chat" style="color: #667eea; text-decoration: none; margin-right: 20px;">
            <i class="fas fa-comments"></i> Go to AI Chat
        </a>
        <a href="/test-openai" style="color: #667eea; text-decoration: none;">
            <i class="fas fa-redo"></i> Refresh Test
        </a>
    </div>
</div>

</body>
</html>
