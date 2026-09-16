<?php
/**
 * Migration: Add per-institution status columns to apply_for_me_submissions
 * Allows tracking status of each university preference separately
 */

chdir(__DIR__);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Get existing columns
$existingColumns = $db->query("PRAGMA table_info(apply_for_me_submissions)")->fetchAll(PDO::FETCH_ASSOC);
$existingNames = array_column($existingColumns, 'name');

$columnsToAdd = [
    // Institution 1 status tracking
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_1_status TEXT DEFAULT 'pending'",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_1_notes TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_1_response TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_1_submitted_at DATETIME DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_1_completed_at DATETIME DEFAULT NULL",
    
    // Institution 2 status tracking
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_2_status TEXT DEFAULT 'pending'",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_2_notes TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_2_response TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_2_submitted_at DATETIME DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_2_completed_at DATETIME DEFAULT NULL",
    
    // Institution 3 status tracking
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_3_status TEXT DEFAULT 'pending'",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_3_notes TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_3_response TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_3_submitted_at DATETIME DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_3_completed_at DATETIME DEFAULT NULL",
];

// Extract column name from ALTER TABLE statement
function getColumnName($sql) {
    if (preg_match('/ADD COLUMN (\w+)/', $sql, $matches)) {
        return $matches[1];
    }
    return null;
}

echo "<h2>Adding Institution Status Columns</h2>\n";

foreach ($columnsToAdd as $sql) {
    $colName = getColumnName($sql);
    if ($colName && in_array($colName, $existingNames)) {
        echo "SKIP (already exists): $colName<br>\n";
        continue;
    }
    
    try {
        $db->exec($sql);
        echo "OK: " . ($colName ?? $sql) . "<br>\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column') !== false) {
            echo "SKIP (duplicate): " . ($colName ?? $sql) . "<br>\n";
        } else {
            echo "ERROR: $sql - " . $e->getMessage() . "<br>\n";
        }
    }
}

echo "<br><strong>Migration complete!</strong><br>\n";
echo "Institution status columns have been added to apply_for_me_submissions table.";