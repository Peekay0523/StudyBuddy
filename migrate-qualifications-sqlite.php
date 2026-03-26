<?php
/**
 * Migration: Update Existing Records with Qualifications
 * 
 * This script updates the career_institutions table to include qualification names.
 * Run this once to populate qualifications for existing records.
 * 
 * Usage: http://localhost:8000/migrate-qualifications-sqlite
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Qualifications Migration for SQLite</h1>";
echo "<p>This will update existing career-institution records with qualification names.</p>";
echo "<hr>";

try {
    $db = Database::getInstance()->getConnection();

    // Updates for Software Developer
    $updates = [
        // Software Developer at UCT (career_id=7, institution_id=1)
        [
            'career_id' => 7,
            'institution_id' => 1,
            'subject_requirements' => '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BSc Computer Science", "type": "Degree", "duration": "3 years", "qualification_code": "UCT-CS-01"}, {"name": "BEng Computer Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UCT-CE-01"}]}',
            'additional_requirements' => null
        ],
        // Software Developer at Wits (career_id=7, institution_id=2)
        [
            'career_id' => 7,
            'institution_id' => 2,
            'subject_requirements' => '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BSc Computer Science and Information Technology", "type": "Degree", "duration": "3 years", "qualification_code": "WITS-CS-01"}, {"name": "BSc Data Science", "type": "Degree", "duration": "3 years", "qualification_code": "WITS-DS-01"}]}',
            'additional_requirements' => null
        ],
        // Doctor at UCT (career_id=1, institution_id=1)
        [
            'career_id' => 1,
            'institution_id' => 1,
            'subject_requirements' => '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Life Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}], "qualifications": [{"name": "MBChB Bachelor of Medicine and Bachelor of Surgery", "type": "Degree", "duration": "6 years", "qualification_code": "UCT-MED-01"}]}',
            'additional_requirements' => 'National Benchmark Test (NBT) required'
        ],
        // Doctor at Wits (career_id=1, institution_id=2)
        [
            'career_id' => 1,
            'institution_id' => 2,
            'subject_requirements' => '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Life Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}], "qualifications": [{"name": "MBBCh Bachelor of Medicine and Bachelor of Surgery", "type": "Degree", "duration": "6 years", "qualification_code": "WITS-MED-01"}]}',
            'additional_requirements' => 'National Benchmark Test (NBT) required'
        ],
        // Engineer at Wits (career_id=2, institution_id=2)
        [
            'career_id' => 2,
            'institution_id' => 2,
            'subject_requirements' => '{"subjects": [{"subject": "Mathematics", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BSc Engineering (Civil)", "type": "Degree", "duration": "4 years", "qualification_code": "WITS-ENG-CIV"}, {"name": "BSc Engineering (Mechanical)", "type": "Degree", "duration": "4 years", "qualification_code": "WITS-ENG-MEC"}, {"name": "BSc Engineering (Electrical)", "type": "Degree", "duration": "4 years", "qualification_code": "WITS-ENG-ELE"}]}',
            'additional_requirements' => 'NBT recommended'
        ],
        // Engineer at UP (career_id=2, institution_id=4)
        [
            'career_id' => 2,
            'institution_id' => 4,
            'subject_requirements' => '{"subjects": [{"subject": "Mathematics", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BEng Civil Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UP-ENG-CIV"}, {"name": "BEng Mechanical Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UP-ENG-MEC"}, {"name": "BEng Chemical Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UP-ENG-CHE"}]}',
            'additional_requirements' => null
        ],
        // Teacher at UJ (career_id=3, institution_id=9)
        [
            'career_id' => 3,
            'institution_id' => 9,
            'subject_requirements' => '{"subjects": [{"subject": "English", "level": 4, "description": "Level 4 (50-69%)"}, {"subject": "Mathematics", "level": 3, "description": "Level 3 (40-49%)"}], "qualifications": [{"name": "Bachelor of Education (Foundation Phase)", "type": "Degree", "duration": "4 years", "qualification_code": "UJ-BED-FP"}, {"name": "Bachelor of Education (Intermediate Phase)", "type": "Degree", "duration": "4 years", "qualification_code": "UJ-BED-IP"}]}',
            'additional_requirements' => null
        ],
        // Teacher at NWU (career_id=3, institution_id=11)
        [
            'career_id' => 3,
            'institution_id' => 11,
            'subject_requirements' => '{"subjects": [{"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 3, "description": "Level 3 (40-49%)"}], "qualifications": [{"name": "BEd Foundation Phase Teaching", "type": "Degree", "duration": "4 years", "qualification_code": "NWU-BED-FP"}, {"name": "BEd Intermediate Phase Teaching", "type": "Degree", "duration": "4 years", "qualification_code": "NWU-BED-IP"}]}',
            'additional_requirements' => null
        ],
        // Accountant at UCT (career_id=4, institution_id=1)
        [
            'career_id' => 4,
            'institution_id' => 1,
            'subject_requirements' => '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Accounting", "level": 5, "description": "Level 5 (60-69%) - Recommended"}], "qualifications": [{"name": "BCom Accounting Sciences", "type": "Degree", "duration": "3 years", "qualification_code": "UCT-ACC-01"}, {"name": "BCom Finance and Tax", "type": "Degree", "duration": "3 years", "qualification_code": "UCT-FIN-01"}]}',
            'additional_requirements' => null
        ],
        // Accountant at Wits (career_id=4, institution_id=2)
        [
            'career_id' => 4,
            'institution_id' => 2,
            'subject_requirements' => '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BCom Accounting Sciences", "type": "Degree", "duration": "3 years", "qualification_code": "WITS-ACC-01"}, {"name": "BCom Financial Accounting", "type": "Degree", "duration": "3 years", "qualification_code": "WITS-FA-01"}]}',
            'additional_requirements' => null
        ],
        // Lawyer at UCT (career_id=5, institution_id=1)
        [
            'career_id' => 5,
            'institution_id' => 1,
            'subject_requirements' => '{"subjects": [{"subject": "English", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "LLB Bachelor of Laws", "type": "Degree", "duration": "4 years", "qualification_code": "UCT-LLB-01"}, {"name": "BCom Law", "type": "Degree", "duration": "3 years", "qualification_code": "UCT-LAW-01"}]}',
            'additional_requirements' => null
        ],
        // Lawyer at UP (career_id=5, institution_id=4)
        [
            'career_id' => 5,
            'institution_id' => 4,
            'subject_requirements' => '{"subjects": [{"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "LLB Bachelor of Laws", "type": "Degree", "duration": "4 years", "qualification_code": "UP-LLB-01"}, {"name": "BCom Law", "type": "Degree", "duration": "3 years", "qualification_code": "UP-LAW-01"}]}',
            'additional_requirements' => null
        ],
        // Nurse at UKZN (career_id=6, institution_id=5)
        [
            'career_id' => 6,
            'institution_id' => 5,
            'subject_requirements' => '{"subjects": [{"subject": "Mathematics", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Life Sciences", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "Bachelor of Nursing (Professional)", "type": "Degree", "duration": "4 years", "qualification_code": "UKZN-NUR-01"}, {"name": "Diploma in Nursing (Higher)", "type": "Diploma", "duration": "3 years", "qualification_code": "UKZN-NUR-DIP"}]}',
            'additional_requirements' => null
        ],
        // Electrician at CPUT (career_id=12, institution_id=6)
        [
            'career_id' => 12,
            'institution_id' => 6,
            'subject_requirements' => '{"subjects": [{"subject": "Mathematics", "level": 3, "description": "Level 3 (40-49%)"}, {"subject": "Physical Sciences", "level": 3, "description": "Level 3 (40-49%)"}, {"subject": "English", "level": 3, "description": "Level 3 (40-49%)"}], "qualifications": [{"name": "National Diploma in Electrical Engineering", "type": "Diploma", "duration": "3 years", "qualification_code": "CPUT-ELC-DIP"}, {"name": "Higher Certificate in Electrical Engineering", "type": "Certificate", "duration": "1 year", "qualification_code": "CPUT-ELC-HC"}]}',
            'additional_requirements' => null
        ],
        // Chef at CPUT (career_id=13, institution_id=6)
        [
            'career_id' => 13,
            'institution_id' => 6,
            'subject_requirements' => '{"subjects": [{"subject": "English", "level": 3, "description": "Level 3 (40-49%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 2, "description": "Level 2 (30-39%)"}], "qualifications": [{"name": "Diploma in Culinary Arts", "type": "Diploma", "duration": "3 years", "qualification_code": "CPUT-CHEF-DIP"}, {"name": "Higher Certificate in Food Preparation", "type": "Certificate", "duration": "1 year", "qualification_code": "CPUT-CHEF-HC"}]}',
            'additional_requirements' => 'Practical assessment may be required'
        ]
    ];

    $updated = 0;
    $skipped = 0;

    foreach ($updates as $update) {
        $stmt = $db->prepare("
            UPDATE career_institutions 
            SET subject_requirements = ?, additional_requirements = ?, updated_at = CURRENT_TIMESTAMP
            WHERE career_id = ? AND institution_id = ?
        ");

        try {
            $stmt->execute([
                $update['subject_requirements'],
                $update['additional_requirements'],
                $update['career_id'],
                $update['institution_id']
            ]);

            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✓ Updated: " . 
                    getCareerName($db, $update['career_id']) . " at " . 
                    getInstitutionName($db, $update['institution_id']) . "</p>";
                $updated++;
            } else {
                echo "<p style='color: orange;'>⚠ Skipped (record not found): Career ID {$update['career_id']}, Institution ID {$update['institution_id']}</p>";
                $skipped++;
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            $skipped++;
        }
    }

    echo "<hr>";
    echo "<h2 style='color: green;'>✓ Migration Complete!</h2>";
    echo "<p>Successfully updated <strong>{$updated}</strong> records.</p>";
    echo "<p>Skipped <strong>{$skipped}</strong> records.</p>";
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li><a href='/search-careers' style='color: #667eea; font-weight: 600;'>Go to Career Search Page</a></li>";
    echo "<li><a href='/test-career-api-page'>Test Career API</a></li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Migration Failed</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}

function getCareerName($db, $id) {
    $stmt = $db->prepare("SELECT name FROM careers WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ? $result['name'] : 'Unknown';
}

function getInstitutionName($db, $id) {
    $stmt = $db->prepare("SELECT name FROM institutions WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ? $result['name'] : 'Unknown';
}
?>
