<?php
/**
 * Migration: Add institutions_data to career_recommendations
 */
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column exists
    $stmt = $db->query("PRAGMA table_info(career_recommendations)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $exists = false;
    foreach ($columns as $column) {
        if ($column['name'] === 'institutions_data') {
            $exists = true;
            break;
        }
    }
    
    if (!$exists) {
        $db->exec("ALTER TABLE career_recommendations ADD COLUMN institutions_data TEXT DEFAULT '[]'");
        echo "Successfully added institutions_data column to career_recommendations table.\n";
    } else {
        echo "institutions_data column already exists in career_recommendations table.\n";
    }
    
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
