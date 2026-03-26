<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Get all institution IDs with fees
$stmt = $db->query('SELECT id, name, application_fee FROM institutions');
$institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$instMap = array_column($institutions, null, 'id');
$allInstIds = array_column($institutions, 'id');

echo "<h2>Adding institutions for more careers...</h2>";

// Helper function to add career to institutions
function addCareerToInstitutions($db, $careerId, $careerName, $baseAps, $subjects, $qualifications) {
    global $allInstIds, $instMap;
    
    // Get existing institutions for this career
    $stmt = $db->prepare('SELECT institution_id FROM career_institutions WHERE career_id = ?');
    $stmt->execute([$careerId]);
    $existingInstIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>$careerName (currently at " . count($existingInstIds) . " institutions)</h3><ul>";
    
    foreach ($allInstIds as $instId) {
        if (!in_array($instId, $existingInstIds)) {
            $inst = $instMap[$instId];
            $subjectsJson = json_encode($subjects);
            $aps = $baseAps + rand(-2, 2);
            
            $stmt = $db->prepare('INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $careerId, 
                $instId, 
                $subjectsJson, 
                max(20, min(40, $aps)), // Keep APS between 20-40
                'Contact institution for specific requirements'
            ]);
            
            echo "<li>Added to " . htmlspecialchars($inst['name']) . " (APS: " . max(20, min(40, $aps)) . ", Fee: R" . $inst['application_fee'] . ")</li>";
        }
    }
    echo "</ul>";
}

// Engineer (career_id=2) - Mathematics, Physical Sciences, English
$engineerSubjects = [
    ['subject' => 'Mathematics', 'level' => 6, 'description' => 'Level 6 (70-79%)'],
    ['subject' => 'Physical Sciences', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)']
];
$engineerQuals = [
    ['name' => 'BEng Engineering', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'ENG-01'],
    ['name' => 'BSc Engineering', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'ENG-SCI-01'],
    ['name' => 'Diploma in Engineering', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'ENG-DIP-01']
];
addCareerToInstitutions($db, 2, 'Engineer', 32, $engineerSubjects, $engineerQuals);

// Teacher (career_id=3) - English, and subject specialization
$teacherSubjects = [
    ['subject' => 'English', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'Mathematics', 'level' => 4, 'description' => 'Level 4 (50-59%)'],
    ['subject' => 'Life Sciences', 'level' => 4, 'description' => 'Level 4 (50-59%)']
];
$teacherQuals = [
    ['name' => 'Bachelor of Education (BEd)', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'BED-01'],
    ['name' => 'BA in Education', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'BA-EDU-01'],
    ['name' => 'Diploma in Early Childhood Development', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'ECD-DIP-01']
];
addCareerToInstitutions($db, 3, 'Teacher', 24, $teacherSubjects, $teacherQuals);

// Accountant (career_id=4) - Mathematics, Accounting, English
$accountantSubjects = [
    ['subject' => 'Mathematics', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'Accounting', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)']
];
$accountantQuals = [
    ['name' => 'BCom Accounting Sciences', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'ACC-01'],
    ['name' => 'BCom Finance and Tax', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'FIN-01'],
    ['name' => 'Diploma in Accounting', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'ACC-DIP-01']
];
addCareerToInstitutions($db, 4, 'Accountant', 28, $accountantSubjects, $accountantQuals);

// Lawyer (career_id=5) - English, History/Legal Studies
$lawyerSubjects = [
    ['subject' => 'English', 'level' => 6, 'description' => 'Level 6 (70-79%)'],
    ['subject' => 'History', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'Mathematics', 'level' => 4, 'description' => 'Level 4 (50-59%)']
];
$lawyerQuals = [
    ['name' => 'LLB Bachelor of Laws', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'LLB-01'],
    ['name' => 'BCom Law', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'LAW-01'],
    ['name' => 'BA Law', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'BA-LAW-01']
];
addCareerToInstitutions($db, 5, 'Lawyer', 30, $lawyerSubjects, $lawyerQuals);

// Nurse (career_id=6) - Life Sciences, Mathematics, English
$nurseSubjects = [
    ['subject' => 'Life Sciences', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'Mathematics', 'level' => 4, 'description' => 'Level 4 (50-59%)'],
    ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)']
];
$nurseQuals = [
    ['name' => 'Bachelor of Nursing', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'NUR-DEG-01'],
    ['name' => 'BSc Nursing Science', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'NUR-SCI-01'],
    ['name' => 'Diploma in Nursing', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'NUR-DIP-01']
];
addCareerToInstitutions($db, 6, 'Nurse', 26, $nurseSubjects, $nurseQuals);

// Software Developer (career_id=7) - Mathematics, Physical Sciences/IT
$devSubjects = [
    ['subject' => 'Mathematics', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'Physical Sciences', 'level' => 4, 'description' => 'Level 4 (50-59%)'],
    ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)']
];
$devQuals = [
    ['name' => 'BSc Computer Science', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'CS-DEG-01'],
    ['name' => 'BEng Computer Engineering', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'CE-DEG-01'],
    ['name' => 'Diploma in Computer Science', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'CS-DIP-01'],
    ['name' => 'National Diploma in Software Development', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'SDEV-DIP-01']
];
addCareerToInstitutions($db, 7, 'Software Developer', 28, $devSubjects, $devQuals);

echo "<p><a href='/api/search-careers?q=Engineer' target='_blank'>Test: Search Engineer</a></p>";
echo "<p><a href='/api/search-careers?q=Teacher' target='_blank'>Test: Search Teacher</a></p>";
echo "<p><a href='/api/search-careers?q=Nurse' target='_blank'>Test: Search Nurse</a></p>";
echo "<p><a href='/upload-report-card'>Go to Upload Report Card</a></p>";
