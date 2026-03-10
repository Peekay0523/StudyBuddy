<?php
/**
 * Direct test for scan conversion - bypasses the frontend
 * Upload an image file directly via POST to test the conversion
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html');

echo "<h1>Scan Conversion Direct Test</h1>";

// Check session
echo "<h2>1. Session Check</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red'>NOT LOGGED IN! Please login first.</p>";
    echo "<p><a href='/login'>Go to Login</a></p>";
    exit;
}

// Check if file was uploaded
echo "<h2>2. File Upload Check</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    $image = $_FILES['test_image'];
    echo "<pre>";
    print_r($image);
    echo "</pre>";
    
    if ($image['error'] === UPLOAD_ERR_OK) {
        echo "<p style='color:green'>File uploaded successfully!</p>";
        
        // Try to process it
        try {
            $user = getCurrentUser();
            echo "<p>Current User: " . $user['username'] . " (ID: {$user['id']})</p>";
            
            // Check scan limit
            if (!canUserScan($user['id'])) {
                echo "<p style='color:red'>Scan limit reached!</p>";
            } else {
                echo "<p style='color:green'>Scan limit OK</p>";
            }
            
            // Read image
            $imageBinary = file_get_contents($image['tmp_name']);
            $imageType = mime_content_type($image['tmp_name']);
            $imgInfo = @getimagesize($image['tmp_name']);
            
            echo "<p>Image Type: $imageType</p>";
            echo "<p>Image Size: " . strlen($imageBinary) . " bytes</p>";
            echo "<p>Image Dimensions: " . ($imgInfo ? $imgInfo[0] . "x" . $imgInfo[1] : 'UNKNOWN') . "</p>";
            
            // Generate PDF
            require_once __DIR__ . '/controllers/ScanController.php';
            $pdf = new PurePhpPdf();
            $pdf->addPage($imgInfo[0], $imgInfo[1], $imageBinary, $imageType);
            $pdfContent = $pdf->generate();
            
            echo "<p>PDF Generated: " . strlen($pdfContent) . " bytes</p>";
            
            // Save to database
            $db = Database::getInstance()->getConnection();
            $pdfFilename = 'test_scan_' . date('Y-m-d_H-i-s') . '.pdf';
            $stmt = $db->prepare("
                INSERT INTO scans (user_id, filename, original_filename, file_data, file_size, mime_type, is_saved)
                VALUES (?, ?, ?, ?, ?, 'application/pdf', 0)
            ");
            $stmt->execute([$user['id'], $pdfFilename, $pdfFilename, $pdfContent, strlen($pdfContent)]);
            $scanId = $db->lastInsertId();
            
            echo "<p style='color:green; font-size: 20px;'>SUCCESS! Scan ID: <strong>$scanId</strong></p>";
            echo "<p><a href='/download-scan/$scanId'>Download PDF</a></p>";
            
        } catch (Exception $e) {
            echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    } else {
        echo "<p style='color:red'>Upload error code: " . $image['error'] . "</p>";
    }
} else {
    echo "<p>No file uploaded yet. Use the form below:</p>";
}

echo "<h2>3. Test Upload Form</h2>";
echo "<form method='POST' enctype='multipart/form-data' style='border: 2px solid blue; padding: 20px; margin: 20px 0;'>";
echo "<input type='file' name='test_image' accept='image/*' required>";
echo "<button type='submit'>Test Conversion</button>";
echo "</form>";

echo "<h2>4. Recent Scans for Your User</h2>";
try {
    $userId = $_SESSION['user_id'];
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, filename, file_size, created_at FROM scans WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$userId]);
    $scans = $stmt->fetchAll();
    
    if (empty($scans)) {
        echo "<p>No scans found for your user.</p>";
    } else {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Filename</th><th>Size</th><th>Date</th><th>Action</th></tr>";
        foreach ($scans as $scan) {
            echo "<tr>";
            echo "<td>{$scan['id']}</td>";
            echo "<td>{$scan['filename']}</td>";
            echo "<td>{$scan['file_size']} bytes</td>";
            echo "<td>{$scan['created_at']}</td>";
            echo "<td><a href='/download-scan/{$scan['id']}'>Download</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
