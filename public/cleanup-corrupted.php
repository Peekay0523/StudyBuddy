<?php
/**
 * Clean up corrupted career recommendations
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h1>Clean Up Corrupted Data</h1>";

// Check career recommendations for garbage data
$stmt = $db->query("
    SELECT cr.id, cr.student_id, cr.report_card_id, cr.recommended_careers, rc.file_path
    FROM career_recommendations cr
    JOIN report_cards rc ON cr.report_card_id = rc.id
");

$corrupted = [];
while ($row = $stmt->fetch()) {
    $careers = json_decode($row['recommended_careers'], true) ?? [];
    
    // Check if careers contain PDF garbage
    if (is_array($careers)) {
        foreach ($careers as $career) {
            if (is_string($career) && (
                strpos($career, 'TreeRoot') !== false || 
                strpos($career, 'FontDescriptor') !== false ||
                strpos($career, 'Length') !== false ||
                strlen($career) > 200)) {
                $corrupted[] = $row;
                break;
            }
        }
    }
}

if (empty($corrupted)) {
    echo "<p style='color:green;'>✓ No corrupted career recommendations found!</p>";
} else {
    echo "<p style='color:red;'><strong>Found " . count($corrupted) . " corrupted career recommendation(s)</strong></p>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Student ID</th><th>Report Card ID</th><th>File</th></tr>";
    
    foreach ($corrupted as $row) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['student_id']}</td>";
        echo "<td>{$row['report_card_id']}</td>";
        echo "<td>{$row['file_path']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>Delete Corrupted Data?</h2>";
    echo "<form method='post'>";
    echo "<button type='submit' name='delete' value='1' style='background:#dc3545; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;'>Delete All Corrupted Career Recommendations</button>";
    echo "</form>";
    
    if (isset($_POST['delete'])) {
        foreach ($corrupted as $row) {
            $db->prepare("DELETE FROM career_recommendations WHERE id = ?")->execute([$row['id']]);
            echo "<p style='color:green;'>✓ Deleted career recommendation ID: {$row['id']}</p>";
        }
        echo "<p><strong>Done! You can now re-upload the report cards.</strong></p>";
        echo "<a href='/upload-report-card' style='display:inline-block; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Upload Report Card</a>";
    }
}

// Also check report cards with garbage grades_data
echo "<h2>Checking Report Cards</h2>";
$rcStmt = $db->query("SELECT id, student_id, file_path, grades_data FROM report_cards");
$corruptedRC = [];

while ($row = $rcStmt->fetch()) {
    $grades = json_decode($row['grades_data'], true) ?? [];
    
    // Check if grades data contains PDF garbage
    if (is_array($grades)) {
        foreach ($grades as $subject => $grade) {
            if ((is_string($subject) && (
                strpos($subject, 'TreeRoot') !== false || 
                strpos($subject, 'Font') !== false)) ||
                (is_string($grade) && strpos($grade, 'Stream') !== false) ||
                is_array($grade)) {
                $corruptedRC[] = $row;
                break;
            }
        }
    }
}

if (!empty($corruptedRC)) {
    echo "<p style='color:red;'><strong>Found " . count($corruptedRC) . " corrupted report card(s)</strong></p>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Student ID</th><th>File</th><th>Action</th></tr>";
    
    foreach ($corruptedRC as $row) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['student_id']}</td>";
        echo "<td>{$row['file_path']}</td>";
        echo "<td><a href='?delete_rc={$row['id']}' onclick='return confirm(\"Delete this corrupted report card?\")' style='color:red;'>Delete</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if (isset($_GET['delete_rc'])) {
        $id = (int)$_GET['delete_rc'];
        $db->prepare("DELETE FROM career_recommendations WHERE report_card_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM report_cards WHERE id = ?")->execute([$id]);
        echo "<p style='color:green;'><strong>✓ Deleted report card ID: $id</strong></p>";
        echo "<p><a href='cleanup-corrupted.php'>Refresh page</a></p>";
    }
    
    echo "<p><strong>After deleting, re-upload the report cards from the upload page.</strong></p>";
    echo "<p><a href='/upload-report-card' style='display:inline-block; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Upload Report Card</a></p>";
} else {
    echo "<p style='color:green;'>✓ No corrupted report cards found!</p>";
}

echo "<hr><p><a href='/dashboard'>← Back to Dashboard</a></p>";
?>
