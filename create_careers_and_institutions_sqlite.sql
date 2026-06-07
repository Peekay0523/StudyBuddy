-- Create careers table (SQLite)
CREATE TABLE IF NOT EXISTS careers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    category TEXT,
    min_aps_score INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create index for careers
CREATE INDEX IF NOT EXISTS idx_careers_name ON careers(name);
CREATE INDEX IF NOT EXISTS idx_careers_category ON careers(category);
CREATE INDEX IF NOT EXISTS idx_careers_aps ON careers(min_aps_score);

-- Create institutions table (SQLite)
CREATE TABLE IF NOT EXISTS institutions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    type TEXT NOT NULL CHECK(type IN ('University', 'University of Technology', 'TVET College', 'Private College')),
    location TEXT,
    province TEXT,
    website TEXT,
    contact_email TEXT,
    contact_phone TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create index for institutions
CREATE INDEX IF NOT EXISTS idx_institutions_name ON institutions(name);
CREATE INDEX IF NOT EXISTS idx_institutions_type ON institutions(type);
CREATE INDEX IF NOT EXISTS idx_institutions_province ON institutions(province);

-- Create career_institutions table (SQLite)
CREATE TABLE IF NOT EXISTS career_institutions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    career_id INTEGER NOT NULL,
    institution_id INTEGER NOT NULL,
    subject_requirements TEXT,
    min_aps_score INTEGER DEFAULT 0,
    additional_requirements TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE
);

-- Create index for career_institutions
CREATE INDEX IF NOT EXISTS idx_career_inst_career ON career_institutions(career_id);
CREATE INDEX IF NOT EXISTS idx_career_inst_institution ON career_institutions(institution_id);

-- Insert sample careers
INSERT INTO careers (name, description, category, min_aps_score) VALUES
('Doctor', 'Medical practitioner specializing in diagnosis and treatment of illnesses', 'Healthcare', 36),
('Engineer', 'Design and build structures, machines, and systems', 'Engineering', 32),
('Teacher', 'Educator who facilitates learning in schools', 'Education', 24),
('Accountant', 'Financial professional managing accounts and finances', 'Commerce', 28),
('Lawyer', 'Legal professional providing advice and representation', 'Law', 30),
('Nurse', 'Healthcare professional providing patient care', 'Healthcare', 26),
('Software Developer', 'Professional who designs and builds software applications', 'Technology', 28),
('Architect', 'Designer of buildings and structures', 'Engineering', 32),
('Psychologist', 'Professional studying mind and behavior', 'Social Sciences', 28),
('Pharmacist', 'Healthcare professional specializing in medicines', 'Healthcare', 34),
('Business Manager', 'Professional managing business operations', 'Commerce', 24),
('Electrician', 'Skilled tradesperson specializing in electrical systems', 'Trades', 22),
('Chef', 'Professional cook, typically in a restaurant setting', 'Hospitality', 20),
('Journalist', 'Professional who writes and reports news', 'Media', 26),
('Social Worker', 'Professional helping people cope with challenges', 'Social Sciences', 24),
('Veterinarian', 'Medical professional for animals', 'Healthcare', 34),
('Dentist', 'Medical professional specializing in oral health', 'Healthcare', 36),
('Data Scientist', 'Professional analyzing complex data', 'Technology', 30),
('Marketing Specialist', 'Professional promoting products and services', 'Commerce', 24),
('Environmental Scientist', 'Scientist studying the environment', 'Sciences', 28),
('Civil Engineer', 'Designs and oversees construction of infrastructure like roads and bridges', 'Engineering', 32),
('Mechanical Engineer', 'Designs and builds mechanical systems and machinery', 'Engineering', 32),
('Chemical Engineer', 'Develops chemical processes for manufacturing products', 'Engineering', 32),
('Graphic Designer', 'Creates visual concepts to communicate ideas that inspire and inform', 'Media', 24),
('UI/UX Designer', 'Focuses on the user experience and interface of digital products', 'Technology', 26),
('Cybersecurity Analyst', 'Protects computer networks and systems from cyber attacks', 'Technology', 30),
('Financial Analyst', 'Provides guidance to businesses and individuals making investment decisions', 'Commerce', 28),
('Human Resources Manager', 'Coordinates the administrative functions of an organization', 'Commerce', 24),
('Physiotherapist', 'Helps patients improve their physical movement and manage pain', 'Healthcare', 30),
('Occupational Therapist', 'Helps patients develop, recover, and improve skills for daily living', 'Healthcare', 28),
('Radiographer', 'Uses medical imaging equipment to help diagnose and treat illnesses', 'Healthcare', 26),
('Biomedical Scientist', 'Conducts research to improve human health through laboratory tests', 'Sciences', 32),
('Microbiologist', 'Studies microorganisms such as bacteria, viruses, and fungi', 'Sciences', 28),
('Actuary', 'Uses mathematics and statistics to assess risk in insurance and finance', 'Commerce', 36),
('Quantity Surveyor', 'Manages all costs relating to building and civil engineering projects', 'Engineering', 28),
('Town Planner', 'Develops plans and programs for the use of land', 'Social Sciences', 26),
('Aviation Pilot', 'Operates and controls aircraft to transport passengers or cargo', 'Media', 30),
('Geologist', 'Studies the Earth, including its composition and structure', 'Sciences', 26),
('Zoologist', 'Studies animals and their behavior, physiology, and ecosystems', 'Sciences', 26),
('Animator', 'Creates multiple images, known as frames, to create an illusion of movement', 'Media', 22);

