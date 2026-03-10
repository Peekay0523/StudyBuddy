<?php
/**
 * Migration: Update existing report cards with user_id
 */

require_once __DIR__ . '/config/database.php';

echo "Running migration: Update report_cards user_id...\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Update report_cards to set user_id from students table
    $updated = $db->exec("
        UPDATE report_cards
        SET user_id = (
            SELECT user_id FROM students WHERE students.id = report_cards.student_id
        )
        WHERE user_id IS NULL AND student_id IS NOT NULL
    ");
    
    echo "Updated $updated report card records with user_id.\n";
    
    // Verify the update
    $remaining = $db->query("SELECT COUNT(*) FROM report_cards WHERE user_id IS NULL")->fetchColumn();
    echo "Remaining records with NULL user_id: $remaining\n";
    
    // Show updated records
    echo "\nUpdated report cards:\n";
    $reportCards = $db->query("SELECT id, student_id, user_id, file_path, grade FROM report_cards ORDER BY uploaded_at DESC LIMIT 10")->fetchAll();
    foreach ($reportCards as $rc) {
        echo "ID: {$rc['id']}, student_id: {$rc['student_id']}, user_id: {$rc['user_id']}, file: {$rc['file_path']}\n";
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
