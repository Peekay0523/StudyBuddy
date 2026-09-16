<?php
/**
 * Migration: Create saved_application_profiles table
 * This stores personal/academic info so students don't have to re-enter it for future applications.
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS saved_application_profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id INTEGER NOT NULL,
            full_name TEXT,
            surname TEXT,
            id_number TEXT,
            date_of_birth TEXT,
            gender TEXT,
            citizenship TEXT DEFAULT 'South African',
            email TEXT,
            phone TEXT,
            home_address TEXT,
            school_name TEXT,
            matric_year TEXT,
            aps_score TEXT,
            grade_12_results TEXT,
            subjects TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
        )
    ");
    
    echo "Migration completed: saved_application_profiles table created successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}