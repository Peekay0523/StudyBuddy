<?php
/**
 * PayFast Payment Gateway Integration
 * 
 * PayFast Documentation: https://developers.payfast.co.za/
 */

class PayFastHelper {
    
    private $merchantId;
    private $merchantKey;
    private $passPhrase;
    private $testMode;
    private $baseUrl;
    
    public function __construct() {
        $this->merchantId = defined('PAYFAST_MERCHANT_ID') ? PAYFAST_MERCHANT_ID : '';
        $this->merchantKey = defined('PAYFAST_MERCHANT_KEY') ? PAYFAST_MERCHANT_KEY : '';
        $this->passPhrase = defined('PAYFAST_PASSPHRASE') ? PAYFAST_PASSPHRASE : '';
        $this->testMode = defined('PAYFAST_SANDBOX') ? PAYFAST_SANDBOX : true;
        
        // PayFast URLs
        $this->baseUrl = $this->testMode 
            ? 'https://sandbox.payfast.co.za/eng/process' 
            : 'https://www.payfast.co.za/eng/process';
    }
    
    /**
     * Generate PayFast payment form data
     */
    public function generatePaymentData($data) {
        // Required data
        $pfData = [
            'merchant_id' => $this->merchantId,
            'merchant_key' => $this->merchantKey,
            'return_url' => $data['return_url'] ?? APP_URL . '/subscription/payfast/return',
            'cancel_url' => $data['cancel_url'] ?? APP_URL . '/subscription/payfast/cancel',
            'notify_url' => $data['notify_url'] ?? APP_URL . '/subscription/payfast/notify',
            'amount' => number_format($data['amount'], 2, '.', ''),
            'item_name' => $data['item_name'] ?? 'StudySmart Subscription',
            'item_description' => $data['item_description'] ?? '',
            'custom_str1' => $data['custom_str1'] ?? '', // User ID
            'custom_str2' => $data['custom_str2'] ?? '', // Plan
            'custom_str3' => $data['custom_str3'] ?? '', // Session ID or token
            'email_confirm' => $data['email_confirm'] ?? '1',
            'confirmation_url' => $data['confirmation_url'] ?? '',
        ];
        
        // Add signature
        $pfData['signature'] = $this->generateSignature($pfData);
        
        return $pfData;
    }
    
    /**
     * Generate signature for PayFast
     */
    private function generateSignature($data) {
        // Create parameter string
        $params = [];
        foreach ($data as $key => $value) {
            if ($key !== 'signature') {
                $params[] = urlencode($key) . '=' . urlencode($value);
            }
        }
        $parameterString = implode('&', $params);
        
        // Add passphrase if set
        if (!empty($this->passPhrase)) {
            $parameterString .= '&passphrase=' . urlencode($this->passPhrase);
        }
        
        // Generate MD5 signature
        return md5($parameterString);
    }
    
    /**
     * Verify PayFast ITN (Instant Transaction Notification) signature
     */
    public function verifySignature($postData) {
        // Remove signature from data
        $signature = $postData['signature'] ?? '';
        unset($postData['signature']);
        
        // Create parameter string
        $params = [];
        foreach ($postData as $key => $value) {
            $params[] = urlencode($key) . '=' . urlencode($value);
        }
        $parameterString = implode('&', $params);
        
        // Add passphrase if set
        if (!empty($this->passPhrase)) {
            $parameterString .= '&passphrase=' . urlencode($this->passPhrase);
        }
        
        // Generate signature and compare
        $generatedSignature = md5($parameterString);
        
        return hash_equals($signature, $generatedSignature);
    }
    
    /**
     * Get PayFast endpoint URL
     */
    public function getEndpointUrl() {
        return $this->baseUrl;
    }
    
    /**
     * Check if PayFast is configured
     */
    public function isConfigured() {
        return !empty($this->merchantId) && !empty($this->merchantKey);
    }
    
    /**
     * Get payment URL with query parameters (for redirect)
     */
    public function getPaymentUrl($data) {
        $pfData = $this->generatePaymentData($data);
        return $this->baseUrl . '?' . http_build_query($pfData);
    }
}
