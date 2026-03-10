<?php
/**
 * Tesseract OCR Helper
 * Uses Tesseract CLI for Optical Character Recognition
 */

class TesseractHelper {
    
    private $tesseractPath;
    private $tempDir;
    
    public function __construct() {
        // Windows: Typically installed at C:\Program Files\Tesseract-OCR\tesseract.exe
        // Linux/Mac: Usually just 'tesseract' in PATH
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Try common installation paths including user local installs
            $commonPaths = [
                'C:\Program Files\Tesseract-OCR\tesseract.exe',
                'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe',
                'C:\Users\mmereko\AppData\Local\Programs\Tesseract-OCR\tesseract.exe',
                getenv('TESSERACT_PATH') ?: 'tesseract'
            ];
            
            // Find the first working path
            $this->tesseractPath = 'tesseract'; // default
            foreach ($commonPaths as $path) {
                if ($path && file_exists($path)) {
                    $this->tesseractPath = $path;
                    break;
                }
            }
        } else {
            $this->tesseractPath = 'tesseract';
        }
        
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tesseract_ocr';
        
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }
    
    /**
     * Check if Tesseract is available and working
     */
    public function isAvailable() {
        try {
            $cmd = '"' . $this->tesseractPath . '" --version 2>&1';
            $output = shell_exec($cmd);
            return $output !== null && stripos($output, 'tesseract') !== false;
        } catch (Exception $e) {
            error_log("Tesseract availability check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get Tesseract version
     */
    public function getVersion() {
        try {
            $cmd = '"' . $this->tesseractPath . '" --version 2>&1';
            $output = shell_exec($cmd);
            return trim($output);
        } catch (Exception $e) {
            return 'Unknown';
        }
    }
    
    /**
     * Extract text from an image file using Tesseract
     * 
     * @param string $imagePath Path to the image file
     * @param string $language Language code (default: eng)
     * @return string|false Extracted text or false on failure
     */
    public function extractTextFromImage($imagePath, $language = 'eng') {
        if (!file_exists($imagePath)) {
            error_log("TesseractHelper: Image file not found: {$imagePath}");
            return false;
        }
        
        if (!$this->isAvailable()) {
            error_log("TesseractHelper: Tesseract OCR not available");
            return false;
        }
        
        $outputFile = $this->tempDir . DIRECTORY_SEPARATOR . uniqid('ocr_');
        
        // Tesseract command: tesseract <input> <output> -l <language>
        $cmd = '"' . $this->tesseractPath . '" "' . $imagePath . '" "' . $outputFile . '" -l ' . $language . ' 2>&1';
        
        error_log("TesseractHelper: Running command: {$cmd}");
        
        $output = shell_exec($cmd);
        
        $textFile = $outputFile . '.txt';
        
        if (file_exists($textFile)) {
            $text = file_get_contents($textFile);
            
            // Cleanup temp files
            @unlink($textFile);
            @unlink($outputFile);
            
            error_log("TesseractHelper: Extracted " . strlen($text) . " characters");
            return trim($text);
        }
        
        error_log("TesseractHelper: Output file not created. Command output: " . ($output ?? 'NULL'));
        return false;
    }
    
    /**
     * Extract text from PDF using Tesseract
     * Converts PDF pages to images first, then OCRs each page
     * 
     * @param string $pdfPath Path to the PDF file
     * @param string $language Language code (default: eng)
     * @param int $maxPages Maximum pages to process (default: 3)
     * @return string|false Extracted text or false on failure
     */
    public function extractTextFromPdf($pdfPath, $language = 'eng', $maxPages = 3) {
        if (!file_exists($pdfPath)) {
            error_log("TesseractHelper: PDF file not found: {$pdfPath}");
            return false;
        }
        
        if (!$this->isAvailable()) {
            error_log("TesseractHelper: Tesseract OCR not available");
            return false;
        }
        
        // Check if ImageMagick (convert) is available for PDF to image conversion
        $convertAvailable = $this->isConvertAvailable();
        
        if (!$convertAvailable) {
            error_log("TesseractHelper: ImageMagick 'convert' command not found. Cannot convert PDF to images.");
            return false;
        }
        
        $tempDir = $this->tempDir . DIRECTORY_SEPARATOR . uniqid('pdf_');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $fullText = '';
        
        try {
            // Convert PDF to images (one per page)
            $imagePattern = $tempDir . DIRECTORY_SEPARATOR . 'page_%d.jpg';
            $convertCmd = '"' . $this->getConvertPath() . '" -density 150 -quality 90 "' . $pdfPath . '" "' . $imagePattern . '" 2>&1';
            
            error_log("TesseractHelper: Converting PDF to images: {$convertCmd}");
            
            $convertOutput = shell_exec($convertCmd);
            
            // Find all generated images
            $images = glob($tempDir . DIRECTORY_SEPARATOR . 'page_*.jpg');
            
            if (empty($images)) {
                // Try with png extension
                $images = glob($tempDir . DIRECTORY_SEPARATOR . 'page_*.png');
            }
            
            if (empty($images)) {
                error_log("TesseractHelper: No images created from PDF. Convert output: " . ($convertOutput ?? 'NULL'));
                return false;
            }
            
            error_log("TesseractHelper: Created " . count($images) . " image(s) from PDF");
            
            // Process each page (up to maxPages)
            $pageCount = 0;
            foreach ($images as $index => $imagePath) {
                if ($pageCount >= $maxPages) {
                    break;
                }
                
                $pageText = $this->extractTextFromImage($imagePath, $language);
                
                if ($pageText) {
                    $fullText .= "\n--- Page " . ($index + 1) . " ---\n";
                    $fullText .= $pageText;
                    $pageCount++;
                }
                
                // Cleanup image file
                @unlink($imagePath);
            }
            
            // Cleanup temp directory
            @rmdir($tempDir);
            
            if (!empty(trim($fullText))) {
                error_log("TesseractHelper: Successfully extracted " . strlen($fullText) . " characters from {$pageCount} page(s)");
                return trim($fullText);
            }
            
            error_log("TesseractHelper: No text extracted from PDF");
            return false;
            
        } catch (Exception $e) {
            error_log("TesseractHelper: Error processing PDF: " . $e->getMessage());
            
            // Cleanup on error
            if (isset($images)) {
                foreach ($images as $img) {
                    @unlink($img);
                }
            }
            @rmdir($tempDir);
            
            return false;
        }
    }
    
    /**
     * Check if ImageMagick's convert command is available
     */
    private function isConvertAvailable() {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // On Windows, check common ImageMagick locations including user installs
                $paths = [
                    'C:\Program Files\ImageMagick-7.1.2-Q16\magick.exe',
                    'C:\Program Files\ImageMagick\magick.exe',
                    'C:\Program Files (x86)\ImageMagick\magick.exe',
                    'C:\Users\mmereko\AppData\Local\Programs\ImageMagick\magick.exe',
                    'C:\Users\mmereko\AppData\Local\Programs\ImageMagick-7.1.2-Q16\magick.exe',
                    'magick.exe'
                ];

                foreach ($paths as $path) {
                    $cmd = '"' . $path . '" --version 2>&1';
                    $output = @shell_exec($cmd);
                    if ($output && stripos($output, 'imagemagick') !== false) {
                        error_log("TesseractHelper: Found ImageMagick at: $path");
                        return true;
                    }
                }

                error_log("TesseractHelper: ImageMagick not found in common locations");
                return false;
            } else {
                $output = @shell_exec('which convert 2>&1');
                return !empty(trim($output));
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get the convert/magick command path
     */
    private function getConvertPath() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // ImageMagick 7+ uses 'magick' instead of 'convert'
            $paths = [
                'C:\Program Files\ImageMagick\magick.exe',
                'C:\Program Files (x86)\ImageMagick\magick.exe'
            ];
            
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
            
            return 'magick';
        }
        
        return 'convert';
    }
    
    /**
     * Cleanup temporary files
     */
    public function cleanup() {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            foreach ($files as $file) {
                @unlink($file);
            }
            @rmdir($this->tempDir);
        }
    }
}
