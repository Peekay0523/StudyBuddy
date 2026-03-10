<?php
/**
 * Test Tesseract OCR Setup
 * Run this file to verify Tesseract is properly installed and configured
 */

require_once __DIR__ . '/../helpers/TesseractHelper.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Tesseract OCR Test - StudySmart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: #004085; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        .step { background: #fff; border-left: 4px solid #007bff; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔍 Tesseract OCR Setup Test</h1>
";

$tesseract = new TesseractHelper();

echo "<h2>1. Tesseract Availability Check</h2>";

if ($tesseract->isAvailable()) {
    echo "<div class='success'>";
    echo "<strong>✓ Tesseract is installed and accessible!</strong><br>";
    echo "Version info:<br><pre>" . htmlspecialchars($tesseract->getVersion()) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<strong>✗ Tesseract is NOT available</strong><br><br>";
    echo "The Tesseract OCR executable was not found. This could mean:<br>";
    echo "<ul>";
    echo "<li>Tesseract is not installed</li>";
    echo "<li>Tesseract is not in your system PATH</li>";
    echo "<li>The TESSERACT_PATH environment variable is not set correctly</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>How to Install Tesseract on Windows</h2>";
    echo "<div class='step'>";
    echo "<h3>Step 1: Download Tesseract</h3>";
    echo "<p>Download the installer from: <a href='https://github.com/UB-Mannheim/tesseract/wiki' target='_blank'>https://github.com/UB-Mannheim/tesseract/wiki</a></p>";
    echo "<p>Recommended: Download <code>tesseract-ocr-w64-setup-5.x.x.exe</code> (64-bit)</p>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 2: Install Tesseract</h3>";
    echo "<p>Run the installer and follow these steps:</p>";
    echo "<ol>";
    echo "<li>Accept the license agreement</li>";
    echo "<li>Choose installation directory (default: <code>C:\\Program Files\\Tesseract-OCR</code>)</li>";
    echo "<li><strong>Important:</strong> Check 'Additional language data' and select the languages you need (e.g., English)</li>";
    echo "<li>Complete the installation</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 3: Add to PATH (if needed)</h3>";
    echo "<p>Tesseract installer usually adds itself to PATH automatically. If not:</p>";
    echo "<ol>";
    echo "<li>Press <code>Win + R</code>, type <code>sysdm.cpl</code>, press Enter</li>";
    echo "<li>Click 'Advanced' tab → 'Environment Variables'</li>";
    echo "<li>Under 'System variables', find 'Path' and click 'Edit'</li>";
    echo "<li>Click 'New' and add: <code>C:\\Program Files\\Tesseract-OCR</code></li>";
    echo "<li>Click OK to save</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 4: Set TESSERACT_PATH Environment Variable (Optional)</h3>";
    echo "<p>In the Environment Variables window:</p>";
    echo "<ol>";
    echo "<li>Under 'System variables', click 'New'</li>";
    echo "<li>Variable name: <code>TESSERACT_PATH</code></li>";
    echo "<li>Variable value: <code>C:\\Program Files\\Tesseract-OCR\\tesseract.exe</code></li>";
    echo "<li>Click OK to save</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 5: Restart Your Web Server</h3>";
    echo "<p>After installing Tesseract and updating PATH:</p>";
    echo "<ul>";
    echo "<li>If using PHP built-in server: Stop it (Ctrl+C) and run <code>php -S localhost:8000 -t public</code> again</li>";
    echo "<li>If using Apache/Nginx: Restart the service</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h2>2. ImageMagick Check (for PDF to Image conversion)</h2>";

// Check if ImageMagick is available
$imagickInstalled = extension_loaded('imagick');
$convertAvailable = false;

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $output = @shell_exec('"C:\Program Files\ImageMagick\magick.exe" --version 2>&1');
    $convertAvailable = ($output && stripos($output, 'imagemagick') !== false);
} else {
    $output = @shell_exec('which convert 2>&1');
    $convertAvailable = !empty(trim($output));
}

if ($imagickInstalled) {
    echo "<div class='success'>";
    echo "<strong>✓ PHP Imagick extension is installed</strong><br>";
    $imagickVersion = phpversion('imagick');
    echo "Imagick version: <code>$imagickVersion</code>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<strong>⚠ PHP Imagick extension is NOT installed</strong><br><br>";
    echo "Imagick is needed to convert PDF pages to images for OCR processing.<br>";
    echo "To install: <code>pecl install imagick</code> (Linux) or download from Windows PECL repository";
    echo "</div>";
}

if ($convertAvailable && !$imagickInstalled) {
    echo "<div class='info'>";
    echo "<strong>ℹ ImageMagick CLI is available</strong><br>";
    echo "The <code>magick</code> or <code>convert</code> command is available, which Tesseract can use for PDF conversion.";
    echo "</div>";
} elseif (!$convertAvailable) {
    echo "<div class='warning'>";
    echo "<strong>⚠ ImageMagick CLI is NOT available</strong><br><br>";
    echo "To install ImageMagick on Windows:<br>";
    echo "<ol>";
    echo "<li>Download from: <a href='https://imagemagick.org/script/download.php#windows' target='_blank'>https://imagemagick.org</a></li>";
    echo "<li>Run the installer and follow the prompts</li>";
    echo "<li>Check 'Install legacy utilities (e.g. convert)' if prompted</li>";
    echo "<li>Restart your web server after installation</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h2>2. Summary</h2>";

$canUseOcr = $tesseract->isAvailable() && ($convertAvailable || $imagickInstalled);

if ($canUseOcr) {
    echo "<div class='success'>";
    echo "<strong>✓ Your system is ready for OCR!</strong><br><br>";
    echo "Configuration:<br>";
    echo "<ul>";
    echo "<li><strong>Primary OCR:</strong> Tesseract (local, free)</li>";
    echo "<li><strong>PDF Conversion:</strong> " . ($imagickInstalled ? "PHP Imagick" : "ImageMagick CLI") . "</li>";
    echo "<li><strong>Fallback:</strong> " . ($tesseract->isAvailable() ? "OpenAI Vision API" : "None") . "</li>";
    echo "</ul>";
    echo "<br>You can now upload image-based PDFs and the system will automatically extract text using OCR.";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<strong>✗ OCR is not fully configured</strong><br><br>";
    echo "Missing components:<br>";
    echo "<ul>";
    if (!$tesseract->isAvailable()) {
        echo "<li>❌ Tesseract OCR</li>";
    }
    if (!$convertAvailable && !$imagickInstalled) {
        echo "<li>❌ ImageMagick (for PDF to image conversion)</li>";
    }
    echo "</ul>";
    echo "<br>Once you install the missing components above, OCR will work automatically.";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h3>What happens after OCR is configured?</h3>";
echo "<p>When you upload an image-based PDF (scanned document):</p>";
echo "<ol>";
echo "<li>The system detects it's an image-based PDF</li>";
echo "<li>PDF pages are converted to images</li>";
echo "<li>Tesseract OCR extracts text from the images</li>";
echo "<li>The extracted text is used to generate the memorandum</li>";
echo "</ol>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>Testing with a real PDF</h3>";
echo "<p>To test OCR with an actual PDF:</p>";
echo "<ol>";
echo "<li>Upload a scanned/image-based PDF through the normal upload interface</li>";
echo "<li>Click 'Generate Memorandum'</li>";
echo "<li>If OCR works, you'll see the extracted text and generated memorandum</li>";
echo "<li>Check the error logs for detailed OCR processing information</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
