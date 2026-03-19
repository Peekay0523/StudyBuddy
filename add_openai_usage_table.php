<?php
/**
 * Add OpenAI Usage Tracking Table
 * Run this file once to add the usage tracking table to your database
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Create the table
    $db->exec("
        CREATE TABLE IF NOT EXISTS openai_usage_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            prompt_tokens INTEGER DEFAULT 0,
            completion_tokens INTEGER DEFAULT 0,
            total_tokens INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    
    // Create indexes
    $db->exec("CREATE INDEX IF NOT EXISTS idx_usage_user_id ON openai_usage_logs(user_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_usage_created_at ON openai_usage_logs(created_at)");
    
    echo "✓ OpenAI usage tracking table created successfully!<br>";
    echo "✓ Indexes created for faster queries.<br><br>";
    echo "You can now view token usage statistics in the <a href='/admin'>Admin Dashboard</a>.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
