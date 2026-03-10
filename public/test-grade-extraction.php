<?php
/**
 * Test what grades are being extracted
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/FileHelper.php';
require_once __DIR__ . '/../helpers/AIHelper.php';

$db = Database::getInstance()->getConnection();

// Get latest report card
$stmt = $db->query("SELECT * FROM report_cards ORDER BY uploaded_at DESC LIMIT 1");
$rc = $stmt->fetch();

if (!$rc) {
    echo "No report cards found!";
    exit;
}

echo "<h1>Testing Grade Extraction</h1>";
echo "<p><strong>Report Card ID:</strong> {$rc['id']}</p>";

$filePath = UPLOAD_DIR_REPORT_CARDS . $rc['file_path'];

echo "<p><strong>File:</strong> {$rc['file_path']}</p>";
echo "<p><strong>Exists:</strong> " . (file_exists($filePath) ? "YES" : "NO") . "</p>";

// Extract text
echo "<h2>Extracted Text (FileHelper)</h2>";
$textContent = FileHelper::extractTextFromFile($filePath);
echo "<pre style='background:#f0f0f0; padding:15px; max-height:400px; overflow:auto;'>" . htmlspecialchars(substr($textContent ?: 'NULL', 0, 2000)) . "</pre>";

// Check if garbage
$isGarbage = false;
if (!empty($textContent)) {
    if (strpos($textContent, '%PDF-') !== false ||
        strpos($textContent, 'TreeRoot') !== false ||
        strpos($textContent, 'FontDescriptor') !== false) {
        $isGarbage = true;
    }
}
echo "<p><strong>Is Garbage:</strong> " . ($isGarbage ? "YES" : "NO") . "</p>";

// Extract grades
echo "<h2>Extracted Grades</h2>";
$gradesData = FileHelper::extractGradesFromText($textContent);
echo "<pre>" . json_encode($gradesData, JSON_PRETTY_PRINT) . "</pre>";

if (empty($gradesData)) {
    echo "<p style='color:red;'><strong>NO GRADES EXTRACTED!</strong> This is why you see default values.</p>";
}

// Test OpenAI Vision
echo "<h2>Testing OpenAI Vision OCR</h2>";
$aiHelper = new AIHelper();

$tempDir = sys_get_temp_dir() . '\\test_ocr_' . uniqid();
mkdir($tempDir, 0755, true);

$magickPath = 'C:\Program Files\ImageMagick-7.1.2-Q16\magick.exe';
$imagePath = $tempDir . '\\test.jpg';
$escapedFilePath = str_replace('[', '\\[', $filePath);
$convertCmd = "\"$magickPath\" -density 150 -quality 85 \"{$escapedFilePath}[0]\" \"$imagePath\" 2>&1";

echo "<p><strong>Converting PDF to image...</strong></p>";
shell_exec($convertCmd);

if (file_exists($imagePath)) {
    echo "<p style='color:green;'><strong>✓ Image created!</strong></p>";
    
    $imageData = file_get_contents($imagePath);
    $ocrText = $aiHelper->extractTextFromImage($imageData, 'image/jpeg');
    
    echo "<h2>OpenAI Vision OCR Text</h2>";
    echo "<pre style='background:#f0f0f0; padding:15px; max-height:400px; overflow:auto;'>" . htmlspecialchars($ocrText ?: 'NULL') . "</pre>";
    
    echo "<h2>Grades from OpenAI Text</h2>";
    $ocrGrades = FileHelper::extractGradesFromText($ocrText);
    echo "<pre>" . json_encode($ocrGrades, JSON_PRETTY_PRINT) . "</pre>";
    
    @unlink($imagePath);
} else {
    echo "<p style='color:red;'>Failed to create image</p>";
}

@rmdir($tempDir);

echo "<hr><p><a href='javascript:history.back()'>← Back</a></p>";
?>
