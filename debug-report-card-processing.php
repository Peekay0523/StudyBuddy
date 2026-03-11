<?php
/**
 * Debug Report Card Processing
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/ReportCard.php';
require_once __DIR__ . '/models/CareerRecommendation.php';

// Get the report card ID from URL or default to 15
$reportCardId = isset($_GET['id']) ? (int)$_GET['id'] : 15;

echo "<h1>Debug Report Card Processing</h1>";
echo "<p><strong>Report Card ID:</strong> $reportCardId</p>";

// Check if .env file exists and has valid API key
echo "<h2>1. Environment Configuration</h2>";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "<p style='color: green;'>✓ .env file exists</p>";
    $envContent = file_get_contents($envFile);
    if (strpos($envContent, 'OPENAI_API_KEY=') !== false && strpos($envContent, 'YOUR_OPENAI_API_KEY_HERE') === false) {
        echo "<p style='color: green;'>✓ OPENAI_API_KEY is set</p>";
    } else {
        echo "<p style='color: red;'>✗ OPENAI_API_KEY is not properly set</p>";
    }
} else {
    echo "<p style='color: red;'>✗ .env file does not exist</p>";
}

echo "<p><strong>OPENAI_API_KEY constant:</strong> " . (defined('OPENAI_API_KEY') ? 'Defined' : 'NOT DEFINED') . "</p>";
if (defined('OPENAI_API_KEY')) {
    echo "<p><strong>Key starts with:</strong> " . substr(OPENAI_API_KEY, 0, 15) . "...</p>";
    echo "<p><strong>Key length:</strong> " . strlen(OPENAI_API_KEY) . "</p>";
}

// Get report card from database
echo "<h2>2. Report Card Data</h2>";
$reportCardModel = new ReportCard();
$reportCard = $reportCardModel->findById($reportCardId);

if ($reportCard) {
    echo "<pre>" . htmlspecialchars(json_encode($reportCard, JSON_PRETTY_PRINT)) . "</pre>";
    
    $filePath = UPLOAD_DIR_REPORT_CARDS . $reportCard['file_path'];
    echo "<p><strong>File Path:</strong> $filePath</p>";
    echo "<p><strong>File Exists:</strong> " . (file_exists($filePath) ? 'YES' : 'NO') . "</p>";
    
    if (file_exists($filePath)) {
        echo "<p><strong>File Size:</strong> " . filesize($filePath) . " bytes</p>";
        echo "<p><strong>File Extension:</strong> " . pathinfo($filePath, PATHINFO_EXTENSION) . "</p>";
    }
    
    // Decode grades_data
    if (!empty($reportCard['grades_data'])) {
        $gradesData = json_decode($reportCard['grades_data'], true);
        echo "<p><strong>Grades Data:</strong></p>";
        echo "<pre>" . htmlspecialchars(json_encode($gradesData, JSON_PRETTY_PRINT)) . "</pre>";
    } else {
        echo "<p><strong>Grades Data:</strong> EMPTY</p>";
    }
} else {
    echo "<p style='color: red;'>Report card not found!</p>";
}

// Get career recommendations
echo "<h2>3. Career Recommendations</h2>";
$careerRecModel = new CareerRecommendation();
$careerRec = $careerRecModel->findByReportCardId($reportCardId);

if ($careerRec) {
    echo "<pre>" . htmlspecialchars(json_encode($careerRec, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "<p style='color: red;'>Career recommendations not found!</p>";
}

// Test reprocessing
echo "<h2>4. Test Reprocessing</h2>";
if (isset($_POST['reprocess'])) {
    echo "<p>Starting reprocessing...</p>";
    
    require_once __DIR__ . '/controllers/ReportCardController.php';
    
    // Delete existing career recommendations
    $db = Database::getInstance()->getConnection();
    $db->prepare("DELETE FROM career_recommendations WHERE report_card_id = ?")->execute([$reportCardId]);
    echo "<p>Deleted existing career recommendations.</p>";
    
    // Create a mock controller to call processReportCard
    class TestReportCardController extends ReportCardController {
        public function testProcessReportCard($id) {
            $this->processReportCard($id);
        }
    }
    
    $controller = new TestReportCardController();
    
    // Get student
    $student = getCurrentStudent();
    if (!$student) {
        // Create student record if doesn't exist
        $user = getCurrentUser();
        if ($user) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM students WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $student = $stmt->fetch();
            
            if (!$student) {
                $stmt = $db->prepare("INSERT INTO students (user_id) VALUES (?)");
                $stmt->execute([$user['id']]);
                $student['id'] = $db->lastInsertId();
                echo "<p>Created student record with ID: " . $student['id'] . "</p>";
            }
        }
    }
    
    echo "<p>Processing report card...</p>";
    try {
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('processReportCard');
        $method->setAccessible(true);
        $method->invoke($controller, $reportCardId);
        echo "<p style='color: green;'>✓ Report card processed successfully!</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
    
    // Fetch updated career recommendations
    echo "<h3>Updated Career Recommendations:</h3>";
    $careerRec = $careerRecModel->findByReportCardId($reportCardId);
    if ($careerRec) {
        echo "<pre>" . htmlspecialchars(json_encode($careerRec, JSON_PRETTY_PRINT)) . "</pre>";
    } else {
        echo "<p style='color: red;'>Still no career recommendations!</p>";
    }
} else {
    echo "<form method='POST'>";
    echo "<button type='submit' name='reprocess' style='padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer;'>";
    echo "<i class='fas fa-sync'></i> Reprocess Now";
    echo "</button>";
    echo "</form>";
}

// Show error log
echo "<h2>5. Recent Error Log</h2>";
$logFile = __DIR__ . '/error.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = substr($logs, -10000);
    echo "<pre style='font-size: 12px; max-height: 400px; overflow-y: auto;'>" . htmlspecialchars($recentLogs) . "</pre>";
} else {
    echo "<p>No error log file found.</p>";
}

echo "<hr>";
echo "<p><a href='/view-career-recommendations/$reportCardId'>Back to Career Recommendations</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h1 { color: #667eea; }
h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; margin-top: 30px; }
pre { background: #f5f5f5; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 12px; max-height: 300px; overflow-y: auto; }
</style>
