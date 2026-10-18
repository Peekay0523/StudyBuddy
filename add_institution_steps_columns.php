<?php
/**
 * Migration: Add per-institution steps columns to apply_for_me_submissions
 */

chdir(__DIR__);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

$existingNames = db_table_columns($db, 'apply_for_me_submissions');

$columnsToAdd = [
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_1_steps_completed TEXT DEFAULT ('[]')",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_2_steps_completed TEXT DEFAULT ('[]')",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_3_steps_completed TEXT DEFAULT ('[]')",
];

function getColumnName($sql) {
    if (preg_match('/ADD COLUMN (\w+)/', $sql, $matches)) {
        return $matches[1];
    }
    return null;
}

echo "<h2>Adding Institution Steps Columns</h2>\n";

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