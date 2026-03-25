<?php
/**
 * Run Important Reminders Migration
 * Execute this file to add is_important column to study_reminders table
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>Running Important Reminders Migration</h2>";

try {
    $db = Database::getInstance()->getConnection();

    // Check if column already exists
    $columns = $db->query("PRAGMA table_info(study_reminders)")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('is_important', $columns)) {
        echo "<p style='color: orange;'>⚠ Column 'is_important' already exists in study_reminders table.</p>";
    } else {
        // Add is_important column
        $db->exec("ALTER TABLE study_reminders ADD COLUMN is_important INTEGER DEFAULT 0");
        echo "<p style='color: green;'>✓ Added 'is_important' column to study_reminders table.</p>";
        
        // Create index
        try {
            $db->exec("CREATE INDEX IF NOT EXISTS idx_study_reminders_is_important ON study_reminders(is_important)");
            echo "<p style='color: green;'>✓ Created index on is_important column.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Index may already exist: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    echo "<hr>";
    echo "<h3>Migration Complete!</h3>";
    echo "<p style='color: green;'>✓ The 'Mark as Important' feature is now available!</p>";

    echo "<p><a href='/study-plan' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Study Plans</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
