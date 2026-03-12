<?php
/**
 * Run Study Plan Features Migration
 * Execute this file to add study_plan_shares and study_reminders tables
 */

require_once __DIR__ . '/../config/database.php';

echo "<h2>Running Study Plan Features Migration</h2>";

try {
    $db = Database::getInstance()->getConnection();
    
    // First check what tables exist
    echo "<h3>Current Tables:</h3>";
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "<p>• $table</p>";
    }
    
    // Read the SQL file
    $sqlFile = __DIR__ . '/../add_study_plan_features.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "<h3>Running SQL Statements:</h3>";
    
    // Execute each statement separately, handling CREATE TABLE first
    $createTableStatements = [
        "CREATE TABLE IF NOT EXISTS study_plan_shares (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            study_plan_id INTEGER NOT NULL,
            sender_id INTEGER NOT NULL,
            recipient_id INTEGER NOT NULL,
            message TEXT DEFAULT '',
            status TEXT DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (study_plan_id) REFERENCES study_plans(id) ON DELETE CASCADE,
            FOREIGN KEY (sender_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (recipient_id) REFERENCES students(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS study_reminders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            study_plan_id INTEGER,
            title TEXT NOT NULL,
            description TEXT,
            reminder_date DATE NOT NULL,
            reminder_time TIME,
            is_completed INTEGER DEFAULT 0,
            is_recurring INTEGER DEFAULT 0,
            recurring_pattern TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (study_plan_id) REFERENCES study_plans(id) ON DELETE SET NULL
        )"
    ];
    
    foreach ($createTableStatements as $stmt) {
        try {
            $db->exec($stmt);
            echo "<p style='color: green;'>✓ Table created successfully</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Try to add shared_count column
    try {
        $db->exec("ALTER TABLE study_plans ADD COLUMN shared_count INTEGER DEFAULT 0");
        echo "<p style='color: green;'>✓ Added shared_count column to study_plans</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate') !== false) {
            echo "<p style='color: orange;'>⚠ shared_count column already exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding column: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Create indexes
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_study_plan_shares_plan_id ON study_plan_shares(study_plan_id)",
        "CREATE INDEX IF NOT EXISTS idx_study_plan_shares_sender_id ON study_plan_shares(sender_id)",
        "CREATE INDEX IF NOT EXISTS idx_study_plan_shares_recipient_id ON study_plan_shares(recipient_id)",
        "CREATE INDEX IF NOT EXISTS idx_study_reminders_user_id ON study_reminders(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_study_reminders_date ON study_reminders(reminder_date)"
    ];
    
    foreach ($indexes as $index) {
        try {
            $db->exec($index);
            echo "<p style='color: green;'>✓ Index created</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Verify tables exist now
    echo "<h3>Verification:</h3>";
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    $hasShares = in_array('study_plan_shares', $tables);
    $hasReminders = in_array('study_reminders', $tables);
    
    if ($hasShares) {
        echo "<p style='color: green;'>✓ study_plan_shares table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ study_plan_shares table NOT created</p>";
    }
    
    if ($hasReminders) {
        echo "<p style='color: green;'>✓ study_reminders table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ study_reminders table NOT created</p>";
    }
    
    // Check if shared_count column exists
    $columns = $db->query("PRAGMA table_info(study_plans)")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (in_array('shared_count', $columns)) {
        echo "<p style='color: green;'>✓ shared_count column exists in study_plans</p>";
    } else {
        echo "<p style='color: orange;'>⚠ shared_count column NOT in study_plans</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='/study-plan' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Study Plans</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
