-- Add notification tracking for study groups and study plans

-- Add is_viewed column to study_group_messages for tracking viewed messages
ALTER TABLE study_group_messages ADD COLUMN is_viewed INTEGER DEFAULT 0;

-- Add index for better performance on notification queries
CREATE INDEX IF NOT EXISTS idx_study_group_messages_is_viewed ON study_group_messages(is_viewed);
CREATE INDEX IF NOT EXISTS idx_study_group_messages_created_at ON study_group_messages(created_at);

-- Add is_completed column to study_plans if it doesn't exist (for tracking completed plans)
-- Note: This may fail if column already exists, which is okay
ALTER TABLE study_plans ADD COLUMN is_completed INTEGER DEFAULT 0;
CREATE INDEX IF NOT EXISTS idx_study_plans_is_completed ON study_plans(is_completed);
