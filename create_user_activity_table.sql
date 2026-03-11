-- Create user_activity table for tracking online status
CREATE TABLE IF NOT EXISTS user_activity (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    last_active DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_online INTEGER DEFAULT 0,
    subjects_interested TEXT,
    grade_level TEXT,
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_user_activity_last_active ON user_activity(last_active);
CREATE INDEX IF NOT EXISTS idx_user_activity_online ON user_activity(is_online);

-- Initialize activity tracking for existing users
INSERT OR IGNORE INTO user_activity (user_id, last_active, is_online)
SELECT id, datetime('now'), 0 FROM users;
