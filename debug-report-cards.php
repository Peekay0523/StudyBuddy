<?php
/**
 * Debug: Check report cards data
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>Report Cards Debug Information</h2>";

$db = Database::getInstance()->getConnection();

// Check report_cards table structure
echo "<h3>report_cards table structure</h3>";
echo "<pre>";
$columns = $db->query("PRAGMA table_info(report_cards)")->fetchAll();
print_r($columns);
echo "</pre>";

// Count records
echo "<h3>Total Report Cards</h3>";
echo "<p>" . $db->query("SELECT COUNT(*) FROM report_cards")->fetchColumn() . "</p>";

// Show all records
echo "<h3>All Report Cards</h3>";
echo "<pre>";
$reportCards = $db->query("SELECT * FROM report_cards ORDER BY uploaded_at DESC LIMIT 10")->fetchAll();
print_r($reportCards);
echo "</pre>";

// Try the admin query
echo "<h3>Admin Query (with user join)</h3>";
echo "<pre>";
try {
    $query = "
        SELECT rc.*, u.username, u.email
        FROM report_cards rc
        JOIN users u ON rc.user_id = u.id
        ORDER BY rc.uploaded_at DESC
    ";
    $reportCards = $db->query($query)->fetchAll();
    print_r($reportCards);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
echo "</pre>";

// Check if there's a student_id column issue
echo "<h3>Check column names</h3>";
echo "<pre>";
$firstRecord = $db->query("SELECT * FROM report_cards LIMIT 1")->fetch();
if ($firstRecord) {
    echo "Columns in first record:\n";
    foreach ($firstRecord as $key => $value) {
        echo "$key => " . (is_null($value) ? 'NULL' : $value) . "\n";
    }
}
echo "</pre>";
