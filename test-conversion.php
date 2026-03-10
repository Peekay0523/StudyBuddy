<?php
/**
 * Test script to check conversion issues
 * Access via: http://localhost:8000/test-conversion.php
 */

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Conversion Debug Test</h1>";

echo "<h2>Session Info</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Database Check</h2>";
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll();
    echo "<p>Tables found: " . count($tables) . "</p>";
    echo "<pre>";
    print_r($tables);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>File Upload Settings</h2>";
echo "<pre>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "upload_tmp_dir: " . ini_get('upload_tmp_dir') . "\n";
echo "sys_get_temp_dir: " . sys_get_temp_dir() . "\n";
echo "</pre>";

echo "<h2>GD Library</h2>";
echo "<pre>";
echo "GD available: " . (function_exists('imagecreatefromstring') ? 'YES' : 'NO') . "\n";
if (function_exists('gd_info')) {
    print_r(gd_info());
}
echo "</pre>";

echo "<h2>Permissions</h2>";
echo "<pre>";
echo "Database writable: " . (is_writable(__DIR__ . '/../database.sqlite3') ? 'YES' : 'NO') . "\n";
echo "Temp dir writable: " . (is_writable(sys_get_temp_dir()) ? 'YES' : 'NO') . "\n";
echo "</pre>";

echo "<h2>Test Database Insert</h2>";
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO scans (user_id, filename, original_filename, file_size, mime_type, is_saved) VALUES (?, ?, ?, ?, ?, ?)");
    $testUserId = $_SESSION['user_id'] ?? 1;
    $stmt->execute([$testUserId, 'test.pdf', 'test.pdf', 100, 'application/pdf', 0]);
    $scanId = $db->lastInsertId();
    echo "<p style='color:green'>Test insert successful! Scan ID: $scanId</p>";
    
    // Clean up
    $stmt = $db->prepare("DELETE FROM scans WHERE id = ?");
    $stmt->execute([$scanId]);
} catch (Exception $e) {
    echo "<p style='color:red'>Test insert failed: " . $e->getMessage() . "</p>";
}

echo "<h2>API Test Form</h2>";
echo "<form method='POST' action='/api/scan-to-pdf' enctype='multipart/form-data'>";
echo "<input type='file' name='images[]' multiple accept='image/*'>";
echo "<button type='submit'>Test Upload</button>";
echo "</form>";

?>
