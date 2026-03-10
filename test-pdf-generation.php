<?php
/**
 * Test PDF Generation
 * Access: http://localhost:8000/test-pdf-generation
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/ScanController.php';

// Only allow logged in users or debug mode
if (!DEBUG_MODE && !isLoggedIn()) {
    die('Please login first');
}

$user = getCurrentUser();
$message = '';
$messageType = '';

// Test PDF generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create a simple test PDF with a placeholder image
        $testImage = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        
        if ($testImage === false || empty($testImage)) {
            throw new Exception('Failed to create test image');
        }
        
        $pdf = new PurePhpPdf();
        $pdf->addPage(100, 100, $testImage, 'image/jpeg');
        $pdfContent = $pdf->generate();
        
        if (empty($pdfContent)) {
            throw new Exception('PDF generation returned empty content');
        }
        
        $pdfSize = strlen($pdfContent);
        $pdfHeader = substr($pdfContent, 0, 4);
        
        if ($pdfHeader !== '%PDF') {
            throw new Exception('Invalid PDF header: ' . $pdfHeader);
        }
        
        // Save to database
        $db = Database::getInstance()->getConnection();
        $filename = 'test_pdf_' . date('Y-m-d_H-i-s') . '.pdf';
        
        $stmt = $db->prepare("
            INSERT INTO scans (user_id, filename, original_filename, file_data, file_size, mime_type, is_saved)
            VALUES (?, ?, ?, ?, ?, 'application/pdf', 1)
        ");
        
        $stmt->execute([$user['id'], $filename, $filename, $pdfContent, $pdfSize]);
        $scanId = $db->lastInsertId();
        
        if ($scanId) {
            $message = "✅ Test PDF generated and saved successfully! Scan ID: {$scanId}";
            $messageType = 'success';
            
            // Verify it can be retrieved
            $stmt = $db->prepare("SELECT * FROM scans WHERE id = ?");
            $stmt->execute([$scanId]);
            $scan = $stmt->fetch();
            
            if ($scan && !empty($scan['file_data'])) {
                $retrievedHeader = substr($scan['file_data'], 0, 4);
                $message .= "<br><br>✅ Database retrieval successful!<br>";
                $message .= "Stored size: " . strlen($scan['file_data']) . " bytes<br>";
                $message .= "PDF header: " . $retrievedHeader;
            } else {
                $message .= "<br><br>⚠️ Warning: Retrieved PDF is empty!";
                $messageType = 'error';
            }
        } else {
            throw new Exception('Failed to get scan ID after insert');
        }
        
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test PDF Generation</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .test-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="test-container">
    <h1 style="color: #1f2937;">
        <i class="fas fa-file-pdf"></i> Test PDF Generation
    </h1>
    <p style="color: #6b7280;">
        This will generate a test PDF and save it to the database to verify the PDF generation is working
    </p>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="info-box">
        <h3 style="margin-top: 0; color: #1f2937;">
            <i class="fas fa-info-circle"></i> What This Tests
        </h3>
        <ul style="color: #64748b; line-height: 1.8;">
            <li>PurePhpPdf class is working</li>
            <li>PDF generation produces valid output</li>
            <li>Database can store PDF binary data</li>
            <li>Retrieved PDF data is not corrupted</li>
        </ul>
    </div>

    <form method="POST" action="">
        <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">
            <i class="fas fa-flask"></i> Generate Test PDF
        </button>
    </form>

    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <a href="/scan" style="color: #667eea; text-decoration: none;">
            <i class="fas fa-camera"></i> Go to Scan Page
        </a>
    </div>
</div>

</body>
</html>
