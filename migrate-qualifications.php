<?php
/**
 * Migration: Add Qualifications to Career-Institution Records
 * 
 * Run this to add specific qualification names to existing records.
 * Usage: http://localhost:8000/migrate-qualifications
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Qualifications Migration</h1>";

try {
    $db = Database::getInstance()->getConnection();
    
    $sqlFile = __DIR__ . '/add_qualifications_to_careers.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $executed = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $stmt = $db->prepare($statement);
                $stmt->execute();
                $executed++;
                echo "<p style='color: green;'>✓ Updated record {$executed}</p>";
            } catch (PDOException $e) {
                echo "<p style='color: orange;'>⚠ Skipped: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✓ Migration Complete!</h2>";
    echo "<p>Successfully updated <strong>{$executed}</strong> career-institution records with qualification names.</p>";
    echo "<p><a href='/upload-report-card'>Test Career Search</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Migration Failed</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
