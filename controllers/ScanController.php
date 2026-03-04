<?php
/**
 * Scan Controller - Image to PDF Conversion
 * Works without GD/ImageMagick by embedding images directly
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Suppress HTML error output - we'll return JSON errors
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '60');

class ScanController {
    
    public function index() {
        requireStudent();
        include __DIR__ . '/../templates/pages/scan.php';
    }
    
    /**
     * Convert images to PDF
     */
    public function convertToPdf() {
        // Start output buffering
        ob_start();
        
        // Register shutdown function to catch fatal errors
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Fatal error: ' . $error['message']
                ]);
                exit;
            }
        });
        
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Not logged in');
            }
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            if (!isset($_FILES['images']) || empty($_FILES['images']['name'])) {
                throw new Exception('No images uploaded');
            }
            
            $user = getCurrentUser();
            $uploadDir = __DIR__ . '/../uploads/scans/' . $user['id'] . '/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $images = $_FILES['images'];
            $imageData = [];
            $count = is_array($images['name']) ? count($images['name']) : 1;
            
            for ($i = 0; $i < $count; $i++) {
                $tmpName = is_array($images['tmp_name']) ? $images['tmp_name'][$i] : $images['tmp_name'];
                $name = is_array($images['name']) ? $images['name'][$i] : $images['name'];
                $error = is_array($images['error']) ? $images['error'][$i] : $images['error'];
                
                if ($error !== UPLOAD_ERR_OK) {
                    throw new Exception('Upload failed for: ' . $name);
                }
                
                if (!file_exists($tmpName)) {
                    throw new Exception('Temp file not found');
                }
                
                // Read image file directly
                $imageBinary = file_get_contents($tmpName);
                $imageType = mime_content_type($tmpName);
                
                // Get image dimensions using getimagesize
                $imgInfo = @getimagesize($tmpName);
                if (!$imgInfo) {
                    throw new Exception('Could not read image: ' . $name);
                }
                
                $imageData[] = [
                    'data' => $imageBinary,
                    'type' => $imageType,
                    'width' => $imgInfo[0],
                    'height' => $imgInfo[1]
                ];
            }
            
            $pdfFilename = 'scan_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdfPath = $uploadDir . $pdfFilename;
            
            // Generate PDF
            $pdf = new PurePhpPdf();
            foreach ($imageData as $img) {
                $pdf->addPage($img['width'], $img['height'], $img['data'], $img['type']);
            }
            
            $content = $pdf->generate();
            
            if (file_put_contents($pdfPath, $content)) {
                ob_end_clean();
                echo json_encode([
                    'success' => true,
                    'download_url' => '/download-scan/' . basename($pdfPath)
                ]);
                exit;
            }
            
            throw new Exception('Failed to save PDF');
            
        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    public function downloadScan($filename) {
        requireStudent();
        
        $user = getCurrentUser();
        $filePath = __DIR__ . '/../uploads/scans/' . $user['id'] . '/' . basename($filename);
        
        if (!file_exists($filePath)) {
            setFlashMessage('error', 'File not found');
            header('Location: /scan');
            exit;
        }
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

/**
 * Pure PHP PDF Generator - No GD/ImageMagick required
 * Embeds JPEG images directly into PDF
 */
class PurePhpPdf {
    private $pages = [];
    
    public function addPage($width, $height, $imageData, $mimeType) {
        // Convert PNG/GIF/WebP to JPEG using PHP if GD not available
        if ($mimeType !== 'image/jpeg' && $mimeType !== 'image/jpg') {
            // For non-JPEG images, we need to convert or skip
            // Try to use getimagesize to validate
            $tempFile = tempnam(sys_get_temp_dir(), 'img_');
            file_put_contents($tempFile, $imageData);
            $info = @getimagesize($tempFile);
            unlink($tempFile);
            
            if ($info && $info[2] === IMAGETYPE_JPEG) {
                $mimeType = 'image/jpeg';
            } else {
                // Skip non-JPEG images if no conversion available
                return;
            }
        }
        
        $this->pages[] = [
            'width' => $width,
            'height' => $height,
            'data' => $imageData,
            'type' => $mimeType
        ];
    }
    
    public function generate() {
        if (empty($this->pages)) {
            return '';
        }
        
        $objects = [];
        $objNum = 0;
        
        // PDF Header
        $pdf = "%PDF-1.4\n";
        $pdf .= "%\xE2\xE3\xCF\xD3\n";
        
        // Object 1: Catalog
        $objNum++;
        $catalogObj = $objNum;
        $objects[$catalogObj] = "<< /Type /Catalog /Pages " . ($catalogObj + 1) . " 0 R >>";
        
        // Object 2: Pages
        $objNum++;
        $pagesObj = $objNum;
        
        $pageRefs = [];
        foreach ($this->pages as $i => $page) {
            $pageRefs[] = (3 + $i * 2) . " 0 R";
        }
        
        $objects[$pagesObj] = "<< /Type /Pages /Kids [" . implode(' ', $pageRefs) . "] /Count " . count($this->pages) . " >>";
        
        // Page and Image objects
        foreach ($this->pages as $i => $page) {
            $pageObj = 3 + $i * 2;
            $imgObj = $pageObj + 1;
            
            // Image XObject
            $imageStream = $page['data'];
            $imageLength = strlen($imageStream);
            
            $objects[$imgObj] = sprintf(
                "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length %d >>\nstream\n%s\nendstream",
                $page['width'],
                $page['height'],
                $imageLength,
                $imageStream
            );
            
            // Page object
            $objects[$pageObj] = sprintf(
                "<< /Type /Page /Parent %d 0 R /MediaBox [0 0 %d %d] /Contents %d 0 R /Resources << /XObject << /Im%d %d 0 R >> >> >>",
                $pagesObj,
                $page['width'],
                $page['height'],
                $imgObj,
                $i,
                $imgObj
            );
        }
        
        // Write objects
        foreach ($objects as $num => $content) {
            $pdf .= "$num 0 R\n$content\nendobj\n";
        }
        
        // Cross-reference table
        $xrefOffset = strlen($pdf);
        $totalObjects = count($objects);
        
        $pdf .= "xref\n0 " . ($totalObjects + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $totalObjects; $i++) {
            $pdf .= "0000000000 00000 n \n";
        }
        
        // Trailer
        $pdf .= "trailer\n<< /Size " . ($totalObjects + 1) . " /Root $catalogObj 0 R >>\n";
        $pdf .= "startxref\n$xrefOffset\n%%EOF\n";
        
        return $pdf;
    }
}
