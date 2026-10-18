<?php
/**
 * Apply For Me Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/FileHelper.php';
require_once __DIR__ . '/../models/ApplyForMeSubmission.php';
require_once __DIR__ . '/../models/SavedApplicationProfile.php';

class ApplyForMeController {
    private $model;

    public function __construct() {
        $this->model = new ApplyForMeSubmission();
        $this->savedProfileModel = new SavedApplicationProfile();
    }

    public function showForm() {
        requireStudent();

        $student = getCurrentStudent();
        $pageTitle = 'Apply For Me - StudySmart';
        $currentPage = 'apply-for-me';

        $errors = [];
        $formData = [];
        $successMessage = '';

        // Pre-fill from URL parameters (from career recommendations page)
        $prefillBursary = isset($_GET['bursary']) ? trim($_GET['bursary']) : '';
        $prefillProvider = isset($_GET['provider']) ? trim($_GET['provider']) : '';
        $prefillInstitution = isset($_GET['institution']) ? trim($_GET['institution']) : '';
        $prefillType = isset($_GET['type']) ? trim($_GET['type']) : '';

        // Load saved profile for pre-filling personal/academic info
        $savedProfile = $this->savedProfileModel->findByStudentId($student['id']);
        $hasSavedProfile = $savedProfile !== null;

        include __DIR__ . '/../templates/pages/apply_for_me.php';
    }

    public function submit() {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /apply-for-me');
            exit;
        }

        $student = getCurrentStudent();
        $errors = [];
        $formData = [];

        // Check if this is a bursary application
        $isBursaryApplication = !empty($_POST['bursary_name']);
        $isInstitutionApplication = !empty($_POST['institution_name']);

        // Core required fields (always required)
        $requiredFields = [
            'full_name', 'surname', 'id_number', 'date_of_birth', 'gender', 'citizenship',
            'email', 'phone', 'home_address', 'school_name', 'matric_year'
        ];

        // Add university preferences to required fields only if NOT a bursary application
        if (!$isBursaryApplication) {
            $requiredFields[] = 'university_preference_1';
            $requiredFields[] = 'course_preference_1';
        }

        foreach ($requiredFields as $field) {
            $value = trim($_POST[$field] ?? '');
            $formData[$field] = $value;
            if (empty($value)) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }

        $formData['grade_12_results'] = trim($_POST['grade_12_results'] ?? '');
        $formData['aps_score'] = trim($_POST['aps_score'] ?? '');
        $formData['subjects'] = trim($_POST['subjects'] ?? '');
        $formData['university_preference_2'] = trim($_POST['university_preference_2'] ?? '');
        $formData['university_preference_3'] = trim($_POST['university_preference_3'] ?? '');
        $formData['course_preference_2'] = trim($_POST['course_preference_2'] ?? '');
        $formData['course_preference_3'] = trim($_POST['course_preference_3'] ?? '');

        // Validate email format
        if (!empty($formData['email']) && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid Email Address. This email will be used for all communication regarding your application.';
        }

        // Validate email is not empty and looks reasonable
        if (empty($formData['email'])) {
            $errors[] = 'Email Address is required. We will use this email to communicate with you about your application status and payment instructions.';
        }

        // Validate phone number
        if (!empty($formData['phone']) && strlen(preg_replace('/[^0-9]/', '', $formData['phone'])) < 10) {
            $errors[] = 'Please provide a valid phone number with at least 10 digits.';
        }

        // Count selected universities for service fee calculation
        $universityCount = 0;
        if (!empty($formData['university_preference_1'])) $universityCount++;
        if (!empty($formData['university_preference_2'])) $universityCount++;
        if (!empty($formData['university_preference_3'])) $universityCount++;

        // Only require university preferences if NOT a bursary application
        if (!$isBursaryApplication && $universityCount === 0) {
            $errors[] = 'Please select at least one university preference.';
        }

        $formData['bursary_name'] = trim($_POST['bursary_name'] ?? '');
        $formData['bursary_provider'] = trim($_POST['bursary_provider'] ?? '');
        $formData['institution_name'] = trim($_POST['institution_name'] ?? '');
        $formData['institution_type'] = trim($_POST['institution_type'] ?? '');

        // Handle document uploads
        // Support two modes: 1) Direct $_FILES (desktop), 2) Pre-uploaded temp files (mobile AJAX)
        $uploadDir = UPLOAD_DIR_APPLICATIONS . $student['id'] . '/';
        $tempUploads = $_SESSION['temp_uploads'] ?? [];

        $documentFields = [
            'id_copy' => 'ID Copy',
            'matric_certificate' => 'Matric Certificate',
            'proof_of_residence' => 'Proof of Residence'
        ];

        $filePaths = [];
        foreach ($documentFields as $inputName => $label) {
            $hasDirectUpload = isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] !== UPLOAD_ERR_NO_FILE;
            $hasTempUpload = !empty($tempUploads[$inputName]);
            $hasUploadedField = !empty($_POST['uploaded_' . $inputName]);

            if ($hasDirectUpload) {
                // Desktop path: process $_FILES directly
                $validation = FileHelper::validateUpload($_FILES[$inputName], ALLOWED_APPLICATION_DOCUMENT_EXTENSIONS, MAX_FILE_SIZE);
                if (!$validation['valid']) {
                    $errors[] = $label . ': ' . $validation['error'];
                    continue;
                }
                $savedFileName = FileHelper::saveUploadedFile($_FILES[$inputName], $uploadDir);
                if (!$savedFileName) {
                    $errors[] = 'Failed to save ' . $label . '. Please try again.';
                    continue;
                }
                $filePaths[$inputName] = $student['id'] . '/' . $savedFileName;
            } elseif ($hasTempUpload || $hasUploadedField) {
                // Mobile path: move file from temp directory to final location
                $tempPath = $tempUploads[$inputName] ?? $_POST['uploaded_' . $inputName];
                $tempFullPath = UPLOAD_DIR_APPLICATIONS . $tempPath;
                if (file_exists($tempFullPath)) {
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $newFileName = uniqid() . '_' . basename($tempPath);
                    $newFullPath = $uploadDir . $newFileName;
                    if (rename($tempFullPath, $newFullPath)) {
                        $filePaths[$inputName] = $student['id'] . '/' . $newFileName;
                    } else {
                        $errors[] = 'Failed to process ' . $label . '. Please try again.';
                    }
                } else {
                    $errors[] = $label . ' file not found. Please upload again.';
                }
            } else {
                $errors[] = $label . ' is required.';
            }
        }

        // CV (optional)
        $hasCvDirect = isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE;
        $hasCvTemp = !empty($tempUploads['cv']);
        $hasCvUploaded = !empty($_POST['uploaded_cv']);

        if ($hasCvDirect) {
            $validation = FileHelper::validateUpload($_FILES['cv'], ALLOWED_APPLICATION_DOCUMENT_EXTENSIONS, MAX_FILE_SIZE);
            if ($validation['valid']) {
                $savedFileName = FileHelper::saveUploadedFile($_FILES['cv'], $uploadDir);
                if ($savedFileName) {
                    $filePaths['cv'] = $student['id'] . '/' . $savedFileName;
                }
            }
        } elseif ($hasCvTemp || $hasCvUploaded) {
            $tempPath = $tempUploads['cv'] ?? $_POST['uploaded_cv'];
            $tempFullPath = UPLOAD_DIR_APPLICATIONS . $tempPath;
            if (file_exists($tempFullPath)) {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $newFileName = uniqid() . '_cv_' . basename($tempPath);
                $newFullPath = $uploadDir . $newFileName;
                if (rename($tempFullPath, $newFullPath)) {
                    $filePaths['cv'] = $student['id'] . '/' . $newFileName;
                }
            }
        }

        // Clean up temp uploads from session
        unset($_SESSION['temp_uploads']);

        if (!empty($errors)) {
            // Return to form with errors
            $pageTitle = 'Apply For Me - StudySmart';
            $currentPage = 'apply-for-me';
            $prefillBursary = $formData['bursary_name'] ?? '';
            $prefillProvider = $formData['bursary_provider'] ?? '';
            $prefillInstitution = $formData['institution_name'] ?? '';
            $prefillType = $formData['institution_type'] ?? '';
            $savedProfile = $this->savedProfileModel->findByStudentId($student['id']);
            $hasSavedProfile = $savedProfile !== null;
            include __DIR__ . '/../templates/pages/apply_for_me.php';
            return;
        }

        // Save personal info if the user opted in
        if (!empty($_POST['save_info'])) {
            $this->savedProfileModel->save($student['id'], $formData);
        }

        // Store form data and file paths in session for checkout
        $_SESSION['apply_for_me_data'] = $formData;
        $_SESSION['apply_for_me_files'] = $filePaths;
        $_SESSION['apply_for_me_university_count'] = $universityCount;

        header('Location: /apply-for-me/checkout');
        exit;
    }

    /**
     * Checkout/Payout page
     */
    public function checkout() {
        requireStudent();

        if (empty($_SESSION['apply_for_me_data'])) {
            setFlashMessage('error', 'Please fill out the application form first.');
            header('Location: /apply-for-me');
            exit;
        }

        $formData = $_SESSION['apply_for_me_data'];

        // Recalculate university count from actual form data (more reliable than session default)
        $universityCount = 0;
        if (!empty($formData['university_preference_1'])) $universityCount++;
        if (!empty($formData['university_preference_2'])) $universityCount++;
        if (!empty($formData['university_preference_3'])) $universityCount++;
        if ($universityCount === 0) $universityCount = 1;

        $serviceFeePerApp = 20.00;
        $totalFee = $universityCount * $serviceFeePerApp;

        // Build list of selected universities and courses
        $applications = [];
        if (!empty($formData['university_preference_1'])) {
            $applications[] = [
                'university' => $formData['university_preference_1'],
                'course' => $formData['course_preference_1'] ?? '',
                'choice' => '1st'
            ];
        }
        if (!empty($formData['university_preference_2'])) {
            $applications[] = [
                'university' => $formData['university_preference_2'],
                'course' => $formData['course_preference_2'] ?? '',
                'choice' => '2nd'
            ];
        }
        if (!empty($formData['university_preference_3'])) {
            $applications[] = [
                'university' => $formData['university_preference_3'],
                'course' => $formData['course_preference_3'] ?? '',
                'choice' => '3rd'
            ];
        }

        // Load banking details from settings (same as subscription checkout)
        $bankingDetails = [
            'bank_name' => 'FNB',
            'account_type' => 'Current Account',
            'account_number' => '62123456789',
            'branch_code' => '250655',
            'account_holder' => 'StudySmart',
            'reference_instruction' => 'Use your username and application ID (e.g., john-APP5)',
            'email_address' => 'billing@studysmart.co.za',
            'activation_time' => '24-48 hours',
        ];
        try {
            $db = Database::getInstance()->getConnection();
            $result = $db->query("SELECT table_name AS name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'settings'")->fetch();
            if ($result) {
                $storedSettings = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'banking_%'")->fetchAll();
                foreach ($storedSettings as $setting) {
                    $key = str_replace('banking_', '', $setting['setting_key']);
                    if (!empty($setting['setting_value'])) {
                        $bankingDetails[$key] = $setting['setting_value'];
                    }
                }
            }
        } catch (Exception $e) { }

        $pageTitle = 'Apply For Me - Checkout - StudySmart';
        $currentPage = 'apply-for-me';

        include __DIR__ . '/../templates/pages/apply_for_me_checkout.php';
    }

    /**
     * Process checkout and create the submission
     */
    public function processCheckout() {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /apply-for-me/checkout');
            exit;
        }

        if (empty($_SESSION['apply_for_me_data'])) {
            setFlashMessage('error', 'Session expired. Please fill out the application form again.');
            header('Location: /apply-for-me');
            exit;
        }

        // Validate POPIA acceptance
        if (empty($_POST['popia_accept'])) {
            setFlashMessage('error', 'You must accept the POPIA terms before submitting your application.');
            header('Location: /apply-for-me/checkout');
            exit;
        }

        $student = getCurrentStudent();
        $formData = $_SESSION['apply_for_me_data'];
        $filePaths = $_SESSION['apply_for_me_files'] ?? [];

        // Recalculate university count from actual form data
        $universityCount = 0;
        if (!empty($formData['university_preference_1'])) $universityCount++;
        if (!empty($formData['university_preference_2'])) $universityCount++;
        if (!empty($formData['university_preference_3'])) $universityCount++;
        if ($universityCount === 0) $universityCount = 1;

        $serviceFeePerApp = 20.00;
        $totalFee = $universityCount * $serviceFeePerApp;

        // Create the submission
        $data = [
            'student_id' => $student['id'],
            'full_name' => $formData['full_name'],
            'surname' => $formData['surname'],
            'id_number' => $formData['id_number'],
            'date_of_birth' => $formData['date_of_birth'],
            'gender' => $formData['gender'],
            'citizenship' => $formData['citizenship'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
            'home_address' => $formData['home_address'],
            'school_name' => $formData['school_name'],
            'grade_12_results' => $formData['grade_12_results'],
            'aps_score' => $formData['aps_score'],
            'subjects' => $formData['subjects'],
            'matric_year' => $formData['matric_year'],
            'university_preference_1' => $formData['university_preference_1'],
            'university_preference_2' => $formData['university_preference_2'],
            'university_preference_3' => $formData['university_preference_3'],
            'course_preference_1' => $formData['course_preference_1'],
            'course_preference_2' => $formData['course_preference_2'],
            'course_preference_3' => $formData['course_preference_3'],
            'bursary_name' => $formData['bursary_name'] ?? null,
            'bursary_provider' => $formData['bursary_provider'] ?? null,
            'institution_name' => $formData['institution_name'] ?? null,
            'institution_type' => $formData['institution_type'] ?? null,
            'id_copy_path' => $filePaths['id_copy'] ?? null,
            'matric_cert_path' => $filePaths['matric_certificate'] ?? null,
            'transcript_path' => null,
            'proof_res_path' => $filePaths['proof_of_residence'] ?? null,
            'cv_path' => $filePaths['cv'] ?? null,
            'payment_status' => 'pending',
            'payment_amount' => $totalFee,
            'application_status' => 'pending',
            'policy_accepted' => 1
        ];

        try {
            $submissionId = $this->model->create($data);

            // Clear session data
            unset($_SESSION['apply_for_me_data']);
            unset($_SESSION['apply_for_me_files']);
            unset($_SESSION['apply_for_me_university_count']);

            setFlashMessage('success', 'Your application has been submitted successfully! Our team will review your documents and contact you at ' . htmlspecialchars($formData['email']) . ' or via WhatsApp with payment instructions and next steps.');
            header('Location: /my-applications/' . $submissionId);
            exit;
        } catch (Exception $e) {
            setFlashMessage('error', 'Unable to save your application. Please try again.');
            header('Location: /apply-for-me/checkout');
            exit;
        }
    }

    /**
     * Upload proof of payment
     */
    public function uploadProofOfPayment($id) {
        requireStudent();

        $student = getCurrentStudent();
        if (!$student) {
            header('Location: /dashboard');
            exit;
        }

        $submission = $this->model->findById($id);

        if (!$submission || $submission['student_id'] != $student['id']) {
            setFlashMessage('error', 'Application not found');
            header('Location: /my-applications');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /my-applications/{$id}");
            exit;
        }

        if (!isset($_FILES['proof_of_payment']) || $_FILES['proof_of_payment']['error'] !== UPLOAD_ERR_OK) {
            setFlashMessage('error', 'Please select a proof of payment file to upload.');
            header("Location: /my-applications/{$id}");
            exit;
        }

        $file = $_FILES['proof_of_payment'];
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        // Validate file
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            setFlashMessage('error', 'Invalid file type. Please upload PDF, JPG, JPEG, or PNG.');
            header("Location: /my-applications/{$id}");
            exit;
        }

        if ($file['size'] > $maxSize) {
            setFlashMessage('error', 'File is too large. Maximum size is 10MB.');
            header("Location: /my-applications/{$id}");
            exit;
        }

        // Save file
        $uploadDir = UPLOAD_DIR_APPLICATIONS . $student['id'] . '/proof/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'proof_' . time() . '_' . uniqid() . '.' . $ext;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $relativePath = $student['id'] . '/proof/' . $fileName;
            $this->model->updateProofOfPayment($id, $relativePath);
            setFlashMessage('success', 'Proof of payment uploaded successfully! Our team will review and verify your payment.');
        } else {
            setFlashMessage('error', 'Failed to upload file. Please try again.');
        }

        header("Location: /my-applications/{$id}");
        exit;
    }

    /**
     * View proof of payment (student)
     */
    public function viewProofOfPayment($id) {
        requireStudent();

        $student = getCurrentStudent();
        $submission = $this->model->findById($id);

        if (!$submission || $submission['student_id'] != $student['id'] || empty($submission['proof_of_payment_path'])) {
            setFlashMessage('error', 'File not found');
            header("Location: /my-applications/{$id}");
            exit;
        }

        $fullPath = __DIR__ . '/../public/uploads/apply_for_me/' . $submission['proof_of_payment_path'];

        // Security: validate path
        $realPath = realpath($fullPath);
        $allowedDir = realpath(__DIR__ . '/../public/uploads/apply_for_me/');
        if ($realPath === false || $allowedDir === false || strpos($realPath, $allowedDir) !== 0) {
            setFlashMessage('error', 'Access denied');
            header("Location: /my-applications/{$id}");
            exit;
        }

        if (!file_exists($fullPath)) {
            setFlashMessage('error', 'File not found on disk');
            header("Location: /my-applications/{$id}");
            exit;
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    /**
     * AJAX endpoint: Upload a file immediately when selected (for mobile reliability)
     * Returns JSON with the temporary file path
     */
    public function uploadTempFile() {
        header('Content-Type: application/json');
        
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        $student = getCurrentStudent();
        if (!$student) {
            http_response_code(403);
            echo json_encode(['error' => 'No student profile']);
            exit;
        }
        
        $fieldName = $_POST['field_name'] ?? '';
        $allowedFields = ['id_copy', 'matric_certificate', 'proof_of_residence', 'cv'];
        
        if (!in_array($fieldName, $allowedFields)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid field name']);
            exit;
        }
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errorCode = isset($_FILES['file']) ? $_FILES['file']['error'] : -1;
            http_response_code(400);
            echo json_encode(['error' => 'File upload failed (error code: ' . $errorCode . ')']);
            exit;
        }
        
        $file = $_FILES['file'];
        
        // Validate
        $validation = FileHelper::validateUpload($file, ALLOWED_APPLICATION_DOCUMENT_EXTENSIONS, MAX_FILE_SIZE);
        if (!$validation['valid']) {
            http_response_code(400);
            echo json_encode(['error' => $validation['error']]);
            exit;
        }
        
        // Save to temp directory in session
        $uploadDir = UPLOAD_DIR_APPLICATIONS . 'temp/' . session_id() . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $savedFileName = FileHelper::saveUploadedFile($file, $uploadDir);
        if (!$savedFileName) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save file']);
            exit;
        }
        
        // Store path in session so the submit handler can use it
        if (!isset($_SESSION['temp_uploads'])) {
            $_SESSION['temp_uploads'] = [];
        }
        $_SESSION['temp_uploads'][$fieldName] = 'temp/' . session_id() . '/' . $savedFileName;
        
        $sizeMB = round($file['size'] / (1024 * 1024), 1);
        
        echo json_encode([
            'success' => true,
            'field' => $fieldName,
            'filename' => $file['name'],
            'size_mb' => $sizeMB,
            'path' => $_SESSION['temp_uploads'][$fieldName]
        ]);
        exit;
    }

    /**
     * Cancel checkout and return to form
     */
    public function cancelCheckout() {
        requireStudent();
        unset($_SESSION['apply_for_me_data']);
        unset($_SESSION['apply_for_me_files']);
        unset($_SESSION['apply_for_me_university_count']);
        setFlashMessage('info', 'Application cancelled. You can start again.');
        header('Location: /apply-for-me');
        exit;
    }

    /**
     * Show student's application progress
     */
    public function myApplications() {
        requireStudent();

        $student = getCurrentStudent();
        if (!$student) {
            header('Location: /dashboard');
            exit;
        }

        $submissions = $this->model->findByStudentUserId($_SESSION['user_id']);

        $pageTitle = 'My Applications - StudySmart';
        $currentPage = 'apply-for-me';

        include __DIR__ . '/../templates/pages/my_applications.php';
    }

    /**
     * Show single application progress for student
     */
    public function viewApplication($id) {
        requireStudent();

        $student = getCurrentStudent();
        if (!$student) {
            header('Location: /dashboard');
            exit;
        }

        $submission = $this->model->findById($id);

        if (!$submission || $submission['student_id'] != $student['id']) {
            setFlashMessage('error', 'Application not found');
            header('Location: /my-applications');
            exit;
        }

        $pageTitle = 'Application Progress - StudySmart';
        $currentPage = 'apply-for-me';

        include __DIR__ . '/../templates/pages/view_application_progress.php';
    }
}