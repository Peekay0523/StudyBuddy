-- Add BLOB column for storing voice recording data
ALTER TABLE study_group_messages ADD COLUMN voice_data BLOB;

-- Migrate existing voice messages (optional - will be NULL for existing records)
-- The voice_data will be populated for new recordings
