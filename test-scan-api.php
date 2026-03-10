<?php
/**
 * API Test Page - Shows exactly what the scan API returns
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html');

echo "<h1>Scan API Diagnostic Test</h1>";

echo "<h2>Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red; font-size: 18px;'>⚠️ NOT LOGGED IN! Please <a href='/login'>login first</a></p>";
    exit;
}

echo "<p style='color:green;'>✓ Logged in as: " . htmlspecialchars($_SESSION['user']['username'] ?? 'Unknown') . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";

// Check scan limit
$user = getCurrentUser();
$canScan = canUserScan($user['id']);
$scanInfo = getScanLimitInfo($user['id']);
echo "<p>Can Scan: " . ($canScan ? 'YES' : 'NO') . "</p>";
echo "<p>Scan Info: <pre>" . print_r($scanInfo, true) . "</pre></p>";

?>

<h2>Test Upload Form</h2>
<div id="result"></div>

<form id="testForm" style="border: 2px solid #4CAF50; padding: 20px; margin: 20px 0;">
    <input type="file" id="imageInput" name="images[]" accept="image/*" multiple required>
    <button type="submit">Test Conversion</button>
</form>

<script>
document.getElementById('testForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const fileInput = document.getElementById('imageInput');
    const files = fileInput.files;
    
    if (files.length === 0) {
        alert('Please select a file');
        return;
    }
    
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('images[]', files[i]);
    }
    
    document.getElementById('result').innerHTML = '<p>Uploading...</p>';
    
    try {
        console.log('Sending request...');
        const response = await fetch('/api/scan-to-pdf', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        console.log('Response status:', response.status);
        console.log('Response OK:', response.ok);
        console.log('Response headers:', [...response.headers.entries()]);
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            document.getElementById('result').innerHTML = 
                '<p style="color:red;"><strong>Parse Error:</strong> ' + e.message + '</p>' +
                '<pre>' + text.substring(0, 500) + '</pre>';
            return;
        }
        
        console.log('Parsed data:', data);
        
        if (data.success) {
            document.getElementById('result').innerHTML = 
                '<p style="color:green; font-size: 18px;"><strong>✓ SUCCESS!</strong></p>' +
                '<p>Scan ID: <strong>' + data.scan_id + '</strong></p>' +
                '<p>Download URL: <a href="' + data.download_url + '">' + data.download_url + '</a></p>';
        } else {
            document.getElementById('result').innerHTML = 
                '<p style="color:red;"><strong>✗ API Error:</strong> ' + (data.error || 'Unknown error') + '</p>';
        }
    } catch (error) {
        console.error('Fetch error:', error);
        document.getElementById('result').innerHTML = 
            '<p style="color:red;"><strong>✗ Fetch Error:</strong> ' + error.message + '</p>';
    }
});
</script>

<h2>Recent Scans in Database</h2>
<?php
try {
    $userId = $_SESSION['user_id'];
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, filename, file_size, created_at, is_saved FROM scans WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    $scans = $stmt->fetchAll();
    
    if (empty($scans)) {
        echo "<p>No scans found in database for your user.</p>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Filename</th><th>Size</th><th>Date</th><th>Saved</th><th>Action</th></tr>";
        foreach ($scans as $scan) {
            echo "<tr>";
            echo "<td>{$scan['id']}</td>";
            echo "<td>{$scan['filename']}</td>";
            echo "<td>" . number_format($scan['file_size']) . " bytes</td>";
            echo "<td>{$scan['created_at']}</td>";
            echo "<td>" . ($scan['is_saved'] ? 'Yes' : 'No') . "</td>";
            echo "<td><a href='/download-scan/{$scan['id']}'>Download</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
