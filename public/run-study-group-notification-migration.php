<?php
/**
 * Run Study Group Notifications Migration
 * Executes all migrations needed for proper notification tracking
 */

require_once __DIR__ . '/../config/database.php';

echo "<h2>Running Study Group Notifications Migration</h2>";

try {
    $db = Database::getInstance()->getConnection();
    $success = 0;
    $errors = 0;

    // Migration 1: Add last_visited column to study_group_members
    echo "<h3>Migration 1: Adding last_visited column...</h3>";
    // Get column names (index 1 is the 'name' column in PRAGMA table_info)
    $columns = $db->query("PRAGMA table_info(study_group_members)")->fetchAll(PDO::FETCH_COLUMN, 1);
    
    echo "<p style='color: #666;'>Current columns: " . implode(', ', $columns) . "</p>";
    echo "<p style='color: #666;'>Looking for 'last_visited': " . (in_array('last_visited', $columns) ? 'FOUND' : 'NOT FOUND') . "</p>";

    if (in_array('last_visited', $columns)) {
        echo "<p style='color: green;'>✓ Column 'last_visited' already exists (migration was successful).</p>";
        $success++;
    } else {
        try {
            // SQLite doesn't allow non-constant defaults, so we add without default then update
            $db->exec("ALTER TABLE study_group_members ADD COLUMN last_visited DATETIME");
            echo "<p style='color: green;'>✓ Added 'last_visited' column to study_group_members table.</p>";
            $success++;
            
            // Set existing rows to current timestamp
            $db->exec("UPDATE study_group_members SET last_visited = CURRENT_TIMESTAMP WHERE last_visited IS NULL");
            echo "<p style='color: green;'>✓ Updated existing rows with current timestamp.</p>";
            $success++;
            
            $db->exec("CREATE INDEX IF NOT EXISTS idx_study_group_members_last_visited ON study_group_members(last_visited)");
            echo "<p style='color: green;'>✓ Created index on last_visited column.</p>";
            $success++;
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            $errors++;
        }
    }

    echo "<hr>";
    echo "<h3>Migration Complete!</h3>";
    echo "<p style='color: green;'>Successful operations: $success</p>";
    if ($errors > 0) {
        echo "<p style='color: red;'>Errors: $errors</p>";
    }

    if ($errors === 0) {
        echo "<p style='color: green; font-weight: bold;'>✓ Study group notifications will now be tracked properly!</p>";
        echo "<p style='color: #666; font-size: 14px;'>Notifications will be cleared when you:</p>";
        echo "<ul style='color: #666; font-size: 14px;'>";
        echo "<li>Visit the main Study Groups page (/study-group)</li>";
        echo "<li>Enter any study group detail page</li>";
        echo "</ul>";
    }

    echo "<p><a href='/study-group' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Study Groups</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
