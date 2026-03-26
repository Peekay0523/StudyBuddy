<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Add Graphic Design career if it doesn't exist
$stmt = $db->prepare("SELECT id FROM careers WHERE name LIKE '%Graphic Design%' OR name LIKE '%Designer%'");
$stmt->execute();
$existing = $stmt->fetch();

if ($existing) {
    echo "Graphic Design career already exists with ID: " . $existing['id'] . "\n";
    $careerId = $existing['id'];
} else {
    // Add Graphic Design career
    $stmt = $db->prepare("INSERT INTO careers (name, description, category, min_aps_score) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'Graphic Designer',
        'Creative professional who designs visual content for print, digital media, and branding',
        'Creative Arts',
        24
    ]);
    $careerId = $db->lastInsertId();
    echo "Added Graphic Designer career with ID: $careerId\n";
}

// Get all institutions
$stmt = $db->query('SELECT id, name, application_fee FROM institutions');
$institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add Graphic Design to all institutions
$subjectsTemplate = [
    ['subject' => 'Visual Arts', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
    ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)'],
    ['subject' => 'Mathematics', 'level' => 4, 'description' => 'Level 4 (50-59%)']
];

$qualificationsByType = [
    'University' => [
        ['name' => 'Bachelor of Fine Arts (BFA) in Graphic Design', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'BFA-GD-01'],
        ['name' => 'Bachelor of Arts in Visual Communication', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'BA-VC-01'],
        ['name' => 'Bachelor of Arts in Graphic Design', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'BA-GD-01']
    ],
    'University of Technology' => [
        ['name' => 'National Diploma in Graphic Design', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'ND-GD-01'],
        ['name' => 'Bachelor of Technology in Graphic Design', 'type' => 'Degree', 'duration' => '1 year', 'qualification_code' => 'BT-GD-01'],
        ['name' => 'Diploma in Graphic Design', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'DIP-GD-01']
    ],
    'TVET College' => [
        ['name' => 'National Certificate in Graphic Design', 'type' => 'Certificate', 'duration' => '1-2 years', 'qualification_code' => 'NC-GD-01'],
        ['name' => 'Certificate in Desktop Publishing', 'type' => 'Certificate', 'duration' => '1 year', 'qualification_code' => 'CERT-DP-01']
    ],
    'Private College' => [
        ['name' => 'Diploma in Graphic Design & Multimedia', 'type' => 'Diploma', 'duration' => '2 years', 'qualification_code' => 'DIP-GDM-01'],
        ['name' => 'Certificate in Graphic Design', 'type' => 'Certificate', 'duration' => '1 year', 'qualification_code' => 'CERT-GD-01']
    ]
];

echo "\nAdding Graphic Designer to institutions...\n";
echo str_repeat("-", 60) . "\n";

foreach ($institutions as $inst) {
    // Check if already exists
    $stmt = $db->prepare('SELECT id FROM career_institutions WHERE career_id = ? AND institution_id = ?');
    $stmt->execute([$careerId, $inst['id']]);
    
    if (!$stmt->fetch()) {
        $subjectsJson = json_encode($subjectsTemplate);
        $qualsJson = json_encode($qualificationsByType[$inst['type']] ?? $qualificationsByType['University']);
        $aps = rand(22, 28);
        
        $stmt = $db->prepare('INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $careerId,
            $inst['id'],
            $subjectsJson,
            $aps,
            'Portfolio of creative work may be required'
        ]);
        
        echo sprintf("✓ %-45s APS: %2d Fee: R%3d\n", substr($inst['name'], 0, 45), $aps, $inst['application_fee']);
    }
}

echo "\nGraphic Designer is now available at " . count($institutions) . " institutions!\n";
echo "\n<a href='/api/search-careers?q=Graphic+Designer' target='_blank'>Test API: Search Graphic Designer</a>\n";
echo "<br><a href='/upload-report-card'>Go to Upload Report Card</a>\n";
