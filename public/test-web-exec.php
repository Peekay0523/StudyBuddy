<?php
// Test from web context
if (php_sapi_name() !== 'cli') {
    echo "<h2>Web Server Test</h2>";
    
    // Test Tesseract with full path
    $cmd1 = "\"C:\\Users\\mmereko\\AppData\\Local\\Programs\\Tesseract-OCR\\tesseract.exe\" --version 2>&1";
    echo "<p><strong>Tesseract:</strong> </p><pre>";
    $output1 = @shell_exec($cmd1);
    echo htmlspecialchars($output1 ?: "NO OUTPUT - shell_exec may be blocked for web");
    echo "</pre>";
    
    // Test ImageMagick with full path
    $cmd2 = "\"C:\\Program Files\\ImageMagick-7.1.2-Q16\\magick.exe\" --version 2>&1";
    echo "<p><strong>ImageMagick:</strong> </p><pre>";
    $output2 = @shell_exec($cmd2);
    echo htmlspecialchars($output2 ?: "NO OUTPUT");
    echo "</pre>";
    
    // Test simple command
    echo "<p><strong>Simple 'dir' command:</strong> </p><pre>";
    $output3 = @shell_exec("dir C:\\ 2>&1");
    echo htmlspecialchars($output3 ? substr($output3, 0, 500) : "NO OUTPUT");
    echo "</pre>";
    
} else {
    echo "This script must be run via web browser, not CLI";
}
?>