-- Insert sample institutions
INSERT INTO institutions (name, type, location, province, website) VALUES
('University of Cape Town', 'University', 'Cape Town', 'Western Cape', 'https://www.uct.ac.za'),
('University of the Witwatersrand', 'University', 'Johannesburg', 'Gauteng', 'https://www.wits.ac.za'),
('Stellenbosch University', 'University', 'Stellenbosch', 'Western Cape', 'https://www.sun.ac.za'),
('University of Pretoria', 'University', 'Pretoria', 'Gauteng', 'https://www.up.ac.za'),
('University of KwaZulu-Natal', 'University', 'Durban', 'KwaZulu-Natal', 'https://www.ukzn.ac.za'),
('Cape Peninsula University of Technology', 'University of Technology', 'Cape Town', 'Western Cape', 'https://www.cput.ac.za'),
('Tshwane University of Technology', 'University of Technology', 'Pretoria', 'Gauteng', 'https://www.tut.ac.za'),
('Durban University of Technology', 'University of Technology', 'Durban', 'KwaZulu-Natal', 'https://www.dut.ac.za'),
('University of Johannesburg', 'University', 'Johannesburg', 'Gauteng', 'https://www.uj.ac.za'),
('Rhodes University', 'University', 'Makhanda', 'Eastern Cape', 'https://www.ru.ac.za'),
('North-West University', 'University', 'Potchefstroom', 'North West', 'https://www.nwu.ac.za'),
('University of the Free State', 'University', 'Bloemfontein', 'Free State', 'https://www.ufs.ac.za'),
('Nelson Mandela University', 'University', 'Gqeberha', 'Eastern Cape', 'https://www.mandela.ac.za'),
('University of Limpopo', 'University', 'Polokwane', 'Limpopo', 'https://www.ul.ac.za'),
('University of the Western Cape', 'University', 'Cape Town', 'Western Cape', 'https://www.uwc.ac.za');

-- Insert career-institution relationships with subject requirements
-- Doctor at UCT
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(1, 1, '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Life Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}], "qualifications": [{"name": "MBChB Bachelor of Medicine and Bachelor of Surgery", "type": "Degree", "duration": "6 years", "qualification_code": "UCT-MED-01"}]}', 36, 'National Benchmark Test (NBT) required');

-- Doctor at Wits
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(1, 2, '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Life Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}], "qualifications": [{"name": "MBBCh Bachelor of Medicine and Bachelor of Surgery", "type": "Degree", "duration": "6 years", "qualification_code": "WITS-MED-01"}]}', 36, 'National Benchmark Test (NBT) required');

-- Engineer at Wits
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(2, 2, '{"subjects": [{"subject": "Mathematics", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BSc Engineering (Civil)", "type": "Degree", "duration": "4 years", "qualification_code": "WITS-ENG-CIV"}, {"name": "BSc Engineering (Mechanical)", "type": "Degree", "duration": "4 years", "qualification_code": "WITS-ENG-MEC"}, {"name": "BSc Engineering (Electrical)", "type": "Degree", "duration": "4 years", "qualification_code": "WITS-ENG-ELE"}]}', 32, 'NBT recommended');

