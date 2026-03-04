<?php
/**
 * Scan Controller - Image to PDF Conversion
 * Works without GD/ImageMagick by embedding images directly
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Helper function to format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Convert image to grayscale (black and white) - realistic scanned document look
 */
function convertToGrayscale($imageData, $imageType) {
    // Check if GD is available
    if (function_exists('imagecreatefromstring')) {
        // Create temporary files
        $tempFile = tempnam(sys_get_temp_dir(), 'img_');
        $outputFile = tempnam(sys_get_temp_dir(), 'gray_');
        
        try {
            $image = @imagecreatefromstring($imageData);
            if (!$image) {
                @unlink($tempFile);
                @unlink($outputFile);
                return $imageData;
            }
            
            // Apply grayscale filter
            imagefilter($image, IMG_FILTER_GRAYSCALE);
            
            // Apply strong contrast for realistic scan look (negative = more contrast)
            imagefilter($image, IMG_FILTER_CONTRAST, -50);
            
            // Apply brightness adjustment for cleaner white background
            imagefilter($image, IMG_FILTER_BRIGHTNESS, 10);
            
            // Apply sharpening for crisper text
            $sharpenMatrix = [
                [-1, -1, -1],
                [-1, 16, -1],
                [-1, -1, -1]
            ];
            imageconvolution($image, $sharpenMatrix, 8, 0);
            
            // Re-apply grayscale to remove any color artifacts
            imagefilter($image, IMG_FILTER_GRAYSCALE);
            
            // Save as JPEG
            imagejpeg($image, $outputFile, 85);
            imagedestroy($image);
            
            $grayData = file_get_contents($outputFile);
            @unlink($tempFile);
            @unlink($outputFile);
            return $grayData;
            
        } catch (Exception $e) {
            @unlink($tempFile);
            @unlink($outputFile);
            return $imageData;
        }
    }
    
    // GD not available - return original with a note
    error_log("GD library not available, returning original color image");
    return $imageData;
}

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

            // Debug: log black_white parameter
            $blackWhite = isset($_POST['black_white']) && $_POST['black_white'] === 'true';
            error_log("B&W requested: " . ($blackWhite ? 'yes' : 'no') . ", POST: " . print_r($_POST, true));

            if (!isset($_FILES['images']) || empty($_FILES['images']['name'])) {
                throw new Exception('No images uploaded');
            }
            
            $user = getCurrentUser();
            $uploadDir = __DIR__ . '/../uploads/scans/' . $user['id'] . '/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $images = $_FILES['images'];
            // $blackWhite already defined above
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

                // Convert to black and white if requested
                if ($blackWhite) {
                    error_log("B&W conversion: GD=" . (function_exists('imagecreatefromstring') ? 'yes' : 'no'));
                    $imageBinary = convertToGrayscale($imageBinary, $imageType);
                    $imageType = 'image/jpeg';
                    error_log("B&W conversion done, size: " . strlen($imageBinary));
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
                header('Content-Type: application/json');
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

    public function downloadSavedPdf($filename) {
        requireStudent();

        $user = getCurrentUser();
        $filePath = __DIR__ . '/../uploads/scans/' . $user['id'] . '/saved/' . basename($filename);

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

    public function viewSavedPdf($filename) {
        requireStudent();

        $user = getCurrentUser();
        $filePath = __DIR__ . '/../uploads/scans/' . $user['id'] . '/saved/' . basename($filename);

        if (!file_exists($filePath)) {
            setFlashMessage('error', 'File not found');
            header('Location: /scan');
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($filePath));
        // Inline display in browser instead of download
        header('Content-Disposition: inline; filename="' . $filename . '"');
        readfile($filePath);
        exit;
    }

    public function saveScan() {
        requireStudent();

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $pdfUrl = $input['pdf_url'] ?? '';
            $filename = $input['filename'] ?? '';

            if (empty($pdfUrl) || empty($filename)) {
                throw new Exception('Missing PDF URL or filename');
            }

            $user = getCurrentUser();
            
            // Extract the filename from the URL
            $sourceFilename = basename($pdfUrl);
            $sourcePath = __DIR__ . '/../uploads/scans/' . $user['id'] . '/' . $sourceFilename;
            
            if (!file_exists($sourcePath)) {
                throw new Exception('Source PDF not found');
            }

            // Save with new name in the user's saved scans folder
            $saveDir = __DIR__ . '/../uploads/scans/' . $user['id'] . '/saved/';
            if (!is_dir($saveDir)) {
                mkdir($saveDir, 0755, true);
            }

            $destPath = $saveDir . basename($filename);
            
            // If file already exists, add timestamp
            if (file_exists($destPath)) {
                $pathInfo = pathinfo($filename);
                $filename = $pathInfo['filename'] . '_' . time() . '.' . $pathInfo['extension'];
                $destPath = $saveDir . basename($filename);
            }

            if (copy($sourcePath, $destPath)) {
                echo json_encode([
                    'success' => true,
                    'filename' => $filename,
                    'path' => '/uploads/scans/' . $user['id'] . '/saved/' . $filename
                ]);
                exit;
            }

            throw new Exception('Failed to save PDF');

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }

    public function getSavedPdfs() {
        requireStudent();

        try {
            $user = getCurrentUser();
            $saveDir = __DIR__ . '/../uploads/scans/' . $user['id'] . '/saved/';
            
            $files = [];
            if (is_dir($saveDir)) {
                $scanFiles = scandir($saveDir);
                foreach ($scanFiles as $file) {
                    if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                        $filePath = $saveDir . $file;
                        $files[] = [
                            'name' => $file,
                            'size' => formatFileSize(filesize($filePath)),
                            'date' => date('M d, Y H:i', filemtime($filePath)),
                            'url' => '/download-scan-saved/' . urlencode($file)
                        ];
                    }
                }
                // Sort by date (newest first)
                usort($files, function($a, $b) {
                    return strcmp($b['date'], $a['date']);
                });
            }

            echo json_encode([
                'success' => true,
                'files' => $files
            ]);
            exit;

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'files' => []
            ]);
            exit;
        }
    }

    public function deleteSavedPdf() {
        requireStudent();

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $filename = $input['filename'] ?? '';

            if (empty($filename)) {
                throw new Exception('Missing filename');
            }

            $user = getCurrentUser();
            $filePath = __DIR__ . '/../uploads/scans/' . $user['id'] . '/saved/' . basename($filename);
            
            if (!file_exists($filePath)) {
                throw new Exception('File not found');
            }

            if (unlink($filePath)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
                exit;
            }

            throw new Exception('Failed to delete file');

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }
}

