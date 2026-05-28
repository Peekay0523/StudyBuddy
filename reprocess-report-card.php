<?php
/**
 * Direct Reprocess Report Card
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/ReportCard.php';
require_once __DIR__ . '/models/CareerRecommendation.php';
require_once __DIR__ . '/helpers/FileHelper.php';
require_once __DIR__ . '/helpers/AIHelper.php';

// Get the report card ID from URL or default to 17
$reportCardId = isset($_GET['id']) ? (int)$_GET['id'] : 17;

echo "<h1>Reprocess Report Card</h1>";
echo "<p><strong>Report Card ID:</strong> $reportCardId</p>";

// Check API key
echo "<h2>1. API Key Status</h2>";
if (defined('OPENAI_API_KEY')) {
    $key = OPENAI_API_KEY;
    if (strlen($key) > 20 && strpos($key, 'YOUR_') === false) {
        echo "<p style='color: green;'>✓ API Key is valid (length: " . strlen($key) . ")</p>";
    } else {
        echo "<p style='color: red;'>✗ API Key is invalid</p>";
    }
} else {
    echo "<p style='color: red;'>✗ OPENAI_API_KEY not defined</p>";
}

// Get report card
echo "<h2>2. Report Card Data</h2>";
$reportCardModel = new ReportCard();
$reportCard = $reportCardModel->findById($reportCardId);

if (!$reportCard) {
    echo "<p style='color: red;'>Report card not found!</p>";
    exit;
}

echo "<pre>" . htmlspecialchars(json_encode($reportCard, JSON_PRETTY_PRINT)) . "</pre>";

$filePath = UPLOAD_DIR_REPORT_CARDS . $reportCard['file_path'];
echo "<p><strong>File Path:</strong> $filePath</p>";
echo "<p><strong>File Exists:</strong> " . (file_exists($filePath) ? 'YES' : 'NO') . "</p>";

// Show current grades
if (!empty($reportCard['grades_data'])) {
    echo "<p><strong>Current Grades:</strong></p>";
    if (is_array($reportCard['grades_data'])) {
        $gradesData = $reportCard['grades_data'];
    } else {
        $gradesData = json_decode($reportCard['grades_data'], true);
    }
    echo "<pre>" . htmlspecialchars(json_encode($gradesData, JSON_PRETTY_PRINT)) . "</pre>";
}

// Delete old career recommendations
echo "<h2>3. Deleting Old Career Recommendations</h2>";
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("DELETE FROM career_recommendations WHERE report_card_id = ?");
$stmt->execute([$reportCardId]);
echo "<p>Deleted " . $stmt->rowCount() . " existing career recommendation record(s).</p>";

// Calculate APS
echo "<h2>4. Calculating APS Score</h2>";
require_once __DIR__ . '/helpers/AIHelper.php';
$aiHelper = new AIHelper();

// Use reflection to call the private calculateAPS method
$reflection = new ReflectionClass($aiHelper);
$method = $reflection->getMethod('calculateAPS');
$method->setAccessible(true);

if (!is_array($gradesData)) {
    $gradesData = json_decode($reportCard['grades_data'], true);
}

$aps = $method->invoke($aiHelper, $gradesData);
echo "<p><strong>Calculated APS:</strong> <span style='font-size: 24px; color: #667eea; font-weight: bold;'>$aps</span></p>";

// Show grade breakdown
echo "<h3>Grade Breakdown for APS:</h3>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr><th>Subject</th><th>Grade</th><th>Percentage</th><th>APS Points</th></tr>";

foreach ($gradesData as $subject => $grade) {
    // Skip Life Orientation
    if (stripos($subject, 'Life Orientation') !== false || stripos($subject, 'LO') !== false) {
        continue;
    }
    
    // Extract percentage
    $extractMethod = $reflection->getMethod('extractPercentage');
    $extractMethod->setAccessible(true);
    $percentage = $extractMethod->invoke($aiHelper, $grade);
    
    // Convert to points
    $pointsMethod = $reflection->getMethod('percentageToAPSPoints');
    $pointsMethod->setAccessible(true);
    $points = $pointsMethod->invoke($aiHelper, $percentage);
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($subject) . "</td>";
    echo "<td>" . htmlspecialchars($grade) . "</td>";
    echo "<td>$percentage%</td>";
    echo "<td><strong>$points</strong></td>";
    echo "</tr>";
}
echo "</table>";

// Generate career recommendations
echo "<h2>5. Generating Career Recommendations</h2>";
$recommendations = $aiHelper->generateCareerRecommendations($gradesData);

echo "<p><strong>Careers:</strong> " . count($recommendations['careers'] ?? []) . "</p>";
echo "<p><strong>APS from AIHelper:</strong> " . ($recommendations['aps'] ?? 0) . "</p>";
echo "<p><strong>Strengths:</strong> " . count($recommendations['strengths'] ?? []) . "</p>";

if (!empty($recommendations['careers'])) {
    echo "<ul>";
    foreach ($recommendations['careers'] as $career) {
        echo "<li>" . htmlspecialchars($career) . "</li>";
    }
    echo "</ul>";
}

// Save to database
echo "<h2>6. Saving to Database</h2>";

// Get student ID
$stmt = $db->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$reportCard['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    // Create student record
    $stmt = $db->prepare("INSERT INTO students (user_id) VALUES (?)");
    $stmt->execute([$reportCard['user_id']]);
    $studentId = $db->lastInsertId();
    echo "<p>Created new student record with ID: $studentId</p>";
} else {
    $studentId = $student['id'];
    echo "<p>Using existing student ID: $studentId</p>";
}

// Insert career recommendations
$careerRecModel = new CareerRecommendation();
$newId = $careerRecModel->create(
    $studentId,
    $reportCardId,
    $recommendations['careers'] ?? [],
    $recommendations['strengths'] ?? [],
    $recommendations['areas_for_improvement'] ?? [],
    json_encode($recommendations['courses'] ?? []),
    json_encode($recommendations['bursaries'] ?? []),
    $recommendations['aps'] ?? 0,
    $recommendations['institutions'] ?? []
);

echo "<p style='color: green; font-size: 18px;'><strong>✓ Career recommendations saved successfully!</strong></p>";
echo "<p><strong>New Career Recommendation ID:</strong> $newId</p>";

// Verify
echo "<h2>7. Verification</h2>";
$verifyRec = $careerRecModel->findByReportCardId($reportCardId);
if ($verifyRec) {
    echo "<p style='color: green;'>✓ Career recommendations found in database</p>";
    echo "<p><strong>APS Score:</strong> " . ($verifyRec['aps_score'] ?? 0) . "</p>";
    $careersData = $verifyRec['recommended_careers'];
    $careersArray = is_array($careersData) ? $careersData : (json_decode($careersData, true) ?: []);
    echo "<p><strong>Careers:</strong> " . count($careersArray) . "</p>";
} else {
    echo "<p style='color: red;'>✗ Career recommendations NOT found in database!</p>";
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<p><a href='/view-career-recommendations/$reportCardId' style='display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 8px;'>View Career Recommendations</a></p>";
echo "<p><a href='/list-report-cards' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px;'>Back to Report Cards List</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h1 { color: #667eea; }
h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; margin-top: 30px; }
pre { background: #f5f5f5; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 12px; }
table { width: 100%; margin: 20px 0; }
th { background: #667eea; color: white; }
</style>
