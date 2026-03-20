-- Add paper column to uploaded_scripts table for exam paper identification
-- Add memorandum support for shared scripts

-- Add paper column
ALTER TABLE uploaded_scripts 
ADD COLUMN paper INTEGER DEFAULT NULL COMMENT 'Exam paper number (1, 2, or 3)';

-- Add memorandum file path column
ALTER TABLE uploaded_scripts 
ADD COLUMN memorandum_file_path VARCHAR(255) DEFAULT NULL COMMENT 'Path to memorandum file';

-- Create index for paper filtering
CREATE INDEX IF NOT EXISTS idx_paper ON uploaded_scripts(paper);

-- Create combined index for browsing with paper
CREATE INDEX IF NOT EXISTS idx_browse_paper ON uploaded_scripts(grade_level, is_shared, subject, paper);
