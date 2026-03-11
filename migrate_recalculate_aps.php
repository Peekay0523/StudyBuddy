<?php
/**
 * Migration: Recalculate APS for existing career recommendations
 */

require_once __DIR__ . '/config/database.php';

echo "Running migration: Recalculate APS for existing career recommendations...\n\n";

/**
 * Extract percentage from grade string
 */
function extractPercentage($grade) {
    // Handle percentage format like "75%"
    if (preg_match('/(\d+)/', $grade, $matches)) {
        return intval($matches[1]);
    }
    // Handle level format like "Level 5" or just "5"
    if (preg_match('/[Ll]evel\s*(\d+)/', $grade, $matches)) {
        $level = intval($matches[1]);
        $levels = [7 => 85, 6 => 75, 5 => 65, 4 => 55, 3 => 45, 2 => 35, 1 => 20];
        return $levels[$level] ?? 65;
    }
    // Handle range like "70-79%"
    if (preg_match('/(\d+)-(\d+)/', $grade, $matches)) {
        return intval(($matches[1] + $matches[2]) / 2);
    }
    // Default to 65% if can't parse
    return 65;
}

/**
 * Convert percentage to APS points (SA NSC scale)
 */
function percentageToAPSPoints($percentage) {
    if ($percentage >= 80) return 7;
    if ($percentage >= 70) return 6;
    if ($percentage >= 60) return 5;
    if ($percentage >= 50) return 4;
    if ($percentage >= 40) return 3;
    if ($percentage >= 30) return 2;
    if ($percentage >= 0) return 1;
    return 0;
}

/**
 * Calculate APS (Admission Point Score) from grades
 */
function calculateAPS($gradesData) {
    $total = 0;
    $count = 0;

    foreach ($gradesData as $subject => $grade) {
        // Skip Life Orientation for APS (doesn't count for most universities)
        if (stripos($subject, 'Life Orientation') !== false || stripos($subject, 'LO') !== false) {
            continue;
        }

        $percentage = extractPercentage($grade);
        $points = percentageToAPSPoints($percentage);
        $total += $points;
        $count++;
    }

    return $count > 0 ? $total : 0;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get all report cards with grades data
    $reportCards = $db->query("
        SELECT rc.id, rc.grades_data, cr.id as career_rec_id
        FROM report_cards rc
        LEFT JOIN career_recommendations cr ON rc.id = cr.report_card_id
        WHERE rc.grades_data IS NOT NULL AND rc.grades_data != '{}'
        AND cr.id IS NOT NULL
    ")->fetchAll();
    
    $updated = 0;
    foreach ($reportCards as $rc) {
        $gradesData = json_decode($rc['grades_data'], true);
        if ($gradesData) {
            // Calculate APS
            $aps = calculateAPS($gradesData);
            
            // Update career recommendation
            $update = $db->prepare("UPDATE career_recommendations SET aps_score = ? WHERE id = ?");
            $update->execute([$aps, $rc['career_rec_id']]);
            
            echo "Report Card #{$rc['id']}: APS = $aps (Subjects: " . count($gradesData) . ", Excluding LO)\n";
            $updated++;
        }
    }
    
    echo "\nUpdated $updated career recommendations with APS scores.\n";
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
