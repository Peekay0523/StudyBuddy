-- Add last_visited column to study_group_members for tracking script notifications
-- Note: SQLite doesn't support non-constant defaults, so we add the column first
ALTER TABLE study_group_members ADD COLUMN last_visited DATETIME;

-- Update existing rows to current timestamp
UPDATE study_group_members SET last_visited = CURRENT_TIMESTAMP WHERE last_visited IS NULL;

-- Create index for better performance
CREATE INDEX IF NOT EXISTS idx_study_group_members_last_visited ON study_group_members(last_visited);
