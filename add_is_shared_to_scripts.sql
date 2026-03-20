-- Add is_shared column to uploaded_scripts table for admin-uploaded scripts
-- is_shared = 1 means the script is available to all students
-- is_shared = 0 means the script is private to the student who uploaded it

ALTER TABLE uploaded_scripts 
ADD COLUMN is_shared TINYINT(1) DEFAULT 0 COMMENT '0 = private (student uploaded), 1 = shared (admin uploaded)';

-- Add index for faster filtering
CREATE INDEX idx_is_shared ON uploaded_scripts(is_shared);

-- Add grade_level index if not exists
CREATE INDEX IF NOT EXISTS idx_grade_level ON uploaded_scripts(grade_level);

-- Add subject index if not exists
CREATE INDEX IF NOT EXISTS idx_subject ON uploaded_scripts(subject);

-- Combined index for browsing
CREATE INDEX IF NOT EXISTS idx_browse ON uploaded_scripts(grade_level, is_shared, subject);
