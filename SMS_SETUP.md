# SMS/WhatsApp OTP Setup Guide

This guide shows you how to configure real SMS/WhatsApp OTP sending for user registration.

## Quick Start

The system currently logs OTP codes to the error log for testing. To send real SMS/WhatsApp messages:

---

## Option 1: Twilio (Recommended - SMS + WhatsApp)

**Best for:** Global coverage, WhatsApp support, reliable delivery

### Step 1: Create Twilio Account
1. Go to https://console.twilio.com/
2. Sign up for a free account
3. Verify your email and phone number

### Step 2: Get Your Credentials
1. From the Twilio Console Dashboard, copy:
   - **Account SID** (e.g., `ACxxxxxxxxxxxxxxxxxxxxxxxx`)
   - **Auth Token** (click "Show" to reveal)

### Step 3: Get a Phone Number
1. Go to https://console.twilio.com/us1/develop/phone-numbers/manage/incoming
2. Click "Get a phone number"
3. Choose a number with **SMS** capability (and **WhatsApp** if you want WhatsApp support)
4. Note the number (e.g., `+1234567890`)

### Step 4: Enable WhatsApp (Optional)
1. Go to https://console.twilio.com/us1/develop/sandbox/whatsapp
2. Follow instructions to set up WhatsApp sandbox
3. Note the WhatsApp number (e.g., `whatsapp:+14155238886`)

### Step 5: Configure Your Application

Create a `.env` file in your project root (or edit if exists):

```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_PHONE_NUMBER=+1234567890
TWILIO_WHATSAPP_NUMBER=whatsapp:+14155238886
SMS_SERVICE=twilio
```

### Step 6: Test
1. Go to http://localhost:8000/register
2. Enter a phone number and complete registration
3. Check your phone for the OTP code!

---

## Option 2: Africa's Talking (Best for Africa)

**Best for:** Affordable rates in Africa, good deliverability

### Step 1: Create Account
1. Go to https://africastalking.com/
2. Sign up for an account
3. Use **Sandbox** for testing (free)

### Step 2: Get API Key
1. Go to https://account.africastalking.com/apps
2. Copy your **API Key**

### Step 3: Configure
Add to `.env`:

```env
SMS_SERVICE=africastalking
AT_USERNAME=sandbox
AT_API_KEY=your_api_key_here
AT_SHORTCODE=YOUR_SHORTCODE
```

---

## Option 3: ClickSend (Global SMS)

**Best for:** Simple pricing, good global coverage

### Step 1: Create Account
1. Go to https://www.clicksend.com/
2. Sign up and verify account

### Step 2: Get API Credentials
1. Go to https://app.clicksend.com/
2. Navigate to Settings → API
3. Copy your **Username** and **API Key**

### Step 3: Configure
Add to `.env`:

```env
SMS_SERVICE=clicksend
CLICKSEND_USERNAME=your_username
CLICKSEND_API_KEY=your_api_key
```

---

## Testing Without Spending Money

### Twilio Free Tier
- Free trial includes **$15 credit**
- Free phone number (trial)
- Can only send to verified numbers during trial

### Africa's Talking Sandbox
- **Free** for testing
- Can only send to pre-approved numbers
- Add test numbers in the sandbox dashboard

---

## Phone Number Format

Users should enter phone numbers in **international format**:

✅ Correct:
- `+27123456789` (South Africa)
- `+1234567890` (USA)
- `+44123456789` (UK)

❌ Incorrect:
- `0123456789` (missing country code)
- `27 12 345 6789` (spaces - though the system will clean these)

---

## Troubleshooting

### OTP not sending?

1. **Check error logs:**
   ```
   C:\Users\mmereko\Desktop\SchoolApp\SchoolApp\error_log
   ```
   Or check your PHP error log

2. **Verify credentials:**
   - Make sure API keys are correct
   - No extra spaces in `.env` file

3. **Check phone number format:**
   - Must include country code (+27 for South Africa)
   - Remove leading zeros

4. **Test with cURL:**
   ```bash
   curl -X POST https://api.twilio.com/2010-04-01/Accounts/YOUR_SID/Messages.json \
     -u YOUR_SID:YOUR_TOKEN \
     -d "From=+1234567890" \
     -d "To=+27123456789" \
     -d "Body=Test message"
   ```

### WhatsApp not working?

- WhatsApp requires **business verification** for production
- During development, use the **WhatsApp Sandbox**
- Users must send "join" message to activate

---

## Switching Between Services

Edit `.env` file and change:

```env
SMS_SERVICE=twilio          # For Twilio
SMS_SERVICE=africastalking  # For Africa's Talking
SMS_SERVICE=clicksend       # For ClickSend
```

---

## Production Checklist

Before going live:

- [ ] Upgrade from sandbox/trial to paid account
- [ ] Verify business (for WhatsApp)
- [ ] Set up production phone numbers
- [ ] Update API credentials in production
- [ ] Test with real phone numbers
- [ ] Set up monitoring for failed sends
- [ ] Configure rate limiting to prevent abuse

---

## Cost Estimates

**Twilio:**
- SMS: ~$0.0075 per message (USA)
- WhatsApp: ~$0.005 per message
- Phone number: ~$1/month

**Africa's Talking:**
- SMS: ~$0.02 - $0.05 per message (varies by country)
- No monthly fees

**ClickSend:**
- SMS: ~$0.04 per message
- Prepaid credits from $10

---

## Security Notes

1. **Never commit `.env` to Git** - it's in `.gitignore` for a reason
2. **Rate limit OTP requests** - already implemented (60s cooldown)
3. **Log failed attempts** - helps detect abuse
4. **Use HTTPS in production** - protects phone numbers in transit

---

## Need Help?

- **Twilio Support:** https://support.twilio.com/
- **Africa's Talking Support:** support@africastalking.com
- **ClickSend Support:** https://www.clicksend.com/contact

---

## Current Development Mode

While developing, OTP codes are:
1. ✅ Logged to PHP error log
2. ✅ Stored in session (`$_SESSION['dev_otp_codes']`)
3. ✅ Shown in browser console (for testing)

**To view OTP during development:**
1. Complete registration form
2. Check browser console (F12)
3. Or check PHP error log
4. Use the code to verify
