-- Database Schema for School Learning Platform
-- SQLite Database

-- Users table (authentication)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    email TEXT,
    phone TEXT UNIQUE NOT NULL,
    role TEXT DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    joined_date DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE NOT NULL,
    student_id TEXT UNIQUE NOT NULL,
    grade_level TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Uploaded Scripts table (using user_id directly)
CREATE TABLE IF NOT EXISTS scripts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    file_name TEXT NOT NULL,
    file_path TEXT NOT NULL,
    file_size INTEGER DEFAULT 0,
    subject TEXT DEFAULT '',
    memorandum_generated INTEGER DEFAULT 0,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    processed INTEGER DEFAULT 0,
    processing_error TEXT,
    processed_topics TEXT DEFAULT '[]',
    challenging_topics TEXT DEFAULT '[]',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Old scripts table (for backwards compatibility)
CREATE TABLE IF NOT EXISTS uploaded_scripts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    subject TEXT DEFAULT '',
    grade_level TEXT DEFAULT '',
    file_path TEXT NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    processed INTEGER DEFAULT 0,
    processing_error TEXT,
    processed_topics TEXT DEFAULT '[]',
    challenging_topics TEXT DEFAULT '[]',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Memorandum table
CREATE TABLE IF NOT EXISTS memorandums (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    script_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (script_id) REFERENCES uploaded_scripts(id) ON DELETE CASCADE
);

-- Study Plans table
CREATE TABLE IF NOT EXISTS study_plans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active INTEGER DEFAULT 1,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Apply For Me submissions table
CREATE TABLE IF NOT EXISTS apply_for_me_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    application_id TEXT UNIQUE NOT NULL,
    student_id INTEGER NOT NULL,
    full_name TEXT NOT NULL,
    surname TEXT NOT NULL,
    id_number TEXT NOT NULL,
    date_of_birth TEXT,
    gender TEXT,
    citizenship TEXT,
    email TEXT,
    phone TEXT,
    home_address TEXT,
    school_name TEXT,
    grade_12_results TEXT,
    aps_score TEXT,
    subjects TEXT,
    matric_year TEXT,
    university_preference_1 TEXT,
    university_preference_2 TEXT,
    university_preference_3 TEXT,
    course_preference_1 TEXT,
    course_preference_2 TEXT,
    course_preference_3 TEXT,
    bursary_name TEXT,
    bursary_provider TEXT,
    institution_name TEXT,
    institution_type TEXT,
    id_copy_path TEXT,
    matric_cert_path TEXT,
    transcript_path TEXT,
    proof_res_path TEXT,
    cv_path TEXT,
    payment_status TEXT DEFAULT 'pending',
    payment_amount REAL DEFAULT 20.00,
    payment_reference TEXT,
    application_status TEXT DEFAULT 'pending',
    document_verification TEXT DEFAULT 'pending',
    admin_notes TEXT,
    policy_accepted INTEGER DEFAULT 0,
    current_step TEXT DEFAULT 'submitted',
    verified_by INTEGER DEFAULT NULL,
    verified_at DATETIME DEFAULT NULL,
    document_verification_notes TEXT DEFAULT NULL,
    rejection_reason TEXT DEFAULT NULL,
    submitted_to_institution_at DATETIME DEFAULT NULL,
    institution_response TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Report Cards table (using user_id directly)
