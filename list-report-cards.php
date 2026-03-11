<?php
/**
 * List All Report Cards
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/ReportCard.php';
require_once __DIR__ . '/models/CareerRecommendation.php';

echo "<h1>All Report Cards in Database</h1>";

$reportCardModel = new ReportCard();
$db = Database::getInstance()->getConnection();

// Get all report cards
$stmt = $db->query("SELECT * FROM report_cards ORDER BY id DESC");
$reportCards = $stmt->fetchAll();

if (empty($reportCards)) {
    echo "<p style='color: red; font-size: 18px;'>⚠️ NO REPORT CARDS FOUND IN DATABASE!</p>";
    echo "<p>You need to upload a report card first.</p>";
    echo "<p><a href='/upload-report-card' style='display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 8px;'>Upload Report Card</a></p>";
} else {
    echo "<p><strong>Found " . count($reportCards) . " report card(s)</strong></p>";
    
    foreach ($reportCards as $rc) {
        echo "<div style='border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; background: #f9f9f9;'>";
        echo "<h2>Report Card ID: {$rc['id']}</h2>";
        echo "<ul>";
        echo "<li><strong>User ID:</strong> {$rc['user_id']}</li>";
        echo "<li><strong>Student ID:</strong> {$rc['student_id']}</li>";
        echo "<li><strong>File Path:</strong> {$rc['file_path']}</li>";
        echo "<li><strong>Grade:</strong> " . ($rc['grade'] ?: 'N/A') . "</li>";
        echo "<li><strong>Term:</strong> " . ($rc['term'] ?: 'N/A') . "</li>";
        echo "<li><strong>Uploaded At:</strong> {$rc['uploaded_at']}</li>";
        
        // Check if file exists
        $filePath = UPLOAD_DIR_REPORT_CARDS . $rc['file_path'];
        echo "<li><strong>File Exists:</strong> " . (file_exists($filePath) ? 'YES' : 'NO') . "</li>";
        
        // Show grades data
        if (!empty($rc['grades_data'])) {
            $gradesData = json_decode($rc['grades_data'], true);
            echo "<li><strong>Grades Data:</strong></li>";
            echo "<pre>" . htmlspecialchars(json_encode($gradesData, JSON_PRETTY_PRINT)) . "</pre>";
        } else {
            echo "<li><strong>Grades Data:</strong> <span style='color: red;'>EMPTY</span></li>";
        }
        
        // Check for career recommendations
        $careerRecModel = new CareerRecommendation();
        $careerRec = $careerRecModel->findByReportCardId($rc['id']);
        
        echo "<li><strong>Career Recommendations:</strong> " . ($careerRec ? 'YES' : 'NO') . "</li>";
        
        if ($careerRec) {
            echo "<li><strong>APS Score:</strong> " . ($careerRec['aps_score'] ?? 'N/A') . "</li>";
            $careersData = $careerRec['recommended_careers'];
            if (is_array($careersData)) {
                $careersArray = $careersData;
            } else {
                $careersArray = json_decode($careersData, true) ?: [];
            }
            echo "<li><strong>Careers:</strong> " . count($careersArray) . "</li>";
        }
        
        echo "</ul>";
        
        // Actions
        echo "<div style='margin-top: 20px;'>";
        echo "<a href='/view-career-recommendations/{$rc['id']}' style='display: inline-block; padding: 8px 16px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; margin-right: 10px;'>View Career Recommendations</a>";
        echo "<a href='/debug-report-card-processing?id={$rc['id']}' style='display: inline-block; padding: 8px 16px; background: #f59e0b; color: white; text-decoration: none; border-radius: 6px;'>Debug This Report Card</a>";
        echo "</div>";
        
        echo "</div>";
    }
}

// Show upload form
echo "<hr style='margin: 40px 0;'>";
echo "<h2>Upload New Report Card</h2>";
echo "<p><a href='/upload-report-card' style='display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 8px;'>Upload Report Card Now</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h1 { color: #667eea; }
h2 { color: #333; }
pre { background: #f5f5f5; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 12px; }
ul { line-height: 1.8; }
</style>
