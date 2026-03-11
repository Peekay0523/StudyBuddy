<?php
/**
 * Debug: Check career recommendations data
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>Career Recommendations Debug</h2>";

$db = Database::getInstance()->getConnection();

// Check latest career recommendation
echo "<h3>Latest Career Recommendation</h3>";
echo "<pre>";
$careerRec = $db->query("SELECT * FROM career_recommendations ORDER BY created_at DESC LIMIT 1")->fetch();
if ($careerRec) {
    print_r($careerRec);
    echo "\n\nDecoded careers: ";
    print_r(json_decode($careerRec['recommended_careers'], true));
    echo "\n\nDecoded strengths: ";
    print_r(json_decode($careerRec['strengths'], true));
    echo "\n\nAPS Score: " . ($careerRec['aps_score'] ?? 'NULL');
} else {
    echo "No career recommendations found";
}
echo "</pre>";

// Check latest report card with grades
echo "<h3>Latest Report Card with Grades</h3>";
echo "<pre>";
$reportCard = $db->query("SELECT * FROM report_cards WHERE grades_data IS NOT NULL AND grades_data != '{}' ORDER BY uploaded_at DESC LIMIT 1")->fetch();
if ($reportCard) {
    print_r($reportCard);
    echo "\n\nGrades Data: ";
    print_r(json_decode($reportCard['grades_data'], true));
} else {
    echo "No report cards with grades data found";
}
echo "</pre>";

// Check if OpenAI key is configured
echo "<h3>OpenAI Configuration</h3>";
if (defined('OPENAI_API_KEY')) {
    $key = OPENAI_API_KEY;
    echo "API Key defined: " . (strlen($key) > 10 ? substr($key, 0, 10) . "..." : "Too short");
} else {
    echo "OPENAI_API_KEY constant NOT defined!";
}
echo "</pre>";

// Count records
echo "<h3>Record Counts</h3>";
echo "<ul>";
echo "<li>Career Recommendations: " . $db->query("SELECT COUNT(*) FROM career_recommendations")->fetchColumn() . "</li>";
echo "<li>Report Cards: " . $db->query("SELECT COUNT(*) FROM report_cards")->fetchColumn() . "</li>";
echo "<li>Report Cards with grades: " . $db->query("SELECT COUNT(*) FROM report_cards WHERE grades_data IS NOT NULL AND grades_data != '{}'")->fetchColumn() . "</li>";
echo "</ul>";
