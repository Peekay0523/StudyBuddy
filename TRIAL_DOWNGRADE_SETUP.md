# Automated Trial Downgrade Setup

## Overview
New users get a 7-day free trial of the Basic plan. After 7 days, their subscription automatically expires and they're moved to the Free plan.

## Setup Automatic Downgrade (Windows)

### Using Windows Task Scheduler

1. **Open Task Scheduler**
   - Press `Win + R`
   - Type `taskschd.msc`
   - Press Enter

2. **Create Basic Task**
   - Click "Create Basic Task..." in the right panel
   - Name: "StudySmart Trial Downgrade"
   - Description: "Downgrade expired trial subscriptions to free plan"
   - Click Next

3. **Set Trigger**
   - Select "Daily"
   - Click Next
   - Set a time (e.g., 2:00 AM)
   - Click Next

4. **Set Action**
   - Select "Start a program"
   - Click Next
   - Browse to: `C:\Users\mmereko\Desktop\SchoolApp\SchoolApp\downgrade_trials.bat`
   - Or enter:
     - Program/script: `php`
     - Add arguments: `C:\Users\mmereko\Desktop\SchoolApp\SchoolApp\downgrade_trials.php`
     - Start in: `C:\Users\mmereko\Desktop\SchoolApp\SchoolApp`
   - Click Next

5. **Finish**
   - Review settings
   - Click Finish
   - Task is now scheduled!

### Manual Execution
Run manually anytime:
```bash
php downgrade_trials.php
```

## How It Works

1. **New Registration**: When a user registers, they automatically get:
   - A 7-day free trial of the Basic plan (R39/month value)
   - Status: `trial`
   - Trial end date: 7 days from registration

2. **During Trial**: User has access to all Basic plan features:
   - 50 script uploads per month
   - Unlimited AI chat
   - AI study plan recitation
   - Priority email support
   - Ad-free experience
   - Advanced career guidance

3. **After 7 Days**: 
   - The downgrade script runs (daily via scheduler)
   - Finds all expired trials (`status = 'trial'` AND `current_period_end < now`)
   - Changes status to `expired`
   - User is automatically moved to Free plan

4. **User Experience**:
   - During trial: Sees "FREE TRIAL" badge and trial end date
   - After trial: Automatically loses access to Basic features
   - Can upgrade anytime from the subscription page

## Database Schema

### Users Table
- `phone` - UNIQUE NOT NULL (primary identifier)
- `joined_date` - When user registered

### Subscriptions Table
- `status` - Can be: `active`, `trial`, `expired`, `cancelled`
- `current_period_end` - When trial/subscription expires

## Testing

To test the downgrade process:
```bash
# Run the script manually
php downgrade_trials.php
```

To test a new registration:
1. Register a new user at http://localhost:8000/register
2. Login and check subscription page - should show "FREE TRIAL (7 DAYS)"
3. Check database: `SELECT * FROM subscriptions WHERE status = 'trial'`
