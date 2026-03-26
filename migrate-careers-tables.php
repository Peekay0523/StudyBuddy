<?php
/**
 * Migration Script: Create Careers and Institutions Tables
 * 
 * Run this file once to create the careers, institutions, and career_institutions tables
 * with sample data for South African careers and universities.
 * 
 * Usage: Visit http://localhost:8000/migrate-careers-tables
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Careers & Institutions Migration</h1>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if we're using SQLite or MySQL
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver === 'sqlite') {
        $sqlFile = __DIR__ . '/create_careers_and_institutions_sqlite.sql';
    } else {
        $sqlFile = __DIR__ . '/create_careers_and_institutions.sql';
    }
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $executed = 0;
    $skipped = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                $executed++;
                echo "<p style='color: green;'>✓ Executed statement {$executed}</p>";
            } catch (PDOException $e) {
                $errorMsg = $e->getMessage();
                // Check if it's a "table already exists" error
                if (strpos($errorMsg, 'already exists') !== false) {
                    echo "<p style='color: orange;'>⚠ Table already exists, skipping...</p>";
                    $skipped++;
                } else if (strpos($errorMsg, 'Duplicate') !== false) {
                    echo "<p style='color: orange;'>⚠ Duplicate entry, skipping...</p>";
                    $skipped++;
                } else if (strpos($errorMsg, 'SQLSTATE') !== false) {
                    echo "<p style='color: orange;'>⚠ Skipped (may already exist): " . htmlspecialchars($errorMsg) . "</p>";
                    $skipped++;
                } else {
                    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($errorMsg) . "</p>";
                }
            }
        }
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✓ Migration Complete!</h2>";
    echo "<p>Successfully executed <strong>{$executed}</strong> SQL statements.";
    if ($skipped > 0) {
        echo " ({$skipped} skipped - likely already exist)</p>";
    }
    echo "<p><strong>Careers Table:</strong> Contains career information with APS requirements</p>";
    echo "<p><strong>Institutions Table:</strong> Contains South African universities and colleges</p>";
    echo "<p><strong>Career Institutions Table:</strong> Links careers to institutions with subject requirements</p>";
    echo "<hr>";
    echo "<h3>Test the API:</h3>";
    echo "<ul>";
    echo "<li><a href='/api/search-careers?q=doctor' target='_blank'>Search for 'doctor' careers</a></li>";
    echo "<li><a href='/api/search-careers?q=engineer' target='_blank'>Search for 'engineer' careers</a></li>";
    echo "<li><a href='/api/career-categories' target='_blank'>Get all career categories</a></li>";
    echo "<li><a href='/api/institutions' target='_blank'>Get all institutions</a></li>";
    echo "</ul>";
    echo "<p><a href='/upload-report-card' style='display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px;'>Go to Upload Report Card Page</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Migration Failed</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
