<?php
/**
 * Direct OCR Test
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/AIHelper.php';

echo "<h1>Direct OCR Test</h1>";

// Get the latest uploaded report card
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM report_cards ORDER BY uploaded_at DESC LIMIT 1");
$reportCard = $stmt->fetch();

if (!$reportCard) {
    echo "<p>No report cards found!</p>";
    exit;
}

$filePath = UPLOAD_DIR_REPORT_CARDS . $reportCard['file_path'];

echo "<h2>Testing Report Card</h2>";
echo "<p><strong>ID:</strong> {$reportCard['id']}</p>";
echo "<p><strong>File:</strong> {$reportCard['file_path']}</p>";
echo "<p><strong>Full Path:</strong> $filePath</p>";
echo "<p><strong>Exists:</strong> " . (file_exists($filePath) ? "YES ✓" : "NO ✗") . "</p>";
echo "<p><strong>File Size:</strong> " . (file_exists($filePath) ? filesize($filePath) : 0) . " bytes</p>";

if (!file_exists($filePath)) {
    echo "<p style='color:red;'>File not found!</p>";
    exit;
}

// Test ImageMagick conversion
echo "<h2>ImageMagick Conversion Test</h2>";

$tempDir = sys_get_temp_dir() . '\\ocr_test_' . uniqid();
mkdir($tempDir, 0755, true);

$magickPath = 'C:\Program Files\ImageMagick-7.1.2-Q16\magick.exe';
$imagePath = $tempDir . '\\test_page.jpg';

echo "<p><strong>ImageMagick:</strong> $magickPath</p>";
echo "<p><strong>Exists:</strong> " . (file_exists($magickPath) ? "YES ✓" : "NO ✗") . "</p>";

if (file_exists($magickPath)) {
    // Escape [0] properly for Windows
    $escapedFilePath = str_replace('[', '\\[', $filePath);
    $convertCmd = "\"$magickPath\" -density 150 -quality 85 \"{$escapedFilePath}[0]\" \"$imagePath\" 2>&1";
    echo "<p><strong>Command:</strong> <code>$convertCmd</code></p>";
    
    echo "<p><strong>Running...</strong></p>";
    $output = shell_exec($convertCmd);
    
    if ($output) {
        echo "<p><strong>Output:</strong></p><pre style='background:#f0f0f0;padding:10px;'>$output</pre>";
    }
    
    if (file_exists($imagePath)) {
        echo "<p style='color:green;'><strong>✓ Image created successfully!</strong></p>";
        echo "<p><strong>Image Size:</strong> " . filesize($imagePath) . " bytes</p>";
        
        $imgInfo = @getimagesize($imagePath);
        if ($imgInfo) {
            echo "<p><strong>Dimensions:</strong> {$imgInfo[0]}x{$imgInfo[1]} pixels</p>";
            echo "<p><strong>Type:</strong> {$imgInfo['mime']}</p>";
        }
        
        // Show the image
        echo "<p><strong>Preview:</strong></p>";
        echo "<img src='data:image/jpeg;base64," . base64_encode(file_get_contents($imagePath)) . "' style='max-width:500px; border:1px solid #ccc;'>";
        
        // Test OpenAI Vision
        echo "<h2>OpenAI Vision Test</h2>";
        $aiHelper = new AIHelper();
        
        echo "<p>Sending to OpenAI Vision API...</p>";
        $imageData = file_get_contents($imagePath);
        $extractedText = $aiHelper->extractTextFromImage($imageData, 'image/jpeg');
        
        if ($extractedText) {
            echo "<div style='background:#d4edda; padding:15px; border-radius:5px; margin:10px 0;'>";
            echo "<strong>✓ OCR Successful!</strong>";
            echo "<p><strong>Extracted " . strlen($extractedText) . " characters</strong></p>";
            echo "<pre style='max-height:400px; overflow:auto;'>" . htmlspecialchars($extractedText) . "</pre>";
            echo "</div>";
        } else {
            echo "<div style='background:#f8d7da; padding:15px; border-radius:5px;'>";
            echo "<strong>✗ OpenAI Vision returned no text</strong>";
            echo "</div>";
        }
        
    } else {
        echo "<p style='color:red;'><strong>✗ Image was NOT created</strong></p>";
        echo "<p>ImageMagick needs Ghostscript to convert PDFs. Install Ghostscript or use a different approach.</p>";
    }
}

// Cleanup
@unlink($imagePath);
@rmdir($tempDir);

echo "<hr><p><a href='javascript:history.back()'>← Back</a></p>";
?>
