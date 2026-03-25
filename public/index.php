<?php
/**
 * Main Entry Point - School Learning Platform (PHP Version)
 *
 * Usage: php -S localhost:8000 public/router.php
 * The router.php script will route all requests through this file.
 */

// Change to the project root directory
chdir(dirname(__DIR__));

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Initialize database
$db = Database::getInstance()->getConnection();

// Create tables if they don't exist
$schemaFile = __DIR__ . '/../database_schema.sql';
if (file_exists($schemaFile)) {
    $schema = file_get_contents($schemaFile);
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
            } catch (PDOException $e) {
                // Ignore errors for existing tables
            }
        }
    }
}

// Load router
require_once __DIR__ . '/../router.php';

$router = new Router();

// Define routes
$router->get('/', 'HomeController@landing');
$router->get('/home', 'HomeController@landing');

// Test GD library
$router->get('/test-gd', function() {
    echo "<h1>GD Library Test</h1>";
    if (function_exists('imagecreatefromstring')) {
        echo "<p style='color: green;'>✓ GD is available</p>";
        echo "<pre>";
        print_r(gd_info());
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ GD is NOT available</p>";
    }
});

// Add OpenAI usage table
$router->get('/add-openai-usage-table', function() {
    require __DIR__ . '/../add_openai_usage_table.php';
});

// Add Bursaries table
$router->get('/add-bursaries-table', function() {
    require __DIR__ . '/../add_bursaries_table.php';
});

// Test scan conversion directly
$router->get('/test-scan-direct', function() {
    require __DIR__ . '/../test-scan-direct.php';
});
$router->post('/test-scan-direct', function() {
    require __DIR__ . '/../test-scan-direct.php';
});

// Test scan API with JavaScript
$router->get('/test-scan-api', function() {
    require __DIR__ . '/../test-scan-api.php';
});

// Test SMS OTP
$router->get('/test-sms-otp', function() {
    require __DIR__ . '/../test-sms-otp.php';
});
$router->post('/test-sms-otp', function() {
    require __DIR__ . '/../test-sms-otp.php';
});

// Test OpenAI
$router->get('/test-openai', function() {
    require __DIR__ . '/../test-openai.php';
});
$router->post('/test-openai', function() {
    require __DIR__ . '/../test-openai.php';
});

// Simple JSON test endpoint
$router->get('/test-json', function() {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'scan_id' => 123, 'test' => 'hello']);
    exit;
});

