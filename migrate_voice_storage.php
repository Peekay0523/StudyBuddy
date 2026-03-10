<?php
/**
 * Migration script to add voice_data column to study_group_messages table
 */

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

$sql = file_get_contents(__DIR__ . '/add_voice_data_column.sql');

try {
    $db->exec($sql);
    echo "✓ Column 'voice_data' added successfully to study_group_messages table!\n";
} catch (Exception $e) {
    // Column might already exist, that's ok
    if (strpos($e->getMessage(), 'duplicate column name') !== false) {
        echo "✓ Column 'voice_data' already exists!\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}
