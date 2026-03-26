<?php
/**
 * Debug Career API
 */
require_once __DIR__ . '/config/database.php';

echo "<h1>Career API Debug</h1>";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h2>Database Info</h2>";
    echo "<p><strong>Driver:</strong> " . $db->getAttribute(PDO::ATTR_DRIVER_NAME) . "</p>";
    
    echo "<h2>Tables</h2>";
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<pre>" . print_r($tables, true) . "</pre>";
    
    echo "<h2>Careers Count</h2>";
    $stmt = $db->query("SELECT COUNT(*) FROM careers");
    echo "<p>Total careers: " . $stmt->fetchColumn() . "</p>";
    
    echo "<h2>Institutions Count</h2>";
    $stmt = $db->query("SELECT COUNT(*) FROM institutions");
    echo "<p>Total institutions: " . $stmt->fetchColumn() . "</p>";
    
    echo "<h2>Career-Institutions Count</h2>";
    $stmt = $db->query("SELECT COUNT(*) FROM career_institutions");
    echo "<p>Total career-institution records: " . $stmt->fetchColumn() . "</p>";
    
    echo "<h2>Sample Career</h2>";
    $stmt = $db->query("SELECT * FROM careers LIMIT 1");
    $career = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($career, true) . "</pre>";
    
    echo "<h2>Sample Institution</h2>";
    $stmt = $db->query("SELECT * FROM institutions LIMIT 1");
    $institution = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($institution, true) . "</pre>";
    
    echo "<h2>Sample Career-Institution</h2>";
    $stmt = $db->query("SELECT * FROM career_institutions LIMIT 1");
    $ci = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($ci, true) . "</pre>";
    
    echo "<h2>Test Search Query</h2>";
    $searchTerm = "doctor";
    $sql = "SELECT c.*, COUNT(ci.institution_id) as institution_count
            FROM careers c
            LEFT JOIN career_institutions ci ON c.id = ci.career_id
            WHERE (c.name LIKE ? OR c.description LIKE ? OR c.category LIKE ?)
            GROUP BY c.id
            ORDER BY c.min_aps_score ASC";
    $stmt = $db->prepare($sql);
    $searchParam = "%{$searchTerm}%";
    $stmt->execute([$searchParam, $searchParam, $searchParam]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found " . count($results) . " careers matching '{$searchTerm}'</p>";
    echo "<pre>" . print_r($results, true) . "</pre>";
    
    echo "<h2>Test Categories Query</h2>";
    $stmt = $db->query("SELECT DISTINCT category FROM careers ORDER BY category ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Found " . count($categories) . " categories</p>";
    echo "<pre>" . print_r($categories, true) . "</pre>";
    
    echo "<h2>Test Institutions for Career</h2>";
    $careerId = 1;
    $stmt = $db->prepare("
        SELECT i.*, ci.subject_requirements, ci.min_aps_score as required_aps, ci.additional_requirements
        FROM institutions i
        INNER JOIN career_institutions ci ON i.id = ci.institution_id
        WHERE ci.career_id = ?
        ORDER BY i.name ASC
    ");
    $stmt->execute([$careerId]);
    $institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found " . count($institutions) . " institutions for career ID {$careerId}</p>";
    foreach ($institutions as $inst) {
        echo "<pre>" . print_r($inst, true) . "</pre>";
    }
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Database Error</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

echo "<hr>";
echo "<h3>Test API Endpoints</h3>";
echo "<ul>";
echo "<li><a href='/api/search-careers?q=doctor' target='_blank'>/api/search-careers?q=doctor</a></li>";
echo "<li><a href='/api/career-categories' target='_blank'>/api/career-categories</a></li>";
echo "<li><a href='/api/institutions' target='_blank'>/api/institutions</a></li>";
echo "</ul>";
?>
