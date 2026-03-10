<?php
/**
 * Migration: Add APS score column to career_recommendations table
 */

require_once __DIR__ . '/config/database.php';

echo "Running migration: Add APS score to career_recommendations...\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column already exists
    $columns = $db->query("PRAGMA table_info(career_recommendations)")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('aps_score', $columns)) {
        echo "Column 'aps_score' already exists. Skipping migration.\n";
    } else {
        // Add the column
        $db->exec("ALTER TABLE career_recommendations ADD COLUMN aps_score INTEGER DEFAULT 0");
        echo "Successfully added 'aps_score' column to career_recommendations table.\n";
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
