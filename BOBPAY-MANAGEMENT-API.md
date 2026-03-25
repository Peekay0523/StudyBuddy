# BobPay Payment Management - Complete API Integration

## ✅ All BobPay Helper Methods

### Payment Processing

#### 1. **createPayment($data)** - Create Payment Link
Create a payment link for subscription checkout.

```php
$bobPay = new BobPayHelper();
$paymentData = $bobPay->preparePaymentData([
    'amount' => 39.00,
    'customer_email' => 'customer@example.com',
    'customer_name' => 'John Doe',
    'success_url' => APP_URL . '/subscription/bobpay/return',
    'cancel_url' => APP_URL . '/subscription/bobpay/cancel',
    'callback_url' => APP_URL . '/subscription/bobpay/webhook',
    'metadata' => ['user_id' => 1, 'plan' => 'basic']
]);

$response = $bobPay->createPayment($paymentData);
$paymentUrl = $response['short_url'] ?? $response['url'];
```

---

### Payment Intent Management

#### 2. **getPaymentIntents($filters)** - Get Payment List
Retrieve comprehensive payment information with filters.

```php
$filters = [
    'include_retained_amount' => 'true',
    'start_date' => '2024-04-18 00:00:00',
    'end_date' => '2024-06-18 23:59:59',
    'limit' => 10,
    'order' => 'DESC',
    'order_by' => 'status',
    'statuses' => ['paid', 'unpaid'],
    'from_bank' => 'standard-bank',
    'search' => '38NGB'
];
$payments = $bobPay->getPaymentIntents($filters);
```

#### 3. **getPaymentIntent($paymentId)** - Get Single Payment
Retrieve details of a specific payment.

```php
$payment = $bobPay->getPaymentIntent(12345);
```

#### 4. **createPaymentIntentWithSignature($data)** - Create Payment Intent with Signature
Validate signature and create payment intent.

```php
$intentData = [
    'recipient_account_code' => 'SAN001',
    'custom_payment_id' => 'ORDER-123',
    'email' => 'customer@example.com',
    'amount' => 499.99,
    'item_name' => 'Product Name',
    'signature' => 'your-generated-signature',
    'success_url' => APP_URL . '/success',
    'cancel_url' => APP_URL . '/cancel'
];
$intent = $bobPay->createPaymentIntentWithSignature($intentData);
```

#### 5. **shortenPaymentUrl($url)** - Shorten Payment URL
Generate a shortened URL for payment intent.

```php
$longUrl = 'https://sandbox.bobpay.co.za/pay?amount=499.99&...';
$shortUrl = $bobPay->shortenPaymentUrl($longUrl);
// Returns: https://api.sandbox.bob.co.za/r/GS9TRC
```

#### 6. **refundPayment($paymentId)** - Process Refund
Initiate a refund for a specific payment.

```php
$result = $bobPay->refundPayment(72052);
```

---

### Payment Methods

#### 7. **getPublicPaymentMethods($accountCode)** - Get Available Payment Methods
Retrieve available payment methods for an account.

```php
$methods = $bobPay->getPublicPaymentMethods('SAN001');
```

---

### Payout Management

#### 8. **getPayoutRequests($filters)** - Get Payout Requests
Retrieve payout requests with filters.

```php
$filters = [
    'account_id' => 4718,
    'status' => 'pending',
    'order' => 'DESC',
    'offset' => 0,
    'limit' => 20,
    'order_by' => 'time_created'
];
$payouts = $bobPay->getPayoutRequests($filters);
```

**Available Filters:**
- `id` - Payout request ID
- `status` - pending, rejected, processed, cancelled
- `account_id` - Filter by account
- `order` / `order_by` - Sorting
- `offset` / `limit` - Pagination

#### 9. **getPayoutSchedule($filters)** - Get Payout Schedule
Retrieve automated payout schedules.

```php
$filters = [
    'account_id' => 665,
    'payout_frequency' => 'monthly'
];
$schedule = $bobPay->getPayoutSchedule($filters);
```

