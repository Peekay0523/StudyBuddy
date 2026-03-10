<?php
// Test if magick command is available
$paths = [
    'magick',
    'C:\Program Files\ImageMagick\magick.exe',
    'C:\Program Files (x86)\ImageMagick\magick.exe'
];

echo "Testing ImageMagick availability:\n\n";

foreach ($paths as $path) {
    $cmd = "\"$path\" --version 2>&1";
    $output = @shell_exec($cmd);
    
    if ($output && stripos($output, 'imagemagick') !== false) {
        echo "✓ Found ImageMagick at: $path\n";
        echo "Version:\n$output\n";
        exit(0);
    } else {
        echo "✗ Not found: $path\n";
    }
}

echo "\nImageMagick CLI is NOT available to PHP.\n";
echo "\nThis means:\n";
echo "- Tesseract can OCR images\n";
echo "- But PDFs need to be converted to images first\n";
echo "- ImageMagick 'magick' command is needed for PDF conversion\n";
?>
