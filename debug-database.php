<?php
/**
 * Debug Database Connection
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Database Debug</h1>";

$db = Database::getInstance()->getConnection();

echo "<h2>1. Database Connection</h2>";
if ($db) {
    echo "<p style='color: green;'>✓ Connected successfully</p>";
} else {
    echo "<p style='color: red;'>✗ Connection failed</p>";
}

// Show database path
echo "<h2>2. Database Path</h2>";
$dbPath = __DIR__ . '/database.sqlite3';
echo "<p><strong>Expected Path:</strong> $dbPath</p>";
echo "<p><strong>File Exists:</strong> " . (file_exists($dbPath) ? 'YES' : 'NO') . "</p>";
if (file_exists($dbPath)) {
    echo "<p><strong>File Size:</strong> " . filesize($dbPath) . " bytes</p>";
    echo "<p><strong>Last Modified:</strong> " . date('Y-m-d H:i:s', filemtime($dbPath)) . "</p>";
}

// Check for multiple database files
echo "<h2>3. Searching for database files</h2>";
$files = glob(__DIR__ . '/*.sqlite3');
if (!empty($files)) {
    foreach ($files as $file) {
        echo "<p>✓ Found: $file (" . filesize($file) . " bytes)</p>";
    }
} else {
    echo "<p style='color: red;'>No .sqlite3 files found in project root!</p>";
}

// Also check in public folder
$filesPublic = glob(__DIR__ . '/public/*.sqlite3');
if (!empty($filesPublic)) {
    foreach ($filesPublic as $file) {
        echo "<p>✓ Found in public/: $file (" . filesize($file) . " bytes)</p>";
    }
}

// Query report_cards table
echo "<h2>4. Report Cards Table</h2>";
try {
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='report_cards'");
    if ($stmt->fetch()) {
        echo "<p style='color: green;'>✓ report_cards table exists</p>";
        
        // Count records
        $stmt = $db->query("SELECT COUNT(*) as count FROM report_cards");
        $count = $stmt->fetch();
        echo "<p><strong>Total Records:</strong> " . ($count['count'] ?? 0) . "</p>";
        
        // Show all records
        echo "<h3>All Report Cards:</h3>";
        $stmt = $db->query("SELECT * FROM report_cards ORDER BY id DESC");
        $reportCards = $stmt->fetchAll();
        
        if (empty($reportCards)) {
            echo "<p style='color: red;'>No report cards in database!</p>";
        } else {
            echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Student ID</th><th>File Path</th><th>Grade</th><th>Term</th><th>Uploaded At</th></tr>";
            foreach ($reportCards as $rc) {
                echo "<tr>";
                echo "<td><strong>{$rc['id']}</strong></td>";
                echo "<td>{$rc['user_id']}</td>";
                echo "<td>{$rc['student_id']}</td>";
                echo "<td>{$rc['file_path']}</td>";
                echo "<td>" . ($rc['grade'] ?: 'N/A') . "</td>";
                echo "<td>" . ($rc['term'] ?: 'N/A') . "</td>";
                echo "<td>{$rc['uploaded_at']}</td>";
                echo "</tr>";
                
                // Check if file exists
                $filePath = __DIR__ . '/uploads/report_cards/' . $rc['file_path'];
                $fileExists = file_exists($filePath) ? 'YES' : 'NO';
                echo "<tr><td colspan='7' style='background: #f5f5f5;'><strong>File Exists:</strong> $fileExists | <strong>Path:</strong> $filePath</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>✗ report_cards table does NOT exist!</p>";
        
        // Show all tables
        echo "<h3>All Tables:</h3>";
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll();
        foreach ($tables as $table) {
            echo "<p>• {$table['name']}</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Test specific query
echo "<h2>5. Test Query for ID 17</h2>";
$stmt = $db->prepare("SELECT * FROM report_cards WHERE id = ?");
$stmt->execute([17]);
$rc = $stmt->fetch();
if ($rc) {
    echo "<p style='color: green;'>✓ Found report card ID 17</p>";
    echo "<pre>" . htmlspecialchars(json_encode($rc, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "<p style='color: red;'>✗ Report card ID 17 NOT found</p>";
    
    // Show what IDs exist
    echo "<h3>Available Report Card IDs:</h3>";
    $stmt = $db->query("SELECT id, user_id, file_path, uploaded_at FROM report_cards ORDER BY id DESC");
    $allIds = $stmt->fetchAll();
    if (!empty($allIds)) {
        echo "<ul>";
        foreach ($allIds as $r) {
            echo "<li>ID: {$r['id']} | User: {$r['user_id']} | File: {$r['file_path']} | Date: {$r['uploaded_at']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No report cards at all!</p>";
    }
}

echo "<hr>";
echo "<p><a href='/upload-report-card'>Upload Report Card</a> | <a href='/list-report-cards'>List Report Cards</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h1 { color: #667eea; }
h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; margin-top: 30px; }
table { margin: 20px 0; }
th { background: #667eea; color: white; }
pre { background: #f5f5f5; padding: 15px; border-radius: 8px; overflow-x: auto; }
</style>
