<?php
/**
 * Migration: Add missing columns to apply_for_me_submissions table
 * Handles both new admin tracking columns AND columns that were missing from original table creation
 */

chdir(__DIR__);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Get existing columns
$existingColumns = $db->query("PRAGMA table_info(apply_for_me_submissions)")->fetchAll(PDO::FETCH_COLUMN);
$existingNames = array_column($existingColumns, 'name');

$columnsToAdd = [
    // Original schema columns that were missing from the actual table
    "ALTER TABLE apply_for_me_submissions ADD COLUMN application_id TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN bursary_name TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN bursary_provider TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_name TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_type TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN document_verification TEXT DEFAULT 'pending'",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN payment_amount REAL DEFAULT 20.00",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN payment_reference TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN admin_notes TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN policy_accepted INTEGER DEFAULT 0",
    // New admin tracking columns
    "ALTER TABLE apply_for_me_submissions ADD COLUMN verified_by INTEGER DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN verified_at DATETIME DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN submitted_to_institution_at DATETIME DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN institution_response TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN document_verification_notes TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN rejection_reason TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN current_step TEXT DEFAULT 'submitted'",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN proof_of_payment_path TEXT DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN proof_of_payment_uploaded_at DATETIME DEFAULT NULL",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN steps_completed TEXT DEFAULT '[]'",
    "ALTER TABLE apply_for_me_submissions ADD COLUMN steps_updated_at DATETIME DEFAULT NULL",
];

// Extract column name from ALTER TABLE statement
function getColumnName($sql) {
    if (preg_match('/ADD COLUMN (\w+)/', $sql, $matches)) {
        return $matches[1];
    }
    return null;
}

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

// Now verify all columns exist
echo "<br><strong>Final table columns:</strong><br>\n";
$finalColumns = $db->query("PRAGMA table_info(apply_for_me_submissions)")->fetchAll(PDO::FETCH_COLUMN);
foreach ($finalColumns as $col) {
    echo "- $col<br>\n";
}

echo "<br>Migration complete!";