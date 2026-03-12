<?php
/**
 * Quick SEO Database Setup
 * Run this to create SEO tables
 * 
 * Usage: php migrate-seo.php
 */

require_once __DIR__ . '/config/database.php';

echo "Creating SEO tables...\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Create seo_pages table
    $db->exec("
    CREATE TABLE IF NOT EXISTS seo_pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        meta_title TEXT,
        meta_description TEXT,
        meta_keywords TEXT,
        content_type TEXT DEFAULT 'static',
        subject TEXT,
        grade_level TEXT,
        topic TEXT,
        search_intent TEXT DEFAULT 'informational',
        target_keyword TEXT,
        secondary_keywords TEXT DEFAULT '[]',
        full_content TEXT,
        ai_prompt_template TEXT,
        schema_markup TEXT,
        og_image TEXT,
        status TEXT DEFAULT 'draft',
        views INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        published_at DATETIME,
        created_by INTEGER DEFAULT NULL
    )");
    echo "✓ Created seo_pages table\n";
    
    // Create seo_keywords table
    $db->exec("
    CREATE TABLE IF NOT EXISTS seo_keywords (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        keyword TEXT UNIQUE NOT NULL,
        search_volume INTEGER DEFAULT 0,
        competition_level TEXT DEFAULT 'low',
        keyword_difficulty INTEGER DEFAULT 0,
        cpc REAL DEFAULT 0,
        trend_data TEXT DEFAULT '{}',
        related_keywords TEXT DEFAULT '[]',
        question_variants TEXT DEFAULT '[]',
        status TEXT DEFAULT 'active',
        last_checked DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Created seo_keywords table\n";
    
    // Create seo_qa_content table
    $db->exec("
    CREATE TABLE IF NOT EXISTS seo_qa_content (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        page_id INTEGER NOT NULL,
        question_number INTEGER,
        question_text TEXT NOT NULL,
        question_type TEXT DEFAULT 'standard',
        subject_area TEXT,
        grade_level TEXT,
        bloom_taxonomy_level TEXT DEFAULT 'remember',
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
    )");
    echo "✓ Created seo_qa_content table\n";
    
    // Create seo_subject_grades table
    $db->exec("
    CREATE TABLE IF NOT EXISTS seo_subject_grades (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject TEXT NOT NULL,
        subject_display_name TEXT,
        grade_level TEXT NOT NULL,
        curriculum TEXT DEFAULT 'CAPS',
        country TEXT DEFAULT 'ZA',
        is_active INTEGER DEFAULT 1,
        popular_topics TEXT DEFAULT '[]',
        exam_papers_available INTEGER DEFAULT 0,
        memorandum_available INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(subject, grade_level, curriculum)
    )");
    echo "✓ Created seo_subject_grades table\n";
    
    // Create seo_content_templates table
    $db->exec("
    CREATE TABLE IF NOT EXISTS seo_content_templates (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        template_name TEXT UNIQUE NOT NULL,
        template_type TEXT,
        subject TEXT,
        grade_level TEXT,
        template_structure TEXT NOT NULL,
        ai_system_prompt TEXT,
        ai_user_prompt_template TEXT,
        output_format TEXT DEFAULT 'html',
        includes_schema_markup INTEGER DEFAULT 1,
        includes_downloadable_pdf INTEGER DEFAULT 0,
        is_active INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Created seo_content_templates table\n";
    
    // Insert sample data
    echo "\nInserting sample data...\n";
    
    // Sample subject-grades
    $db->exec("INSERT OR IGNORE INTO seo_subject_grades (subject, subject_display_name, grade_level, curriculum, country) VALUES
    ('Mathematics', 'Mathematics', 'Grade 12', 'CAPS', 'ZA'),
    ('Mathematical Literacy', 'Mathematical Literacy', 'Grade 12', 'CAPS', 'ZA'),
    ('Physical Sciences', 'Physical Sciences', 'Grade 12', 'CAPS', 'ZA'),
    ('Life Sciences', 'Life Sciences', 'Grade 12', 'CAPS', 'ZA'),
    ('English Home Language', 'English Home Language', 'Grade 12', 'CAPS', 'ZA')");
    echo "✓ Inserted subject-grade combinations\n";
    
    // Sample templates
    $db->exec("INSERT OR IGNORE INTO seo_content_templates (template_name, template_type, subject, grade_level, template_structure, ai_system_prompt, ai_user_prompt_template) VALUES
    ('Grade 12 Math Memorandum Full', 'memorandum', 'Mathematics', 'Grade 12', '{\"sections\": [\"header\", \"question_answers\", \"summary\"]}', 'You are an expert mathematics educator creating detailed memorandum answers for Grade 12 CAPS Mathematics exams.', 'Create a complete memorandum for Grade 12 Mathematics covering: {topics}. Include question numbers, full solutions, and marks allocation.'),
    ('Physical Sciences Memorandum', 'memorandum', 'Physical Sciences', 'Grade 12', '{\"sections\": [\"header\", \"physics_section\", \"chemistry_section\"]}', 'You are a physical sciences expert creating memorandum answers.', 'Create memorandum for Physical Sciences Grade 12 covering {topic}.')");
    echo "✓ Inserted content templates\n";
    
    // Sample keywords
    $db->exec("INSERT OR IGNORE INTO seo_keywords (keyword, search_volume, competition_level, keyword_difficulty) VALUES
    ('math memorandum for grade 12 full answers', 50, 'low', 15),
    ('grade 12 mathematical literacy memorandum', 80, 'low', 18),
    ('physical sciences grade 12 physics memorandum', 40, 'low', 12),
    ('life sciences grade 12 dna and genetics', 30, 'low', 10),
    ('grade 12 calculus questions and answers', 60, 'low', 16)");
    echo "✓ Inserted keywords\n";
    
    // Sample SEO pages
    $db->exec("INSERT OR IGNORE INTO seo_pages (title, slug, meta_title, meta_description, content_type, subject, grade_level, topic, target_keyword, status, published_at, full_content, schema_markup) VALUES
    ('Mathematics Memorandum for Grade 12 - Full Answers', 'math-memorandum-grade-12-full-answers', 'Math Memorandum for Grade 12 Full Answers', 'Complete mathematics memorandum for Grade 12 with full answers and step-by-step solutions. CAPS curriculum aligned.', 'hybrid', 'Mathematics', 'Grade 12', 'Complete Memorandum', 'math memorandum for grade 12 full answers', 'published', CURRENT_TIMESTAMP, '<h2>Mathematics Grade 12 Complete Memorandum</h2><p>This comprehensive memorandum covers all major topics in the Grade 12 Mathematics CAPS curriculum.</p><h3>Algebra</h3><p>Question 1: Solve for x: 2x² - 5x - 3 = 0</p><p><strong>Solution:</strong></p><ul><li>Using the quadratic formula: x = (-b ± √(b² - 4ac)) / 2a</li><li>a = 2, b = -5, c = -3</li><li>x = (5 ± 7) / 4</li><li>x = 3 or x = -½</li></ul>', '{\"@context\":\"https://schema.org\",\"@type\":\"LearningResource\",\"name\":\"Mathematics Memorandum Grade 12\",\"educationalLevel\":\"Grade 12\"}'),
    ('Mathematical Literacy Grade 12 Finance Memorandum', 'math-lit-grade-12-finance-memorandum', 'Math Lit Grade 12 Finance Memorandum', 'Grade 12 Mathematical Literacy finance questions with complete answers. Learn interest, depreciation, and investments.', 'hybrid', 'Mathematical Literacy', 'Grade 12', 'Finance', 'mathematical literacy grade 12 finance memorandum', 'published', CURRENT_TIMESTAMP, '<h2>Mathematical Literacy - Finance</h2><p>Complete memorandum for Finance topics.</p><h3>Simple Interest</h3><p>Formula: SI = P × i × n</p>', '{\"@context\":\"https://schema.org\",\"@type\":\"LearningResource\",\"name\":\"Math Lit Finance\",\"educationalLevel\":\"Grade 12\"}'),
    ('Physical Sciences Grade 12 Physics Memorandum', 'physical-sciences-grade-12-physics-memorandum', 'Physical Sciences Grade 12 Physics Memorandum', 'Grade 12 Physical Sciences physics memorandum with solutions for mechanics and electricity.', 'hybrid', 'Physical Sciences', 'Grade 12', 'Physics', 'physical sciences grade 12 physics memorandum', 'published', CURRENT_TIMESTAMP, '<h2>Physical Sciences - Physics</h2><p>Complete solutions for Mechanics and Electricity.</p><h3>Newtons Second Law</h3><p>F = ma</p>', '{\"@context\":\"https://schema.org\",\"@type\":\"LearningResource\",\"name\":\"Physics Memorandum\",\"educationalLevel\":\"Grade 12\"}')");
    echo "✓ Inserted sample SEO pages\n";
    
    echo "\n==============================================\n";
    echo "✓ SEO setup complete!\n";
    echo "==============================================\n\n";
    echo "You can now:\n";
    echo "1. Visit /admin/seo/pages to manage pages\n";
    echo "2. Visit /seo to browse published pages\n";
    echo "3. Visit /seo/math-memorandum-grade-12-full-answers to see a sample page\n\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
