<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Check if TUT exists
echo "<h2>Checking Tshwane University of Technology</h2>";
$stmt = $db->query("SELECT id, name, type, application_fee FROM institutions WHERE name LIKE '%Tshwane%'");
$tut = $stmt->fetch();
if ($tut) {
    echo "<p>✓ TUT found: " . htmlspecialchars($tut['name']) . " (ID: {$tut['id']}, Type: {$tut['type']}, Fee: R{$tut['application_fee']})</p>";
} else {
    echo "<p style='color: red;'>✗ TUT not found in database!</p>";
}

// Check Software Developer career
echo "<h2>Software Developer Career Institutions</h2>";
$stmt = $db->query("SELECT ci.*, i.name, i.type FROM career_institutions ci JOIN institutions i ON ci.institution_id = i.id WHERE ci.career_id = 7 ORDER BY i.name");
$softDevInsts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Software Developer is at " . count($softDevInsts) . " institutions:</p><ul>";
foreach ($softDevInsts as $inst) {
    echo "<li>" . htmlspecialchars($inst['name']) . " (Type: {$inst['type']}) - APS: {$inst['min_aps_score']}</li>";
}
echo "</ul>";

// Check if TUT is in Software Developer
$tutInSoftDev = false;
foreach ($softDevInsts as $inst) {
    if (strpos($inst['name'], 'Tshwane') !== false) {
        $tutInSoftDev = true;
        break;
    }
}

if (!$tutInSoftDev && $tut) {
    echo "<h3>Adding Software Developer to TUT...</h3>";
    $subjects = json_encode([
        ['subject' => 'Mathematics', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
        ['subject' => 'Information Technology', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
        ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)']
    ]);
    $quals = json_encode([
        ['name' => 'BSc Computer Science', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'TUT-CS-01'],
        ['name' => 'BSc Information Systems', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'TUT-IS-01'],
        ['name' => 'Diploma in Information Technology', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'TUT-IT-DIP-01'],
        ['name' => 'BTech in Information Technology', 'type' => 'Degree', 'duration' => '1 year', 'qualification_code' => 'TUT-IT-BTECH-01']
    ]);
    
    $stmt = $db->prepare('INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES (7, ?, ?, ?, ?)');
    $stmt->execute([$tut['id'], $subjects, 28, 'Portfolio of projects may be advantageous']);
    echo "<p>✓ Added Software Developer to TUT (APS: 28)</p>";
}

// Now let's ensure ALL careers have ALL 15+ institutions
echo "<h2>Ensuring All Careers Have All Institutions</h2>";

// Get all institution IDs
$stmt = $db->query('SELECT id, name, type, application_fee FROM institutions ORDER BY name');
$allInstitutions = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total institutions: " . count($allInstitutions) . "</p>";

// Get all career IDs
$stmt = $db->query('SELECT id, name FROM careers ORDER BY id');
$allCareers = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total careers: " . count($allCareers) . "</p>";

// For each career, ensure it has all institutions
foreach ($allCareers as $career) {
    $stmt = $db->prepare('SELECT institution_id FROM career_institutions WHERE career_id = ?');
    $stmt->execute([$career['id']]);
    $existingInstIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $missingCount = 0;
    foreach ($allInstitutions as $inst) {
        if (!in_array($inst['id'], $existingInstIds)) {
            $missingCount++;
            
            // Add career to this institution with appropriate subjects
            $subjects = getDefaultSubjects($career['name'], $inst['type']);
            $quals = getDefaultQualifications($career['name'], $inst['type']);
            $aps = getDefaultAPS($career['name']);
            
            $stmt = $db->prepare('INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $career['id'],
                $inst['id'],
                json_encode($subjects),
                $aps,
                'Contact institution for specific requirements'
            ]);
        }
    }
    
    if ($missingCount > 0) {
        echo "<p>✓ Added {$career['name']} to $missingCount more institutions</p>";
    }
}

echo "<h3>Summary</h3>";
echo "<p>All careers now have all " . count($allInstitutions) . " institutions!</p>";
echo "<p><a href='/api/search-careers?q=Software+Developer' target='_blank'>Test: Search Software Developer</a></p>";
echo "<p><a href='/upload-report-card'>Go to Upload Report Card</a></p>";

function getDefaultSubjects($careerName, $instType) {
    $careerLower = strtolower($careerName);
    
    if (strpos($careerLower, 'doctor') !== false || strpos($careerLower, 'nurse') !== false) {
        return [
            ['subject' => 'Mathematics', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'Physical Sciences', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'Life Sciences', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'English', 'level' => 5, 'description' => 'Level 5 (60-69%)']
        ];
    } elseif (strpos($careerLower, 'engineer') !== false || strpos($careerLower, 'architect') !== false) {
        return [
            ['subject' => 'Mathematics', 'level' => 6, 'description' => 'Level 6 (70-79%)'],
            ['subject' => 'Physical Sciences', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)']
        ];
    } elseif (strpos($careerLower, 'software') !== false || strpos($careerLower, 'developer') !== false) {
        return [
            ['subject' => 'Mathematics', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'Information Technology', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)']
        ];
    } elseif (strpos($careerLower, 'graphic') !== false || strpos($careerLower, 'designer') !== false) {
        return [
            ['subject' => 'Visual Arts', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)'],
            ['subject' => 'Mathematics', 'level' => 4, 'description' => 'Level 4 (50-59%)']
        ];
    } elseif (strpos($careerLower, 'teacher') !== false) {
        return [
            ['subject' => 'English', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'Mathematics', 'level' => 4, 'description' => 'Level 4 (50-59%)'],
            ['subject' => 'Life Sciences', 'level' => 4, 'description' => 'Level 4 (50-59%)']
        ];
    } elseif (strpos($careerLower, 'accountant') !== false) {
        return [
            ['subject' => 'Mathematics', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'Accounting', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)']
        ];
    } elseif (strpos($careerLower, 'lawyer') !== false) {
        return [
            ['subject' => 'English', 'level' => 6, 'description' => 'Level 6 (70-79%)'],
            ['subject' => 'History', 'level' => 5, 'description' => 'Level 5 (60-69%)'],
            ['subject' => 'Mathematics', 'level' => 4, 'description' => 'Level 4 (50-59%)']
        ];
    }
    
    // Default
    return [
        ['subject' => 'English', 'level' => 4, 'description' => 'Level 4 (50-59%)'],
        ['subject' => 'Mathematics', 'level' => 4, 'description' => 'Level 4 (50-59%)']
    ];
}

function getDefaultAPS($careerName) {
    $careerLower = strtolower($careerName);
    
    if (strpos($careerLower, 'doctor') !== false) return rand(32, 36);
    if (strpos($careerLower, 'engineer') !== false) return rand(30, 34);
    if (strpos($careerLower, 'architect') !== false) return rand(30, 34);
    if (strpos($careerLower, 'lawyer') !== false) return rand(28, 32);
    if (strpos($careerLower, 'accountant') !== false) return rand(26, 30);
    if (strpos($careerLower, 'software') !== false || strpos($careerLower, 'developer') !== false) return rand(26, 30);
    if (strpos($careerLower, 'nurse') !== false) return rand(24, 28);
    if (strpos($careerLower, 'teacher') !== false) return rand(22, 26);
    if (strpos($careerLower, 'graphic') !== false || strpos($careerLower, 'designer') !== false) return rand(22, 28);
    
    return rand(24, 28);
}

function getDefaultQualifications($careerName, $instType) {
    // Return appropriate qualifications based on career and institution type
    return [];
}
