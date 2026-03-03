<?php
/**
 * Main Entry Point - School Learning Platform (PHP Version)
 * 
 * Usage: php -S localhost:8000 public/index.php
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
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');

// Authentication
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// Dashboard
$router->get('/dashboard', 'DashboardController@index');

// Scripts
$router->get('/upload-script', 'ScriptController@upload');
$router->post('/upload-script', 'ScriptController@upload');
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

// Report Cards
$router->get('/upload-report-card', 'ReportCardController@upload');
$router->post('/upload-report-card', 'ReportCardController@upload');
$router->get('/view-career-recommendations/{id}', function($recId) {
    require_once __DIR__ . '/../controllers/ReportCardController.php';
    $controller = new ReportCardController();
    $controller->viewCareerRecommendations($recId);
});
$router->get('/api/get-user-report-cards', 'ReportCardController@getUserReportCards');

// AI Chat
$router->get('/ai-chat', 'AIChatController@index');
$router->post('/api/chatbot', 'AIChatController@chat');

// Subscription
$router->get('/subscription', 'SubscriptionController@index');
$router->get('/subscription/checkout', 'SubscriptionController@checkout');
$router->get('/subscription/success', 'SubscriptionController@success');
$router->post('/subscription/process-payment', 'SubscriptionController@processPayment');
$router->post('/subscription/subscribe', 'SubscriptionController@subscribe');
$router->post('/subscription/cancel', 'SubscriptionController@cancel');

// Admin Routes
$router->get('/admin', 'AdminController@index');
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/users/{id}', 'AdminController@viewUser');
$router->post('/admin/users/toggle-role', 'AdminController@toggleRole');
$router->get('/admin/subscriptions', 'AdminController@subscriptions');
$router->post('/admin/subscriptions/cancel', 'AdminController@cancelSubscription');
$router->post('/admin/subscriptions/change-status', 'AdminController@changeSubscriptionStatus');
$router->post('/admin/subscriptions/approve-eft', 'AdminController@approveEFTPayment');
$router->post('/admin/subscriptions/reject-eft', 'AdminController@rejectEFTPayment');
$router->post('/admin/subscriptions/delete', 'AdminController@deleteSubscription');
$router->get('/admin/subscriptions/download-proof/{id}', 'AdminController@downloadProof');
$router->get('/admin/scripts', 'AdminController@scripts');
$router->post('/admin/scripts/delete', 'AdminController@deleteScript');
$router->get('/admin/report-cards', 'AdminController@reportCards');
$router->get('/admin/topics', 'AdminController@topicsMastered');

// Scripts API endpoints
$router->get('/api/get-user-scripts', 'ScriptController@getUserScripts');
$router->post('/api/generate-memorandum', 'ScriptController@generateMemorandum');
$router->get('/download-memorandum/{id}', 'ScriptController@downloadMemorandum');

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
