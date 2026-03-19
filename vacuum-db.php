<?php
/**
 * Vacuum and optimize the SQLite database
 * Run this after freeing up disk space
 */

require_once __DIR__ . '/config/database.php';

echo "Starting database vacuum...\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Run VACUUM to reclaim space
    $db->exec('VACUUM');
    echo "✓ VACUUM completed\n";
    
    // Run integrity check
    $result = $db->query('PRAGMA integrity_check')->fetchColumn();
    if ($result === 'ok') {
        echo "✓ Integrity check passed\n";
    } else {
        echo "✗ Integrity check failed: $result\n";
    }
    
    // Show database size
    $dbPath = __DIR__ . '/database.sqlite3';
    if (file_exists($dbPath)) {
        $size = filesize($dbPath);
        echo "✓ Database size: " . number_format($size) . " bytes\n";
    }
    
    echo "\nDatabase optimization complete!\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
