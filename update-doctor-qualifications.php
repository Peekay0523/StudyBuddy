<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Update qualifications for Doctor career institutions
$qualifications = json_encode([
    ['name' => 'MBChB Bachelor of Medicine and Bachelor of Surgery', 'type' => 'Degree', 'duration' => '6 years', 'qualification_code' => 'MED-01'],
    ['name' => 'BSc Medical Sciences', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'MED-SCI-01'],
    ['name' => 'Bachelor of Nursing', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'NUR-01']
]);

$subjects = json_encode([
    ['subject' => 'Mathematics', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'Physical Sciences', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'Life Sciences', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'English', 'level' => 5, 'description' => 'Level 5 (60-69%)']
]);

// Update all Doctor career institutions with proper qualifications
$stmt = $db->prepare('UPDATE career_institutions SET subject_requirements = ?, min_aps_score = ?, additional_requirements = ? WHERE career_id = 1 AND institution_id = ?');

$institutionsData = [
    1 => ['aps' => 36, 'req' => 'National Benchmark Test (NBT) required'],
    2 => ['aps' => 36, 'req' => 'National Benchmark Test (NBT) required'],
    3 => ['aps' => 34, 'req' => 'NBT recommended'],
    4 => ['aps' => 35, 'req' => 'NBT required'],
    5 => ['aps' => 35, 'req' => 'NBT required'],
    6 => ['aps' => 32, 'req' => 'NBT may be required'],
    7 => ['aps' => 32, 'req' => 'NBT may be required'],
    8 => ['aps' => 32, 'req' => 'NBT may be required'],
    9 => ['aps' => 34, 'req' => 'NBT recommended'],
    10 => ['aps' => 35, 'req' => 'NBT required'],
    11 => ['aps' => 33, 'req' => 'NBT may be required'],
    12 => ['aps' => 34, 'req' => 'NBT recommended'],
    13 => ['aps' => 33, 'req' => 'NBT may be required'],
    14 => ['aps' => 30, 'req' => 'NBT may be required'],
    15 => ['aps' => 33, 'req' => 'NBT recommended']
];

foreach ($institutionsData as $instId => $data) {
    $stmt->execute([$subjects, $data['aps'], $data['req'], $instId]);
}

echo "Updated Doctor career institutions with proper qualifications and requirements.\n";

// Verify
$stmt = $db->query('SELECT ci.institution_id, i.name, ci.min_aps_score, ci.subject_requirements FROM career_institutions ci JOIN institutions i ON ci.institution_id = i.id WHERE ci.career_id = 1 ORDER BY ci.min_aps_score ASC');
echo "\nDoctor Career Institutions (sorted by APS):\n";
echo str_repeat("-", 80) . "\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("%-45s APS: %2d\n", substr($row['name'], 0, 45), $row['min_aps_score']);
}
