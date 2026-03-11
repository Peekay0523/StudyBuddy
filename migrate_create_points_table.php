<?php
/**
 * Migration script to create user_points and points_transactions tables
 */

require_once __DIR__ . '/config/database.php';

echo "Running migration: Create user_points tables...\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Create user_points table
    $db->exec("
        CREATE TABLE IF NOT EXISTS user_points (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            points INTEGER DEFAULT 0,
            free_scans INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Created user_points table.\n";
    
    // Create points_transactions table
    $db->exec("
        CREATE TABLE IF NOT EXISTS points_transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            points INTEGER NOT NULL,
            transaction_type TEXT NOT NULL,
            description TEXT,
            reference_id INTEGER,
            reference_type TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Created points_transactions table.\n";
    
    // Create indexes
    $db->exec("CREATE INDEX IF NOT EXISTS idx_user_points_user_id ON user_points(user_id)");
    echo "✓ Created index on user_points.user_id.\n";
    
    $db->exec("CREATE INDEX IF NOT EXISTS idx_points_transactions_user_id ON points_transactions(user_id)");
    echo "✓ Created index on points_transactions.user_id.\n";
    
    $db->exec("CREATE INDEX IF NOT EXISTS idx_points_transactions_type ON points_transactions(transaction_type)");
    echo "✓ Created index on points_transactions.transaction_type.\n";
    
    // Initialize points for existing users
    $db->exec("
        INSERT OR IGNORE INTO user_points (user_id, points, free_scans)
        SELECT id, 0, 0 FROM users
    ");
    echo "✓ Initialized points for existing users.\n";
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
