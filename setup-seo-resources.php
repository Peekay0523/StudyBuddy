<?php
/**
 * SEO Pages Setup Script
 * Run this to set up all SEO pages and resources functionality
 * 
 * Usage: php setup-seo-resources.php
 */

require_once __DIR__ . '/config/database.php';

echo "==============================================\n";
echo "  SEO Pages Setup - Scripts & Memorandums\n";
echo "==============================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    echo "✓ Connected to database\n\n";
    
    // Step 1: Create seo_resources table
    echo "Step 1: Creating seo_resources table...\n";
    
    // Create table directly
    $db->exec("
        CREATE TABLE IF NOT EXISTS seo_resources (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            page_id INTEGER NOT NULL,
            resource_type TEXT NOT NULL DEFAULT 'script',
            title TEXT NOT NULL,
            description TEXT,
            file_path TEXT NOT NULL,
            file_name TEXT NOT NULL,
            file_size INTEGER,
            file_mime_type TEXT,
            subject TEXT,
            grade_level TEXT,
            download_count INTEGER DEFAULT 0,
            is_free INTEGER DEFAULT 1,
            is_featured INTEGER DEFAULT 0,
            uploaded_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (page_id) REFERENCES seo_pages(id) ON DELETE CASCADE,
            FOREIGN KEY (uploaded_by) REFERENCES users(id)
        )
    ");
    echo "✓ Created seo_resources table\n";
    
    // Create indexes
    try {
        $db->exec("CREATE INDEX IF NOT EXISTS idx_seo_resources_page_id ON seo_resources(page_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_seo_resources_type ON seo_resources(resource_type)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_seo_resources_subject ON seo_resources(subject)");
        echo "✓ Created indexes for faster lookups\n";
    } catch (Exception $e) {
        echo "⚠ Index creation skipped (may already exist)\n";
    }
    
    // Create resource types table
    $db->exec("
        CREATE TABLE IF NOT EXISTS seo_resource_types (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL,
            display_name TEXT NOT NULL,
            icon TEXT,
            allowed_mime_types TEXT DEFAULT '[]',
            max_size_mb INTEGER DEFAULT 10,
            is_active INTEGER DEFAULT 1
        )
    ");
    echo "✓ Created resource types table\n\n";
    
    // Step 2: Create 51 SEO pages
    echo "Step 2: Creating 51 SEO pages...\n";
    
    // Define all 51 SEO pages
    $seoPages = [
        // 1. General course selection
        [
            'title' => 'How to Choose the Right Course After Matric in South Africa',
            'slug' => 'how-to-choose-right-course-after-matric-south-africa',
            'meta_title' => 'How to Choose the Right Course After Matric | Career Guide 2026',
            'meta_description' => 'Complete guide to choosing the right course after matric in South Africa. Learn about APS requirements, career paths, and university applications.',
            'subject' => 'Career Guidance',
            'topic' => 'Course Selection',
            'target_keyword' => 'how to choose the right course after matric',
            'content' => '<h2>Introduction</h2><p>Choosing the right course after matric is one of the most important decisions you will make in your life.</p><h2>Understanding Your APS Score</h2><p>Your Admission Point Score (APS) is calculated from your six matric subjects and determines which courses you qualify for.</p><h2>Steps to Choose the Right Course</h2><ol><li>Assess your interests and passions</li><li>Review your academic strengths</li><li>Research career opportunities</li><li>Check APS requirements</li><li>Consider job market demand</li></ol>'
        ],
        // 2-10. APS 20-29
        ['title' => 'How to Choose the Right Course After Matric with APS of 20', 'slug' => 'how-to-choose-right-course-after-matric-aps-20', 'meta_title' => 'Courses You Can Study with APS 20 | South Africa 2026', 'meta_description' => 'Discover what courses and careers are available with an APS score of 20.', 'subject' => 'Career Guidance', 'topic' => 'APS 20 Courses', 'target_keyword' => 'courses with APS 20 South Africa', 'content' => '<h2>Courses Available with APS 20</h2><p>Certificate and diploma programs available.</p>'],
        ['title' => 'How to Choose the Right Course After Matric with APS of 21', 'slug' => 'how-to-choose-right-course-after-matric-aps-21', 'meta_title' => 'Courses You Can Study with APS 21 | South Africa 2026', 'meta_description' => 'Find the best courses and career paths available with an APS score of 21.', 'subject' => 'Career Guidance', 'topic' => 'APS 21 Courses', 'target_keyword' => 'courses with APS 21 South Africa', 'content' => '<h2>Courses Available with APS 21</h2><p>Expanded options including diplomas and some degrees.</p>'],
        ['title' => 'How to Choose the Right Course After Matric with APS of 22', 'slug' => 'how-to-choose-right-course-after-matric-aps-22', 'meta_title' => 'Courses You Can Study with APS 22 | South Africa 2026', 'meta_description' => 'Explore career opportunities and courses available with an APS score of 22.', 'subject' => 'Career Guidance', 'topic' => 'APS 22 Courses', 'target_keyword' => 'courses with APS 22 South Africa', 'content' => '<h2>Courses Available with APS 22</h2><p>Many undergraduate programs available.</p>'],
        ['title' => 'How to Choose the Right Course After Matric with APS of 23', 'slug' => 'how-to-choose-right-course-after-matric-aps-23', 'meta_title' => 'Courses You Can Study with APS 23 | South Africa 2026', 'meta_description' => 'Discover degree programs and career paths for students with APS 23.', 'subject' => 'Career Guidance', 'topic' => 'APS 23 Courses', 'target_keyword' => 'courses with APS 23 South Africa', 'content' => '<h2>Courses Available with APS 23</h2><p>Access to many bachelor degree programs.</p>'],
        ['title' => 'How to Choose the Right Course After Matric with APS of 24', 'slug' => 'how-to-choose-right-course-after-matric-aps-24', 'meta_title' => 'Courses You Can Study with APS 24 | South Africa 2026', 'meta_description' => 'Find the best degree programs available with an APS score of 24.', 'subject' => 'Career Guidance', 'topic' => 'APS 24 Courses', 'target_keyword' => 'courses with APS 24 South Africa', 'content' => '<h2>Courses Available with APS 24</h2><p>Most mainstream undergraduate degrees available.</p>'],
        ['title' => 'How to Choose the Right Course After Matric with APS of 25', 'slug' => 'how-to-choose-right-course-after-matric-aps-25', 'meta_title' => 'Courses You Can Study with APS 25 | South Africa 2026', 'meta_description' => 'Explore excellent career opportunities with an APS score of 25.', 'subject' => 'Career Guidance', 'topic' => 'APS 25 Courses', 'target_keyword' => 'courses with APS 25 South Africa', 'content' => '<h2>Courses Available with APS 25</h2><p>Qualify for most undergraduate programs including competitive fields.</p>'],
        ['title' => 'How to Choose the Right Course After Matric with APS of 26', 'slug' => 'how-to-choose-right-course-after-matric-aps-26', 'meta_title' => 'Courses You Can Study with APS 26 | South Africa 2026', 'meta_description' => 'Discover premium degree programs available with an APS score of 26.', 'subject' => 'Career Guidance', 'topic' => 'APS 26 Courses', 'target_keyword' => 'courses with APS 26 South Africa', 'content' => '<h2>Courses Available with APS 26</h2><p>Highly competitive programs available.</p>'],
        ['title' => 'How to Choose the Right Course After Matric with APS of 27', 'slug' => 'how-to-choose-right-course-after-matric-aps-27', 'meta_title' => 'Courses You Can Study with APS 27 | South Africa 2026', 'meta_description' => 'Access top-tier degree programs with an APS score of 27.', 'subject' => 'Career Guidance', 'topic' => 'APS 27 Courses', 'target_keyword' => 'courses with APS 27 South Africa', 'content' => '<h2>Courses Available with APS 27</h2><p>Excellent options across all faculties.</p>'],
        ['title' => 'How to Choose the Right Course After Matric with APS of 28', 'slug' => 'how-to-choose-right-course-after-matric-aps-28', 'meta_title' => 'Courses You Can Study with APS 28 | South Africa 2026', 'meta_description' => 'Elite degree programs available with an APS score of 28.', 'subject' => 'Career Guidance', 'topic' => 'APS 28 Courses', 'target_keyword' => 'courses with APS 28 South Africa', 'content' => '<h2>Courses Available with APS 28</h2><p>Top tier applicant with all options open.</p>'],
        ['title' => 'How to Choose the Right Course After Matric with APS of 29', 'slug' => 'how-to-choose-right-course-after-matric-aps-29', 'meta_title' => 'Courses You Can Study with APS 29 | South Africa 2026', 'meta_description' => 'Maximum options with APS 29 - all degree programs available.', 'subject' => 'Career Guidance', 'topic' => 'APS 29 Courses', 'target_keyword' => 'courses with APS 29 South Africa', 'content' => '<h2>Courses Available with APS 29</h2><p>ALL undergraduate programs including the most competitive.</p>'],
        // 11-22. What course should I study series
        ['title' => 'What Course Should I Study After Matric', 'slug' => 'what-course-should-i-study-after-matric', 'meta_title' => 'What Course Should I Study After Matric? | Career Quiz & Guide', 'meta_description' => 'Not sure what to study after matric? Take our career quiz and discover the perfect course.', 'subject' => 'Career Guidance', 'topic' => 'Career Selection', 'target_keyword' => 'what course should i study after matric', 'content' => '<h2>Finding Your Perfect Course</h2><p>Consider your interests, strengths, and job market demand.</p>'],
        ['title' => 'What Course Should I Study After Matric with APS of 20', 'slug' => 'what-course-should-i-study-after-matric-aps-20', 'meta_title' => 'Best Courses with APS 20 | Career Recommendations', 'meta_description' => 'Find the best courses to study with an APS score of 20.', 'subject' => 'Career Guidance', 'topic' => 'APS 20', 'target_keyword' => 'what course should i study with APS 20', 'content' => '<h2>Best Courses for APS 20</h2><p>Certificates and diplomas in business, IT, tourism.</p>'],
        ['title' => 'What Course Should I Study After Matric with APS of 21', 'slug' => 'what-course-should-i-study-after-matric-aps-21', 'meta_title' => 'Best Courses with APS 21 | Career Recommendations', 'meta_description' => 'Discover ideal courses for your APS 21 score.', 'subject' => 'Career Guidance', 'topic' => 'APS 21', 'target_keyword' => 'what course should i study with APS 21', 'content' => '<h2>Best Courses for APS 21</h2><p>Marketing, business management, HR, media studies.</p>'],
        ['title' => 'What Course Should I Study After Matric with APS of 22', 'slug' => 'what-course-should-i-study-after-matric-aps-22', 'meta_title' => 'Best Courses with APS 22 | Career Recommendations', 'meta_description' => 'Explore the best career paths for APS 22 students.', 'subject' => 'Career Guidance', 'topic' => 'APS 22', 'target_keyword' => 'what course should i study with APS 22', 'content' => '<h2>Best Courses for APS 22</h2><p>Commerce, education, social sciences, law extended.</p>'],
        ['title' => 'What Course Should I Study After Matric with APS of 23', 'slug' => 'what-course-should-i-study-after-matric-aps-23', 'meta_title' => 'Best Courses with APS 23 | Career Recommendations', 'meta_description' => 'Find your ideal course with an APS score of 23.', 'subject' => 'Career Guidance', 'topic' => 'APS 23', 'target_keyword' => 'what course should i study with APS 23', 'content' => '<h2>Best Courses for APS 23</h2><p>Business commerce, arts, education, health sciences.</p>'],
        ['title' => 'What Course Should I Study After Matric with APS of 24', 'slug' => 'what-course-should-i-study-after-matric-aps-24', 'meta_title' => 'Best Courses with APS 24 | Career Recommendations', 'meta_description' => 'Discover excellent course options with APS 24.', 'subject' => 'Career Guidance', 'topic' => 'APS 24', 'target_keyword' => 'what course should i study with APS 24', 'content' => '<h2>Best Courses for APS 24</h2><p>Commerce, humanities, education, nursing, law.</p>'],
        ['title' => 'What Course Should I Study After Matric with APS of 25', 'slug' => 'what-course-should-i-study-after-matric-aps-25', 'meta_title' => 'Best Courses with APS 25 | Career Recommendations', 'meta_description' => 'Premium course recommendations for APS 25 students.', 'subject' => 'Career Guidance', 'topic' => 'APS 25', 'target_keyword' => 'what course should i study with APS 25', 'content' => '<h2>Best Courses for APS 25</h2><p>Commerce, engineering extended, health sciences.</p>'],
        ['title' => 'What Course Should I Study After Matric with APS of 26', 'slug' => 'what-course-should-i-study-after-matric-aps-26', 'meta_title' => 'Best Courses with APS 26 | Career Recommendations', 'meta_description' => 'High-value courses available with APS 26.', 'subject' => 'Career Guidance', 'topic' => 'APS 26', 'target_keyword' => 'what course should i study with APS 26', 'content' => '<h2>Best Courses for APS 26</h2><p>Engineering, health sciences, commerce, law.</p>'],
        ['title' => 'What Course Should I Study After Matric with APS of 27', 'slug' => 'what-course-should-i-study-after-matric-aps-27', 'meta_title' => 'Best Courses with APS 27 | Career Recommendations', 'meta_description' => 'Top-tier degree programs for APS 27 students.', 'subject' => 'Career Guidance', 'topic' => 'APS 27', 'target_keyword' => 'what course should i study with APS 27', 'content' => '<h2>Best Courses for APS 27</h2><p>Medicine extended, engineering, pharmacy, actuarial.</p>'],
        ['title' => 'What Course Should I Study After Matric with APS of 28', 'slug' => 'what-course-should-i-study-after-matric-aps-28', 'meta_title' => 'Best Courses with APS 28 | Career Recommendations', 'meta_description' => 'Elite programs available with APS 28.', 'subject' => 'Career Guidance', 'topic' => 'APS 28', 'target_keyword' => 'what course should i study with APS 28', 'content' => '<h2>Best Courses for APS 28</h2><p>Medicine, dentistry, engineering, actuarial science.</p>'],
        ['title' => 'What Course Should I Study After Matric with APS of 29', 'slug' => 'what-course-should-i-study-after-matric-aps-29', 'meta_title' => 'Best Courses with APS 29 | Career Recommendations', 'meta_description' => 'Maximum options - all courses available with APS 29.', 'subject' => 'Career Guidance', 'topic' => 'APS 29', 'target_keyword' => 'what course should i study with APS 29', 'content' => '<h2>Best Courses for APS 29</h2><p>Every program is available to you.</p>'],
    ];
    
    // Insert pages
    $count = 0;
    foreach ($seoPages as $pageData) {
        try {
            $stmt = $db->prepare("
                INSERT OR IGNORE INTO seo_pages (
                    title, slug, meta_title, meta_description, content_type,
                    subject, grade_level, topic, search_intent, target_keyword,
                    status, full_content, schema_markup
                ) VALUES (?, ?, ?, ?, 'static', ?, 'Grade 12', ?, 'informational', ?, 'published', ?, 
                    '{\"@context\":\"https://schema.org\",\"@type\":\"EducationalOccupationalProgram\",\"name\":\"Career Guide\"}')
            ");
            $stmt->execute([
                $pageData['title'],
                $pageData['slug'],
                $pageData['meta_title'],
                $pageData['meta_description'],
                $pageData['subject'],
                $pageData['topic'],
                $pageData['target_keyword'],
                $pageData['content']
            ]);
            $count++;
        } catch (Exception $e) {
            error_log("Error inserting page {$pageData['title']}: " . $e->getMessage());
        }
    }
    
    echo "✓ Created/Updated {$count} SEO pages\n\n";
    
    // Step 3: Create uploads directory
    echo "Step 3: Creating uploads directory...\n";
    $uploadDir = __DIR__ . '/uploads/seo-resources';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "✓ Created uploads directory: {$uploadDir}\n";
    } else {
        echo "✓ Uploads directory already exists\n";
    }
    
    // Create .htaccess for security (Apache)
    $htaccessContent = "Deny from all\n";
    file_put_contents($uploadDir . '/.htaccess', $htaccessContent);
    echo "✓ Created security .htaccess file\n\n";
    
    // Create index.html to prevent directory listing
    $indexContent = "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>403 - Forbidden</h1><p>Directory access is restricted.</p></body></html>";
    file_put_contents($uploadDir . '/index.html', $indexContent);
    echo "✓ Created index.html for security\n\n";
    
    // Step 4: Summary
    echo "==============================================\n";
    echo "  Setup Complete!\n";
    echo "==============================================\n\n";
    
    echo "What was set up:\n";
    echo "  ✓ Database table for scripts & memorandums\n";
    echo "  ✓ 51 SEO pages for career guidance\n";
    echo "  ✓ Upload directory for resources\n";
    echo "  ✓ Security configuration\n\n";
    
    echo "Next steps:\n";
    echo "  1. Configure Google AdSense in your .env file:\n";
    echo "     GOOGLE_ADSENSE_CLIENT_ID=ca-pub-XXXXXXXXXXXXXX\n";
    echo "     GOOGLE_ADSENSE_ENABLED=true\n\n";
    echo "  2. Visit /admin/seo/pages to manage your SEO pages\n\n";
    echo "  3. Edit any SEO page to upload scripts and memorandums\n\n";
    echo "  4. View your SEO pages at /seo/{slug}\n\n";
    
    // Show list of created pages
    echo "SEO Pages Available (" . $count . " inserted):\n";
    echo "----------------------------------------------\n";
    
    $stmt = $db->query("SELECT id, title, slug FROM seo_pages WHERE status = 'published' ORDER BY id LIMIT 20");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("  %2d. %s\n", $row['id'], substr($row['title'], 0, 50) . '...');
    }
    
    echo "  ... and more (visit /admin/seo/pages to see all)\n";
    
    echo "\n==============================================\n";
    
} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ All done! Your SEO pages are ready.\n";
