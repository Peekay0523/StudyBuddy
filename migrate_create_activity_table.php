<?php
/**
 * Migration script to create user_activity table
 */

require_once __DIR__ . '/config/database.php';

echo "Running migration: Create user_activity table...\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Create user_activity table
    $db->exec("
        CREATE TABLE IF NOT EXISTS user_activity (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            last_active DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_online INTEGER DEFAULT 0,
            subjects_interested TEXT,
            grade_level TEXT,
            bio TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Created user_activity table.\n";
    
    // Create indexes
    $db->exec("CREATE INDEX IF NOT EXISTS idx_user_activity_last_active ON user_activity(last_active)");
    echo "✓ Created index on last_active.\n";
    
    $db->exec("CREATE INDEX IF NOT EXISTS idx_user_activity_online ON user_activity(is_online)");
    echo "✓ Created index on is_online.\n";
    
    // Initialize activity tracking for existing users
    $db->exec("
        INSERT OR IGNORE INTO user_activity (user_id, last_active, is_online)
        SELECT id, datetime('now'), 0 FROM users
    ");
    echo "✓ Initialized activity tracking for existing users.\n";
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
