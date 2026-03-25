<?php
/**
 * Migration: Add notification tracking for study groups and study plans
 * 
 * Run this once to add is_viewed column to study_group_messages
 */

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "Adding notification tracking columns...\n";

try {
    // Check if is_viewed column already exists in study_group_messages
    $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN);
    $hasIsViewed = in_array('is_viewed', $columns);
    
    if (!$hasIsViewed) {
        $db->exec("ALTER TABLE study_group_messages ADD COLUMN is_viewed INTEGER DEFAULT 0");
        echo "✓ Added is_viewed column to study_group_messages\n";
    } else {
        echo "✓ is_viewed column already exists\n";
    }
    
    // Check if is_completed column already exists in study_plans
    $columns = $db->query("PRAGMA table_info(study_plans)")->fetchAll(PDO::FETCH_COLUMN);
    $hasIsCompleted = in_array('is_completed', $columns);
    
    if (!$hasIsCompleted) {
        $db->exec("ALTER TABLE study_plans ADD COLUMN is_completed INTEGER DEFAULT 0");
        echo "✓ Added is_completed column to study_plans\n";
    } else {
        echo "✓ is_completed column already exists\n";
    }
    
    // Create indexes
    try {
        $db->exec("CREATE INDEX IF NOT EXISTS idx_study_group_messages_is_viewed ON study_group_messages(is_viewed)");
        echo "✓ Created index on study_group_messages.is_viewed\n";
    } catch (Exception $e) {
        echo "⚠ Index on is_viewed skipped\n";
    }
    
    try {
        $db->exec("CREATE INDEX IF NOT EXISTS idx_study_group_messages_created_at ON study_group_messages(created_at)");
        echo "✓ Created index on study_group_messages.created_at\n";
    } catch (Exception $e) {
        echo "⚠ Index on created_at skipped\n";
    }
    
    try {
        $db->exec("CREATE INDEX IF NOT EXISTS idx_study_plans_is_completed ON study_plans(is_completed)");
        echo "✓ Created index on study_plans.is_completed\n";
    } catch (Exception $e) {
        echo "⚠ Index on is_completed skipped\n";
    }
    
    echo "\n✓ Migration complete!\n";
    
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
