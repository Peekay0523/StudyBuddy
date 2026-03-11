-- Create study_group_invites table for inviting friends
CREATE TABLE IF NOT EXISTS study_group_invites (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    study_group_id INTEGER,
    user_id INTEGER NOT NULL,
    friend_email TEXT NOT NULL,
    friend_name TEXT,
    invite_token TEXT UNIQUE NOT NULL,
    message TEXT,
    status TEXT DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    claimed_at DATETIME,
    claimed_user_id INTEGER,
    FOREIGN KEY (study_group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (claimed_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_study_group_invites_token ON study_group_invites(invite_token);
CREATE INDEX IF NOT EXISTS idx_study_group_invites_email ON study_group_invites(friend_email);
CREATE INDEX IF NOT EXISTS idx_study_group_invites_status ON study_group_invites(status);
