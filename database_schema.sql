-- Database Schema for School Learning Platform
-- SQLite Database

-- Users table (authentication)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    email TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
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

-- Uploaded Scripts table
CREATE TABLE IF NOT EXISTS uploaded_scripts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    subject TEXT DEFAULT '',
    grade_level TEXT DEFAULT '',
    file_path TEXT NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
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

-- Report Cards table
CREATE TABLE IF NOT EXISTS report_cards (
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

-- Indexes for better performance
CREATE INDEX IF NOT EXISTS idx_students_user_id ON students(user_id);
CREATE INDEX IF NOT EXISTS idx_scripts_student_id ON uploaded_scripts(student_id);
CREATE INDEX IF NOT EXISTS idx_study_plans_student_id ON study_plans(student_id);
CREATE INDEX IF NOT EXISTS idx_report_cards_student_id ON report_cards(student_id);
CREATE INDEX IF NOT EXISTS idx_career_rec_student_id ON career_recommendations(student_id);
