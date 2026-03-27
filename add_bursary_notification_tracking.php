<?php
/**
 * Add Bursary Notification Tracking
 * Adds last_viewed column to users table for tracking bursary views
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>Adding Bursary Notification Tracking</h2>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column already exists
    $columns = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (in_array('bursaries_last_viewed', $columns)) {
        echo "<p style='color: green;'>✓ Column 'bursaries_last_viewed' already exists.</p>";
    } else {
        // Add column
        $db->exec("ALTER TABLE users ADD COLUMN bursaries_last_viewed DATETIME DEFAULT NULL");
        echo "<p style='color: green;'>✓ Added 'bursaries_last_viewed' column to users table.</p>";
        
        // Update existing rows
        $db->exec("UPDATE users SET bursaries_last_viewed = CURRENT_TIMESTAMP WHERE bursaries_last_viewed IS NULL");
        echo "<p style='color: green;'>✓ Updated existing users with current timestamp.</p>";
        
        // Create index
        $db->exec("CREATE INDEX IF NOT EXISTS idx_users_bursaries_last_viewed ON users(bursaries_last_viewed)");
        echo "<p style='color: green;'>✓ Created index on bursaries_last_viewed column.</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>✓ Bursary notifications will now be tracked properly!</p>";
    echo "<p><a href='/upload-report-card' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Careers Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