-- Engineer at UP
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(2, 4, '{"subjects": [{"subject": "Mathematics", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BEng Civil Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UP-ENG-CIV"}, {"name": "BEng Mechanical Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UP-ENG-MEC"}, {"name": "BEng Chemical Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UP-ENG-CHE"}]}', 32, NULL);

-- Teacher at UJ
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(3, 9, '{"subjects": [{"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Mathematics", "level": 3, "description": "Level 3 (40-49%)"}]}', 24, NULL);

-- Teacher at NWU
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(3, 11, '{"subjects": [{"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 3, "description": "Level 3 (40-49%)"}]}', 24, NULL);

-- Accountant at UCT
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(4, 1, '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Accounting", "level": 5, "description": "Level 5 (60-69%) - Recommended"}]}', 28, NULL);

-- Accountant at Wits
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(4, 2, '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}]}', 28, NULL);

-- Lawyer at UCT
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(5, 1, '{"subjects": [{"subject": "English", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 4, "description": "Level 4 (50-59%)"}]}', 30, NULL);

-- Lawyer at UP
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(5, 4, '{"subjects": [{"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 4, "description": "Level 4 (50-59%)"}]}', 30, NULL);

-- Nurse at UKZN
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(6, 5, '{"subjects": [{"subject": "Mathematics", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Life Sciences", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}]}', 26, NULL);

-- Software Developer at UCT
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(7, 1, '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BSc Computer Science", "type": "Degree", "duration": "3 years", "qualification_code": "UCT-CS-01"}, {"name": "BEng Computer Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UCT-CE-01"}]}', 28, NULL);

-- Software Developer at Wits
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(7, 2, '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BSc Computer Science and Information Technology", "type": "Degree", "duration": "3 years", "qualification_code": "WITS-CS-01"}, {"name": "BSc Data Science", "type": "Degree", "duration": "3 years", "qualification_code": "WITS-DS-01"}]}', 28, NULL);

-- Architect at UP
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(8, 4, '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Physical Sciences", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}]}', 32, 'Portfolio may be required');

-- Psychologist at Stellenbosch
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(9, 3, '{"subjects": [{"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Life Sciences", "level": 4, "description": "Level 4 (50-59%) - Recommended"}]}', 28, NULL);

-- Pharmacist at UP
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(10, 4, '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Life Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}]}', 34, NULL);

-- Business Manager at UJ
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(11, 9, '{"subjects": [{"subject": "Mathematics OR Mathematical Literacy", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}]}', 24, NULL);

-- Electrician at CPUT
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(12, 6, '{"subjects": [{"subject": "Mathematics", "level": 3, "description": "Level 3 (40-49%)"}, {"subject": "Physical Sciences", "level": 3, "description": "Level 3 (40-49%)"}, {"subject": "English", "level": 3, "description": "Level 3 (40-49%)"}]}', 22, NULL);

-- Chef at CPUT
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(13, 6, '{"subjects": [{"subject": "English", "level": 3, "description": "Level 3 (40-49%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 2, "description": "Level 2 (30-39%)"}]}', 20, 'Practical assessment may be required');

-- Journalist at Rhodes
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(14, 10, '{"subjects": [{"subject": "English", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 4, "description": "Level 4 (50-59%)"}]}', 26, 'Portfolio of written work recommended');

-- Social Worker at UWC
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(15, 15, '{"subjects": [{"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 3, "description": "Level 3 (40-49%)"}]}', 24, NULL);

-- Veterinarian at UP
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(16, 4, '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Life Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}]}', 34, NULL);

-- Dentist at Wits
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(17, 2, '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Life Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}]}', 36, 'NBT required');

-- Data Scientist at Wits
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(18, 2, '{"subjects": [{"subject": "Mathematics", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}]}', 30, NULL);

-- Marketing Specialist at UJ
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(19, 9, '{"subjects": [{"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 4, "description": "Level 4 (50-59%)"}]}', 24, NULL);

-- Environmental Scientist at Stellenbosch
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
(20, 3, '{"subjects": [{"subject": "Mathematics", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Physical Sciences OR Life Sciences", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}]}', 28, NULL);
