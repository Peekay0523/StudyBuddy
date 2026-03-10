-- Add APS score column to career_recommendations table
ALTER TABLE career_recommendations ADD COLUMN aps_score INTEGER DEFAULT 0;
