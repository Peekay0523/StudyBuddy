<?php
/**
 * Migration: Add is_shared column to uploaded_scripts table
 * This allows admin to upload scripts that are shared with all students
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "=== Migration: Add is_shared column to uploaded_scripts ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column already exists (SQLite way)
    $stmt = $db->query("PRAGMA table_info(uploaded_scripts)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('is_shared', $columns)) {
        echo "✓ Column 'is_shared' already exists\n";
    } else {
        // Add is_shared column (SQLite syntax)
        $db->exec("
            ALTER TABLE uploaded_scripts 
            ADD COLUMN is_shared INTEGER DEFAULT 0
        ");
        echo "✓ Added 'is_shared' column to uploaded_scripts table\n";
    }
    
    // Create indexes (SQLite doesn't support IF NOT EXISTS for all versions)
    $indexes = [
        'idx_is_shared' => 'CREATE INDEX IF NOT EXISTS idx_is_shared ON uploaded_scripts(is_shared)',
        'idx_grade_level' => 'CREATE INDEX IF NOT EXISTS idx_grade_level ON uploaded_scripts(grade_level)',
        'idx_subject' => 'CREATE INDEX IF NOT EXISTS idx_subject ON uploaded_scripts(subject)',
        'idx_browse' => 'CREATE INDEX IF NOT EXISTS idx_browse ON uploaded_scripts(grade_level, is_shared, subject)'
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
    echo "\nNext steps:\n";
    echo "1. Admin can now upload scripts with is_shared = 1\n";
    echo "2. Students can browse shared scripts by grade at /browse-scripts/{grade}\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
