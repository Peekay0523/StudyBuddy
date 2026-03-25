# BobPay Subscription Management Guide for Admins

## Overview

This guide explains how administrators can view and manage subscriptions paid via BobPay in the StudySmart system.

## Payment Methods Tracked

The system now tracks the following payment methods:
- **BobPay** - Card payments via BobPay gateway (automatic activation)
- **EFT** - Electronic Funds Transfer (requires manual approval)

## How BobPay Payments Work

1. **Automatic Activation**: When a user pays via BobPay, their subscription is automatically activated with status `active`
2. **Transaction Tracking**: Each BobPay payment is assigned a unique transaction ID for verification
3. **Webhook Confirmation**: BobPay sends a webhook to confirm payment completion

## Viewing BobPay Payments as Admin

### Step 1: Navigate to Subscription Management

Go to: `http://localhost:8000/admin/subscriptions?filter=all`

### Step 2: Use Payment Method Filters

Click on the filter buttons at the top of the page:

- **All** - Shows all subscriptions regardless of payment method or status
- **BobPay** - Shows ONLY subscriptions paid via BobPay
- **Pending EFT** - Shows EFT payments awaiting manual approval
- **Active** - Shows all active subscriptions (includes BobPay and approved EFT)
- **Trial** - Shows trial subscriptions
- **Expired** - Shows expired subscriptions
- **Cancelled** - Shows cancelled subscriptions

### Step 3: Review Subscription Details

The subscription table now displays:

| Column | Description |
|--------|-------------|
| ID | Subscription ID |
| User | Username and email |
| Plan | basic, premium, or free |
| Price | Amount paid |
| Status | active, pending_eft, trial, expired, cancelled |
| **Payment Method** | BobPay or EFT (with icon) |
| **Transaction ID** | BobPay transaction reference |
| Payment Reference | EFT reference number (if applicable) |
| Period Start | Subscription start date |
| Period End | Subscription end date |
| Actions | Manage subscription |

## Payment Method Indicators

- 🏦 **EFT** (Red) - Requires manual approval by admin
- 💳 **BobPay** (Green) - Automatically activated, no approval needed
- 💳 **Card** (Gray) - Generic card payment (legacy)

## Managing BobPay Subscriptions

### For Active BobPay Subscriptions

Since BobPay payments are automatic, you can:

1. **Change Status** - Use the dropdown to change to Trial, Expire, or Cancel
2. **Cancel** - Click the cancel button to immediately cancel
3. **Delete** - Click the trash icon to permanently delete (use with caution)

### For Pending EFT Subscriptions

EFT payments require manual verification:

1. Click **View EFT** to see payment details
2. Review proof of payment uploaded by user
3. Click **Approve** to activate subscription
4. Or click **Reject** with a reason if payment is invalid

## Troubleshooting

### User Claims They Paid But Subscription Not Active

1. Check **BobPay filter** to see if payment was recorded
2. Look for transaction ID in the Transaction ID column
3. Check BobPay webhook logs at: `logs/bobpay_webhook.log`
4. If webhook failed, you can manually activate:
   - Change status from dropdown to "Activate"

### Transaction ID Not Showing

- Older subscriptions (before migration) may not have transaction IDs
- This doesn't affect subscription validity
- Transaction IDs are stored for new BobPay payments

### Payment Method Not Showing

- Run the migration script: `http://localhost:8000/migrate_payment_method.php`
- This adds the `payment_method` column to existing subscriptions

## Database Schema

The subscriptions table includes:

```sql
payment_method TEXT DEFAULT 'eft'  -- bobpay, eft
transaction_id TEXT                 -- Gateway transaction ID
```

## Migration Instructions

If you're upgrading from an older version:

1. **Run the migration script once:**
   ```
   http://localhost:8000/migrate_payment_method.php
   ```

2. **Verify columns were added:**
   - Check that `payment_method` and `transaction_id` columns exist
   - Existing subscriptions will be updated with appropriate payment methods

3. **Test the filters:**
   - Navigate to `/admin/subscriptions?filter=bobpay`
   - Verify BobPay subscriptions are displayed correctly

## Logs and Debugging

Payment gateway logs are stored in:

- **BobPay**: `logs/bobpay_webhook.log`

These logs show:
- When payments were received
- Transaction IDs
- Subscription activation events

## Security Notes

- All BobPay transactions are verified via webhook signature
- Transaction IDs are stored for audit purposes
- Only admins can access subscription management
- Payment proof files are stored securely in `public/uploads/eft_proofs/`

## Support

For issues with BobPay integration:
1. Check webhook logs
2. Verify BobPay credentials in `config/bobpay.php`
3. Test with BobPay sandbox mode first
4. Contact BobPay support for gateway-specific issues

---

**Last Updated**: March 25, 2026
**Version**: 2.0 (BobPay Only)
