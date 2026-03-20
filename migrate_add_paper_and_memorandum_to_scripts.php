<?php
/**
 * Migration: Add paper and memorandum_file_path columns to uploaded_scripts table
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "=== Migration: Add paper and memorandum columns to uploaded_scripts ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if columns already exist (SQLite way)
    $stmt = $db->query("PRAGMA table_info(uploaded_scripts)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Add paper column
    if (in_array('paper', $columns)) {
        echo "✓ Column 'paper' already exists\n";
    } else {
        $db->exec("ALTER TABLE uploaded_scripts ADD COLUMN paper INTEGER DEFAULT NULL");
        echo "✓ Added 'paper' column to uploaded_scripts table\n";
    }
    
    // Add memorandum_file_path column
    if (in_array('memorandum_file_path', $columns)) {
        echo "✓ Column 'memorandum_file_path' already exists\n";
    } else {
        $db->exec("ALTER TABLE uploaded_scripts ADD COLUMN memorandum_file_path VARCHAR(255) DEFAULT NULL");
        echo "✓ Added 'memorandum_file_path' column to uploaded_scripts table\n";
    }
    
    // Create indexes
    $indexes = [
        'idx_paper' => 'CREATE INDEX IF NOT EXISTS idx_paper ON uploaded_scripts(paper)',
        'idx_browse_paper' => 'CREATE INDEX IF NOT EXISTS idx_browse_paper ON uploaded_scripts(grade_level, is_shared, subject, paper)'
    ];
    
    foreach ($indexes as $indexName => $sql) {
        try {
            $db->exec($sql);
            echo "✓ Created index: $indexName\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "✓ Index $indexName already exists\n";
            } else {
                echo "⚠ Index $indexName: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n=== Migration completed successfully! ===\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
