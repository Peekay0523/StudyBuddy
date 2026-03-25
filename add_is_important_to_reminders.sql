-- Add is_important column to study_reminders for marking important reminders
ALTER TABLE study_reminders ADD COLUMN is_important INTEGER DEFAULT 0;

-- Create index for better performance on important reminder queries
CREATE INDEX IF NOT EXISTS idx_study_reminders_is_important ON study_reminders(is_important);
