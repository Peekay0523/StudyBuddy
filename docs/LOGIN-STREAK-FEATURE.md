# Login Streak Rewards Feature

## Overview
Users now earn **1 point for every 3 consecutive days** of logging in to StudySmart. This encourages daily engagement and rewards consistent learners.

## How It Works

### Point System
- **Day 1-2**: Login streak tracked, no points yet
- **Day 3**: Earn 1 point (streak = 3)
- **Day 4-5**: Continue streak, no additional points
- **Day 6**: Earn another point (streak = 6)
- **Day 7+**: Pattern continues (1 point every 3 days)

### Streak Rules
- Logging in on consecutive days increases your streak
- Missing a day **resets the streak to 1** (not 0)
- Points are only awarded on day 3, 6, 9, 12, etc.
- Multiple logins on the same day don't increase the streak

## Implementation Details

### Files Modified

1. **`models/UserActivity.php`**
   - Added point reward logic in `updateActivity()` method
   - Awards 1 point every 3 consecutive login days
   - Points are recorded in the `points_transactions` table with type `login_streak`

2. **`controllers/AuthController.php`**
   - Calls `updateActivity()` on successful login
   - Tracks login streak starting from first login

3. **`controllers/DashboardController.php`**
   - Fetches user's login streak from `user_activity` table
   - Calculates login streak points: `floor(login_streak / 3)`
   - Includes login streak points in total activity score
   - Added API endpoint: `/dashboard/login-streak-info`

4. **`templates/pages/dashboard.php`**
   - Displays "Login Streak" card in score breakdown
   - Shows current streak duration
   - Shows progress toward next reward (e.g., "2 more to earn point")

5. **`public/index.php`**
   - Added route: `GET /dashboard/login-streak-info`

### Database Schema

The `user_activity` table includes:
- `login_streak` (INTEGER): Current consecutive login days
- `last_login_date` (DATE): Last date user logged in (YYYY-MM-DD)

### Points Integration

Login streak points are:
- Added to the user's total points balance
- Recorded in `points_transactions` table
- Included in the dashboard "Learning Score"
- Can be used like any other earned points (e.g., convert 500 points to 1 free scan)

## Testing

### Manual Testing Steps

1. **Day 1**: Log in → Streak = 1, Points = 0
2. **Day 2**: Log in → Streak = 2, Points = 0
3. **Day 3**: Log in → Streak = 3, Points = 1 ✓
4. **Day 4**: Log in → Streak = 4, Points = 1
5. **Day 5**: Log in → Streak = 5, Points = 1
6. **Day 6**: Log in → Streak = 6, Points = 2 ✓

### Testing Streak Reset

1. Log in today → Streak increases
2. Skip a day
3. Log in next day → Streak resets to 1

### API Endpoint

```bash
GET /dashboard/login-streak-info
```

Response:
```json
{
  "success": true,
  "login_streak": 5,
  "login_streak_points": 1,
  "next_reward_at": 1
}
```

## Migration

Run the migration to add required columns:

```bash
php migrate_add_login_streak.php
```

This adds:
- `login_streak` column to `user_activity` table
- `last_login_date` column to `user_activity` table

## Future Enhancements

Potential improvements:
- Larger rewards for longer streaks (e.g., 5 points at 30 days)
- Visual streak counter on dashboard
- Email notifications when streak is about to expire
- Streak freeze items (premium feature)
- Leaderboard for longest streaks
