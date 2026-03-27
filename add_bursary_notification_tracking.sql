-- Add last_viewed column to users table for tracking bursary views
ALTER TABLE users ADD COLUMN bursaries_last_viewed DATETIME DEFAULT NULL;

-- Update existing users to have current timestamp
UPDATE users SET bursaries_last_viewed = CURRENT_TIMESTAMP WHERE bursaries_last_viewed IS NULL;

-- Create index for better performance
CREATE INDEX IF NOT EXISTS idx_users_bursaries_last_viewed ON users(bursaries_last_viewed);
