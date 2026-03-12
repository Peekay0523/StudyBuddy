-- SEO Long-tail Pages Database Schema
-- Run this to create tables for SEO content management

-- SEO Pages table - stores all SEO-optimized pages
CREATE TABLE IF NOT EXISTS seo_pages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    meta_title TEXT,
    meta_description TEXT,
    meta_keywords TEXT,
    content_type TEXT DEFAULT 'static', -- static, dynamic, ai-generated, hybrid
    subject TEXT,
    grade_level TEXT,
    topic TEXT,
    search_intent TEXT, -- informational, navigational, transactional
    target_keyword TEXT,
    secondary_keywords TEXT DEFAULT '[]',
    full_content TEXT,
    ai_prompt_template TEXT,
    schema_markup TEXT,
    og_image TEXT,
    status TEXT DEFAULT 'draft', -- draft, published, archived
    views INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    published_at DATETIME,
    created_by INTEGER DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Long-tail Keywords table - tracks target keywords and performance
CREATE TABLE IF NOT EXISTS seo_keywords (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    keyword TEXT UNIQUE NOT NULL,
    search_volume INTEGER DEFAULT 0,
    competition_level TEXT DEFAULT 'low', -- low, medium, high
    keyword_difficulty INTEGER DEFAULT 0,
    cpc REAL DEFAULT 0,
    trend_data TEXT DEFAULT '{}', -- JSON storage for monthly trends
    related_keywords TEXT DEFAULT '[]',
    question_variants TEXT DEFAULT '[]',
    status TEXT DEFAULT 'active',
    last_checked DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Q&A Content table - for question/answer based pages (memorandums)
CREATE TABLE IF NOT EXISTS seo_qa_content (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page_id INTEGER NOT NULL,
    question_number INTEGER,
    question_text TEXT NOT NULL,
    question_type TEXT DEFAULT 'standard', -- standard, multiple_choice, essay, calculation
    subject_area TEXT,
    grade_level TEXT,
    bloom_taxonomy_level TEXT DEFAULT 'remember', -- remember, understand, apply, analyze, evaluate, create
    full_answer TEXT NOT NULL,
    step_by_step_solution TEXT,
    marks_allocated INTEGER,
    difficulty_level TEXT DEFAULT 'medium',
    related_concepts TEXT DEFAULT '[]',
    common_mistakes TEXT,
    tips_and_tricks TEXT,
    video_explanation_url TEXT,
    image_urls TEXT DEFAULT '[]',
    latex_formula TEXT,
    position_order INTEGER DEFAULT 0,
    is_featured INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES seo_pages(id) ON DELETE CASCADE
);

-- Subject-Grade Combinations table - pre-defined combinations for quick page generation
CREATE TABLE IF NOT EXISTS seo_subject_grades (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject TEXT NOT NULL,
    subject_display_name TEXT,
    grade_level TEXT NOT NULL,
    curriculum TEXT DEFAULT 'CAPS', -- CAPS, IEB, Cambridge, etc.
    country TEXT DEFAULT 'ZA',
    is_active INTEGER DEFAULT 1,
    popular_topics TEXT DEFAULT '[]',
    exam_papers_available INTEGER DEFAULT 0,
    memorandum_available INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(subject, grade_level, curriculum)
);

-- SEO Page Analytics table - tracks page performance
CREATE TABLE IF NOT EXISTS seo_page_analytics (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page_id INTEGER NOT NULL,
    date DATE NOT NULL,
    views INTEGER DEFAULT 0,
    unique_visitors INTEGER DEFAULT 0,
    avg_time_on_page INTEGER DEFAULT 0, -- seconds
    bounce_rate REAL DEFAULT 0,
    clicks_outbound INTEGER DEFAULT 0,
    downloads INTEGER DEFAULT 0,
    social_shares INTEGER DEFAULT 0,
    keyword_rankings TEXT DEFAULT '{}', -- JSON: {keyword: position}
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES seo_pages(id) ON DELETE CASCADE,
    UNIQUE(page_id, date)
);

-- Internal Links table - manages internal linking structure
CREATE TABLE IF NOT EXISTS seo_internal_links (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    source_page_id INTEGER NOT NULL,
    target_page_id INTEGER NOT NULL,
    anchor_text TEXT NOT NULL,
    link_type TEXT DEFAULT 'contextual', -- contextual, navigation, related, footer
    position TEXT DEFAULT 'content', -- content, sidebar, footer, header
    click_count INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (source_page_id) REFERENCES seo_pages(id) ON DELETE CASCADE,
    FOREIGN KEY (target_page_id) REFERENCES seo_pages(id) ON DELETE CASCADE
);

-- Content Templates table - reusable templates for AI generation
CREATE TABLE IF NOT EXISTS seo_content_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    template_name TEXT UNIQUE NOT NULL,
    template_type TEXT, -- memorandum, study_guide, practice_test, summary_notes
    subject TEXT,
    grade_level TEXT,
    template_structure TEXT NOT NULL, -- JSON structure
    ai_system_prompt TEXT,
    ai_user_prompt_template TEXT,
    output_format TEXT DEFAULT 'html',
    includes_schema_markup INTEGER DEFAULT 1,
    includes_downloadable_pdf INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample subject-grade combinations (South African CAPS curriculum)
INSERT OR IGNORE INTO seo_subject_grades (subject, subject_display_name, grade_level, curriculum, country, popular_topics) VALUES
('Mathematics', 'Mathematics', 'Grade 12', 'CAPS', 'ZA', '["Algebra", "Calculus", "Geometry", "Trigonometry", "Statistics", "Probability"]'),
('Mathematical Literacy', 'Mathematical Literacy', 'Grade 12', 'CAPS', 'ZA', '["Finance", "Measurement", "Maps and Plans", "Data Handling"]'),
('Physical Sciences', 'Physical Sciences', 'Grade 12', 'CAPS', 'ZA', '["Mechanics", "Waves and Light", "Electricity", "Chemical Bonding", "Organic Chemistry"]'),
('Life Sciences', 'Life Sciences', 'Grade 12', 'CAPS', 'ZA', '["DNA and Genetics", "Evolution", "Human Systems", "Ecology"]'),
('English Home Language', 'English Home Language', 'Grade 12', 'CAPS', 'ZA', '["Paper 1 - Language", "Paper 2 - Literature", "Essay Writing", "Comprehension"]'),
('Afrikaans Home Language', 'Afrikaans Home Language', 'Grade 12', 'CAPS', 'ZA', '["Taalgebruik", "Literatuur", "Opstel", "Begripstoets"]'),
('Geography', 'Geography', 'Grade 12', 'CAPS', 'ZA', '["Climate and Weather", "Geomorphology", "Economic Geography", "GIS"]'),
('History', 'History', 'Grade 12', 'CAPS', 'ZA', '["Cold War", "Civil Rights", "Apartheid", "Independence Movements"]'),
('Accounting', 'Accounting', 'Grade 12', 'CAPS', 'ZA', '["Financial Statements", "Cost Accounting", "Budgets", "Analysis and Interpretation"]'),
('Business Studies', 'Business Studies', 'Grade 12', 'CAPS', 'ZA', '["Business Environment", "Business Operations", "Business Roles", "Business Ethics"]');

-- Insert content templates
INSERT OR IGNORE INTO seo_content_templates (template_name, template_type, subject, grade_level, template_structure, ai_system_prompt, ai_user_prompt_template) VALUES
('Grade 12 Math Memorandum Full', 'memorandum', 'Mathematics', 'Grade 12', 
 '{"sections": ["header", "question_answers", "step_by_step_solutions", "summary"], "include_marks": true, "include_tips": true}',
 'You are an expert mathematics educator creating detailed memorandum answers for Grade 12 CAPS Mathematics exams. Provide clear, step-by-step solutions with explanations.',
 'Create a complete memorandum for Grade 12 Mathematics covering: {topics}. Include question numbers, full solutions, marks allocation, and common mistakes to avoid.'),
('Physical Sciences Memorandum', 'memorandum', 'Physical Sciences', 'Grade 12',
 '{"sections": ["header", "physics_section", "chemistry_section", "formulas"], "include_diagrams": true}',
 'You are a physical sciences expert creating memorandum answers with physics and chemistry solutions.',
 'Create memorandum for Physical Sciences Grade 12 covering {topic}. Include formulas, calculations, and explanations.'),
('Life Sciences Study Guide', 'study_guide', 'Life Sciences', 'Grade 12',
 '{"sections": ["key_concepts", "diagrams", "practice_questions", "answers"], "include_glossary": true}',
 'You are a life sciences teacher creating comprehensive study guides.',
 'Create study guide for {topic} including key concepts, diagrams descriptions, and practice questions.');

-- Insert sample long-tail keywords (low competition, high specificity)
INSERT OR IGNORE INTO seo_keywords (keyword, search_volume, competition_level, keyword_difficulty, question_variants) VALUES
('math memorandum for grade 12 full answers', 50, 'low', 15, '["where can I find grade 12 math memorandum with full answers", "complete math memorandum grade 12 CAPS"]'),
('grade 12 mathematical literacy memorandum 2024', 80, 'low', 18, '["math lit grade 12 memorandum with working out", "mathematical literacy past papers with answers grade 12"]'),
('physical sciences grade 12 physics memorandum', 40, 'low', 12, '["grade 12 physics memorandum CAPS", "physical sciences p1 memorandum grade 12"]'),
('life sciences grade 12 dna and genetics memorandum', 30, 'low', 10, '["genetics questions and answers grade 12", "life sciences grade 12 paper 2 memorandum"]'),
('grade 12 calculus questions and answers pdf', 60, 'low', 16, '["calculus grade 12 past exam questions", "grade 12 differentiation and integration answers"]'),
('trigonometry memorandum grade 12 with steps', 25, 'low', 8, '["trigonometry grade 12 solved examples", "grade 12 trig identities memorandum"]'),
('grade 12 algebra equations with solutions', 35, 'low', 11, '["algebra grade 12 past papers", "quadratic equations grade 12 answers"]'),
('euclidean geometry grade 12 theorems and proofs', 20, 'low', 9, '["geometry grade 12 riders with solutions", "circle geometry grade 12 memorandum"]'),
('grade 12 statistics and probability memorandum', 28, 'low', 10, '["statistics grade 12 past exam questions", "probability grade 12 worked examples"]'),
('grade 12 financial mathematics memorandum', 45, 'low', 14, '["finance questions grade 12 math lit", "interest and depreciation grade 12 answers"]');
