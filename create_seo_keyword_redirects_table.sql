-- SEO Keyword Redirects Tracking Table
-- Tracks when users visit via SEO keyword redirects

CREATE TABLE IF NOT EXISTS seo_keyword_redirects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    keyword TEXT NOT NULL,
    visited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address TEXT,
    user_agent TEXT,
    user_id INTEGER,
    converted BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Index for faster queries
CREATE INDEX IF NOT EXISTS idx_keyword_visited ON seo_keyword_redirects(keyword, visited_at);
CREATE INDEX IF NOT EXISTS idx_visited_at ON seo_keyword_redirects(visited_at);
