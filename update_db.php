<?php
// Script to add missing columns to career_recommendations table
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Check if columns exist
$columns = $db->query("PRAGMA table_info(career_recommendations)")->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('courses_data', $columns)) {
    $db->exec("ALTER TABLE career_recommendations ADD COLUMN courses_data TEXT DEFAULT '[]'");
    echo "Added courses_data column\n";
} else {
    echo "courses_data column already exists\n";
}

if (!in_array('bursaries_data', $columns)) {
    $db->exec("ALTER TABLE career_recommendations ADD COLUMN bursaries_data TEXT DEFAULT '[]'");
    echo "Added bursaries_data column\n";
} else {
    echo "bursaries_data column already exists\n";
}

echo "Database update complete!\n";
?>
