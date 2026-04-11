<?php
/**
 * Comprehensive AI Model Testing Page
 * Tests OpenAI, Grok/LLaMA, and AI Router independently
 * Shows detailed results with response times and status
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/AIHelper.php';
require_once __DIR__ . '/helpers/GrokAI.php';
require_once __DIR__ . '/helpers/AIRouter.php';

// Initialize helpers
$openAI = new AIHelper();
$grokAI = new GrokAI();
$aiRouter = new AIRouter();

// Test results storage
$testResults = [];

// Run tests if requested
if (isset($_GET['run_tests']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $testType = $_POST['test_type'] ?? 'all';
    $testMessage = $_POST['test_message'] ?? 'Explain what photosynthesis is in simple terms.';
    
    // Test 1: OpenAI
    if ($testType === 'all' || $testType === 'openai') {
        $startTime = microtime(true);
        try {
            $response = $openAI->chat($testMessage, 'You are a helpful AI assistant. Provide clear, concise answers.');
            $endTime = microtime(true);
            $testResults['openai'] = [
                'status' => $response ? 'success' : 'failed',
                'response' => $response,
                'time' => round(($endTime - $startTime) * 1000, 2),
                'message' => $response ? 'OpenAI responded successfully!' : 'OpenAI returned no response'
            ];
        } catch (Exception $e) {
            $endTime = microtime(true);
            $testResults['openai'] = [
                'status' => 'error',
                'response' => null,
                'time' => round(($endTime - $startTime) * 1000, 2),
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    // Test 2: Grok/LLaMA
    if ($testType === 'all' || $testType === 'grok') {
        $startTime = microtime(true);
        try {
            $response = $grokAI->chat($testMessage, 'You are a helpful AI assistant. Provide clear, concise answers.');
            $endTime = microtime(true);
            $testResults['grok'] = [
                'status' => $response ? 'success' : 'failed',
                'response' => $response,
                'time' => round(($endTime - $startTime) * 1000, 2),
                'message' => $response ? 'Grok/LLaMA responded successfully!' : 'Grok/LLaMA returned no response'
            ];
        } catch (Exception $e) {
            $endTime = microtime(true);
            $testResults['grok'] = [
                'status' => 'error',
                'response' => null,
                'time' => round(($endTime - $startTime) * 1000, 2),
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    // Test 3: AI Router
    if ($testType === 'all' || $testType === 'router') {
        $startTime = microtime(true);
        try {
            $response = $aiRouter->chat($testMessage);
            $endTime = microtime(true);
            $testResults['router'] = [
                'status' => $response ? 'success' : 'failed',
                'response' => $response,
                'time' => round(($endTime - $startTime) * 1000, 2),
                'message' => $response ? 'AI Router responded successfully!' : 'AI Router returned no response'
            ];
        } catch (Exception $e) {
            $endTime = microtime(true);
            $testResults['router'] = [
                'status' => 'error',
                'response' => null,
                'time' => round(($endTime - $startTime) * 1000, 2),
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    // Test 4: Career Search (AI)
    if ($testType === 'all' || $testType === 'career') {
        require_once __DIR__ . '/controllers/CareerController.php';
        $careerController = new CareerController();
        
        $startTime = microtime(true);
        try {
            // Simulate a career search
            ob_start();
            $_GET['q'] = $testMessage;
            $careerController->search();
            $output = ob_get_clean();
            $endTime = microtime(true);
            
            $responseData = json_decode($output, true);
            $testResults['career'] = [
                'status' => ($responseData && $responseData['success']) ? 'success' : 'failed',
                'response' => $responseData['careers'] ?? null,
                'time' => round(($endTime - $startTime) * 1000, 2),
                'message' => $responseData['success'] 
                    ? "Career search returned " . ($responseData['count'] ?? 0) . " results (from_ai: " . ($responseData['from_ai'] ? 'true' : 'false') . ")"
                    : ($responseData['error'] ?? 'Career search failed')
            ];
        } catch (Exception $e) {
            ob_end_clean();
            $endTime = microtime(true);
            $testResults['career'] = [
                'status' => 'error',
                'response' => null,
                'time' => round(($endTime - $startTime) * 1000, 2),
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}

// Get system status
$openAIStatus = defined('OPENAI_API_KEY') && OPENAI_API_KEY !== 'YOUR_OPENAI_API_KEY_HERE';
$grokStatus = defined('GROK_API_KEY') && GROK_API_KEY !== 'YOUR_GROK_API_KEY_HERE';
$routerInfo = $aiRouter->getRoutingInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Model Testing Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
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
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .status-banner {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .status-card {
            padding: 20px;
            border-radius: 12px;
            border-left: 5px solid;
            background: #f9fafb;
        }
        .status-card.active { border-left-color: #10a37f; background: #d1fae5; }
        .status-card.inactive { border-left-color: #ef4444; background: #fee2e2; }
        .status-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-badge.active { background: #10a37f; color: white; }
        .status-badge.inactive { background: #ef4444; color: white; }
        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }
        .status-item:last-child { border-bottom: none; }
        .status-label { color: #6b7280; }
        .status-value { font-weight: bold; color: #374151; }
        .test-form {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 2px solid #e5e7eb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .radio-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .btn-test {
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .results {
            margin-top: 30px;
        }
        .result-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .result-card.success { border-left: 5px solid #10a37f; }
        .result-card.error { border-left: 5px solid #ef4444; }
        .result-card.failed { border-left: 5px solid #f59e0b; }
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
        }
        .result-title {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
        }
        .result-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .result-status-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        .result-status-icon.success { background: #10a37f; }
        .result-status-icon.error { background: #ef4444; }
        .result-status-icon.failed { background: #f59e0b; }
        .result-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .result-meta span {
            padding: 6px 12px;
            background: #f3f4f6;
            border-radius: 6px;
        }
        .result-response {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.6;
            color: #374151;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
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
        .model-grok { background: #dbeafe; color: #1e40af; }
        .model-openai { background: #d1fae5; color: #065f46; }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-info { background: #dbeafe; color: #1e40af; border-left: 4px solid #2563eb; }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10a37f; }
        .alert-warning { background: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b; }
        .quick-tests {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        .quick-test-btn {
            padding: 10px;
            background: white;
            border: 2px solid #667eea;
            border-radius: 8px;
            color: #667eea;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }
        .quick-test-btn:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            🤖 AI Model Testing Dashboard
        </h1>
        <p class="subtitle">Test and monitor all AI models - OpenAI, Grok/LLaMA, and AI Router</p>

        <?php if ($openAIStatus): ?>
            <div class="alert alert-success">
                ✅ <strong>OpenAI is configured!</strong> API key is set and ready for testing.
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                ⚠️ <strong>OpenAI is not configured.</strong> Add your API key to the <code>.env</code> file.
            </div>
        <?php endif; ?>

        <?php if ($grokStatus): ?>
            <div class="alert alert-success">
                ✅ <strong>Grok/LLaMA is configured!</strong> Provider: <strong><?php echo ucfirst(GROK_PROVIDER); ?></strong>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                ⚠️ <strong>Grok/LLaMA is not configured.</strong> Tests will fall back to OpenAI only.
            </div>
        <?php endif; ?>

        <!-- Status Cards -->
        <div class="status-banner">
            <div class="status-card <?php echo $openAIStatus ? 'active' : 'inactive'; ?>">
                <h3>
                    🟢 OpenAI (GPT-4o-mini)
                    <span class="status-badge <?php echo $openAIStatus ? 'active' : 'inactive'; ?>">
                        <?php echo $openAIStatus ? '✓ Active' : '✗ Inactive'; ?>
                    </span>
                </h3>
                <div class="status-item">
                    <span class="status-label">API Key</span>
                    <span class="status-value"><?php echo $openAIStatus ? 'Configured' : 'Not Set'; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Used For</span>
                    <span class="status-value">Advanced Tasks</span>
                </div>
            </div>

            <div class="status-card <?php echo $grokStatus ? 'active' : 'inactive'; ?>">
                <h3>
                    🔵 Grok/LLaMA
                    <span class="status-badge <?php echo $grokStatus ? 'active' : 'inactive'; ?>">
                        <?php echo $grokStatus ? '✓ Active' : '✗ Inactive'; ?>
                    </span>
                </h3>
                <div class="status-item">
                    <span class="status-label">Provider</span>
                    <span class="status-value"><?php echo $grokStatus ? ucfirst(GROK_PROVIDER) : 'N/A'; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Model</span>
                    <span class="status-value"><?php echo $grokStatus ? (GROK_MODEL ?: 'Default') : 'N/A'; ?></span>
                </div>
            </div>

            <div class="status-card active">
                <h3>
                    🟣 AI Router
                    <span class="status-badge active">✓ Active</span>
                </h3>
                <div class="status-item">
                    <span class="status-label">Basic Tasks</span>
                    <span class="status-value">
                        <span class="model-badge <?php echo defined('AI_BASIC_MODEL') && AI_BASIC_MODEL === 'grok' ? 'model-grok' : 'model-openai'; ?>">
                            <?php echo defined('AI_BASIC_MODEL') ? strtoupper(AI_BASIC_MODEL) : 'NOT SET'; ?>
                        </span>
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label">Intermediate Tasks</span>
                    <span class="status-value">
                        <span class="model-badge <?php echo defined('AI_INTERMEDIATE_MODEL') && AI_INTERMEDIATE_MODEL === 'grok' ? 'model-grok' : 'model-openai'; ?>">
                            <?php echo defined('AI_INTERMEDIATE_MODEL') ? strtoupper(AI_INTERMEDIATE_MODEL) : 'NOT SET'; ?>
                        </span>
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label">Advanced Tasks</span>
                    <span class="status-value">
                        <span class="model-badge <?php echo defined('AI_ADVANCED_MODEL') && AI_ADVANCED_MODEL === 'openai' ? 'model-openai' : 'model-grok'; ?>">
                            <?php echo defined('AI_ADVANCED_MODEL') ? strtoupper(AI_ADVANCED_MODEL) : 'NOT SET'; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Test Form -->
        <div class="test-form">
            <h2 style="margin-bottom: 20px; color: #1f2937;">🧪 Run AI Tests</h2>
            <form method="POST" id="testForm">
                <div class="form-group">
                    <label>Test Type</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="test_type" value="all" checked>
                            Test All Models
                        </label>
                        <label>
                            <input type="radio" name="test_type" value="openai">
                            Test OpenAI Only
                        </label>
                        <label>
                            <input type="radio" name="test_type" value="grok">
                            Test Grok/LLaMA Only
                        </label>
                        <label>
                            <input type="radio" name="test_type" value="router">
                            Test AI Router
                        </label>
                        <label>
                            <input type="radio" name="test_type" value="career">
                            Test Career Search
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="test_message">Test Message</label>
                    <textarea id="test_message" name="test_message" placeholder="Enter your test message..."><?php echo htmlspecialchars($_POST['test_message'] ?? 'Explain what photosynthesis is in simple terms.'); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Quick Test Messages</label>
                    <div class="quick-tests">
                        <button type="button" class="quick-test-btn" onclick="setMessage('Hello!')">👋 Basic Greeting</button>
                        <button type="button" class="quick-test-btn" onclick="setMessage('What is 2+2?')">🔢 Simple Math</button>
                        <button type="button" class="quick-test-btn" onclick="setMessage('Explain photosynthesis')">🌱 Science Question</button>
                        <button type="button" class="quick-test-btn" onclick="setMessage('Create a study plan for algebra')">📚 Study Plan</button>
                        <button type="button" class="quick-test-btn" onclick="setMessage('Database Administrator')">💼 Career Search</button>
                        <button type="button" class="quick-test-btn" onclick="setMessage('Compare renewable energy sources')">⚡ Complex Analysis</button>
                    </div>
                </div>

                <button type="submit" class="btn-test">🚀 Run Tests</button>
            </form>
        </div>

        <!-- Test Results -->
        <?php if (!empty($testResults)): ?>
            <div class="results">
                <h2 style="margin-bottom: 20px; color: #1f2937;">📊 Test Results</h2>
                
                <?php foreach ($testResults as $model => $result): ?>
                    <div class="result-card <?php echo $result['status']; ?>">
                        <div class="result-header">
                            <div class="result-title">
                                <?php 
                                $title = strtoupper($model);
                                $icon = $model === 'openai' ? '🟢' : ($model === 'grok' ? '🔵' : ($model === 'router' ? '🟣' : '🎯'));
                                echo $icon . ' ' . $title . ' Test';
                                ?>
                            </div>
                            <div class="result-status">
                                <div class="result-status-icon <?php echo $result['status']; ?>">
                                    <?php echo $result['status'] === 'success' ? '✓' : ($result['status'] === 'error' ? '✗' : '!'); ?>
                                </div>
                                <span style="font-weight: bold; color: <?php echo $result['status'] === 'success' ? '#10a37f' : '#ef4444'; ?>">
                                    <?php echo strtoupper($result['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="result-meta">
                            <span>⏱️ Response Time: <strong><?php echo $result['time']; ?>ms</strong></span>
                            <span>📝 Status: <strong><?php echo $result['message']; ?></strong></span>
                        </div>

                        <?php if ($result['response']): ?>
                            <div class="result-response"><?php 
                                if (is_array($result['response'])) {
                                    echo htmlspecialchars(json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                } else {
                                    echo htmlspecialchars($result['response']);
                                }
                            ?></div>
                        <?php else: ?>
                            <div class="result-response" style="color: #ef4444;">
                                No response received. Check error logs for details.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Routing Configuration -->
        <div style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 12px;">
            <h3 style="margin-bottom: 15px; color: #1f2937;">📋 Current AI Routing Configuration</h3>
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
        </div>

        <!-- Next Steps -->
        <div style="margin-top: 30px; padding: 20px; background: #dbeafe; border-radius: 12px;">
            <h3 style="margin-bottom: 15px; color: #1e40af;">📚 Setup & Configuration</h3>
            <ul style="margin-left: 20px; line-height: 2; color: #1e40af;">
                <li>Run migration: <code style="background: white; padding: 4px 8px; border-radius: 4px;">php add_model_used_to_usage.php</code></li>
                <li>Configure Grok/LLaMA in <code style="background: white; padding: 4px 8px; border-radius: 4px;">.env</code> file</li>
                <li>Read <a href="HYBRID_AI_SETUP.md" style="color: #2563eb;">Hybrid AI Setup Guide</a></li>
                <li>Monitor usage at <a href="/admin/openai-settings" style="color: #2563eb;">/admin/openai-settings</a></li>
            </ul>
        </div>
    </div>

    <script>
        function setMessage(message) {
            document.getElementById('test_message').value = message;
        }
        
        // Auto-run tests if requested
        <?php if (isset($_GET['auto_test'])): ?>
            document.getElementById('testForm').submit();
        <?php endif; ?>
    </script>
</body>
</html>
