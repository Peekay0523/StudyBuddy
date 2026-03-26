<?php
/**
 * Cleanup Duplicate Careers Data
 */
require_once __DIR__ . '/config/database.php';

echo "<h1>Cleanup Duplicate Careers</h1>";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h2>Before Cleanup</h2>";
    $stmt = $db->query("SELECT COUNT(*) FROM careers");
    echo "<p>Total careers: " . $stmt->fetchColumn() . "</p>";
    
    $stmt = $db->query("SELECT COUNT(*) FROM career_institutions");
    echo "<p>Total career_institutions: " . $stmt->fetchColumn() . "</p>";
    
    // Find and remove duplicate careers (keeping the lowest ID)
    echo "<h2>Removing Duplicate Careers</h2>";
    $stmt = $db->exec("
        DELETE FROM careers 
        WHERE id NOT IN (
            SELECT MIN(id) 
            FROM careers 
            GROUP BY name, category
        )
    ");
    echo "<p>Deleted duplicate careers</p>";
    
    // Remove orphaned career_institutions records
    echo "<h2>Removing Orphaned Records</h2>";
    $stmt = $db->exec("
        DELETE FROM career_institutions 
        WHERE career_id NOT IN (SELECT id FROM careers)
    ");
    echo "<p>Deleted orphaned career_institutions records</p>";
    
    echo "<h2>After Cleanup</h2>";
    $stmt = $db->query("SELECT COUNT(*) FROM careers");
    echo "<p>Total careers: " . $stmt->fetchColumn() . "</p>";
    
    $stmt = $db->query("SELECT COUNT(*) FROM career_institutions");
    echo "<p>Total career_institutions: " . $stmt->fetchColumn() . "</p>";
    
    echo "<h2>Unique Careers</h2>";
    $stmt = $db->query("SELECT id, name, category, min_aps_score FROM careers ORDER BY category, name");
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Category</th><th>Min APS</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['category']}</td>";
        echo "<td>{$row['min_aps_score']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<p style='color: green;'><strong>✓ Cleanup Complete!</strong></p>";
    echo "<p><a href='/upload-report-card'>Go to Upload Report Card</a></p>";
    echo "<p><a href='/api/search-careers?q=doctor'>Test API: Search for Doctor</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>
