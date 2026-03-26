-- Create careers table
CREATE TABLE IF NOT EXISTS careers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    min_aps_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category),
    INDEX idx_aps (min_aps_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create institutions table
CREATE TABLE IF NOT EXISTS institutions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('University', 'University of Technology', 'TVET College', 'Private College') NOT NULL,
    location VARCHAR(255),
    province VARCHAR(100),
    website VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_type (type),
    INDEX idx_province (province)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create career_institutions table (many-to-many relationship)
CREATE TABLE IF NOT EXISTS career_institutions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    career_id INT NOT NULL,
    institution_id INT NOT NULL,
    subject_requirements JSON,
    min_aps_score INT DEFAULT 0,
    additional_requirements TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    INDEX idx_career (career_id),
    INDEX idx_institution (institution_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('Environmental Scientist', 'Scientist studying the environment', 'Sciences', 28);

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
INSERT INTO career_institutions (career_id, institution_id, subject_requirements, min_aps_score, additional_requirements) VALUES
-- Doctor at UCT
(1, 1, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Physical Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Life Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 5, 'description', 'Level 5 (60-69%)')
), 36, 'National Benchmark Test (NBT) required'),

-- Doctor at Wits
(1, 2, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Physical Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Life Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 5, 'description', 'Level 5 (60-69%)')
), 36, 'National Benchmark Test (NBT) required'),

-- Engineer at Wits
(2, 2, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 6, 'description', 'Level 6 (70-79%)'),
    JSON_OBJECT('subject', 'Physical Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)')
), 32, 'NBT recommended'),

-- Engineer at UP
(2, 4, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 6, 'description', 'Level 6 (70-79%)'),
    JSON_OBJECT('subject', 'Physical Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)')
), 32, NULL),

-- Teacher at UJ
(3, 9, JSON_ARRAY(
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'Mathematics', 'level', 3, 'description', 'Level 3 (40-49%)')
), 24, NULL),

-- Teacher at NWU
(3, 11, JSON_ARRAY(
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'Mathematics OR Mathematical Literacy', 'level', 3, 'description', 'Level 3 (40-49%)')
), 24, NULL),

-- Accountant at UCT
(4, 1, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'Accounting', 'level', 5, 'description', 'Level 5 (60-69%) - Recommended')
), 28, NULL),

-- Accountant at Wits
(4, 2, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)')
), 28, NULL),

-- Lawyer at UCT
(5, 1, JSON_ARRAY(
    JSON_OBJECT('subject', 'English', 'level', 6, 'description', 'Level 6 (70-79%)'),
    JSON_OBJECT('subject', 'Mathematics OR Mathematical Literacy', 'level', 4, 'description', 'Level 4 (50-59%)')
), 30, NULL),

-- Lawyer at UP
(5, 4, JSON_ARRAY(
    JSON_OBJECT('subject', 'English', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Mathematics OR Mathematical Literacy', 'level', 4, 'description', 'Level 4 (50-59%)')
), 30, NULL),

-- Nurse at UKZN
(6, 5, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'Life Sciences', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)')
), 26, NULL),

-- Software Developer at UCT
(7, 1, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)')
), 28, NULL),

-- Software Developer at Wits
(7, 2, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)')
), 28, NULL),

-- Architect at UP
(8, 4, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Physical Sciences', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)')
), 32, 'Portfolio may be required'),

-- Psychologist at Stellenbosch
(9, 3, JSON_ARRAY(
    JSON_OBJECT('subject', 'English', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Mathematics OR Mathematical Literacy', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'Life Sciences', 'level', 4, 'description', 'Level 4 (50-59%) - Recommended')
), 28, NULL),

-- Pharmacist at UP
(10, 4, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Physical Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Life Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 5, 'description', 'Level 5 (60-69%)')
), 34, NULL),

-- Business Manager at UJ
(11, 9, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics OR Mathematical Literacy', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)')
), 24, NULL),

-- Electrician at CPUT
(12, 6, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 3, 'description', 'Level 3 (40-49%)'),
    JSON_OBJECT('subject', 'Physical Sciences', 'level', 3, 'description', 'Level 3 (40-49%)'),
    JSON_OBJECT('subject', 'English', 'level', 3, 'description', 'Level 3 (40-49%)')
), 22, NULL),

-- Chef at CPUT
(13, 6, JSON_ARRAY(
    JSON_OBJECT('subject', 'English', 'level', 3, 'description', 'Level 3 (40-49%)'),
    JSON_OBJECT('subject', 'Mathematics OR Mathematical Literacy', 'level', 2, 'description', 'Level 2 (30-39%)')
), 20, 'Practical assessment may be required'),

-- Journalist at Rhodes
(14, 10, JSON_ARRAY(
    JSON_OBJECT('subject', 'English', 'level', 6, 'description', 'Level 6 (70-79%)'),
    JSON_OBJECT('subject', 'Mathematics OR Mathematical Literacy', 'level', 4, 'description', 'Level 4 (50-59%)')
), 26, 'Portfolio of written work recommended'),

-- Social Worker at UWC
(15, 15, JSON_ARRAY(
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'Mathematics OR Mathematical Literacy', 'level', 3, 'description', 'Level 3 (40-49%)')
), 24, NULL),

-- Veterinarian at UP
(16, 4, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Physical Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Life Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 5, 'description', 'Level 5 (60-69%)')
), 34, NULL),

-- Dentist at Wits
(17, 2, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Physical Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'Life Sciences', 'level', 5, 'description', 'Level 5 (60-69%)'),
    JSON_OBJECT('subject', 'English', 'level', 5, 'description', 'Level 5 (60-69%)')
), 36, 'NBT required'),

-- Data Scientist at Wits
(18, 2, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 6, 'description', 'Level 6 (70-79%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)')
), 30, NULL),

-- Marketing Specialist at UJ
(19, 9, JSON_ARRAY(
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'Mathematics OR Mathematical Literacy', 'level', 4, 'description', 'Level 4 (50-59%)')
), 24, NULL),

-- Environmental Scientist at Stellenbosch
(20, 3, JSON_ARRAY(
    JSON_OBJECT('subject', 'Mathematics', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'Physical Sciences OR Life Sciences', 'level', 4, 'description', 'Level 4 (50-59%)'),
    JSON_OBJECT('subject', 'English', 'level', 4, 'description', 'Level 4 (50-59%)')
), 28, NULL);
