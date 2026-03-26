<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h1>All Careers and Institutions</h1>";
echo "<p><a href='/upload-report-card'>Go to Upload Report Card</a></p>";

// Get all careers
$stmt = $db->query('SELECT c.id, c.name, c.category, COUNT(ci.institution_id) as inst_count FROM careers c LEFT JOIN career_institutions ci ON c.id = ci.career_id GROUP BY c.id ORDER BY c.name');
$careers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Careers Summary</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Career</th><th>Category</th><th>Institutions</th></tr>";
foreach ($careers as $career) {
    $style = $career['inst_count'] >= 15 ? 'background: #d1fae5;' : ($career['inst_count'] > 0 ? 'background: #fef3c7;' : 'background: #fee2e2;');
    echo "<tr style='$style'>";
    echo "<td>" . htmlspecialchars($career['name']) . "</td>";
    echo "<td>" . htmlspecialchars($career['category']) . "</td>";
    echo "<td>" . $career['inst_count'] . " institutions</td>";
    echo "</tr>";
}
echo "</table>";

// Get all institutions
$stmt = $db->query('SELECT id, name, type, application_fee FROM institutions ORDER BY name');
$institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>All Institutions (" . count($institutions) . ")</h2>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Application Fee</th></tr>";
foreach ($institutions as $inst) {
    echo "<tr>";
    echo "<td>" . $inst['id'] . "</td>";
    echo "<td>" . htmlspecialchars($inst['name']) . "</td>";
    echo "<td>" . $inst['type'] . "</td>";
    echo "<td>R" . $inst['application_fee'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test specific career
echo "<h2>Test Search</h2>";
echo "<p><a href='/api/search-careers?q=Software+Developer' target='_blank'>Search: Software Developer</a></p>";
echo "<p><a href='/api/search-careers?q=Graphic+Designer' target='_blank'>Search: Graphic Designer</a></p>";
echo "<p><a href='/api/search-careers?q=Doctor' target='_blank'>Search: Doctor</a></p>";
echo "<p><a href='/api/search-careers?q=Engineer' target='_blank'>Search: Engineer</a></p>";
echo "<p><a href='/api/search-careers?q=TUT' target='_blank'>Search: TUT (should show careers)</a></p>";
