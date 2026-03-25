<?php
/**
 * BobPay Payment Gateway Helper
 * 
 * Integration guide for BobPay payment gateway
 * 
 * Configuration:
 * - BOBPAY_API_KEY: Your API key from BobPay dashboard
 * - BOBPAY_PASSPHRASE: Your passphrase from BobPay dashboard (for signature verification)
 * - BOBPAY_SANDBOX: true for testing, false for production
 * 
 * API Flow:
 * 1. Login to get bearer token
 * 2. Create payment link
 * 3. Redirect customer to payment URL
 * 4. Handle webhook notifications
 */

class BobPayHelper {
    
    private $apiKey;
    private $passphrase;
    private $baseUrl;
    private $isConfigured;
    private $bearerToken;
    
    public function __construct() {
        // Load API key from config or environment
        $this->apiKey = defined('BOBPAY_API_KEY') ? BOBPAY_API_KEY : null;
        $this->passphrase = defined('BOBPAY_PASSPHRASE') ? BOBPAY_PASSPHRASE : null;
        
        // Determine API base URL based on sandbox mode (from BobPay API docs)
        $useSandbox = defined('BOBPAY_SANDBOX') && BOBPAY_SANDBOX;
        
        if ($useSandbox) {
            // Sandbox/Test environment - BobPay API
            $this->baseUrl = 'https://api.sandbox.bobpay.co.za';
        } else {
            // Production environment - BobPay API
            $this->baseUrl = 'https://api.bobpay.co.za';
        }
        
        // Check if configured
        $this->isConfigured = !empty($this->apiKey) && $this->apiKey !== 'YOUR_BOBPAY_API_KEY';
        
        error_log('BobPay: Initialized with base URL: ' . $this->baseUrl . ' (sandbox: ' . ($useSandbox ? 'true' : 'false') . ')');
    }
    
    /**
     * Check if BobPay is configured
     */
    public function isConfigured() {
        return $this->isConfigured;
    }
    
