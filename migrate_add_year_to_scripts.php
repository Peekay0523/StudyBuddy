<?php
/**
 * Migration: Add year column to uploaded_scripts table
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "=== Migration: Add year column to uploaded_scripts ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column already exists (SQLite way)
    $stmt = $db->query("PRAGMA table_info(uploaded_scripts)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Add year column
    if (in_array('year', $columns)) {
        echo "✓ Column 'year' already exists\n";
    } else {
        $db->exec("ALTER TABLE uploaded_scripts ADD COLUMN year INTEGER DEFAULT NULL");
        echo "✓ Added 'year' column to uploaded_scripts table\n";
    }
    
    // Create index for year filtering
    try {
        $db->exec("CREATE INDEX IF NOT EXISTS idx_year ON uploaded_scripts(year)");
        echo "✓ Created index: idx_year\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "✓ Index idx_year already exists\n";
        } else {
            echo "⚠ Index idx_year: " . $e->getMessage() . "\n";
        }
    }
    
    // Create combined index for browsing with year
    try {
        $db->exec("CREATE INDEX IF NOT EXISTS idx_browse_year ON uploaded_scripts(grade_level, is_shared, subject, year)");
        echo "✓ Created index: idx_browse_year\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "✓ Index idx_browse_year already exists\n";
        } else {
            echo "⚠ Index idx_browse_year: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Migration completed successfully! ===\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
