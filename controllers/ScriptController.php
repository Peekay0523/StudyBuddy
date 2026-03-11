<?php
/**
 * Script Controller - Upload and manage scripts
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UploadedScript.php';
require_once __DIR__ . '/../models/Memorandum.php';
require_once __DIR__ . '/../models/StudyPlan.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/FileHelper.php';
require_once __DIR__ . '/../helpers/AIHelper.php';
require_once __DIR__ . '/../helpers/TesseractHelper.php';

class ScriptController {
    private $scriptModel;
    private $memorandumModel;
    private $studyPlanModel;
    private $aiHelper;
    private $tesseractHelper;

    public function __construct() {
        $this->scriptModel = new UploadedScript();
        $this->memorandumModel = new Memorandum();
        $this->studyPlanModel = new StudyPlan();
        $this->aiHelper = new AIHelper();
        $this->tesseractHelper = new TesseractHelper();
    }
    
    public function upload() {
        requireStudent();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student = getCurrentStudent();

            // Handle selected scan file (from database)
            if (isset($_POST['selected_scan_file']) && !empty($_POST['selected_scan_file'])) {
                $selectedScan = basename($_POST['selected_scan_file']);
                
                error_log("Selected scan filename: " . $selectedScan);
                error_log("Student user_id: " . $student['user_id']);
                
                // Get scan from database
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT * FROM scans WHERE filename = ? AND user_id = ? AND is_saved = 1");
                $stmt->execute([$selectedScan, $student['user_id']]);
                $scan = $stmt->fetch();

                error_log("Scan query result: " . print_r($scan, true));

                if ($scan && !empty($scan['file_data'])) {
                    // Copy scan to scripts folder (matching existing file structure)
                    $newFileName = 'script_' . time() . '_' . $selectedScan;
                    $destPath = UPLOAD_DIR_SCRIPTS . $newFileName;

                    if (!is_dir(UPLOAD_DIR_SCRIPTS)) {
                        mkdir(UPLOAD_DIR_SCRIPTS, 0755, true);
                    }

                    if (file_put_contents($destPath, $scan['file_data'])) {
                        $title = $_POST['title'] ?? $selectedScan;
                        $subject = $_POST['subject'] ?? '';
                        $gradeLevel = $_POST['grade_level'] ?? '';

                        $scriptId = $this->scriptModel->create(
                            $student['id'],
                            $title,
                            $newFileName,
                            $subject,
                            $gradeLevel
                        );

                        // Process the script and check for errors
                        $processResult = $this->processScript($scriptId);

                        if ($processResult !== true) {
                            $error = $processResult ?: 'Failed to process script. Please try a different file.';
                        } else {
                            // Check if redirecting to study plan page
                            if (isset($_POST['for_study_plan']) && $_POST['for_study_plan'] == '1') {
                                setFlashMessage('success', 'Script uploaded successfully! Your study plan has been generated.');
                                header('Location: /study-plan?generated=1');
                                exit;
                            }

                            setFlashMessage('success', 'Script uploaded and processed successfully!');
                            header('Location: /dashboard');
                            exit;
                        }
                    } else {
                        $error = 'Failed to save scan file';
                    }
                } else {
                    $error = 'Selected scan not found in database';
                }
            }
            // Handle regular file upload
            elseif (isset($_FILES['script_file'])) {
                $validation = FileHelper::validateUpload(
                    $_FILES['script_file'],
                    ALLOWED_SCRIPT_EXTENSIONS
                );

                if (!$validation['valid']) {
                    $error = $validation['error'];
                } else {
                    $fileName = FileHelper::saveUploadedFile($_FILES['script_file'], UPLOAD_DIR_SCRIPTS);

                    if ($fileName) {
                        $title = $_POST['title'] ?? $_FILES['script_file']['name'];
                        $subject = $_POST['subject'] ?? '';
                        $gradeLevel = $_POST['grade_level'] ?? '';

                        $scriptId = $this->scriptModel->create(
                            $student['id'],
                            $title,
                            $fileName,
                            $subject,
                            $gradeLevel
                        );

                        // Process the script and check for errors
                        $processResult = $this->processScript($scriptId);
                        
                        if ($processResult !== true) {
                            $error = $processResult ?: 'Failed to process script. Please try a different file.';
                        } else {
                            // Check if redirecting to study plan page
                            if (isset($_POST['for_study_plan']) && $_POST['for_study_plan'] == '1') {
                                setFlashMessage('success', 'Script uploaded successfully! Your study plan has been generated.');
                                header('Location: /study-plan?generated=1');
                                exit;
                            }

                            setFlashMessage('success', 'Script uploaded and processed successfully!');
                            header('Location: /dashboard');
                            exit;
                        }
                    } else {
                        $error = 'Failed to save file';
                    }
                }
            }
        }

        include __DIR__ . '/../templates/pages/upload_script.php';
    }
    
    private function processScript($scriptId) {
        try {
            $script = $this->scriptModel->findById($scriptId);
            $filePath = UPLOAD_DIR_SCRIPTS . $script['file_path'];

            // Extract text
            $textContent = FileHelper::extractTextFromFile($filePath);

            // Fallback for empty extraction
            if (empty($textContent)) {
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if ($extension === 'txt') {
                    $textContent = file_get_contents($filePath);
                }
            }

            // Check if extracted content is actual text or PDF binary garbage
            $isCorrupted = false;
            $isImageBasedPdf = false;
            if (!empty($textContent)) {
                // Check for PDF binary markers in extracted text
                if (strpos($textContent, '%PDF-') !== false || 
                    strpos($textContent, '/Type /Catalog') !== false ||
                    strpos($textContent, '/Filter /DCTDecode') !== false ||
                    preg_match('/[^\x20-\x7E\x0A\x0D]{50,}/', $textContent)) {
                    // This is likely binary PDF data, not actual text
                    error_log("Extracted content appears to be PDF binary, not readable text");
                    $isCorrupted = true;
                    
                    // Check if it's an image-based PDF - try OCR
                    $pdfContent = file_get_contents($filePath);
                    if (strpos($pdfContent, '/Filter /DCTDecode') !== false || 
                        strpos($pdfContent, '/Type /XObject') !== false) {
                        error_log("Detected image-based PDF, attempting OCR with OpenAI Vision");
                        $isImageBasedPdf = true;
                    }
                }
            }

            // Try OCR for image-based PDFs
            if ($isImageBasedPdf) {
                $textContent = $this->extractTextFromPdfWithOcr($filePath);
                if (!empty($textContent)) {
                    error_log("OCR successfully extracted " . strlen($textContent) . " characters");
                    $isCorrupted = false;
                }
            }

            if (empty($textContent) || $isCorrupted) {
                error_log("Could not extract text from file: " . $filePath);

                // Try OCR for image-based PDFs
                if ($isImageBasedPdf) {
                    error_log("Attempting OCR for image-based PDF");
                    $ocrText = $this->extractTextFromPdfWithOcr($filePath);
                    
                    if ($ocrText) {
                        error_log("OCR successfully extracted " . strlen($ocrText) . " characters");
                        $textContent = $ocrText;
                        $isCorrupted = false;
                    }
                }

                // Still save the script but mark as unprocessed if still no content
                if (empty($textContent) || $isCorrupted) {
                    $this->scriptModel->update($scriptId, [
                        'processed' => 0,
                        'processing_error' => 'Text extraction failed - file may be image-based or encrypted'
                    ]);

                    if ($isImageBasedPdf) {
                        return 'This PDF appears to be <strong>image-based (scanned)</strong>. OCR extraction failed.<br><br><strong>Options:</strong><ul style="margin: 10px 0;"><li>Check error logs for OCR details</li><li>Ensure Tesseract OCR and ImageMagick are installed (see <code>/test-tesseract.php</code>)</li><li>Upload a text-based PDF instead</li><li>Use OCR software to convert the PDF to text first, then upload the .txt file</li></ul>';
                    }

                    return 'File uploaded successfully but text extraction failed. The PDF may be <strong>image-based (scanned)</strong> or encrypted. Memorandum generation requires text-based PDFs.<br><br><strong>Options:</strong><ul style="margin: 10px 0;"><li>Upload a text-based PDF (not scanned images)</li><li>You can still download and share this file</li></ul>';
                }
            }

            // Analyze topics
            $topics = $this->aiHelper->analyzeDocumentTopics($textContent);
            $this->scriptModel->updateProcessedTopics($scriptId, $topics);

            // Identify challenging topics
            $challengingTopics = $this->aiHelper->identifyChallengingTopics($topics, $textContent);
            $this->scriptModel->updateChallengingTopics($scriptId, $challengingTopics);

            // Generate memorandum
            $memorandumContent = $this->aiHelper->generateMemorandum($textContent, $topics);
            $this->memorandumModel->create($scriptId, $memorandumContent);

            // Generate study plan
            $studentModel = new Student();
            $student = $studentModel->findByUserId($script['student_id']);
            $userModel = new User();
            $user = $userModel->findById($student['user_id']);

            $studyPlanData = $this->aiHelper->generateStudyPlan($challengingTopics, $user['username']);
            $studyPlanId = $this->studyPlanModel->create($student['id'], $studyPlanData['title'], $studyPlanData['content']);
            error_log("Study plan created successfully with ID: " . $studyPlanId . " for student: " . $student['id']);

            return true;

        } catch (Exception $e) {
            error_log("Error processing script: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return 'Error processing script: ' . $e->getMessage();
        }
    }
    
    public function viewMemorandum($scriptId) {
        requireLogin();

        $student = getCurrentStudent();
        $script = $this->scriptModel->findById($scriptId);

        if (!$script || $script['student_id'] != $student['id']) {
            header('Location: /dashboard');
            exit;
        }

        $memorandum = $this->memorandumModel->findByScriptId($scriptId);

        include __DIR__ . '/../templates/pages/view_memorandum.php';
    }

    public function getUserScripts() {
        requireLogin();

        header('Content-Type: application/json');

        $student = getCurrentStudent();
        $scripts = $this->scriptModel->findByStudentId($student['id']);

        echo json_encode(['scripts' => $scripts ?: []]);
    }

    public function deleteScript($scriptId) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard');
            exit;
        }

        $student = getCurrentStudent();
        $script = $this->scriptModel->findById($scriptId);

        // Verify ownership
        if (!$script || $script['student_id'] != $student['id']) {
            setFlashMessage('error', 'Script not found or you do not have permission to delete it.');
            header('Location: /dashboard');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Delete memorandum record (no file to delete, content is stored in database)
        $db->prepare("DELETE FROM memorandums WHERE script_id = ?")->execute([$scriptId]);

        // Delete script file
        if (!empty($script['file_path']) && file_exists(UPLOAD_DIR_SCRIPTS . $script['file_path'])) {
            unlink(UPLOAD_DIR_SCRIPTS . $script['file_path']);
        }

        // Delete script record
        $db->prepare("DELETE FROM uploaded_scripts WHERE id = ?")->execute([$scriptId]);

        setFlashMessage('success', 'Script deleted successfully.');
        header('Location: /dashboard');
        exit;
    }

    public function generateMemorandum() {
        requireLogin();

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $scriptId = $_POST['script_id'] ?? null;

        if (!$scriptId) {
            echo json_encode(['success' => false, 'error' => 'No script ID provided']);
            exit;
        }

        $student = getCurrentStudent();
        $script = $this->scriptModel->findById($scriptId);

        if (!$script || $script['student_id'] != $student['id']) {
            echo json_encode(['success' => false, 'error' => 'Script not found']);
            exit;
        }

        try {
            $filePath = UPLOAD_DIR_SCRIPTS . $script['file_path'];

            if (!file_exists($filePath)) {
                echo json_encode(['success' => false, 'error' => 'File not found']);
                exit;
            }

            // Extract text
            $textContent = FileHelper::extractTextFromFile($filePath);

            if (empty($textContent)) {
                // Try to get some content even if extraction fails
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if ($extension === 'txt') {
                    $textContent = file_get_contents($filePath);
                }
            }

            if (empty($textContent)) {
                echo json_encode(['success' => false, 'error' => 'Could not extract text from file. Try uploading a different format.']);
                exit;
            }

            // Check if extracted content is valid text or PDF binary garbage
            if (strpos($textContent, '%PDF-') !== false ||
                strpos($textContent, '/Type /Catalog') !== false ||
                preg_match('/[^\x20-\x7E\x0A\x0D]{50,}/', $textContent)) {
                
                // Try OCR for image-based PDFs
                $ocrText = $this->extractTextFromPdfWithOcr($filePath);
                
                if ($ocrText) {
                    // OCR succeeded, use this text
                    $textContent = $ocrText;
                    error_log("OCR extracted " . strlen($textContent) . " characters from image-based PDF");
                } else {
                    // OCR failed
                    echo json_encode([
                        'success' => false,
                        'error' => 'The uploaded PDF appears to be <strong>image-based (scanned)</strong> or encrypted. Memorandum generation requires text-based PDFs.<br><br><strong>Options:</strong><ul style="margin: 10px 0;"><li>Upload a text-based PDF (not scanned images)</li><li>Ensure Tesseract OCR is properly installed (see <code>/test-tesseract.php</code>)</li><li>Use OCR software to convert the PDF to text first, then upload the text file</li></ul>'
                    ]);
                    exit;
                }
            }

            // Analyze topics
            $topics = $this->aiHelper->analyzeDocumentTopics($textContent);
            $this->scriptModel->updateProcessedTopics($scriptId, $topics);

            // Generate memorandum
            $memorandumContent = $this->aiHelper->generateMemorandum($textContent, $topics);

            // Save or update memorandum
            $existingMemo = $this->memorandumModel->findByScriptId($scriptId);
            if ($existingMemo) {
                $this->memorandumModel->update($existingMemo['id'], $memorandumContent);
            } else {
                $this->memorandumModel->create($scriptId, $memorandumContent);
            }

            echo json_encode(['success' => true, 'memorandum' => $memorandumContent]);

        } catch (Exception $e) {
            error_log("Error generating memorandum: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to generate memorandum: ' . $e->getMessage()]);
        }
    }

    public function downloadMemorandum($scriptId) {
        requireLogin();

        $student = getCurrentStudent();
        $script = $this->scriptModel->findById($scriptId);

        if (!$script || $script['student_id'] != $student['id']) {
            header('Location: /dashboard');
            exit;
        }

        $memorandum = $this->memorandumModel->findByScriptId($scriptId);

        if (!$memorandum) {
            header('Location: /dashboard');
            exit;
        }

        // Generate PDF using TCPDF or similar library
        // For now, create a simple text file
        $fileName = 'memorandum_' . $script['id'] . '_' . uniqid() . '.txt';
        $filePath = UPLOAD_DIR_SCRIPTS . $fileName;

        file_put_contents($filePath, $memorandum['content']);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        readfile($filePath);
        exit;
    }

    /**
     * Extract text from PDF using OCR (OpenAI Vision or Tesseract)
     * Converts PDF pages to images and extracts text
     */
    private function extractTextFromPdfWithOcr($filePath) {
        try {
            // Priority 1: Use OpenAI Vision API (works without Ghostscript)
            error_log("Attempting OCR with OpenAI Vision API");
            $ocrText = $this->extractTextWithOpenAIVision($filePath);
            
            if ($ocrText) {
                error_log("OpenAI Vision OCR extracted " . strlen($ocrText) . " characters");
                return $ocrText;
            }
            
            // Priority 2: Use Tesseract if available (free, local)
            if ($this->tesseractHelper->isAvailable()) {
                error_log("Using Tesseract OCR for text extraction");
                $textContent = $this->tesseractHelper->extractTextFromPdf($filePath);
                if ($textContent) {
                    return $textContent;
                }
            }
            
            // No OCR method succeeded
            error_log("All OCR methods failed");
            return null;

        } catch (Exception $e) {
            error_log("OCR extraction error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Use OpenAI Vision API to extract text from PDF
     * Converts PDF pages to images using ImageMagick CLI
     */
    private function extractTextWithOpenAIVision($filePath) {
        try {
            // Verify file exists
            if (!file_exists($filePath)) {
                error_log("PDF file not found: $filePath");
                return null;
            }
            
            // Use ImageMagick CLI to convert PDF to images
            $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_' . uniqid();
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // ImageMagick path
            $magickPath = 'C:\Program Files\ImageMagick-7.1.2-Q16\magick.exe';

            if (!file_exists($magickPath)) {
                error_log("ImageMagick not found at: $magickPath");
                return null;
            }

            // Convert first page to image (limit to avoid API costs)
            $imagePath = $tempDir . DIRECTORY_SEPARATOR . 'page_0.jpg';
            // Escape the [0] page selector properly for Windows
            $escapedFilePath = str_replace('[', '\\[', $filePath);
            $convertCmd = "\"$magickPath\" -density 150 -quality 85 \"{$escapedFilePath}[0]\" \"$imagePath\" 2>&1";

            error_log("Converting PDF to image: $convertCmd");
            error_log("PDF file size: " . filesize($filePath) . " bytes");
            
            $convertOutput = shell_exec($convertCmd);
            
            if ($convertOutput) {
                error_log("ImageMagick output: $convertOutput");
            }

            if (!file_exists($imagePath)) {
                error_log("ImageMagick conversion failed - image not created");
                @rmdir($tempDir);
                return null;
            }
            
            // Verify it's a valid image
            $imageInfo = @getimagesize($imagePath);
            if (!$imageInfo) {
                error_log("Created file is not a valid image: " . filesize($imagePath) . " bytes");
                @unlink($imagePath);
                @rmdir($tempDir);
                return null;
            }
            
            error_log("Image created successfully: {$imageInfo[0]}x{$imageInfo[1]} pixels");

            // Read image and encode as base64
            $imageData = file_get_contents($imagePath);
            $base64Image = base64_encode($imageData);

            // Cleanup
            @unlink($imagePath);
            @rmdir($tempDir);

            // Send to OpenAI Vision
            error_log("Sending image to OpenAI Vision API (" . strlen($base64Image) . " bytes)");
            $extractedText = $this->aiHelper->extractTextFromImage($imageData, 'image/jpeg');

            if ($extractedText) {
                error_log("OpenAI Vision extracted: " . strlen($extractedText) . " characters");
                // Clean up the extracted text - remove any PDF structure artifacts
                $extractedText = preg_replace('/^[^\w]{0,100}/', '', $extractedText);
                return trim($extractedText);
            }

            return null;

        } catch (Exception $e) {
            error_log("OpenAI Vision OCR error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Use Imagick to convert PDF pages to images and extract text with OpenAI Vision
     */
    private function extractTextWithImagick($filePath) {
        try {
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150); // Good resolution for OCR
            $imagick->readImage($filePath);
            $imagick->setImageFormat('jpeg');
            
            $fullText = '';
            $pageCount = $imagick->getNumberImages();
            
            error_log("PDF has {$pageCount} page(s)");
            
            // Process each page (limit to first 3 pages to avoid API limits)
            $maxPages = min($pageCount, 3);
            
            for ($i = 0; $i < $maxPages; $i++) {
                $imagick->setIteratorIndex($i);
                
                // Get image blob
                $imageBlob = $imagick->getImageBlob();
                
                // Use OpenAI Vision to extract text
                $extractedText = $this->aiHelper->extractTextFromImage($imageBlob, 'image/jpeg');
                
                if ($extractedText) {
                    $fullText .= "\n--- Page " . ($i + 1) . " ---\n";
                    $fullText .= $extractedText;
                }
            }
            
            $imagick->clear();
            
            return trim($fullText);
            
        } catch (Exception $e) {
            error_log("Imagick OCR error: " . $e->getMessage());
            return null;
        }
    }
}