    /**
     * Login to BobPay API to get bearer token
     * Note: For sandbox, use: johan+sandbox@bob.co.za / sandboxtest
     * 
     * @param string|null $email Optional email (uses sandbox credentials if null)
     * @param string|null $password Optional password (uses sandbox credentials if null)
     * @return string|false Bearer token or false on failure
     */
    public function login($email = null, $password = null) {
        if (!$this->isConfigured) {
            error_log('BobPay: Not configured for login');
            return false;
        }
        
        // Use sandbox credentials if not provided
        if ($email === null || $password === null) {
            $email = 'johan+sandbox@bob.co.za';
            $password = 'sandboxtest';
        }
        
        $loginUrl = $this->baseUrl . '/login';
        
        error_log('BobPay: Logging in to: ' . $loginUrl);
        error_log('BobPay: Login credentials - Email: ' . $email . ', Password: ' . substr($password, 0, 3) . '***');
        
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $email,
            'password' => $password
        ]));
        
        // Add verbose output
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Log verbose output
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        error_log('BobPay Login Verbose: ' . $verboseLog);
        
        if ($error) {
            error_log('BobPay Login cURL Error: ' . $error);
            return false;
        }
        
        error_log('BobPay Login HTTP Code: ' . $httpCode);
        error_log('BobPay Login Response: ' . $response);
        
        if ($httpCode !== 200) {
            error_log('BobPay Login HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('BobPay Login JSON Error: ' . json_last_error_msg());
            return false;
        }
        
        if (isset($result['access_token'])) {
            $this->bearerToken = $result['access_token'];
            error_log('BobPay Login Success - Token: ' . substr($result['access_token'], 0, 10) . '...');
            return $result['access_token'];
        }
        
        error_log('BobPay Login Failed - No access_token in response. Response keys: ' . implode(', ', array_keys($result)));
        return false;
    }
    
    /**
     * Get the current bearer token (login if needed)
     */
    private function getBearerToken() {
        if (empty($this->bearerToken)) {
            $this->login();
        }
        return $this->bearerToken;
    }
    
    /**
     * Create a payment request (payment link)
     * 
     * @param array $data Payment data
     * @return array|false Payment response or false on failure
     */
    public function createPayment($data) {
        if (!$this->isConfigured) {
            error_log('BobPay: Not configured - missing API key');
            return false;
        }
        
        // Get bearer token (login first)
        $token = $this->getBearerToken();
        if (empty($token)) {
            error_log('BobPay: Failed to get bearer token');
            return false;
        }
        
        // Full endpoint URL for creating payment links
        $endpointUrl = $this->baseUrl . '/payments/intents/link';
        
        // Generate signature for this payment
        $signature = $this->generateSignature($data);
        
        // Determine if sandbox (for recipient account code)
        $useSandbox = defined('BOBPAY_SANDBOX') && BOBPAY_SANDBOX;
        
        // Prepare payment link request (BobPay API v2 format)
        $paymentData = [
            'custom_payment_id' => (string)($data['metadata']['payment_id'] ?? 'payment_' . time()),
            'email' => $data['customer_email'] ?? '',
            'phone_number' => $data['customer_phone'] ?? '',
            'amount' => (float)$data['amount'],
            'item_name' => $data['item_name'] ?? 'Subscription Payment',
            'item_description' => $data['item_description'] ?? '',
            'notify_url' => $data['callback_url'] ?? '',
            'success_url' => $data['success_url'] ?? '',
            'pending_url' => $data['success_url'] ?? '',
            'cancel_url' => $data['cancel_url'] ?? '',
            'signature' => $signature,
            'transacting_as_email' => $data['customer_email'] ?? '',
            'short_url' => true
        ];
        
        // Only add recipient_account_code if it's configured (optional for some accounts)
        if (defined('BOBPAY_ACCOUNT_CODE') && BOBPAY_ACCOUNT_CODE) {
            $paymentData['recipient_account_code'] = BOBPAY_ACCOUNT_CODE;
        } else {
            // Use sandbox default only if in sandbox mode
            if ($useSandbox) {
                $paymentData['recipient_account_code'] = 'SAN001';
            }
        }
        
        // Log the request for debugging
        error_log('BobPay: Creating payment link at: ' . $endpointUrl);
        error_log('BobPay: Payment data: ' . json_encode($paymentData));
        
        $ch = curl_init($endpointUrl);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
        
        // Add verbose output for debugging
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        
        // Log verbose output
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        if (!empty($verboseLog)) {
            error_log('BobPay cURL Verbose: ' . $verboseLog);
        }
        
        if ($error) {
            error_log('BobPay cURL Error (' . $errno . '): ' . $error);
            return false;
        }
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            error_log('BobPay HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('BobPay JSON Decode Error: ' . json_last_error_msg() . ' - Raw: ' . $response);
            return false;
        }
        
        error_log('BobPay Success: ' . json_encode($result));
        return $result;
    }
    
    /**
     * Generate signature for payment
     * Uses passphrase to create HMAC signature
     * 
     * @param array $data Payment data
     * @return string Signature
     */
    private function generateSignature($data) {
        if (empty($this->passphrase)) {
            // Generate a simple signature if no passphrase
            return bin2hex(random_bytes(16));
        }
        
        // Create signature from payment data
        // Format: amount|email|custom_payment_id|item_name
        $signatureData = $data['amount'] . '|' . 
                        $data['customer_email'] . '|' . 
                        ($data['metadata']['payment_id'] ?? 'payment_' . time()) . '|' .
                        ($data['item_name'] ?? 'Subscription');
        
        error_log('BobPay Signature Data: ' . $signatureData);
        $signature = hash_hmac('sha256', $signatureData, $this->passphrase);
        error_log('BobPay Signature: ' . $signature);
        
        return $signature;
    }
    
    /**
     * Verify a payment transaction
     * 
     * @param string $transactionId Transaction ID to verify
     * @return array|false Payment details or false on failure
     */
    public function verifyPayment($transactionId) {
        if (!$this->isConfigured) {
            return false;
        }
        
        $url = $this->apiUrl . '/' . urlencode($transactionId);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json"
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Verify webhook signature using BobPay passphrase
     * 
     * @param string $payload Raw POST data
     * @param string $signature Signature from header
     * @return bool True if valid
     */
    public function verifyWebhookSignature($payload, $signature) {
        // Skip verification if no passphrase or signature provided
        if (empty($this->passphrase) || empty($signature)) {
            return true;
        }
        
        // BobPay likely uses HMAC SHA256 signature
        // The signature is typically: hash_hmac('sha256', $payload, $passphrase)
        $expectedSignature = hash_hmac('sha256', $payload, $this->passphrase);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Get Payment Intents
     * Retrieve comprehensive information about payments with filters
     * 
     * @param array $filters Query parameters
     * @return array|false Payment intents or false on failure
     */
    public function getPaymentIntents($filters = []) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/payments/intents';
        
        // Build query string
        $queryParams = http_build_query(array_filter($filters));
        if (!empty($queryParams)) {
            $endpointUrl .= '?' . $queryParams;
        }
        
        error_log('BobPay: Getting payment intents from: ' . $endpointUrl);
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Get Payment Intents cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Get Payment Intents HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get Public Payment Methods
     * Retrieve available payment methods for an account
     * 
     * @param string $accountCode Account code
     * @return array|false Payment methods or false on failure
     */
    public function getPublicPaymentMethods($accountCode) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/payments/payment-methods/public?account_code=' . urlencode($accountCode);
        
        error_log('BobPay: Getting payment methods for account: ' . $accountCode);
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Get Payment Methods cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Get Payment Methods HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Refund Payment (Payment Reversal)
     * Initiate a refund for a specific payment
     * 
     * @param int $paymentId ID of the payment to refund
     * @return array|false Refund response or false on failure
     */
    public function refundPayment($paymentId) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/payments/reversal';
        
        error_log('BobPay: Initiating refund for payment ID: ' . $paymentId);
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id' => $paymentId]));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Refund cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Refund HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get Single Payment Intent
     * Retrieve details of a specific payment by ID
     * 
     * @param int $paymentId Payment ID
     * @return array|false Payment details or false on failure
     */
    public function getPaymentIntent($paymentId) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/payments/intents/' . (int)$paymentId;
        
        error_log('BobPay: Getting payment intent: ' . $paymentId);
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Get Payment Intent cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Get Payment Intent HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Create Payment Intent with Signature
     * Validate signature and create payment intent
     * 
     * @param array $data Payment intent data
     * @return array|false Payment intent or false on failure
     */
    public function createPaymentIntentWithSignature($data) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/payments/intents/signature';
        
        error_log('BobPay: Creating payment intent with signature');
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Create Payment Intent cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            error_log('BobPay Create Payment Intent HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Shorten Payment URL
     * Generate a shortened URL for payment intent
     * 
     * @param string $url Original payment URL
     * @return string|false Shortened URL or false on failure
     */
    public function shortenPaymentUrl($url) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/payments/intents/shorten';
        
        error_log('BobPay: Shortening URL');
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $url]));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Shorten URL cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Shorten URL HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        $result = json_decode($response, true);
        return $result['short_url'] ?? false;
    }
    
    /**
     * Get Payout Requests
     * Retrieve payout requests with filters
     * 
     * @param array $filters Query parameters
     * @return array|false Payout requests or false on failure
     */
    public function getPayoutRequests($filters = []) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/payout-requests';
        
        // Build query string
        $queryParams = http_build_query(array_filter($filters));
        if (!empty($queryParams)) {
            $endpointUrl .= '?' . $queryParams;
        }
        
        error_log('BobPay: Getting payout requests from: ' . $endpointUrl);
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Get Payout Requests cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Get Payout Requests HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get Payout Schedule
     * Retrieve automated payout schedules
     * 
     * @param array $filters Query parameters
     * @return array|false Payout schedules or false on failure
     */
    public function getPayoutSchedule($filters = []) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/payout-requests/schedule';
        
        // Build query string
        $queryParams = http_build_query(array_filter($filters));
        if (!empty($queryParams)) {
            $endpointUrl .= '?' . $queryParams;
        }
        
        error_log('BobPay: Getting payout schedule from: ' . $endpointUrl);
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Get Payout Schedule cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Get Payout Schedule HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get Credit Notes
     * Retrieve credit note data with PDF/CSV export options
     * 
     * @param array $filters Query parameters
     * @return array|false Credit notes or false on failure
     */
    public function getCreditNotes($filters = []) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/billing/credit-notes';
        
        // Build query string
        $queryParams = http_build_query(array_filter($filters));
        if (!empty($queryParams)) {
            $endpointUrl .= '?' . $queryParams;
        }
        
        error_log('BobPay: Getting credit notes from: ' . $endpointUrl);
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Get Credit Notes cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Get Credit Notes HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get Invoices
     * Retrieve invoice data with PDF/CSV export and status filtering
     * 
     * @param array $filters Query parameters
     * @return array|false Invoices or false on failure
     */
    public function getInvoices($filters = []) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/billing/invoices';
        
        // Build query string
        $queryParams = http_build_query(array_filter($filters));
        if (!empty($queryParams)) {
            $endpointUrl .= '?' . $queryParams;
        }
        
        error_log('BobPay: Getting invoices from: ' . $endpointUrl);
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Get Invoices cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Get Invoices HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get Billing Transactions
     * Retrieve detailed transaction information with filters
     * 
     * @param array $filters Query parameters
     * @return array|false Transactions or false on failure
     */
    public function getBillingTransactions($filters = []) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/billing/transactions';
        
        // Build query string
        $queryParams = http_build_query(array_filter($filters));
        if (!empty($queryParams)) {
            $endpointUrl .= '?' . $queryParams;
        }
        
        error_log('BobPay: Getting billing transactions from: ' . $endpointUrl);
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Get Billing Transactions cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Get Billing Transactions HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get Billing Statement
     * Retrieve account statement for a specified period with PDF download option
     * 
     * @param array $filters Query parameters (account_id required)
     * @return array|false Statement or false on failure
     */
    public function getBillingStatement($filters = []) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        // Account ID is required
        if (empty($filters['account_id'])) {
            error_log('BobPay: account_id is required for getBillingStatement');
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/v2/billing/statements';
        
        // Build query string
        $queryParams = http_build_query(array_filter($filters));
        if (!empty($queryParams)) {
            $endpointUrl .= '?' . $queryParams;
        }
        
        error_log('BobPay: Getting billing statement from: ' . $endpointUrl);
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Get Billing Statement cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('BobPay Get Billing Statement HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Validate Payment Intent (Webhook Handler)
     * Validate payment intent data received from webhook
     * 
     * @param array $paymentData Payment intent data from webhook
     * @return array|false Validation response or false on failure
     */
    public function validatePaymentIntent($paymentData) {
        $token = $this->getBearerToken();
        if (empty($token)) {
            return false;
        }
        
        $endpointUrl = $this->baseUrl . '/payments/intents/validate';
        
        error_log('BobPay: Validating payment intent');
        
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('BobPay Validate Payment Intent cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            error_log('BobPay Validate Payment Intent HTTP Error: ' . $httpCode . ' - Response: ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Prepare payment data for BobPay
     * 
     * @param array $paymentData Payment information
     * @return array Formatted payment data
     */
    public function preparePaymentData($paymentData) {
        return [
            'amount' => (float)$paymentData['amount'],
            'currency' => 'ZAR',
            'customer_email' => $paymentData['customer_email'],
            'customer_name' => $paymentData['customer_name'] ?? '',
            'customer_phone' => $paymentData['customer_phone'] ?? '',
            'callback_url' => $paymentData['callback_url'] ?? '',
            'success_url' => $paymentData['success_url'],
            'cancel_url' => $paymentData['cancel_url'],
            'item_name' => $paymentData['item_name'] ?? 'Subscription Payment',
            'item_description' => $paymentData['item_description'] ?? '',
            'metadata' => $paymentData['metadata'] ?? [],
        ];
    }
}
