<?php
/**
 * Migration: Update strengths based on actual subjects
 */

require_once __DIR__ . '/config/database.php';

echo "Running migration: Update strengths based on actual subjects...\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Get all career recommendations with their report cards
    $careerRecs = $db->query("
        SELECT cr.id, rc.grades_data
        FROM career_recommendations cr
        JOIN report_cards rc ON cr.report_card_id = rc.id
        WHERE rc.grades_data IS NOT NULL AND rc.grades_data != '{}'
    ")->fetchAll();
    
    $updated = 0;
    foreach ($careerRecs as $cr) {
        $gradesData = json_decode($cr['grades_data'], true);
        if ($gradesData) {
            $subjectList = array_keys($gradesData);
            $strengths = [];
            
            foreach ($subjectList as $subject) {
                if (stripos($subject, 'Math') !== false) {
                    $strengths[] = 'Mathematical proficiency';
                } elseif (stripos($subject, 'Science') !== false || stripos($subject, 'Physics') !== false || stripos($subject, 'Chemistry') !== false) {
                    $strengths[] = 'Scientific understanding';
                } elseif (stripos($subject, 'English') !== false || stripos($subject, 'Language') !== false) {
                    $strengths[] = 'Language skills';
                } elseif (stripos($subject, 'Geography') !== false) {
                    $strengths[] = 'Geographical knowledge';
                } elseif (stripos($subject, 'History') !== false) {
                    $strengths[] = 'Historical analysis';
                } elseif (stripos($subject, 'Accounting') !== false || stripos($subject, 'Business') !== false) {
                    $strengths[] = 'Business acumen';
                } elseif (stripos($subject, 'Life') !== false) {
                    $strengths[] = 'Life sciences knowledge';
                } else {
                    $strengths[] = $subject;
                }
            }
            
            $strengths = array_slice(array_unique($strengths), 0, 5);
            
            $update = $db->prepare("UPDATE career_recommendations SET strengths = ? WHERE id = ?");
            $update->execute([json_encode($strengths), $cr['id']]);
            
            echo "Career Rec #{$cr['id']}: Strengths = " . json_encode($strengths) . "\n";
            $updated++;
        }
    }
    
    echo "\nUpdated $updated career recommendations with meaningful strengths.\n";
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
