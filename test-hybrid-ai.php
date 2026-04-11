<?php
/**
 * Hybrid AI Test Page
 * Tests the AI Router and shows which model is being used
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/AIRouter.php';

// Test the AI Router
$aiRouter = new AIRouter();
$routingInfo = $aiRouter->getRoutingInfo();

// Test basic request
$testMessage = $_POST['test_message'] ?? 'Hello, can you help me with math?';
$testResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startTime = microtime(true);
    $response = $aiRouter->chat($testMessage);
    $endTime = microtime(true);
    
    $testResult = [
        'message' => $testMessage,
        'response' => $response,
        'response_time' => round(($endTime - $startTime) * 1000, 2) . 'ms',
        'detected_complexity' => 'auto-detected'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hybrid AI System Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #1f2937;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .status-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #10a37f;
        }
        .status-card.openai { border-left-color: #10a37f; }
        .status-card.grok { border-left-color: #2563eb; }
        .status-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #1f2937;
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .status-item:last-child { border-bottom: none; }
        .status-label { color: #6b7280; font-size: 14px; }
        .status-value {
            font-weight: bold;
            font-size: 14px;
            padding: 4px 12px;
            border-radius: 12px;
        }
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        .routing-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .routing-table th, .routing-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .routing-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        .model-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .model-grok {
            background: #dbeafe;
            color: #1e40af;
        }
        .model-openai {
            background: #d1fae5;
            color: #065f46;
        }
        .test-form {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
        }
        .test-form textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
        }
        .test-form button {
            margin-top: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .test-form button:hover {
            transform: translateY(-2px);
        }
        .result-box {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        .result-box h3 {
            color: #1f2937;
            margin-bottom: 15px;
        }
        .result-item {
            margin-bottom: 15px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .result-item label {
            display: block;
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .result-item div {
            color: #6b7280;
            line-height: 1.6;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #2563eb;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10a37f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Hybrid AI System Test</h1>
        <p class="subtitle">Test and monitor your hybrid AI routing configuration</p>

        <?php if (defined('GROK_API_KEY') && GROK_API_KEY !== 'YOUR_GROK_API_KEY_HERE'): ?>
            <div class="alert alert-success">
                ✅ <strong>Grok/LLaMA is configured!</strong> The system will route basic and intermediate tasks to Grok/LLaMA.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                ⚠️ <strong>Grok/LLaMA is not configured.</strong> All requests will use OpenAI. 
                <a href="HYBRID_AI_SETUP.md">See setup guide</a> to configure Grok/LLaMA.
            </div>
        <?php endif; ?>

        <div class="status-grid">
            <div class="status-card openai">
                <h3>🟢 OpenAI (GPT-4o-mini)</h3>
                <div class="status-item">
                    <span class="status-label">Status</span>
                    <span class="status-value <?php echo (defined('OPENAI_API_KEY') && OPENAI_API_KEY !== 'YOUR_OPENAI_API_KEY_HERE') ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo (defined('OPENAI_API_KEY') && OPENAI_API_KEY !== 'YOUR_OPENAI_API_KEY_HERE') ? '✓ Active' : '✗ Not Configured'; ?>
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label">Used For</span>
                    <span class="status-value">Advanced Tasks</span>
                </div>
            </div>

            <div class="status-card grok">
                <h3>🔵 Grok/LLaMA</h3>
                <div class="status-item">
                    <span class="status-label">Status</span>
                    <span class="status-value <?php echo (defined('GROK_API_KEY') && GROK_API_KEY !== 'YOUR_GROK_API_KEY_HERE') ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo (defined('GROK_API_KEY') && GROK_API_KEY !== 'YOUR_GROK_API_KEY_HERE') ? '✓ Active' : '✗ Not Configured'; ?>
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label">Provider</span>
                    <span class="status-value"><?php echo defined('GROK_PROVIDER') ? ucfirst(GROK_PROVIDER) : 'N/A'; ?></span>
                </div>
            </div>
        </div>

        <h2>📊 AI Routing Configuration</h2>
        <table class="routing-table">
            <thead>
                <tr>
                    <th>Complexity Level</th>
                    <th>Assigned Model</th>
                    <th>Example Tasks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Basic</strong></td>
                    <td>
                        <span class="model-badge <?php echo defined('AI_BASIC_MODEL') && AI_BASIC_MODEL === 'grok' ? 'model-grok' : 'model-openai'; ?>">
                            <?php echo defined('AI_BASIC_MODEL') ? strtoupper(AI_BASIC_MODEL) : 'NOT SET'; ?>
                        </span>
                    </td>
                    <td>Simple Q&A, greetings, basic explanations</td>
                </tr>
                <tr>
                    <td><strong>Intermediate</strong></td>
                    <td>
                        <span class="model-badge <?php echo defined('AI_INTERMEDIATE_MODEL') && AI_INTERMEDIATE_MODEL === 'grok' ? 'model-grok' : 'model-openai'; ?>">
                            <?php echo defined('AI_INTERMEDIATE_MODEL') ? strtoupper(AI_INTERMEDIATE_MODEL) : 'NOT SET'; ?>
                        </span>
                    </td>
                    <td>Study plans, topic analysis, explanations</td>
                </tr>
                <tr>
                    <td><strong>Advanced</strong></td>
                    <td>
                        <span class="model-badge <?php echo defined('AI_ADVANCED_MODEL') && AI_ADVANCED_MODEL === 'openai' ? 'model-openai' : 'model-grok'; ?>">
                            <?php echo defined('AI_ADVANCED_MODEL') ? strtoupper(AI_ADVANCED_MODEL) : 'NOT SET'; ?>
                        </span>
                    </td>
                    <td>Memorandum generation, career recommendations, SEO content</td>
                </tr>
            </tbody>
        </table>

        <div class="test-form">
            <h2>🧪 Test AI Routing</h2>
            <form method="POST">
                <textarea name="test_message" placeholder="Enter a test message (e.g., 'What is 2+2?' or 'Explain photosynthesis')" required><?php echo htmlspecialchars($testMessage); ?></textarea>
                <button type="submit">🚀 Test AI Response</button>
            </form>

            <?php if ($testResult): ?>
                <div class="result-box">
                    <h3>Test Results</h3>
                    <div class="result-item">
                        <label>Your Message</label>
                        <div><?php echo htmlspecialchars($testResult['message']); ?></div>
                    </div>
                    <div class="result-item">
                        <label>AI Response</label>
                        <div><?php echo htmlspecialchars($testResult['response'] ?? 'No response'); ?></div>
                    </div>
                    <div class="result-item">
                        <label>Response Time</label>
                        <div><?php echo $testResult['response_time']; ?></div>
                    </div>
                    <div class="result-item">
                        <label>Detected Complexity</label>
                        <div><?php echo $testResult['detected_complexity']; ?> (auto-detected by AI Router)</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #f0f9ff; border-radius: 12px;">
            <h3>📚 Next Steps</h3>
            <ul style="margin-left: 20px; margin-top: 10px; line-height: 2; color: #374151;">
                <li>Run the database migration: <code style="background: white; padding: 4px 8px; border-radius: 4px;">php add_model_used_to_usage.php</code></li>
                <li>Configure Grok/LLaMA in your <code style="background: white; padding: 4px 8px; border-radius: 4px;">.env</code> file</li>
                <li>Monitor usage at <a href="/admin/openai-settings" style="color: #2563eb;">/admin/openai-settings</a></li>
                <li>Read the full <a href="HYBRID_AI_SETUP.md" style="color: #2563eb;">Hybrid AI Setup Guide</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
