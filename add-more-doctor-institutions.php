<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Get all institution IDs
$stmt = $db->query('SELECT id, name, application_fee FROM institutions');
$institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Current Institutions (" . count($institutions) . ")</h2><ul>";
foreach ($institutions as $inst) {
    echo "<li>" . htmlspecialchars($inst['name']) . " - R" . $inst['application_fee'] . "</li>";
}
echo "</ul>";

// Check current Doctor institutions
$stmt = $db->query('SELECT ci.*, i.name FROM career_institutions ci JOIN institutions i ON ci.institution_id = i.id WHERE ci.career_id = 1');
$doctorInsts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<h3>Doctor currently at " . count($doctorInsts) . " institutions</h3>";

// Add Doctor to more institutions if not already there
$existingInstIds = array_column($doctorInsts, 'institution_id');
$allInstIds = array_column($institutions, 'id');
$instMap = array_column($institutions, null, 'id');

$subjectsTemplate = [
    ['subject' => 'Mathematics', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'Physical Sciences', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'Life Sciences', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'English', 'level' => 5, 'description' => 'Level 5 (60-69%)']
];

$qualificationsTemplate = [
    ['name' => 'MBChB Bachelor of Medicine and Bachelor of Surgery', 'type' => 'Degree', 'duration' => '6 years', 'qualification_code' => 'MED-01'],
    ['name' => 'BSc Medical Sciences', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'MED-SCI-01']
];

echo "<h3>Adding Doctor to more institutions...</h3><ul>";
foreach ($allInstIds as $instId) {
    if (!in_array($instId, $existingInstIds)) {
        $inst = $instMap[$instId];
        $subjectsJson = json_encode($subjectsTemplate);
        $qualsJson = json_encode($qualificationsTemplate);
        $aps = rand(30, 36);
        
        $stmt = $db->prepare('INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES (1, ?, ?, ?, ?)');
        $stmt->execute([
            $instId, 
            $subjectsJson, 
            $aps,
            'National Benchmark Test (NBT) may be required'
        ]);
        
        echo "<li>Added Doctor to " . htmlspecialchars($inst['name']) . " (APS: $aps, Fee: R" . $inst['application_fee'] . ")</li>";
    }
}
echo "</ul>";

// Verify
$stmt = $db->query('SELECT ci.*, i.name, i.application_fee FROM career_institutions ci JOIN institutions i ON ci.institution_id = i.id WHERE ci.career_id = 1');
$doctorInsts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<h3>Doctor now at " . count($doctorInsts) . " institutions</h3><ul>";
foreach ($doctorInsts as $inst) {
    echo "<li>" . htmlspecialchars($inst['name']) . " - APS: " . $inst['min_aps_score'] . ", Fee: R" . $inst['application_fee'] . "</li>";
}
echo "</ul>";

echo "<p><a href='/api/search-careers?q=Doctor' target='_blank'>Test API: Search for Doctor</a></p>";
echo "<p><a href='/upload-report-card'>Go to Upload Report Card</a></p>";
