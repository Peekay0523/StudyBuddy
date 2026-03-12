<?php
/**
 * Run Study Plan Features Migration
 * Execute this file to add study_plan_shares and study_reminders tables
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>Running Study Plan Features Migration</h2>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Read the SQL file
    $sqlFile = __DIR__ . '/add_study_plan_features.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\/\*/', $stmt);
        }
    );
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        try {
            $db->exec($statement);
            echo "<p style='color: green;'>✓ Executed: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
            $success++;
        } catch (PDOException $e) {
            // Ignore "already exists" errors
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "<p style='color: orange;'>⚠ Table already exists: " . htmlspecialchars(substr($statement, 0, 100)) . "</p>";
                $success++;
            } else {
                echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                $errors++;
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>Migration Complete!</h3>";
    echo "<p style='color: green;'>Successful: $success</p>";
    if ($errors > 0) {
        echo "<p style='color: red;'>Errors: $errors</p>";
    }
    
    echo "<p><a href='/study-plan' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Study Plans</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
