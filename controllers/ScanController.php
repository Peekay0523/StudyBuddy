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

/**
 * Convert any image format to JPEG
 */
function convertToJpeg($imageData, $imageType) {
    if (!function_exists('imagecreatefromstring')) {
        error_log("GD library not available for JPEG conversion");
        return false;
    }
    
    try {
        $image = @imagecreatefromstring($imageData);
        if (!$image) {
            error_log("Failed to create image from data");
            return false;
        }
        
        // Create temporary output file
        $outputFile = tempnam(sys_get_temp_dir(), 'jpg_');
        
        // Save as JPEG with 85% quality
        imagejpeg($image, $outputFile, 85);
        imagedestroy($image);
        
        $jpegData = file_get_contents($outputFile);
        @unlink($outputFile);
        
        return $jpegData;
        
    } catch (Exception $e) {
        error_log("JPEG conversion error: " . $e->getMessage());
        return false;
    }
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
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Start fresh output buffering
        ob_start();
        
        // Disable all error output to response
        ini_set('display_errors', 0);
        error_reporting(0);
        
        error_log("convertToPdf: Starting conversion process");

        // Register shutdown function to catch fatal errors
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                if (ob_get_level()) ob_end_clean();
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
                error_log("convertToPdf: Not logged in");
                throw new Exception('Not logged in');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                error_log("convertToPdf: Invalid request method: " . $_SERVER['REQUEST_METHOD']);
                throw new Exception('Invalid request method');
            }

            // Check scan limit for free tier users
            $user = getCurrentUser();
            error_log("convertToPdf: User ID = " . $user['id'] . ", Username = " . ($user['username'] ?? 'unknown'));
            
            if (!canUserScan($user['id'])) {
                $scanInfo = getScanLimitInfo($user['id']);
                error_log("convertToPdf: Scan limit reached");
                throw new Exception('Free tier limit reached. You have used your ' . FREE_TIER_SCAN_LIMIT . ' free scan(s) for this period. Please upgrade to Basic or Premium for unlimited scans.');
            }

            // Debug: log black_white parameter
            $blackWhite = isset($_POST['black_white']) && $_POST['black_white'] === 'true';
            error_log("B&W requested: " . ($blackWhite ? 'yes' : 'no') . ", POST: " . print_r($_POST, true));

            if (!isset($_FILES['images']) || empty($_FILES['images']['name'])) {
                error_log("convertToPdf: No images uploaded. FILES: " . print_r($_FILES, true));
                throw new Exception('No images uploaded');
            }

            $user = getCurrentUser();

            $images = $_FILES['images'];
            // $blackWhite already defined above
            $imageData = [];
            $count = is_array($images['name']) ? count($images['name']) : 1;
            
            error_log("convertToPdf: Processing $count image(s)");

            for ($i = 0; $i < $count; $i++) {
                $tmpName = is_array($images['tmp_name']) ? $images['tmp_name'][$i] : $images['tmp_name'];
                $name = is_array($images['name']) ? $images['name'][$i] : $images['name'];
                $error = is_array($images['error']) ? $images['error'][$i] : $images['error'];

                error_log("convertToPdf: Image $i - Name: $name, Temp: $tmpName, Error: $error");

                if ($error !== UPLOAD_ERR_OK) {
                    throw new Exception('Upload failed for: ' . $name);
                }

                if (!file_exists($tmpName)) {
                    throw new Exception('Temp file not found');
                }

                // Read image file directly
                $imageBinary = file_get_contents($tmpName);
                $imageType = mime_content_type($tmpName);
                
                error_log("convertToPdf: Image type = $imageType, size = " . strlen($imageBinary) . " bytes");

                // Get image dimensions using getimagesize
                $imgInfo = @getimagesize($tmpName);
                if (!$imgInfo) {
                    throw new Exception('Could not read image: ' . $name);
                }
                
                error_log("convertToPdf: Image dimensions = " . $imgInfo[0] . "x" . $imgInfo[1]);

                // Convert PNG/GIF/WEBP to JPEG for PDF compatibility
                if ($imageType !== 'image/jpeg' && $imageType !== 'image/jpg') {
                    error_log("convertToPdf: Converting $imageType to JPEG");
                    $imageBinary = convertToJpeg($imageBinary, $imageType);
                    if ($imageBinary === false) {
                        throw new Exception('Failed to convert image to JPEG: ' . $name);
                    }
                    $imageType = 'image/jpeg';
                    error_log("convertToPdf: Conversion to JPEG complete, new size: " . strlen($imageBinary));
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
            error_log("convertToPdf: Generating PDF with filename: $pdfFilename");
            error_log("convertToPdf: Number of images to process: " . count($imageData));

            // Generate PDF
            $pdf = new PurePhpPdf();
            $pagesAdded = 0;
            foreach ($imageData as $index => $img) {
                error_log("convertToPdf: Adding page " . ($index + 1) . " - Width: {$img['width']}, Height: {$img['height']}, Type: {$img['type']}, Data size: " . strlen($img['data']));
                $pdf->addPage($img['width'], $img['height'], $img['data'], $img['type']);
                $pagesAdded++;
            }
            
            error_log("convertToPdf: Pages added: $pagesAdded");
            error_log("convertToPdf: Internal pages count: " . count($pdf->getPages()));

            $pdfContent = $pdf->generate();
            
            // Validate PDF was generated
            if (empty($pdfContent)) {
                error_log("convertToPdf: PDF generation failed - empty content");
                error_log("convertToPdf: PurePhpPdf pages: " . print_r($pdf->getPages(), true));
                throw new Exception('Failed to generate PDF - empty content. Make sure images are valid JPEG/PNG format.');
            }
            
            $pdfSize = strlen($pdfContent);
            error_log("convertToPdf: PDF generated, size = $pdfSize bytes");
            
            // Verify PDF header
            $pdfHeader = substr($pdfContent, 0, 4);
            error_log("convertToPdf: PDF header = " . $pdfHeader);
            if ($pdfHeader !== '%PDF') {
                error_log("convertToPdf: Invalid PDF header: " . $pdfHeader);
                throw new Exception('Failed to generate valid PDF');
            }

            // Save to database
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO scans (user_id, filename, original_filename, file_data, file_size, mime_type, is_saved)
                VALUES (?, ?, ?, ?, ?, 'application/pdf', 0)
            ");

            error_log("convertToPdf: Executing database insert for user_id = " . $user['id']);

            $stmt->execute([$user['id'], $pdfFilename, $pdfFilename, $pdfContent, $pdfSize]);
            $scanId = $db->lastInsertId();

            error_log("convertToPdf: Insert completed, scanId = $scanId");

            if ($scanId) {
                // Record scan usage (uses free scans first, then regular quota)
                recordScan($user['id']);
                error_log("convertToPdf: Recorded scan usage");

                // Clear buffer and send clean JSON response
                ob_end_clean();
                header('Content-Type: application/json');
                header('X-Scan-ID: ' . $scanId); // Debug header
                $response = json_encode([
                    'success' => true,
                    'download_url' => '/download-scan/' . $scanId,
                    'scan_id' => $scanId
                ]);
                error_log("convertToPdf: Sending response: " . $response);
                echo $response;
                exit;
            }

            throw new Exception('Failed to save PDF to database');

        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            $errorResponse = json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            error_log("convertToPdf: Error response: " . $errorResponse);
            echo $errorResponse;
            exit;
        }
    }

    public function downloadScan($id) {
        requireStudent();

        $user = getCurrentUser();
        
        // Get scan from database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM scans WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
        $scan = $stmt->fetch();

        if (!$scan) {
            setFlashMessage('error', 'File not found');
            header('Location: /scan');
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($scan['filename']) . '"');
        header('Content-Length: ' . strlen($scan['file_data']));
        echo $scan['file_data'];
        exit;
    }

    public function downloadSavedPdf($id) {
        requireStudent();

        $user = getCurrentUser();

        // Get saved scan from database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM scans WHERE id = ? AND user_id = ? AND is_saved = 1");
        $stmt->execute([$id, $user['id']]);
        $scan = $stmt->fetch();

        if (!$scan) {
            setFlashMessage('error', 'File not found');
            header('Location: /scan');
            exit;
        }

        // Check if file_data is empty or null
        if (empty($scan['file_data'])) {
            setFlashMessage('error', 'PDF file is corrupted or empty');
            header('Location: /scan');
            exit;
        }

        // Clear any previous output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($scan['filename']) . '"');
        header('Content-Length: ' . strlen($scan['file_data']));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        echo $scan['file_data'];
        exit;
    }

    public function viewSavedPdf($id) {
        requireStudent();

        $user = getCurrentUser();

        // Get saved scan from database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM scans WHERE id = ? AND user_id = ? AND is_saved = 1");
        $stmt->execute([$id, $user['id']]);
        $scan = $stmt->fetch();

        if (!$scan) {
            setFlashMessage('error', 'File not found');
            header('Location: /scan');
            exit;
        }

        // Check if file_data is empty or null
        if (empty($scan['file_data'])) {
            setFlashMessage('error', 'PDF file is corrupted or empty');
            header('Location: /scan');
            exit;
        }

        // Clear any previous output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set proper headers for PDF display
        header('Content-Type: application/pdf');
        header('Content-Length: ' . strlen($scan['file_data']));
        header('Content-Disposition: inline; filename="' . basename($scan['filename']) . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        // Disable output buffering and send PDF
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        echo $scan['file_data'];
        exit;
    }

    public function saveScan() {
        requireStudent();

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $rawInput = file_get_contents('php://input');
            error_log("saveScan raw input: " . $rawInput);
            
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error: " . json_last_error_msg());
                throw new Exception('Invalid JSON input: ' . json_last_error_msg());
            }
            
            error_log("saveScan decoded input: " . print_r($input, true));
            
            $scanId = $input['scan_id'] ?? '';
            $filename = $input['filename'] ?? '';

            if (empty($scanId)) {
                throw new Exception('Missing scan ID. Please try converting the images to PDF again.');
            }
            
            if (empty($filename)) {
                throw new Exception('Missing filename. Please enter a filename for the PDF.');
            }

            $user = getCurrentUser();
            $db = Database::getInstance()->getConnection();

            // Get the scan from database
            $stmt = $db->prepare("SELECT * FROM scans WHERE id = ? AND user_id = ?");
            $stmt->execute([$scanId, $user['id']]);
            $scan = $stmt->fetch();

            if (!$scan) {
                throw new Exception('Scan not found');
            }

            // Mark as saved with new filename
            $stmt = $db->prepare("UPDATE scans SET is_saved = 1, filename = ?, original_filename = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$filename, $filename, $scanId, $user['id']]);

            echo json_encode([
                'success' => true,
                'filename' => $filename,
                'scan_id' => $scanId
            ]);
            exit;

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
            $userId = $user['id'];
            $db = Database::getInstance()->getConnection();

            // Get saved scans from database
            $stmt = $db->prepare("
                SELECT * FROM scans 
                WHERE user_id = ? AND is_saved = 1 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            $scans = $stmt->fetchAll();

            $files = [];
            foreach ($scans as $scan) {
                $files[] = [
                    'id' => $scan['id'],
                    'name' => $scan['filename'],
                    'size' => formatFileSize($scan['file_size']),
                    'date' => date('M d, Y H:i', strtotime($scan['created_at'])),
                    'url' => '/download-scan-saved/' . $scan['id']
                ];
            }

            echo json_encode([
                'success' => true,
                'files' => $files
            ]);
            exit;

        } catch (Exception $e) {
            error_log("getSavedPdfs error: " . $e->getMessage());
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
            $id = $input['id'] ?? '';

            if (empty($id)) {
                throw new Exception('Missing scan ID');
            }

            $user = getCurrentUser();
            $db = Database::getInstance()->getConnection();

            // Delete from database
            $stmt = $db->prepare("DELETE FROM scans WHERE id = ? AND user_id = ? AND is_saved = 1");
            $stmt->execute([$id, $user['id']]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
                exit;
            }

            throw new Exception('File not found');

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
        error_log("PurePhpPdf::addPage called - Width: $width, Height: $height, MIME: $mimeType, Data length: " . strlen($imageData));
        
        // Only support JPEG images
        if ($mimeType !== 'image/jpeg' && $mimeType !== 'image/jpg') {
            error_log("PurePhpPdf: Non-JPEG mime type detected: $mimeType. Attempting conversion...");
            $tempFile = tempnam(sys_get_temp_dir(), 'img_');
            file_put_contents($tempFile, $imageData);
            $info = @getimagesize($tempFile);
            unlink($tempFile);

            if ($info && $info[2] === IMAGETYPE_JPEG) {
                $mimeType = 'image/jpeg';
                error_log("PurePhpPdf: Successfully identified as JPEG");
            } else {
                error_log("PurePhpPdf: Not a valid JPEG image, skipping");
                return;
            }
        }
        
        // Validate image data
        if (empty($imageData)) {
            error_log("PurePhpPdf: Empty image data, skipping");
            return;
        }
        
        if ($width <= 0 || $height <= 0) {
            error_log("PurePhpPdf: Invalid dimensions {$width}x{$height}, skipping");
            return;
        }

        $this->pages[] = [
            'width' => $width,
            'height' => $height,
            'data' => $imageData,
            'type' => $mimeType
        ];
        
        error_log("PurePhpPdf: Page added successfully. Total pages: " . count($this->pages));
    }
    
    /**
     * Get pages array (for debugging)
     */
    public function getPages() {
        return $this->pages;
    }

    public function generate() {
        error_log("PurePhpPdf::generate called. Pages count: " . count($this->pages));
        
        if (empty($this->pages)) {
            error_log("PurePhpPdf::generate: No pages to generate");
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

    /**
     * Convert points to free scan
     */
    public function convertPoints() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /scan');
            exit;
        }

        $user = getCurrentUser();
        require_once __DIR__ . '/../models/UserPoints.php';
        $pointsModel = new UserPoints();

        // Check if user has enough points
        if (!$pointsModel->hasPoints($user['id'], 500)) {
            setFlashMessage('error', 'Insufficient points. You need at least 500 points to convert.');
            header('Location: /scan');
            exit;
        }

        // Convert points to free scan
        if ($pointsModel->spendPoints($user['id'], 500)) {
            setFlashMessage('success', 'Successfully converted 500 points to 1 free scan!');
        } else {
            setFlashMessage('error', 'Failed to convert points. Please try again.');
        }

        header('Location: /scan');
        exit;
    }

    /**
     * Test mobile upload endpoint
     */
    public function testMobileUpload() {
        header('Content-Type: application/json');

        try {
            // Log all request info
            error_log("testMobileUpload: REQUEST_METHOD = " . $_SERVER['REQUEST_METHOD']);
            error_log("testMobileUpload: FILES = " . print_r($_FILES, true));
            error_log("testMobileUpload: POST = " . print_r($_POST, true));

            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                return json_encode(['success' => false, 'error' => 'Not logged in']);
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                return json_encode(['success' => false, 'error' => 'Method not allowed']);
            }

            if (!isset($_FILES['test_image']) || $_FILES['test_image']['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'PHP extension stopped the upload'
                ];
                $errorCode = isset($_FILES['test_image']) ? $_FILES['test_image']['error'] : UPLOAD_ERR_NO_FILE;
                $errorMsg = $errorMessages[$errorCode] ?? 'Unknown error';
                error_log("testMobileUpload: Upload error code: $errorCode - $errorMsg");
                http_response_code(400);
                return json_encode([
                    'success' => false,
                    'error' => $errorMsg,
                    'error_code' => $errorCode,
                    'php_info' => [
                        'upload_max_filesize' => ini_get('upload_max_filesize'),
                        'post_max_size' => ini_get('post_max_size'),
                        'max_file_uploads' => ini_get('max_file_uploads')
                    ]
                ]);
            }

            $user = getCurrentUser();
            $file = $_FILES['test_image'];

            // Get file info
            $fileInfo = [
                'name' => $file['name'],
                'type' => $file['type'],
                'size' => $file['size'],
                'tmp_name' => $file['tmp_name'],
                'error' => $file['error'],
                'tmp_name_exists' => file_exists($file['tmp_name']),
                'is_uploaded_file' => is_uploaded_file($file['tmp_name'])
            ];

            error_log("testMobileUpload: File info = " . print_r($fileInfo, true));

            // Get image dimensions
            $imgInfo = @getimagesize($file['tmp_name']);
            if ($imgInfo) {
                $fileInfo['dimensions'] = $imgInfo[0] . 'x' . $imgInfo[1];
                $fileInfo['mime'] = $imgInfo['type'];
            }

            // Save to database like a normal scan
            $fileData = file_get_contents($file['tmp_name']);
            $testFilename = 'test_' . date('Y-m-d_H-i-s') . '_' . basename($file['name']);

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO scans (user_id, filename, original_filename, file_data, file_size, mime_type, is_saved)
                VALUES (?, ?, ?, ?, ?, 'image/jpeg', 0)
            ");

            // Save original image (not PDF) for testing
            $stmt->execute([
                $user['id'],
                $testFilename,
                $file['name'],
                $fileData,
                $file['size']
            ]);

            $scanId = $db->lastInsertId();

            error_log("testMobileUpload: Saved to database with scan_id = $scanId");

            return json_encode([
                'success' => true,
                'message' => 'Test upload successful!',
                'scan_id' => $scanId,
                'file_info' => $fileInfo,
                'session_user_id' => $_SESSION['user_id'],
                'php_info' => [
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size'),
                    'max_file_uploads' => ini_get('max_file_uploads'),
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time')
                ]
            ]);

        } catch (Exception $e) {
            error_log("testMobileUpload: Exception - " . $e->getMessage());
            error_log("testMobileUpload: Stack trace - " . $e->getTraceAsString());
            http_response_code(500);
            return json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
