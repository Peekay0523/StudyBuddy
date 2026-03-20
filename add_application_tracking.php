<?php
/**
 * Add Application Tracking Tables Script
 * Run this to add bursary and institution application tracking to the database
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Adding Application Tracking Tables</h1>";

try {
    $db = Database::getInstance()->getConnection();

    // Read and execute SQL file
    $sqlFile = __DIR__ . '/add_application_tracking.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $db->exec($statement);
                echo "<p style='color: green;'>✓ Executed: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
            }
        }

        echo "<h2 style='color: green;'>✓ Application tracking tables added successfully!</h2>";
        echo "<p><a href='/dashboard' class='btn-primary' style='display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 8px;'>Go to Dashboard</a></p>";
    } else {
        echo "<p style='color: red;'>SQL file not found!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
