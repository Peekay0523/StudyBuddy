-- Add study plan sharing and calendar reminders features

-- Table for sharing study plans with friends
CREATE TABLE IF NOT EXISTS study_plan_shares (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    study_plan_id INTEGER NOT NULL,
    sender_id INTEGER NOT NULL,
    recipient_id INTEGER NOT NULL,
    message TEXT DEFAULT '',
    status TEXT DEFAULT 'pending', -- pending, accepted, declined
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (study_plan_id) REFERENCES study_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Table for study reminders/calendar events
CREATE TABLE IF NOT EXISTS study_reminders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    study_plan_id INTEGER,
    title TEXT NOT NULL,
    description TEXT,
    reminder_date DATE NOT NULL,
    reminder_time TIME,
    is_completed INTEGER DEFAULT 0,
    is_recurring INTEGER DEFAULT 0,
    recurring_pattern TEXT, -- daily, weekly, weekdays, monthly
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (study_plan_id) REFERENCES study_plans(id) ON DELETE SET NULL
);

-- Add shared_with_count to study_plans for quick stats
ALTER TABLE study_plans ADD COLUMN shared_count INTEGER DEFAULT 0;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_study_plan_shares_plan_id ON study_plan_shares(study_plan_id);
CREATE INDEX IF NOT EXISTS idx_study_plan_shares_sender_id ON study_plan_shares(sender_id);
CREATE INDEX IF NOT EXISTS idx_study_plan_shares_recipient_id ON study_plan_shares(recipient_id);
CREATE INDEX IF NOT EXISTS idx_study_reminders_user_id ON study_reminders(user_id);
CREATE INDEX IF NOT EXISTS idx_study_reminders_date ON study_reminders(reminder_date);
