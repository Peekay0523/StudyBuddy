<?php
/**
 * Migration script to add school_name column to study_groups table
 */

require_once __DIR__ . '/config/database.php';

echo "Running migration: Add school_name column to study_groups table...\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column already exists
    $stmt = $db->query("PRAGMA table_info(study_groups)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('school_name', $columns)) {
        echo "✓ Column 'school_name' already exists. No migration needed.\n";
    } else {
        // Add the column
        $db->exec("ALTER TABLE study_groups ADD COLUMN school_name TEXT");
        echo "✓ Successfully added 'school_name' column to study_groups table.\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