// Authentication
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@register');
$router->post('/register/resend-otp', 'AuthController@resendOtp');
$router->post('/register/check-phone', 'AuthController@checkPhone');
$router->get('/forgot-password', 'AuthController@forgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->post('/forgot-password/resend-otp', 'AuthController@resendOtpForgotPassword');
$router->get('/logout', 'AuthController@logout');

// Dashboard
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/login-streak-info', 'DashboardController@getLoginStreakInfo');

// Scripts
$router->get('/upload-script', 'ScriptController@upload');
$router->post('/upload-script', 'ScriptController@upload');
$router->get('/browse-scripts/{grade}', 'ScriptController@browseScripts');
$router->get('/api/browse-scripts/{grade}', 'ScriptController@getBrowseScripts');

// View and Download Script files
$router->get('/view-script/{id}', function($scriptId) {
    require_once __DIR__ . '/../controllers/ScriptController.php';
    $ctrl = new ScriptController();
    $ctrl->viewScript($scriptId);
});

$router->get('/download-script/{id}', function($scriptId) {
    require_once __DIR__ . '/../controllers/ScriptController.php';
    $ctrl = new ScriptController();
    $ctrl->downloadScript($scriptId);
});

$router->get('/view-memorandum/{id}', function($scriptId) {
    require_once __DIR__ . '/../controllers/ScriptController.php';
    $controller = new ScriptController();
    $controller->viewMemorandum($scriptId);
});

// Study Plans
$router->get('/study-plan', 'StudyPlanController@index');
$router->get('/view-study-plan/{id}', function($planId) {
    require_once __DIR__ . '/../controllers/StudyPlanController.php';
    $controller = new StudyPlanController();
    $controller->view($planId);
});
$router->get('/recite-study-plan/{id}', function($planId) {
    require_once __DIR__ . '/../controllers/StudyPlanController.php';
    $controller = new StudyPlanController();
    $controller->recite($planId);
});
$router->post('/study-plan/share', function() {
    require_once __DIR__ . '/../controllers/StudyPlanController.php';
    $controller = new StudyPlanController();
    $controller->share();
});
$router->post('/study-plan/share-respond/{id}', function($shareId) {
    require_once __DIR__ . '/../controllers/StudyPlanController.php';
    $controller = new StudyPlanController();
    $controller->respondToShare($shareId);
});
$router->post('/study-plan/reminders', function() {
    require_once __DIR__ . '/../controllers/StudyPlanController.php';
    $controller = new StudyPlanController();
    $controller->createReminder();
});
$router->get('/study-plan/calendar', function() {
    require_once __DIR__ . '/../controllers/StudyPlanController.php';
    $controller = new StudyPlanController();
    $controller->getCalendarData();
});
$router->post('/study-plan/reminder-complete/{id}', function($reminderId) {
    require_once __DIR__ . '/../controllers/StudyPlanController.php';
    $controller = new StudyPlanController();
    $controller->completeReminder($reminderId);
});
$router->post('/study-plan/reminder-delete/{id}', function($reminderId) {
    require_once __DIR__ . '/../controllers/StudyPlanController.php';
    $controller = new StudyPlanController();
    $controller->deleteReminder($reminderId);
});
$router->post('/study-plan/complete/{id}', function($planId) {
    require_once __DIR__ . '/../controllers/StudyPlanController.php';
    $controller = new StudyPlanController();
    $controller->complete($planId);
});

// Report Cards
$router->get('/upload-report-card', 'ReportCardController@upload');
$router->post('/upload-report-card', 'ReportCardController@upload');
$router->get('/view-career-recommendations/{id}', function($recId) {
    require_once __DIR__ . '/../controllers/ReportCardController.php';
    $controller = new ReportCardController();
    $controller->viewCareerRecommendations($recId);
});
$router->post('/reprocess-report-card/{id}', function($recId) {
    require_once __DIR__ . '/../controllers/ReportCardController.php';
    $controller = new ReportCardController();
    $controller->reprocessReportCard($recId);
});
$router->get('/api/get-user-report-cards', 'ReportCardController@getUserReportCards');
$router->get('/api/get-available-bursaries', 'ReportCardController@getAvailableBursaries');
$router->post('/api/mark-bursary-applied', 'ReportCardController@markBursaryAsApplied');
$router->post('/api/mark-institution-applied', 'ReportCardController@markInstitutionAsApplied');
$router->get('/api/get-bursary-applications', 'ReportCardController@getBursaryApplications');
$router->get('/api/get-institution-applications', 'ReportCardController@getInstitutionApplications');
$router->post('/api/add-bursary-application', 'ReportCardController@addBursaryApplication');
$router->post('/api/add-institution-application', 'ReportCardController@addInstitutionApplication');
$router->post('/api/delete-bursary-application', 'ReportCardController@deleteBursaryApplication');
$router->post('/api/delete-institution-application', 'ReportCardController@deleteInstitutionApplication');

// AI Chat
$router->get('/ai-chat', 'AIChatController@index');
$router->post('/api/chatbot', 'AIChatController@chat');

// Study Groups
$router->get('/study-group', 'StudyGroupController@index');
$router->post('/study-group/create', 'StudyGroupController@create');
$router->post('/study-group/join/{id}', 'StudyGroupController@join');
$router->post('/study-group/leave/{id}', 'StudyGroupController@leave');
$router->post('/study-group/delete/{id}', 'StudyGroupController@delete');
$router->post('/study-group/update/{id}', 'StudyGroupController@update');
$router->get('/study-group/view/{id}', 'StudyGroupController@view');
$router->post('/study-group/{id}/upload-script', 'StudyGroupController@uploadScript');
$router->get('/study-group/{groupId}/download-script/{scriptId}', 'StudyGroupController@downloadScript');
$router->post('/study-group/{groupId}/delete-script/{scriptId}', 'StudyGroupController@deleteScript');

// Study Group Invites
$router->post('/study-group/send-invite', 'StudyGroupController@sendInvite');
$router->get('/study-group/accept-invite/{token}', 'StudyGroupController@acceptInvite');
$router->get('/invites', 'StudyGroupController@viewInvites');
$router->post('/study-group/cancel-invite/{id}', 'StudyGroupController@cancelInvite');
$router->post('/study-group/{id}/mark-viewed', function($groupId) {
    require_once __DIR__ . '/../controllers/StudyGroupController.php';
    $controller = new StudyGroupController();
    $controller->markMessagesAsViewed($groupId);
});

// Serve voice recordings from database (MUST be before /send-message and /get-messages)
$router->get('/study-group/{groupId}/voice/{messageId}', function($groupId, $messageId) {
    requireLogin();

    require_once __DIR__ . '/../models/StudyGroupMessage.php';
    require_once __DIR__ . '/../models/StudyGroup.php';

    $messageModel = new StudyGroupMessage();
    $studyGroupModel = new StudyGroup();

    // Verify user is a member of the group
    $user = getCurrentUser();
    if (!$studyGroupModel->isMember($groupId, $user['id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }

    // Get message from database
    $message = $messageModel->findById($messageId);
    
    if (!$message || $message['message_type'] !== 'voice') {
        http_response_code(404);
        echo json_encode(['error' => 'Voice message not found']);
        exit;
    }

    // Try to serve from database (voice_data BLOB)
    if (!empty($message['voice_data'])) {
        header('Content-Type: audio/webm');
        header('Content-Length: ' . strlen($message['voice_data']));
        header('Cache-Control: no-cache');
        echo $message['voice_data'];
        exit;
    }
    
    // Fallback to filesystem for old recordings (backward compatibility)
    if (!empty($message['file_path'])) {
        $filePath = __DIR__ . '/../uploads/study_groups/' . $groupId . '/voice/' . basename($message['file_path']);
        if (file_exists($filePath)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: no-cache');
            readfile($filePath);
            exit;
        }
    }

    http_response_code(404);
    echo json_encode(['error' => 'Voice file not found']);
    exit;
});

$router->post('/study-group/{id}/send-message', 'StudyGroupController@sendMessage');
$router->get('/study-group/{id}/get-messages', 'StudyGroupController@getMessages');
$router->post('/study-group/{groupId}/delete-message/{messageId}', 'StudyGroupController@deleteMessage');
$router->post('/study-group/{groupId}/remove-member/{userId}', 'StudyGroupController@removeMember');

// Old route for filesystem-based voice recordings (deprecated - for backward compatibility)
$router->get('/uploads/study_groups/{groupId}/voice/{filename}', function($groupId, $filename) {
    requireStudent();
    // Decode filename in case it's URL encoded
    $filename = urldecode($filename);
    $filePath = __DIR__ . '/../uploads/study_groups/' . $groupId . '/voice/' . basename($filename);
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo 'File not found: ' . $filePath;
        exit;
    }
    // Detect MIME type properly
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));
    header('Accept-Ranges: bytes');
    header('Cache-Control: no-cache');
    readfile($filePath);
});

// Subscription
$router->get('/subscription', 'SubscriptionController@index');
$router->get('/subscription/checkout', 'SubscriptionController@checkout');
$router->get('/subscription/success', 'SubscriptionController@success');
$router->post('/subscription/process-payment', 'SubscriptionController@processPayment');
$router->post('/subscription/subscribe', 'SubscriptionController@subscribe');
$router->post('/subscription/cancel', 'SubscriptionController@cancel');
$router->post('/subscription/downgrade', 'SubscriptionController@downgrade');

// BobPay Payment Gateway
$router->get('/subscription/bobpay/return', 'SubscriptionController@bobpayReturn');
$router->post('/subscription/bobpay/return', 'SubscriptionController@bobpayReturn');
$router->get('/subscription/bobpay/cancel', 'SubscriptionController@bobpayCancel');
$router->post('/subscription/bobpay/cancel', 'SubscriptionController@bobpayCancel');
$router->post('/subscription/bobpay/webhook', 'SubscriptionController@bobpayWebhook');

// Scan to PDF
$router->get('/scan', 'ScanController@index');
$router->post('/scan/convert-points', 'ScanController@convertPoints');
$router->post('/api/scan-to-pdf', 'ScanController@convertToPdf');
$router->post('/api/scan-save', 'ScanController@saveScan');
$router->get('/api/scan-saved-list', 'ScanController@getSavedPdfs');
$router->post('/api/scan-delete-saved', 'ScanController@deleteSavedPdf');

// Scan routes - serve PDFs from database
$router->get('/view-scan-saved/{id}', function($id) {
    require_once __DIR__ . '/../controllers/ScanController.php';
    $controller = new ScanController();
    $controller->viewSavedPdf($id);
});

$router->get('/download-scan-saved/{id}', function($id) {
    require_once __DIR__ . '/../controllers/ScanController.php';
    $controller = new ScanController();
    $controller->downloadSavedPdf($id);
});

$router->get('/download-scan/{id}', function($id) {
    require_once __DIR__ . '/../controllers/ScanController.php';
    $controller = new ScanController();
    $controller->downloadScan($id);
});

// Debug scan
$router->get('/debug-scan/{id}', function($id) {
    require __DIR__ . '/../debug-scan.php';
});

// Test PDF generation
$router->get('/test-pdf-generation', function() {
    require __DIR__ . '/../test-pdf-generation.php';
});
$router->post('/test-pdf-generation', function() {
    require __DIR__ . '/../test-pdf-generation.php';
});

// Check GD status
$router->get('/check-gd-status', function() {
    require __DIR__ . '/../check-gd-status.php';
});

// Check OpenAI configuration
$router->get('/check-openai-config', function() {
    require __DIR__ . '/../check-openai-config.php';
});

// Debug report card processing
$router->get('/debug-report-card-processing', function() {
    require __DIR__ . '/../debug-report-card-processing.php';
});

// List all report cards
$router->get('/list-report-cards', function() {
    require __DIR__ . '/../list-report-cards.php';
});

// Reprocess report card directly
$router->get('/reprocess-report-card-direct', function() {
    require __DIR__ . '/../reprocess-report-card.php';
});

// Debug database
$router->get('/debug-database', function() {
    require __DIR__ . '/../debug-database.php';
});

// Admin Routes
$router->get('/admin', 'AdminController@index');
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/users/{id}', 'AdminController@viewUser');
$router->post('/admin/users/toggle-role', 'AdminController@toggleRole');
$router->post('/admin/users/delete', 'AdminController@deleteUser');
$router->get('/admin/subscriptions', 'AdminController@subscriptions');
$router->post('/admin/subscriptions/cancel', 'AdminController@cancelSubscription');
$router->post('/admin/subscriptions/change-status', 'AdminController@changeSubscriptionStatus');
$router->post('/admin/subscriptions/approve-eft', 'AdminController@approveEFTPayment');
$router->post('/admin/subscriptions/reject-eft', 'AdminController@rejectEFTPayment');
$router->post('/admin/subscriptions/delete', 'AdminController@deleteSubscription');
$router->get('/admin/subscriptions/download-proof/{id}', 'AdminController@downloadProof');
$router->get('/admin/scripts', 'AdminController@scripts');
$router->post('/admin/scripts/delete', 'AdminController@deleteScript');
$router->post('/admin/scripts/upload-shared', 'AdminController@uploadSharedScript');
$router->get('/admin/report-cards', 'AdminController@reportCards');
$router->post('/admin/report-cards/delete', 'AdminController@deleteReportCard');
$router->get('/admin/topics', 'AdminController@topicsMastered');
$router->get('/admin/openai-settings', 'AdminController@openaiSettings');
$router->post('/admin/openai-settings/update', 'AdminController@updateOpenaiSettings');
$router->get('/admin/banking-settings', 'AdminController@bankingSettings');
$router->post('/admin/banking-settings/update', 'AdminController@updateBankingSettings');

// Bursaries Management
$router->get('/admin/bursaries', 'AdminController@bursaries');
$router->get('/admin/bursaries/add', 'AdminController@addBursary');
$router->post('/admin/bursaries/create', 'AdminController@createBursary');
$router->get('/admin/bursaries/edit/{id}', 'AdminController@editBursary');
$router->post('/admin/bursaries/update/{id}', 'AdminController@updateBursary');
$router->post('/admin/bursaries/delete', 'AdminController@deleteBursary');
$router->post('/admin/bursaries/toggle-status', 'AdminController@toggleBursaryStatus');

// Admin - BobPay Payment Management
$router->get('/admin/bobpay', 'AdminController@bobpayPayments');
$router->post('/admin/bobpay/refund', 'AdminController@bobpayRefund');
$router->get('/admin/bobpay/payment/{id}', 'AdminController@bobpayPaymentDetails');

// Scripts API endpoints
$router->get('/api/get-user-scripts', 'ScriptController@getUserScripts');
$router->post('/api/generate-memorandum', 'ScriptController@generateMemorandum');
$router->get('/download-memorandum/{id}', 'ScriptController@downloadMemorandum');
$router->post('/delete-script/{id}', 'ScriptController@deleteScript');

// Test download directly
$router->get('/test-download/{id}', function($scriptId) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
    
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/Memorandum.php';
    require_once __DIR__ . '/../models/UploadedScript.php';
    
    $db = Database::getInstance()->getConnection();
    
    // Use uploaded_scripts table (correct table)
    $stmt = $db->prepare("SELECT * FROM uploaded_scripts WHERE id = ?");
    $stmt->execute([$scriptId]);
    $script = $stmt->fetch();
    
    if (!$script) {
        echo "Script not found (ID: $scriptId)";
        exit;
    }
    
    $stmt = $db->prepare("SELECT * FROM memorandums WHERE script_id = ?");
    $stmt->execute([$scriptId]);
    $memorandum = $stmt->fetch();
    
    if (!$memorandum) {
        echo "Memorandum not found for script ID: $scriptId";
        exit;
    }
    
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
    
    $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $script['title']);
    $fileName = 'memorandum_' . $safeTitle . '.html';
    
    $subject = htmlspecialchars($script['subject'] ?? 'N/A');
    $gradeLevel = htmlspecialchars($script['grade_level'] ?? 'N/A');
    
    $htmlContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Memorandum - {$script['title']}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; color: #333; }
        h1 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; font-size: 24px; }
        h2 { color: #1f2937; font-size: 18px; margin-top: 20px; }
        h3 { color: #1f2937; font-size: 16px; margin-top: 15px; }
        p { margin: 10px 0; }
        ul { margin: 10px 0; padding-left: 20px; }
        li { margin: 5px 0; }
        .meta { color: #6b7280; font-size: 14px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Memorandum</h1>
    <p class="meta"><strong>Subject:</strong> $subject | <strong>Grade:</strong> $gradeLevel</p>
    $formattedContent
</body>
</html>
HTML;
    
    // Clear buffers and send download
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . strlen($htmlContent));
    echo $htmlContent;
    exit;
});

// Report Cards API endpoints
$router->get('/api/get-user-report-cards', 'ReportCardController@getUserReportCards');
$router->post('/delete-report-card/{id}', 'ReportCardController@deleteReportCard');

// Static files (for development) - serve from public folder
if (isset($_SERVER['REQUEST_URI'])) {
    $uri = $_SERVER['REQUEST_URI'];
    if (strpos($uri, '/css/') === 0 || strpos($uri, '/js/') === 0 || strpos($uri, '/images/') === 0) {
        $path = __DIR__ . $uri;
        if (file_exists($path)) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon'
            ];

            if (isset($mimeTypes[$ext])) {
                header('Content-Type: ' . $mimeTypes[$ext]);
            }
            readfile($path);
            exit;
        }
    }
}

// Dispatch the request
$router->dispatch();