CREATE TABLE IF NOT EXISTS report_cards (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    file_name TEXT NOT NULL,
    file_path TEXT NOT NULL,
    grade TEXT DEFAULT '',
    term TEXT DEFAULT '',
    average REAL DEFAULT 0,
    career_recommendations_generated INTEGER DEFAULT 0,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    grades_data TEXT DEFAULT '{}',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Old report cards table (for backwards compatibility)
CREATE TABLE IF NOT EXISTS old_report_cards (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    file_path TEXT NOT NULL,
    grade TEXT DEFAULT '',
    term TEXT DEFAULT '',
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    grades_data TEXT DEFAULT '{}',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Career Recommendations table
CREATE TABLE IF NOT EXISTS career_recommendations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    report_card_id INTEGER NOT NULL,
    recommended_careers TEXT DEFAULT '[]',
    strengths TEXT DEFAULT '[]',
    areas_for_improvement TEXT DEFAULT '[]',
    courses_data TEXT DEFAULT '[]',
    bursaries_data TEXT DEFAULT '[]',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (report_card_id) REFERENCES report_cards(id) ON DELETE CASCADE
);

-- Subscriptions table
CREATE TABLE IF NOT EXISTS subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    plan TEXT NOT NULL,
    price REAL NOT NULL,
    status TEXT DEFAULT 'active',
    current_period_start DATETIME,
    current_period_end DATETIME,
    cancelled_at DATETIME,
    payment_reference TEXT,
    payment_date DATETIME,
    proof_path TEXT,
    payment_method TEXT DEFAULT 'eft',
    transaction_id TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Study Groups table
CREATE TABLE IF NOT EXISTS study_groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    grade_level TEXT,
    creator_user_id INTEGER NOT NULL,
    max_members INTEGER DEFAULT 10,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Study Group Members table
CREATE TABLE IF NOT EXISTS study_group_members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    study_group_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    role TEXT DEFAULT 'member',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (study_group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(study_group_id, user_id)
);

-- Study Group Scripts table (shared resources)
CREATE TABLE IF NOT EXISTS study_group_scripts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    study_group_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    file_name TEXT NOT NULL,
    file_path TEXT NOT NULL,
    file_size INTEGER DEFAULT 0,
    description TEXT,
    resource_type TEXT DEFAULT 'script',
    video_url TEXT,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (study_group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tutors table (tutors available for students to learn from)
CREATE TABLE IF NOT EXISTS tutors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE NOT NULL,
    full_name TEXT,
    subjects TEXT,
    bio TEXT,
    hourly_rate TEXT,
    phone TEXT,
    email TEXT,
    is_available INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tutor Students table (links tutors to the students they teach)
CREATE TABLE IF NOT EXISTS tutor_students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tutor_id INTEGER NOT NULL,
    student_user_id INTEGER NOT NULL,
    status TEXT DEFAULT 'pending',
    teaching_mode TEXT DEFAULT 'online',
    extra_classes INTEGER DEFAULT 0,
    payment_method TEXT DEFAULT 'eft',
    grade TEXT,
    subjects TEXT,
    schedule TEXT,
    estimated_price TEXT,
    rejection_reason TEXT,
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME,
    FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(tutor_id, student_user_id)
);


CREATE TABLE IF NOT EXISTS my_resources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    resource_type TEXT DEFAULT 'script',
    file_name TEXT NOT NULL,
    file_path TEXT NOT NULL,
    file_size INTEGER DEFAULT 0,
    description TEXT,
    video_url TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_my_resources_user_id ON my_resources(user_id);


-- Study Group Messages table (chat)
CREATE TABLE IF NOT EXISTS study_group_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    study_group_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    message TEXT NOT NULL,
    message_type TEXT DEFAULT 'text',
    file_path TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (study_group_id) REFERENCES study_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Scan Usage Tracking (for free tier limits)
CREATE TABLE IF NOT EXISTS scan_usage (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    scan_count INTEGER DEFAULT 1,
    period_start DATETIME DEFAULT CURRENT_TIMESTAMP,
    period_end DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(user_id, period_start)
);

-- Scans table (PDF files stored in database)
CREATE TABLE IF NOT EXISTS scans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    filename TEXT NOT NULL,
    original_filename TEXT,
    file_data BLOB NOT NULL,
    file_size INTEGER DEFAULT 0,
    mime_type TEXT DEFAULT 'application/pdf',
    is_saved INTEGER DEFAULT 0,
    ocr_text TEXT DEFAULT '',
    ocr_extracted_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tutor Announcements (announcements tutors post for their active students:
-- online session links and contact lesson dates, scoped per grade)
CREATE TABLE IF NOT EXISTS tutor_announcements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tutor_id INTEGER NOT NULL,
    grade TEXT DEFAULT '',
    type TEXT DEFAULT 'online',
    title TEXT NOT NULL,
    description TEXT,
    link TEXT,
    session_date TEXT,
    session_time TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_tutor_announcements_tutor ON tutor_announcements(tutor_id);

-- Indexes for better performance
CREATE INDEX IF NOT EXISTS idx_students_user_id ON students(user_id);
CREATE INDEX IF NOT EXISTS idx_scripts_student_id ON uploaded_scripts(student_id);
CREATE INDEX IF NOT EXISTS idx_study_plans_student_id ON study_plans(student_id);
CREATE INDEX IF NOT EXISTS idx_report_cards_student_id ON report_cards(student_id);
CREATE INDEX IF NOT EXISTS idx_career_rec_student_id ON career_recommendations(student_id);
CREATE INDEX IF NOT EXISTS idx_study_groups_creator ON study_groups(creator_user_id);
CREATE INDEX IF NOT EXISTS idx_study_group_members_group ON study_group_members(study_group_id);
CREATE INDEX IF NOT EXISTS idx_study_group_members_user ON study_group_members(user_id);
CREATE INDEX IF NOT EXISTS idx_study_group_scripts_group ON study_group_scripts(study_group_id);
CREATE INDEX IF NOT EXISTS idx_study_group_messages_group ON study_group_messages(study_group_id);
CREATE INDEX IF NOT EXISTS idx_scan_usage_user_id ON scan_usage(user_id);
CREATE INDEX IF NOT EXISTS idx_scans_user_id ON scans(user_id);
CREATE INDEX IF NOT EXISTS idx_scans_is_saved ON scans(is_saved);

UPDATE users SET role = 'admin' WHERE username = 'Pontsho09';