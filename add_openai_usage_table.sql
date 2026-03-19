-- OpenAI Usage Tracking Table
CREATE TABLE IF NOT EXISTS openai_usage_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    prompt_tokens INTEGER DEFAULT 0,
    completion_tokens INTEGER DEFAULT 0,
    total_tokens INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create index for faster queries
CREATE INDEX IF NOT EXISTS idx_usage_user_id ON openai_usage_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_usage_created_at ON openai_usage_logs(created_at);
