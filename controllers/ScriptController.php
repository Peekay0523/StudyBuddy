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

        // Debug logging
        error_log("Upload request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student = getCurrentStudent();
            error_log("Current student ID: " . ($student['id'] ?? 'NOT FOUND'));

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

        if (!$memorandum) {
            setFlashMessage('error', 'No memorandum found for this script. Please generate one first.');
            header('Location: /upload-script');
            exit;
        }

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
        // Debug logging
        error_log("Download request for script ID: $scriptId");
        error_log("Format: " . ($_GET['format'] ?? 'not set'));
        
        // Start output buffering to prevent any whitespace issues
        if (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        requireLogin();

        $student = getCurrentStudent();
        error_log("Current student ID: " . ($student['id'] ?? 'NOT FOUND'));
        
        $script = $this->scriptModel->findById($scriptId);
        error_log("Script found: " . ($script ? 'YES' : 'NO'));

        if (!$script || $script['student_id'] != $student['id']) {
            error_log("Script not found or student mismatch");
            header('Location: /dashboard');
            exit;
        }

        $memorandum = $this->memorandumModel->findByScriptId($scriptId);
        error_log("Memorandum found: " . ($memorandum ? 'YES' : 'NO'));

        if (!$memorandum) {
            error_log("Memorandum not found for script ID: $scriptId");
            header('Location: /dashboard');
            exit;
        }

        // Get format from query parameter (default to pdf)
        $format = $_GET['format'] ?? 'pdf';
        $format = strtolower($format);
        error_log("Format selected: $format");

        // Clean the title for filename
        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $script['title']);

        if ($format === 'docx') {
            error_log("Calling downloadMemorandumAsDocx");
            $this->downloadMemorandumAsDocx($memorandum, $safeTitle);
        } else {
            error_log("Calling downloadMemorandumAsPdf");
            $this->downloadMemorandumAsPdf($memorandum, $safeTitle);
        }

        ob_end_flush();
        exit;
    }

    private function downloadMemorandumAsPdf($memorandum, $safeTitle) {
        // Get the script data for additional info
        $script = $this->scriptModel->findById($memorandum['script_id']);

        // Create HTML content for PDF
        $htmlContent = $this->getMemorandumHtml($memorandum, $script);

        // Use TCPDF or Dompdf if available, otherwise create a simple PDF
        if (class_exists('\TCPDF')) {
            // Use TCPDF
            $pdf = new \TCPDF();
            $pdf->SetCreator('StudySmart');
            $pdf->SetAuthor('StudySmart AI');
            $pdf->SetTitle('Memorandum - ' . $script['title']);
            $pdf->AddPage();
            $pdf->writeHTML($htmlContent);

            $fileName = 'memorandum_' . $safeTitle . '.pdf';
            $pdf->Output($fileName, 'D');
        } elseif (class_exists('\Dompdf\Dompdf')) {
            // Use Dompdf
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($htmlContent);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $fileName = 'memorandum_' . $safeTitle . '.pdf';
            $dompdf->stream($fileName);
        } else {
            // Fallback: Download as HTML file (can be opened in browser and printed to PDF)
            $fileName = 'memorandum_' . $safeTitle . '.html';

            // Clear any existing buffers
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            header('Content-Type: text/html; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');
            header('Content-Length: ' . strlen($htmlContent));

            echo $htmlContent;
        }
    }

    private function downloadMemorandumAsDocx($memorandum, $safeTitle) {
        // Get the script data for additional info
        $script = $this->scriptModel->findById($memorandum['script_id']);

        // Create DOCX file using PHPWord if available
        if (class_exists('\PhpOffice\PhpWord\PhpWord')) {
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection();

            // Add title
            $section->addText('Memorandum', ['size' => 16, 'bold' => true]);
            $section->addTextBreak(1);

            // Add script info
            if ($script) {
                $section->addText('Subject: ' . ($script['subject'] ?? 'N/A') .
                                 ' | Grade Level: ' . ($script['grade_level'] ?? 'N/A'),
                                 ['size' => 10, 'color' => '666666']);
                $section->addTextBreak(2);
            }

            // Add content with proper formatting
            $content = $memorandum['content'];
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (empty($trimmedLine)) {
                    $section->addTextBreak(1);
                } elseif (strpos($trimmedLine, '# ') === 0) {
                    $section->addText(substr($trimmedLine, 2), ['size' => 14, 'bold' => true]);
                } elseif (strpos($trimmedLine, '## ') === 0) {
                    $section->addText(substr($trimmedLine, 3), ['size' => 12, 'bold' => true]);
                } elseif (strpos($trimmedLine, '- ') === 0 || strpos($trimmedLine, '* ') === 0) {
                    $section->addListItem(substr($trimmedLine, 2), ['size' => 11]);
                } else {
                    $section->addText($trimmedLine, ['size' => 11]);
                }
            }

            $fileName = 'memorandum_' . $safeTitle . '.docx';
            $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($filePath);

            // Clear any existing buffers
            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            @unlink($filePath);
        } else {
            // Fallback: Create simple HTML document that opens in Word
            $content = $memorandum['content'];
            $lines = explode("\n", $content);
            $formattedContent = '';
            
            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (empty($trimmedLine)) {
                    $formattedContent .= '<br>';
                } elseif (strpos($trimmedLine, '# ') === 0) {
                    $formattedContent .= '<h2>' . htmlspecialchars(substr($trimmedLine, 2)) . '</h2>';
                } elseif (strpos($trimmedLine, '## ') === 0) {
                    $formattedContent .= '<h3>' . htmlspecialchars(substr($trimmedLine, 3)) . '</h3>';
                } elseif (strpos($trimmedLine, '- ') === 0 || strpos($trimmedLine, '* ') === 0) {
                    $formattedContent .= '<li>' . htmlspecialchars(substr($trimmedLine, 2)) . '</li>';
                } else {
                    $formattedContent .= '<p>' . htmlspecialchars($trimmedLine) . '</p>';
                }
            }
            
            $formattedContent = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $formattedContent);
            $formattedContent = str_replace('</ul><ul>', '', $formattedContent);
            
            $subject = htmlspecialchars($script['subject'] ?? 'N/A');
            $gradeLevel = htmlspecialchars($script['grade_level'] ?? 'N/A');
            
            $htmlContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Memorandum - {$script['title']}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        h1 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        h2 { font-size: 18px; margin-top: 20px; }
        h3 { font-size: 16px; margin-top: 15px; }
        p { margin: 10px 0; }
        ul { margin: 10px 0; padding-left: 20px; }
        .meta { color: #666; font-size: 14px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Memorandum</h1>
    <p class="meta"><strong>Subject:</strong> $subject | <strong>Grade Level:</strong> $gradeLevel</p>
    $formattedContent
</body>
</html>
HTML;

            $fileName = 'memorandum_' . $safeTitle . '.doc';

            // Clear any existing buffers
            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: application/msword');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');
            header('Content-Length: ' . strlen($htmlContent));
            
            echo $htmlContent;
        }
    }

    private function getMemorandumHtml($memorandum, $script = null) {
        $content = $memorandum['content'];
        
        // Convert newlines to paragraphs and handle formatting
        $lines = explode("\n", $content);
        $formattedContent = '';
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine)) {
                $formattedContent .= '<br>';
            } elseif (strpos($trimmedLine, '# ') === 0) {
                // Heading level 1
                $formattedContent .= '<h2>' . htmlspecialchars(substr($trimmedLine, 2)) . '</h2>';
            } elseif (strpos($trimmedLine, '## ') === 0) {
                // Heading level 2
                $formattedContent .= '<h3>' . htmlspecialchars(substr($trimmedLine, 3)) . '</h3>';
            } elseif (strpos($trimmedLine, '- ') === 0 || strpos($trimmedLine, '* ') === 0) {
                // Bullet point
                $formattedContent .= '<li>' . htmlspecialchars(substr($trimmedLine, 2)) . '</li>';
            } else {
                // Regular paragraph
                $formattedContent .= '<p>' . htmlspecialchars($trimmedLine) . '</p>';
            }
        }
        
        // Wrap bullet points in unordered list
        $formattedContent = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $formattedContent);
        $formattedContent = str_replace('</ul><ul>', '', $formattedContent);

        $scriptInfo = '';
        if ($script) {
            $scriptInfo = '<p><strong>Subject:</strong> ' . htmlspecialchars($script['subject'] ?? 'N/A') . 
                         ' | <strong>Grade Level:</strong> ' . htmlspecialchars($script['grade_level'] ?? 'N/A') . '</p>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; color: #333; }
        h1 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; font-size: 24px; }
        h2 { color: #1f2937; font-size: 18px; margin-top: 20px; }
        h3 { color: #1f2937; font-size: 16px; margin-top: 15px; }
        p { margin: 10px 0; }
        ul { margin: 10px 0; padding-left: 20px; }
        li { margin: 5px 0; }
        br { line-height: 1.6; }
        .meta { color: #6b7280; font-size: 14px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Memorandum</h1>
    $scriptInfo
    $formattedContent
</body>
</html>
HTML;
    }

    private function createSimplePdf($memorandum) {
        // Basic PDF structure (minimal valid PDF)
        $content = $memorandum['content'];
        $escapeContent = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $content);
        
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= "2 0 obj\n<< /Type /Pages /Count 1 /Kids [3 0 R] >>\nendobj\n";
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $pdf .= "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $pdf .= "5 0 obj\n<< /Length " . strlen($escapeContent) . " >>\nstream\nBT /F1 12 Tf 50 750 Td (" . $escapeContent . ") Tj ET\nendstream\nendobj\n";
        $pdf .= "xref\n0 6\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\n0000000214 00000 n\n0000000281 00000 n\n";
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . (strlen($pdf) + 50) . "\n%%EOF";
        
        return $pdf;
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

    /**
     * View a script file (display in browser)
     */
    public function viewScript($scriptId) {
        requireLogin();

        $student = getCurrentStudent();
        $script = $this->scriptModel->findById($scriptId);

        if (!$script || $script['student_id'] != $student['id']) {
            header('Location: /dashboard');
            exit;
        }

        $filePath = UPLOAD_DIR_SCRIPTS . $script['file_path'];

        if (!file_exists($filePath)) {
            header('Location: /dashboard');
            exit;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = 'application/octet-stream';

        switch ($extension) {
            case 'pdf':
                $mimeType = 'application/pdf';
                break;
            case 'docx':
                $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            case 'txt':
                $mimeType = 'text/plain';
                break;
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        readfile($filePath);
        exit;
    }

    /**
     * Download a script file
     */
    public function downloadScript($scriptId) {
        requireLogin();

        $student = getCurrentStudent();
        $script = $this->scriptModel->findById($scriptId);

        if (!$script || $script['student_id'] != $student['id']) {
            header('Location: /dashboard');
            exit;
        }

        $filePath = UPLOAD_DIR_SCRIPTS . $script['file_path'];

        if (!file_exists($filePath)) {
            header('Location: /dashboard');
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $script['file_path'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
