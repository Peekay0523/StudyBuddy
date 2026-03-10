<?php
require_once __DIR__ . '/../helpers/TesseractHelper.php';

echo "<h1>TesseractHelper Path Debug</h1>";

$helper = new TesseractHelper();

// Use reflection to get private properties
$reflection = new ReflectionClass($helper);

$tesseractPath = $reflection->getProperty('tesseractPath')->getValue($helper);
$tempDir = $reflection->getProperty('tempDir')->getValue($helper);

echo "<h2>Configuration</h2>";
echo "<p><strong>Tesseract Path:</strong> <code>" . htmlspecialchars($tesseractPath) . "</code></p>";
echo "<p><strong>Temp Dir:</strong> <code>" . htmlspecialchars($tempDir) . "</code></p>";
echo "<p><strong>Is Available:</strong> " . ($helper->isAvailable() ? "YES ✓" : "NO ✗") . "</p>";

echo "<h2>Testing PDF Conversion</h2>";

// Create a test to see if ImageMagick can convert PDF
if (isset($_GET['pdf'])) {
    $pdfPath = __DIR__ . '/../uploads/scripts/' . basename($_GET['pdf']);
    
    if (file_exists($pdfPath)) {
        echo "<p>Testing PDF: $pdfPath</p>";
        
        // Test ImageMagick conversion
        $tempDir = sys_get_temp_dir() . '\\tesseract_test_' . uniqid();
        mkdir($tempDir, 0755, true);
        
        $imagePattern = $tempDir . '\\page_%d.jpg';
        $convertCmd = "\"C:\\Program Files\\ImageMagick-7.1.2-Q16\\magick.exe\" -density 150 -quality 90 \"$pdfPath\" \"$imagePattern\" 2>&1";
        
        echo "<p><strong>Convert Command:</strong> <code>" . htmlspecialchars($convertCmd) . "</code></p>";
        
        $convertOutput = @shell_exec($convertCmd);
        
        $images = glob($tempDir . '\\page_*.jpg');
        
        echo "<p><strong>Images Created:</strong> " . count($images) . "</p>";
        
        if (count($images) > 0) {
            echo "<p><strong>First Image:</strong> " . htmlspecialchars($images[0]) . "</p>";
            
            // Test Tesseract on first image
            $tesseractCmd = "\"C:\\Users\\mmereko\\AppData\\Local\\Programs\\Tesseract-OCR\\tesseract.exe\" \"{$images[0]}\" \"{$tempDir}\\output\" 2>&1";
            echo "<p><strong>Tesseract Command:</strong> <code>" . htmlspecialchars($tesseractCmd) . "</code></p>";
            
            $tesseractOutput = @shell_exec($tesseractCmd);
            
            $outputFile = $tempDir . '\\output.txt';
            if (file_exists($outputFile)) {
                $text = file_get_contents($outputFile);
                echo "<div style='background:#d4edda; padding:15px; border-radius:5px;'>";
                echo "<strong>✓ OCR Successful!</strong>";
                echo "<p>Extracted " . strlen($text) . " characters</p>";
                echo "<pre style='max-height:300px; overflow:auto;'>" . htmlspecialchars(substr($text, 0, 1000)) . "...</pre>";
                echo "</div>";
            } else {
                echo "<div style='background:#f8d7da; padding:15px; border-radius:5px;'>";
                echo "<strong>✗ Tesseract Failed</strong>";
                echo "<pre>" . htmlspecialchars($tesseractOutput ?: 'No output') . "</pre>";
                echo "</div>";
            }
        } else {
            echo "<div style='background:#f8d7da; padding:15px; border-radius:5px;'>";
            echo "<strong>✗ ImageMagick Conversion Failed</strong>";
            echo "<pre>" . htmlspecialchars($convertOutput ?: 'No output') . "</pre>";
            echo "</div>";
        }
        
        // Cleanup
        foreach (glob($tempDir . '\\*') as $file) {
            @unlink($file);
        }
        @rmdir($tempDir);
    } else {
        echo "<p style='color:red'>PDF not found: $pdfPath</p>";
    }
} else {
    echo "<p>No PDF specified. Add <code>?pdf=filename.pdf</code> to test a specific file.</p>";
}
?>
