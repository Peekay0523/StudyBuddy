<?php
/**
 * Migration script to add login streak tracking to user_activity table
 */

require_once __DIR__ . '/config/database.php';

echo "Running migration: Add login streak tracking...\n\n";

try {
    $db = Database::getInstance()->getConnection();

    // Check if columns already exist
    $columns = $db->query("PRAGMA table_info(user_activity)")->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');

    // Add login_streak column
    if (!in_array('login_streak', $columnNames)) {
        $db->exec("ALTER TABLE user_activity ADD COLUMN login_streak INTEGER DEFAULT 0");
        echo "✓ Added login_streak column.\n";
    } else {
        echo "✓ login_streak column already exists.\n";
    }

    // Add last_login_date column
    if (!in_array('last_login_date', $columnNames)) {
        $db->exec("ALTER TABLE user_activity ADD COLUMN last_login_date DATE");
        echo "✓ Added last_login_date column.\n";
    } else {
        echo "✓ last_login_date column already exists.\n";
    }

    echo "\nMigration completed successfully!\n";
    echo "\nRun this migration with: php migrate_add_login_streak.php\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
