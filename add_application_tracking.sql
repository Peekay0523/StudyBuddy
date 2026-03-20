-- Add bursary applications table for tracking student applications
CREATE TABLE IF NOT EXISTS bursary_applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    bursary_id INTEGER,
    bursary_name TEXT NOT NULL,
    bursary_provider TEXT NOT NULL,
    application_status TEXT DEFAULT 'pending',
    applied_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    deadline TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (bursary_id) REFERENCES bursaries(id) ON DELETE SET NULL
);

-- Add institution applications table for tracking university/college applications
CREATE TABLE IF NOT EXISTS institution_applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    institution_name TEXT NOT NULL,
    institution_type TEXT DEFAULT 'university',
    course_name TEXT,
    application_status TEXT DEFAULT 'pending',
    applied_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    deadline TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS idx_bursary_apps_student ON bursary_applications(student_id);
CREATE INDEX IF NOT EXISTS idx_bursary_apps_status ON bursary_applications(application_status);
CREATE INDEX IF NOT EXISTS idx_institution_apps_student ON institution_applications(student_id);
CREATE INDEX IF NOT EXISTS idx_institution_apps_status ON institution_applications(application_status);
