<?php
// Simple test
echo "Testing Tesseract from PHP:\n\n";

// Test 1: Direct path
$cmd1 = "\"C:\\Users\\mmereko\\AppData\\Local\\Programs\\Tesseract-OCR\\tesseract.exe\" --version 2>&1";
echo "Command 1: $cmd1\n";
$output1 = @shell_exec($cmd1);
echo "Output: " . ($output1 ?: "NO OUTPUT") . "\n\n";

// Test 2: PATH
$cmd2 = "tesseract --version 2>&1";
echo "Command 2: $cmd2\n";
$output2 = @shell_exec($cmd2);
echo "Output: " . ($output2 ?: "NO OUTPUT") . "\n\n";

// Test 3: ImageMagick
$cmd3 = "\"C:\\Program Files\\ImageMagick-7.1.2-Q16\\magick.exe\" --version 2>&1";
echo "Command 3: $cmd3\n";
$output3 = @shell_exec($cmd3);
echo "Output: " . ($output3 ?: "NO OUTPUT") . "\n";
?>
