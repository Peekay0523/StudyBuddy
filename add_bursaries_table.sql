-- Add bursaries table for admin-managed bursaries
CREATE TABLE IF NOT EXISTS bursaries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    provider TEXT NOT NULL,
    eligibility TEXT NOT NULL,
    covers TEXT DEFAULT '',
    deadline TEXT NOT NULL,
    contact TEXT DEFAULT '',
    apply_url TEXT DEFAULT '',
    min_grade_average REAL DEFAULT 0,
    max_grade_average REAL DEFAULT 100,
    required_subjects TEXT DEFAULT '[]',
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Add index for active bursaries
CREATE INDEX IF NOT EXISTS idx_bursaries_active ON bursaries(is_active);
CREATE INDEX IF NOT EXISTS idx_bursaries_deadline ON bursaries(deadline);
