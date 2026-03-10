<?php
/**
 * Check PDF Text Extraction
 * Access: http://localhost:8000/check-pdf-extraction
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/FileHelper.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check PDF Extraction</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .status-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .status-success {
            background: #f0fdf4;
            color: #166534;
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #bbf7d0;
        }
        .status-error {
            background: #fef2f2;
            color: #dc2626;
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #fecaca;
        }
        .status-info {
            background: #eff6ff;
            color: #1e40af;
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #bfdbfe;
        }
        .test-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 5px;
        }
    </style>
</head>
<body>

<div class="test-container">
    <h1 style="color: #1f2937;">
        <i class="fas fa-file-pdf"></i> PDF Text Extraction Check
    </h1>
    <p style="color: #6b7280;">
        This page checks if your system can extract text from PDF files
    </p>

    <div class="status-box">
        <h3 style="margin-top: 0; color: #1f2937;">
            <i class="fas fa-cog"></i> System Status
        </h3>
        
        <?php
        // Check 1: PDFParser library
        if (class_exists('Smalot\PdfParser\Parser')) {
            echo '<div class="status-success"><i class="fas fa-check-circle"></i> <strong>PDFParser Library:</strong> Installed ✅</div>';
        } else {
            echo '<div class="status-error"><i class="fas fa-times-circle"></i> <strong>PDFParser Library:</strong> NOT Installed ❌</div>';
            echo '<p style="margin-top: 10px; font-size: 14px;">To install, run: <code style="background: #fef3c7; padding: 2px 6px; border-radius: 4px;">composer require smalot/pdf-parser</code></p>';
        }
        
        // Check 2: GD Library
        if (function_exists('imagecreatefromstring')) {
            echo '<div class="status-success"><i class="fas fa-check-circle"></i> <strong>GD Library:</strong> Enabled ✅</div>';
        } else {
            echo '<div class="status-error"><i class="fas fa-times-circle"></i> <strong>GD Library:</strong> Disabled ❌</div>';
        }
        
        // Check 3: ZipArchive (for DOCX)
        if (class_exists('ZipArchive')) {
            echo '<div class="status-success"><i class="fas fa-check-circle"></i> <strong>ZipArchive:</strong> Available ✅</div>';
        } else {
            echo '<div class="status-error"><i class="fas fa-times-circle"></i> <strong>ZipArchive:</strong> NOT Available ❌</div>';
        }
        ?>
    </div>

    <div class="test-section">
        <h3 style="margin-top: 0; color: #1f2937;">
            <i class="fas fa-info-circle"></i> Understanding PDF Types
        </h3>
        
        <h4>✅ Text-based PDFs (Can extract text)</h4>
        <ul style="color: #64748b; line-height: 1.8;">
            <li>PDFs exported from Word, Google Docs, etc.</li>
            <li>PDFs with selectable text</li>
            <li>Digitally generated PDFs</li>
        </ul>
        
        <h4>❌ Image-based PDFs (Cannot extract text)</h4>
        <ul style="color: #64748b; line-height: 1.8;">
            <li>Scanned documents (photos of papers)</li>
            <li>PDFs created from images without OCR</li>
            <li>Photocopies saved as PDF</li>
        </ul>
        
        <div class="status-info" style="margin-top: 15px;">
            <strong>💡 Solution for Image-based PDFs:</strong>
            <ul style="margin-top: 10px; margin-left: 20px;">
                <li>Use OCR (Optical Character Recognition) software</li>
                <li>Re-create the PDF from original text document</li>
                <li>Use online tools like SmallPDF or ILovePDF to convert</li>
            </ul>
        </div>
    </div>

    <div class="test-section">
        <h3 style="margin-top: 0; color: #1f2937;">
            <i class="fas fa-flask"></i> Test PDF Upload
        </h3>
        <form method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
            <input type="file" name="test_pdf" accept=".pdf" required style="margin-bottom: 10px;">
            <br>
            <button type="submit" name="test_extraction" class="btn">
                <i class="fas fa-vial"></i> Test Extraction
            </button>
        </form>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_extraction'])) {
            if (isset($_FILES['test_pdf']) && $_FILES['test_pdf']['error'] === UPLOAD_ERR_OK) {
                $tmpPath = $_FILES['test_pdf']['tmp_name'];
                $fileName = $_FILES['test_pdf']['name'];
                
                echo '<div style="margin-top: 20px; padding: 15px; background: white; border-radius: 8px;">';
                echo '<h4>Testing: ' . htmlspecialchars($fileName) . '</h4>';
                
                try {
                    $text = FileHelper::extractTextFromFile($tmpPath);
                    
                    if (!empty(trim($text))) {
                        echo '<div class="status-success">';
                        echo '<i class="fas fa-check-circle"></i> <strong>Success!</strong><br><br>';
                        echo 'Extracted ' . strlen($text) . ' characters<br><br>';
                        echo '<strong>Preview (first 500 chars):</strong><br>';
                        echo '<div style="background: #f1f5f9; padding: 10px; border-radius: 6px; margin-top: 10px; max-height: 200px; overflow-y: auto;">';
                        echo htmlspecialchars(substr($text, 0, 500)) . '...';
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<div class="status-error">';
                        echo '<i class="fas fa-times-circle"></i> <strong>Failed!</strong><br><br>';
                        echo 'Could not extract text. The PDF is likely image-based or encrypted.';
                        echo '</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="status-error">';
                    echo '<i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
                    echo '</div>';
                }
                
                echo '</div>';
            } else {
                echo '<div class="status-error">File upload failed. Please try again.</div>';
            }
        }
        ?>
    </div>

    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <a href="/upload-script" class="btn">
            <i class="fas fa-upload"></i> Upload Script
        </a>
        <a href="/check-gd-status" class="btn">
            <i class="fas fa-info-circle"></i> Check GD Status
        </a>
    </div>
</div>

</body>
</html>