**Payout Frequency Options:**
- `daily` - Daily payouts
- `weekly` - Weekly payouts (payout_frequency_day: 0=Sunday, 6=Saturday)
- `monthly` - Monthly payouts (payout_frequency_day: 1-31)

---

## 🧪 Test All Endpoints

**URL:** `http://localhost:8000/test-bobpay-management.php`

Tests all 9 endpoints with sample data.

---

## 🧪 Test Pages

### Test Payment Management
**URL:** `http://localhost:8000/test-bobpay-management.php`

Tests all new endpoints with sample data.

### Test Checkout (Bypasses Auth)
**URL:** `http://localhost:8000/test-bobpay-checkout.php`

Creates a payment link for testing.

---

## 📋 Integration Examples

### Admin Dashboard - View All Payments
```php
// In your admin controller
public function payments() {
    requireAdmin();
    
    $bobPay = new BobPayHelper();
    
    // Get filters from request
    $filters = [
        'limit' => 20,
        'include_retained_amount' => 'true',
        'order' => 'DESC',
        'order_by' => 'time_created'
    ];
    
    if (!empty($_GET['status'])) {
        $filters['statuses'] = [$_GET['status']];
    }
    
    if (!empty($_GET['from_date'])) {
        $filters['start_date'] = $_GET['from_date'] . ' 00:00:00';
    }
    
    if (!empty($_GET['to_date'])) {
        $filters['end_date'] = $_GET['to_date'] . ' 23:59:59';
    }
    
    $payments = $bobPay->getPaymentIntents($filters);
    
    include __DIR__ . '/../templates/pages/admin/payments.php';
}
```

### Process Refund from Admin Panel
```php
public function processRefund() {
    requireAdmin();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /admin/payments');
        exit;
    }
    
    $paymentId = (int)($_POST['payment_id'] ?? 0);
    $reason = $_POST['reason'] ?? '';
    
    $bobPay = new BobPayHelper();
    $result = $bobPay->refundPayment($paymentId);
    
    if ($result && isset($result['payment_method'])) {
        setFlashMessage('success', 'Refund processed successfully!');
        
        // Log refund in your database
        $this->logRefund($paymentId, $reason, $result);
    } else {
        setFlashMessage('error', 'Failed to process refund');
    }
    
    header('Location: /admin/payments');
    exit;
}
```

### Check Payment Status
```php
public function checkPaymentStatus($paymentId) {
    $bobPay = new BobPayHelper();
    $payment = $bobPay->getPaymentIntent($paymentId);
    
    if ($payment) {
        return [
            'status' => $payment['status'],
            'amount' => $payment['amount'],
            'reference' => $payment['reference'],
            'time_created' => $payment['time_created']
        ];
    }
    
    return null;
}
```

---

## 🔐 Security Notes

1. **Always verify payment status** before granting access to paid features
2. **Use webhooks** for real-time payment confirmation
3. **Log all refund operations** for audit trails
4. **Restrict refund access** to admin users only
5. **Validate payment amounts** match expected values

---

## 📊 Database Schema for Payment Tracking

```sql
CREATE TABLE bobpay_payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    payment_intent_id INTEGER UNIQUE,
    custom_payment_id TEXT,
    user_id INTEGER,
    amount REAL,
    status TEXT,
    payment_method TEXT,
    reference TEXT,
    bobpay_response TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE payment_refunds (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    payment_id INTEGER,
    refund_amount REAL,
    reason TEXT,
    bobpay_response TEXT,
    refunded_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES bobpay_payments(id),
    FOREIGN KEY (refunded_by) REFERENCES users(id)
);
```

---

## ✅ Complete Feature List

- ✅ Create payment links (subscription checkout)
- ✅ Handle webhooks (payment notifications)
- ✅ Retrieve payment lists with filters
- ✅ Get single payment details
- ✅ Process refunds/reversals
- ✅ Get available payment methods
- ✅ Export to CSV
- ✅ Handle sandbox & production modes
