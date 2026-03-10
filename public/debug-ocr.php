<?php
/**
 * Debug OCR Test
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/TesseractHelper.php';

echo "<h1>OCR Debug Test</h1>";

// Test Tesseract
$tesseract = new TesseractHelper();
echo "<h2>1. Tesseract Helper</h2>";
echo "<p>Tesseract Path: " . htmlspecialchars($tesseract->isAvailable() ? 'Found' : 'Not Found') . "</p>";

// Test if we can get version
$versionCmd = '"' . (new ReflectionClass($tesseract))->getProperty('tesseractPath')->getValue($tesseract) . '" --version 2>&1';
echo "<p>Version command: <code>" . htmlspecialchars($versionCmd) . "</code></p>";

$version = @shell_exec($versionCmd);
echo "<pre>" . htmlspecialchars($version ?: 'No output') . "</pre>";

// Test ImageMagick
echo "<h2>2. ImageMagick Check</h2>";
$magickPaths = [
    'C:\Program Files\ImageMagick-7.1.2-Q16\magick.exe',
    'C:\Program Files\ImageMagick\magick.exe',
    'magick.exe'
];

foreach ($magickPaths as $path) {
    $cmd = "\"$path\" --version 2>&1";
    $output = @shell_exec($cmd);
    echo "<p><code>$path</code>: ";
    if ($output && stripos($output, 'imagemagick') !== false) {
        echo "<strong style='color:green'>✓ Found</strong>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 200)) . "</pre>";
    } else {
        echo "<strong style='color:red'>✗ Not found</strong>";
    }
    echo "</p>";
}

// Test with a sample PDF if provided
if (isset($_GET['test_pdf'])) {
    $testPdf = __DIR__ . '/../uploads/scripts/' . basename($_GET['test_pdf']);
    
    if (file_exists($testPdf)) {
        echo "<h2>3. Testing OCR on PDF</h2>";
        echo "<p>Testing: $testPdf</p>";
        
        $result = $tesseract->extractTextFromPdf($testPdf);
        
        if ($result) {
            echo "<div style='background:#d4edda; padding:15px; border-radius:5px;'>";
            echo "<strong>✓ OCR Successful!</strong>";
            echo "<p>Extracted " . strlen($result) . " characters</p>";
            echo "<pre style='max-height:300px; overflow:auto;'>" . htmlspecialchars(substr($result, 0, 1000)) . "...</pre>";
            echo "</div>";
        } else {
            echo "<div style='background:#f8d7da; padding:15px; border-radius:5px;'>";
            echo "<strong>✗ OCR Failed</strong>";
            echo "<p>Check error logs for details</p>";
            echo "</div>";
        }
    } else {
        echo "<p style='color:red'>PDF not found: $testPdf</p>";
    }
}

echo "<h2>4. Test Commands</h2>";
echo "<p>Try running these commands manually:</p>";
echo "<ul>";
echo "<li><code>tesseract --version</code></li>";
echo "<li><code>\"C:\\Program Files\\ImageMagick-7.1.2-Q16\\magick.exe\" --version</code></li>";
echo "</ul>";

echo "<h2>5. Shell Exec Test</h2>";
$testCmd = "tesseract --version 2>&1";
echo "<p>Running: <code>$testCmd</code></p>";
$output = @shell_exec($testCmd);
if ($output) {
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
} else {
    echo "<p style='color:red'>No output from shell_exec</p>";
    echo "<p>Check if shell_exec is enabled:</p>";
    echo "<p><code>shell_exec disabled: " . (strpos(ini_get('disable_functions'), 'shell_exec') !== false ? 'YES' : 'NO') . "</code></p>";
}
?>
