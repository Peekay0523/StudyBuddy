<?php
/**
 * Migration: Add is_important column to study_reminders table
 * Run this once to add the important reminder feature
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column already exists
    $columns = $db->query("PRAGMA table_info(study_reminders)")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('is_important', $columns)) {
        echo "Column 'is_important' already exists.\n";
    } else {
        // Add is_important column
        $db->exec("ALTER TABLE study_reminders ADD COLUMN is_important INTEGER DEFAULT 0");
        echo "Added 'is_important' column to study_reminders table.\n";
        
        // Create index
        $db->exec("CREATE INDEX IF NOT EXISTS idx_study_reminders_is_important ON study_reminders(is_important)");
        echo "Created index on is_important column.\n";
    }
    
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
