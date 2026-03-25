<?php
/**
 * Complete BobPay API Test Dashboard
 * Access: http://localhost:8000/test-all-bobpay-endpoints.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/bobpay.php';

$bobPay = new BobPayHelper();
$results = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_payment_intents':
            $filters = [
                'include_retained_amount' => 'true',
                'limit' => 5
            ];
            $results['getPaymentIntents'] = $bobPay->getPaymentIntents($filters);
            break;
            
        case 'get_payment_methods':
            $results['getPublicPaymentMethods'] = $bobPay->getPublicPaymentMethods('SAN001');
            break;
            
        case 'get_credit_notes':
            $filters = [
                'account_id' => 399,
                'pdf' => false
            ];
            $results['getCreditNotes'] = $bobPay->getCreditNotes($filters);
            break;
            
        case 'get_invoices':
            $filters = [
                'account_id' => 242,
                'status' => 'paid',
                'pdf' => false
            ];
            $results['getInvoices'] = $bobPay->getInvoices($filters);
            break;
            
        case 'get_transactions':
            $filters = [
                'type' => ['payment-debit'],
                'start_date' => '2024-06-13 00:00:00',
                'end_date' => '2024-06-18 23:59:59',
                'limit' => 5
            ];
            $results['getBillingTransactions'] = $bobPay->getBillingTransactions($filters);
            break;
            
        case 'get_statement':
            $filters = [
                'account_id' => 399,
                'start_date' => '2023-10-01',
                'end_date' => '2023-10-31',
                'pdf' => false
            ];
            $results['getBillingStatement'] = $bobPay->getBillingStatement($filters);
            break;
            
        case 'get_payout_requests':
            $filters = [
                'account_id' => 4718,
                'limit' => 5,
                'status' => 'pending'
            ];
            $results['getPayoutRequests'] = $bobPay->getPayoutRequests($filters);
            break;
            
        case 'get_payout_schedule':
            $filters = [
                'account_id' => 665,
                'payout_frequency' => 'monthly'
            ];
            $results['getPayoutSchedule'] = $bobPay->getPayoutSchedule($filters);
            break;
            
        case 'create_payment':
            $paymentData = $bobPay->preparePaymentData([
                'amount' => 39.00,
                'customer_email' => 'test@example.com',
                'customer_name' => 'Test User',
                'success_url' => APP_URL . '/bobpay/success',
                'cancel_url' => APP_URL . '/bobpay/cancel',
                'callback_url' => APP_URL . '/bobpay/webhook',
                'metadata' => ['test' => true, 'plan' => 'basic']
            ]);
            $results['createPayment'] = $bobPay->createPayment($paymentData);
            break;
            
        case 'shorten_url':
            $longUrl = 'https://sandbox.bobpay.co.za/pay?amount=499.99&custom_payment_id=test_' . time();
            $results['shortenPaymentUrl'] = $bobPay->shortenPaymentUrl($longUrl);
            $results['shortenPaymentUrl_raw'] = $longUrl;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BobPay Complete Test Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #1e293b; margin-bottom: 10px; }
        h2 { color: #667eea; margin: 30px 0 15px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .subtitle { color: #64748b; margin-bottom: 30px; }
        
        .test-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .test-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .test-card h3 { color: #1e293b; margin-bottom: 15px; font-size: 16px; }
        .test-card p { color: #64748b; font-size: 14px; margin-bottom: 15px; }
        
        button { background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s; }
        button:hover { background: #5568d3; transform: translateY(-2px); }
        button:active { transform: translateY(0); }
        
        .result-card { background: white; padding: 20px; border-radius: 10px; margin-top: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .result-card h3 { color: #16a34a; margin-bottom: 15px; }
        .result-card.error h3 { color: #ef4444; }
        pre { background: #f8fafc; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 13px; border: 1px solid #e2e8f0; }
        
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-right: 8px; }
        .badge-success { background: #dcfce7; color: #16a34a; }
        .badge-info { background: #dbeafe; color: #2563eb; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-value { font-size: 32px; font-weight: 700; margin-bottom: 5px; }
        .stat-label { font-size: 14px; opacity: 0.9; }
        
        .endpoint-list { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .endpoint-list h3 { margin-bottom: 15px; }
        .endpoint-item { padding: 10px; border-left: 3px solid #667eea; background: #f8fafc; margin-bottom: 8px; font-family: monospace; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 BobPay Complete Test Dashboard</h1>
        <p class="subtitle">Test all 15 BobPay API endpoints</p>
        
        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value">15</div>
                <div class="stat-label">Total Endpoints</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #16a34a 0%, #059669 100%);">
                <div class="stat-value"><?php echo $bobPay->isConfigured() ? 'YES' : 'NO'; ?></div>
                <div class="stat-label">Configured</div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="stat-value"><?php echo defined('BOBPAY_SANDBOX') && BOBPAY_SANDBOX ? 'SANDBOX' : 'PRODUCTION'; ?></div>
                <div class="stat-label">Mode</div>
            </div>
        </div>
        
        <!-- All Endpoints List -->
        <div class="endpoint-list">
            <h3>📋 All Available Endpoints</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 10px;">
                <div class="endpoint-item">✅ createPayment() - Create payment link</div>
                <div class="endpoint-item">✅ getPaymentIntents() - List payments</div>
                <div class="endpoint-item">✅ getPaymentIntent() - Single payment</div>
                <div class="endpoint-item">✅ createPaymentIntentWithSignature() - Create with signature</div>
                <div class="endpoint-item">✅ shortenPaymentUrl() - Shorten URL</div>
                <div class="endpoint-item">✅ refundPayment() - Process refund</div>
                <div class="endpoint-item">✅ getPublicPaymentMethods() - Payment methods</div>
                <div class="endpoint-item">✅ getPayoutRequests() - Payout requests</div>
                <div class="endpoint-item">✅ getPayoutSchedule() - Payout schedules</div>
                <div class="endpoint-item">✅ getCreditNotes() - Credit notes</div>
                <div class="endpoint-item">✅ getInvoices() - Invoices</div>
                <div class="endpoint-item">✅ getBillingTransactions() - Transactions</div>
                <div class="endpoint-item">✅ getBillingStatement() - Statements</div>
                <div class="endpoint-item">✅ validatePaymentIntent() - Validate webhook</div>
            </div>
        </div>
        
        <form method="POST">
            <h2>🧪 Test Payment Endpoints</h2>
            <div class="test-grid">
                <div class="test-card">
                    <h3>1. Create Payment Link</h3>
                    <p>Create a payment link for subscription</p>
                    <button type="submit" name="action" value="create_payment">Test Create Payment</button>
                </div>
                
                <div class="test-card">
                    <h3>2. Get Payment Intents</h3>
                    <p>List all payments with filters</p>
                    <button type="submit" name="action" value="get_payment_intents">Test Get Payments</button>
                </div>
                
                <div class="test-card">
                    <h3>3. Get Payment Methods</h3>
                    <p>Get available payment methods</p>
                    <button type="submit" name="action" value="get_payment_methods">Test Get Methods</button>
                </div>
                
                <div class="test-card">
                    <h3>4. Shorten Payment URL</h3>
                    <p>Generate short payment URL</p>
                    <button type="submit" name="action" value="shorten_url">Test URL Shortener</button>
                </div>
            </div>
            
            <h2>🧪 Test Billing Endpoints</h2>
            <div class="test-grid">
                <div class="test-card">
                    <h3>5. Get Credit Notes</h3>
                    <p>Retrieve credit notes (PDF/CSV)</p>
                    <button type="submit" name="action" value="get_credit_notes">Test Credit Notes</button>
                </div>
                
                <div class="test-card">
                    <h3>6. Get Invoices</h3>
                    <p>Retrieve invoices with status filter</p>
                    <button type="submit" name="action" value="get_invoices">Test Invoices</button>
                </div>
                
                <div class="test-card">
                    <h3>7. Get Transactions</h3>
                    <p>Billing transaction history</p>
                    <button type="submit" name="action" value="get_transactions">Test Transactions</button>
                </div>
                
                <div class="test-card">
                    <h3>8. Get Statement</h3>
                    <p>Account statement (PDF)</p>
                    <button type="submit" name="action" value="get_statement">Test Statement</button>
                </div>
            </div>
            
            <h2>🧪 Test Payout Endpoints</h2>
            <div class="test-grid">
                <div class="test-card">
                    <h3>9. Get Payout Requests</h3>
                    <p>View payout requests</p>
                    <button type="submit" name="action" value="get_payout_requests">Test Payout Requests</button>
                </div>
                
                <div class="test-card">
                    <h3>10. Get Payout Schedule</h3>
                    <p>Automated payout schedules</p>
                    <button type="submit" name="action" value="get_payout_schedule">Test Schedules</button>
                </div>
            </div>
        </form>
        
        <!-- Results -->
        <?php if (!empty($results)): ?>
            <h2>📊 Test Results</h2>
            <?php foreach ($results as $testName => $result): ?>
                <div class="result-card <?php echo $result ? '' : 'error'; ?>">
                    <h3>
                        <?php if ($result): ?>
                            <span class="badge badge-success">✅ Success</span>
                        <?php else: ?>
                            <span class="badge badge-warning">❌ Failed</span>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($testName); ?>
                    </h3>
                    <pre><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="margin-top: 40px; padding: 20px; background: #fef3c7; border-radius: 10px; border-left: 4px solid #f59e0b;">
            <h3 style="color: #92400e; margin-bottom: 10px;">💡 Testing Tips</h3>
            <ul style="color: #78350f; padding-left: 20px; line-height: 1.8;">
                <li>Some endpoints may return empty arrays if no data exists in sandbox</li>
                <li>PDF endpoints return download URLs instead of raw data</li>
                <li>Use sandbox credentials: <code>johan+sandbox@bob.co.za</code> / <code>sandboxtest</code></li>
                <li>Check <code>config/bobpay.php</code> for detailed endpoint documentation</li>
                <li>For refund testing, you need a valid payment ID from BobPay</li>
            </ul>
        </div>
    </div>
</body>
</html>
