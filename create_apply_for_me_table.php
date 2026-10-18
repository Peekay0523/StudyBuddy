<?php
/**
 * Migration: Create apply_for_me_submissions table
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    $sql = "CREATE TABLE IF NOT EXISTS apply_for_me_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INTEGER NOT NULL,
        full_name TEXT NOT NULL,
        surname TEXT NOT NULL,
        id_number TEXT NOT NULL,
        date_of_birth TEXT NOT NULL,
        gender TEXT NOT NULL,
        citizenship TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        home_address TEXT NOT NULL,
        school_name TEXT NOT NULL,
        grade_12_results TEXT,
        aps_score INTEGER DEFAULT 0,
        subjects TEXT,
        matric_year INTEGER,
        university_preference_1 TEXT,
        university_preference_2 TEXT,
        university_preference_3 TEXT,
        course_preference_1 TEXT,
        course_preference_2 TEXT,
        course_preference_3 TEXT,
        id_copy_path TEXT,
        matric_cert_path TEXT,
        transcript_path TEXT,
        proof_res_path TEXT,
        cv_path TEXT,
        payment_status TEXT DEFAULT ('pending'),
        application_status TEXT DEFAULT ('pending'),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )";

    $db->exec($sql);
    echo "SUCCESS: apply_for_me_submissions table created successfully!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
