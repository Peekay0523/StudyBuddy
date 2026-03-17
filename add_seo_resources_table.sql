-- SEO Resources (Scripts & Memorandums) Database Schema
-- Run this to add file upload functionality to SEO pages

-- SEO Resources table - stores uploaded scripts and memorandums
CREATE TABLE IF NOT EXISTS seo_resources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page_id INTEGER NOT NULL,
    resource_type TEXT NOT NULL DEFAULT 'script', -- script, memorandum, study_guide, past_paper, checklist
    title TEXT NOT NULL,
    description TEXT,
    file_path TEXT NOT NULL,
    file_name TEXT NOT NULL,
    file_size INTEGER, -- in bytes
    file_mime_type TEXT, -- application/pdf, etc.
    subject TEXT,
    grade_level TEXT,
    download_count INTEGER DEFAULT 0,
    is_free INTEGER DEFAULT 1, -- 1 = free download, 0 = requires login/subscription
    is_featured INTEGER DEFAULT 0,
    uploaded_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES seo_pages(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Resource index for faster lookups
CREATE INDEX IF NOT EXISTS idx_seo_resources_page_id ON seo_resources(page_id);
CREATE INDEX IF NOT EXISTS idx_seo_resources_type ON seo_resources(resource_type);
CREATE INDEX IF NOT EXISTS idx_seo_resources_subject ON seo_resources(subject);

-- Insert sample resource types
INSERT OR IGNORE INTO seo_resource_types (name, display_name, icon, allowed_mime_types, max_size_mb) VALUES
('script', 'Study Scripts', '📝', '["application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"]', 10),
('memorandum', 'Memorandums', '✅', '["application/pdf"]', 10),
('study_guide', 'Study Guides', '📚', '["application/pdf"]', 20),
('past_paper', 'Past Exam Papers', '📋', '["application/pdf"]', 15),
('checklist', 'Study Checklists', '✓', '["application/pdf", "image/jpeg", "image/png"]', 5);

-- Create resource types table if it doesn't exist
CREATE TABLE IF NOT EXISTS seo_resource_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    display_name TEXT NOT NULL,
    icon TEXT,
    allowed_mime_types TEXT DEFAULT '[]',
    max_size_mb INTEGER DEFAULT 10,
    is_active INTEGER DEFAULT 1
);
