<?php
/**
 * Migration script to create study_group_invites table
 */

require_once __DIR__ . '/config/database.php';

echo "Running migration: Create study_group_invites table...\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Create the table
    $db->exec("
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
        )
    ");
    
    echo "✓ Created study_group_invites table.\n";
    
    // Create indexes
    $db->exec("CREATE INDEX IF NOT EXISTS idx_study_group_invites_token ON study_group_invites(invite_token)");
    echo "✓ Created index on invite_token.\n";
    
    $db->exec("CREATE INDEX IF NOT EXISTS idx_study_group_invites_email ON study_group_invites(friend_email)");
    echo "✓ Created index on friend_email.\n";
    
    $db->exec("CREATE INDEX IF NOT EXISTS idx_study_group_invites_status ON study_group_invites(status)");
    echo "✓ Created index on status.\n";
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
