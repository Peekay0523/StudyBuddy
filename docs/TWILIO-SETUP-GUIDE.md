# Twilio SMS Integration Guide for StudySmart

## Quick Start

### Step 1: Sign Up for Twilio

1. Go to [https://console.twilio.com/](https://console.twilio.com/)
2. Create a free account
3. Verify your email and phone number

### Step 2: Get Your Credentials

After signing in:

1. **Account SID**: Found on the Twilio Console Dashboard
2. **Auth Token**: Click on "Show" under Account SID on the dashboard
3. **Get a Phone Number**: 
   - Go to [Phone Numbers](https://console.twilio.com/us1/develop/phone-numbers/manage/incoming)
   - Click "Get a phone number"
   - Choose a number with **SMS capability**
   - Note: Free trial accounts can only send to verified numbers

### Step 3: Configure Your .env File

Copy the example and fill in your credentials:

```bash
# In your SchoolApp directory
cp .env.example .env
```

Edit `.env` and add your Twilio credentials:

```env
# Twilio Configuration
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_PHONE_NUMBER=+1234567890

# Set SMS service to Twilio
SMS_SERVICE=twilio
```

### Step 4: Install Twilio PHP SDK (Optional)

The code works with cURL, but you can install the official SDK:

```bash
composer require twilio/sdk
```

### Step 5: Test Your Configuration

Visit: `http://localhost:8000/test-sms-otp.php`

Or run from command line:
```bash
php test-sms-otp.php
```

### Step 6: Test Registration

1. Go to `http://localhost:8000/register`
2. Enter username, phone number, and password
3. You should receive an SMS with OTP code
4. Enter the OTP to complete registration

---

## Troubleshooting

### Issue: "Twilio not configured" in logs

**Solution**: Make sure your `.env` file exists and has correct values:
```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_token
TWILIO_PHONE_NUMBER=+1234567890
SMS_SERVICE=twilio
```

### Issue: OTP not being sent

**Check**:
1. Phone number format: Must include country code (e.g., `+27123456789`)
2. Twilio account is active (not suspended)
3. For trial accounts: Recipient number must be verified in Twilio console
4. Check error logs: `error_log` file in your project root

### Issue: "Invalid phone number format"

**Solution**: Ensure phone numbers are in international format:
- ✅ `+27123456789` (South Africa)
- ✅ `+12025551234` (USA)
- ✅ `+447700900123` (UK)
- ❌ `0123456789` (missing country code)

### Issue: Works in development but not production

**Check**:
1. `.env` file is uploaded to production
2. Environment variables are set on production server
3. Twilio phone number has SMS capability enabled
4. Production server can make outbound HTTPS requests

---

## Testing Without Sending Real SMS

During development, the system logs OTP codes instead of sending real SMS if:
- Twilio credentials are not configured
- SMS_SERVICE is not set to 'twilio'

**Check OTP in logs**:
```bash
# View error log
tail -f error_log

# Or check PHP's error log location
# (defined in php.ini)
```

**Development fallback**: OTP is also stored in `$_SESSION['dev_otp_codes']`

---

## Alternative SMS Providers

If Twilio is too expensive or unavailable in your region, you can switch to:

### Africa's Talking (Recommended for Africa)

```env
SMS_SERVICE=africastalking
AT_USERNAME=sandbox
AT_API_KEY=your_api_key
AT_SHORTCODE=your_shortcode
```

Get credentials from: [https://africastalking.com/](https://africastalking.com/)

### ClickSend (Global)

```env
SMS_SERVICE=clicksend
CLICKSEND_USERNAME=your_username
CLICKSEND_API_KEY=your_api_key
```

Get credentials from: [https://www.clicksend.com/](https://www.clicksend.com/)

---

## Production Checklist

Before going live:

- [ ] Upgrade Twilio account from trial to paid
- [ ] Verify all recipient numbers (or upgrade to remove restriction)
- [ ] Set up production `.env` with real credentials
- [ ] Enable HTTPS on your server
- [ ] Test with real phone numbers
- [ ] Monitor SMS delivery in Twilio dashboard
- [ ] Set up error logging and monitoring
- [ ] Configure rate limiting to prevent abuse

---

## Cost Estimation

**Twilio Pricing** (varies by country):
- USA: ~$0.0075 per SMS
- South Africa: ~$0.05 per SMS
- UK: ~$0.04 per SMS

**Example**: 1000 registrations/month to South Africa = ~$50/month

**Free Trial**: $15 credit when you upgrade from trial

---

## Security Best Practices

1. **Never commit `.env` to Git** (already in `.gitignore`)
2. **Rotate Auth Token** periodically from Twilio console
3. **Enable Two-Factor Authentication** on Twilio account
4. **Monitor usage** in Twilio dashboard for unusual activity
5. **Rate limit** OTP requests (already implemented: 60s cooldown)
6. **Expire OTPs** after 10 minutes (already configured)
7. **Max 3 attempts** per OTP (already configured)

---

## API Reference

### Send OTP
```php
$otpCode = createOtp($phone, 'registration');
sendOtpSms($phone, $otpCode, 'registration');
```

### Verify OTP
```php
$result = verifyOtp($phone, $otpCode, 'registration');
if ($result['success']) {
    // OTP is valid
} else {
    // Invalid or expired
    echo $result['message'];
}
```

### Resend OTP
```php
$canResend = canResendOtp($phone, 'registration');
if ($canResend['can_resend']) {
    // Allow resend
} else {
    // Wait {$canResend['wait_time']} seconds
}
```

---

## Support

- **Twilio Documentation**: [https://www.twilio.com/docs/sms](https://www.twilio.com/docs/sms)
- **Twilio Support**: [https://support.twilio.com](https://support.twilio.com)
- **StudySmart Logs**: Check `error_log` file for SMS sending status
