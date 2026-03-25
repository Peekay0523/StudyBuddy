<?php
/**
 * Run Last Visited Migration
 * Execute this file to add last_visited column to study_group_members table
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>Running Last Visited Migration</h2>";

try {
    $db = Database::getInstance()->getConnection();

    // Check if column already exists
    $columns = $db->query("PRAGMA table_info(study_group_members)")->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (in_array('last_visited', $columns)) {
        echo "<p style='color: orange;'>⚠ Column 'last_visited' already exists in study_group_members table.</p>";
    } else {
        // SQLite doesn't allow non-constant defaults, so we add without default then update
        $db->exec("ALTER TABLE study_group_members ADD COLUMN last_visited DATETIME");
        echo "<p style='color: green;'>✓ Added 'last_visited' column to study_group_members table.</p>";
        
        // Set existing rows to current timestamp
        $db->exec("UPDATE study_group_members SET last_visited = CURRENT_TIMESTAMP WHERE last_visited IS NULL");
        echo "<p style='color: green;'>✓ Updated existing rows with current timestamp.</p>";
        
        // Create index
        try {
            $db->exec("CREATE INDEX IF NOT EXISTS idx_study_group_members_last_visited ON study_group_members(last_visited)");
            echo "<p style='color: green;'>✓ Created index on last_visited column.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Index may already exist: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    echo "<hr>";
    echo "<h3>Migration Complete!</h3>";
    echo "<p style='color: green;'>✓ Study group script notifications will now be tracked properly!</p>";

    echo "<p><a href='/study-group' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Study Groups</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