/**
 * Pure PHP PDF Generator - No GD/ImageMagick required
 * Embeds JPEG images directly into PDF
 */
class PurePhpPdf {
    private $pages = [];
    private $objectOffsets = [];

    public function addPage($width, $height, $imageData, $mimeType) {
        // Only support JPEG images
        if ($mimeType !== 'image/jpeg' && $mimeType !== 'image/jpg') {
            $tempFile = tempnam(sys_get_temp_dir(), 'img_');
            file_put_contents($tempFile, $imageData);
            $info = @getimagesize($tempFile);
            unlink($tempFile);

            if ($info && $info[2] === IMAGETYPE_JPEG) {
                $mimeType = 'image/jpeg';
            } else {
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

        $pdf = "%PDF-1.4\n";
        $pdf .= "%\xE2\xE3\xCF\xD3\n";
        
        $totalObjects = 2 + (count($this->pages) * 3);
        $objects = [];
        
        // Object 1: Catalog
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        
        // Object 2: Pages
        $pageRefs = [];
        for ($i = 0; $i < count($this->pages); $i++) {
            $pageRefs[] = (3 + ($i * 3)) . " 0 R";
        }
        $objects[2] = "<< /Type /Pages /Kids [" . implode(' ', $pageRefs) . "] /Count " . count($this->pages) . " >>";
        
        // Page, Contents, and Image objects for each page
        foreach ($this->pages as $i => $page) {
            $pageObj = 3 + ($i * 3);
            $contentsObj = $pageObj + 1;
            $imgObj = $pageObj + 2;
            
            $imageStream = $page['data'];
            $imageLength = strlen($imageStream);
            
            // Image XObject
            $objects[$imgObj] = sprintf(
                "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length %d >>\nstream\n%s\nendstream",
                $page['width'],
                $page['height'],
                $imageLength,
                $imageStream
            );
            
            // Contents stream (draw image)
            $contentStream = sprintf("q %d 0 0 %d 0 0 cm /Im%d Do Q", $page['width'], $page['height'], $i);
            $objects[$contentsObj] = sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($contentStream), $contentStream);
            
            // Page object
            $objects[$pageObj] = sprintf(
                "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Contents %d 0 R /Resources << /XObject << /Im%d %d 0 R >> >> >>",
                $page['width'],
                $page['height'],
                $contentsObj,
                $i,
                $imgObj
            );
        }
        
        // Build PDF with object offsets
        $this->objectOffsets[0] = 0;
        foreach ($objects as $num => $content) {
            $this->objectOffsets[$num] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n%s\nendobj\n", $num, $content);
        }
        
        // Cross-reference table
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= "0 " . ($totalObjects + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        
        for ($i = 1; $i <= $totalObjects; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $this->objectOffsets[$i]);
        }
        
        // Trailer
        $pdf .= "trailer\n";
        $pdf .= sprintf("<< /Size %d /Root 1 0 R >>\n", $totalObjects + 1);
        $pdf .= "startxref\n";
        $pdf .= $xrefOffset . "\n";
        $pdf .= "%%EOF\n";
        
        return $pdf;
    }
}
