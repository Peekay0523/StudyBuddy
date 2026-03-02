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

class ScriptController {
    private $scriptModel;
    private $memorandumModel;
    private $studyPlanModel;
    private $aiHelper;
    
    public function __construct() {
        $this->scriptModel = new UploadedScript();
        $this->memorandumModel = new Memorandum();
        $this->studyPlanModel = new StudyPlan();
        $this->aiHelper = new AIHelper();
    }
    
    public function upload() {
        requireLogin();
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['script_file'])) {
            $student = getCurrentStudent();
            
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
                    
                    // Process the script
                    $this->processScript($scriptId);
                    
                    setFlashMessage('success', 'Script uploaded and processed successfully!');
                    header('Location: /dashboard');
                    exit;
                } else {
                    $error = 'Failed to save file';
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

            if (empty($textContent)) {
                error_log("Could not extract text from file: " . $filePath);
                return; // Skip processing but don't fail upload
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
            $this->studyPlanModel->create($student['id'], $studyPlanData['title'], $studyPlanData['content']);

        } catch (Exception $e) {
            error_log("Error processing script: " . $e->getMessage());
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
}
