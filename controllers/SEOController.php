<?php
/**
 * SEO Pages Controller
 * Handles SEO long-tail page generation and display
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SEOPage.php';

class SEOController {
    private $seoModel;
    private $openai;

    public function __construct() {
        $this->seoModel = new SEOPage();
        
        // Initialize OpenAI if available
        $openaiKey = getenv('OPENAI_API_KEY') ?: $_ENV['OPENAI_API_KEY'] ?? '';
        if (!empty($openaiKey)) {
            $this->openai = new OpenAI($openaiKey);
        }
    }

    /**
     * Display SEO page by slug
     * URL: /seo/{slug}
     */
    public function show($slug) {
        // Decode slug (handle URL-encoded characters)
        $slug = urldecode($slug);
        
        $page = $this->seoModel->findBySlug($slug);
        
        if (!$page) {
            // Try to find similar pages or show 404
            $this->show404($slug);
            return;
        }

        // Increment view count
        $this->seoModel->incrementViews($page['id']);

        // Get Q&A content if available
        $qaContent = $this->seoModel->getQAContent($page['id']);

        // Get related pages
        $relatedPages = $this->seoModel->getRelatedPages(
            $page['id'],
            $page['subject'],
            $page['grade_level']
        );

        // Render the page
        include __DIR__ . '/../templates/pages/seo_page.php';
    }

    /**
     * Browse pages by subject and grade
     * URL: /seo/{subject}/{grade}
     */
    public function browse($subject, $grade) {
        $subject = urldecode($subject);
        $grade = urldecode($grade);
        
        $pages = $this->seoModel->getBySubjectAndGrade($subject, $grade);
        
        if (empty($pages)) {
            // No pages yet - show a page suggesting what's available
            $this->showBrowseEmpty($subject, $grade);
            return;
        }

        include __DIR__ . '/../templates/pages/seo_browse.php';
    }

    /**
     * Search SEO pages
     * URL: /seo/search?q={query}
     */
    public function search() {
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            header('Location: /seo');
            exit;
        }

        $results = $this->seoModel->searchByKeyword($query);
        include __DIR__ . '/../templates/pages/seo_search.php';
    }

    /**
     * Generate SEO page using AI
     * Admin function to create new pages
     */
    public function generate() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            include __DIR__ . '/../templates/pages/admin/seo_generate.php';
            return;
        }

        $data = $_POST;

        // Generate slug from title if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['title']);
        }

        // Check if slug already exists
        $existing = $this->seoModel->findBySlug($data['slug']);
        if ($existing) {
            $data['slug'] = $this->createUniqueSlug($data['slug']);
        }

        // Generate content using AI if content_type is ai-generated or hybrid
        if (in_array($data['content_type'], ['ai-generated', 'hybrid'])) {
            $generatedContent = $this->generateContentWithAI($data);
            if ($generatedContent) {
                $data['full_content'] = $generatedContent['content'];
                $data['schema_markup'] = $generatedContent['schema'];
                $data['qa_content'] = $generatedContent['qa_content'] ?? [];
            }
        }

        // Create the page
        $pageId = $this->seoModel->create($data);
        
        if ($pageId && !empty($data['qa_content'])) {
            // Add Q&A content
            $this->seoModel->bulkAddQAContent($pageId, $data['qa_content']);
        }

        // Publish immediately or save as draft
        if ($data['publish_now'] ?? false) {
            $this->seoModel->publish($pageId);
            header('Location: /seo/' . $data['slug'] . '?preview=1');
        } else {
            header('Location: /admin/seo/pages?created=1');
        }
        exit;
    }

    /**
     * Generate content using OpenAI
     */
    private function generateContentWithAI($data) {
        if (!$this->openai) {
            return null;
        }

        $template = $this->seoModel->getTemplate($data['template_name'] ?? 'Grade 12 Math Memorandum Full');
        
        $prompt = $this->buildAIPrompt($data, $template);
        
        try {
            $response = $this->openai->chat([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $template['ai_system_prompt'] ?? $this->getDefaultSystemPrompt()
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000
            ]);

            $content = $response['choices'][0]['message']['content'] ?? '';
            
            // Parse content and extract Q&A if present
            $qaContent = $this->parseQAFromContent($content);
            
            // Generate schema markup
            $schema = $this->generateSchemaMarkup($data, $content);

            return [
                'content' => $content,
                'schema' => json_encode($schema),
                'qa_content' => $qaContent
            ];
        } catch (Exception $e) {
            error_log("AI Generation Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Build AI prompt based on page data
     */
    private function buildAIPrompt($data, $template) {
        $prompt = $template['ai_user_prompt_template'] ?? $this->getDefaultUserPrompt();
        
        // Replace placeholders
        $replacements = [
            '{subject}' => $data['subject'] ?? '',
            '{grade_level}' => $data['grade_level'] ?? '',
            '{topic}' => $data['topic'] ?? '',
            '{target_keyword}' => $data['target_keyword'] ?? '',
            '{search_intent}' => $data['search_intent'] ?? 'informational',
            '{curriculum}' => 'CAPS',
            '{country}' => 'South Africa'
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $prompt);
    }

    /**
     * Parse Q&A content from AI-generated content
     */
    private function parseQAFromContent($content) {
        $qaContent = [];
        
        // Simple pattern to extract question/answer pairs
        // This can be enhanced based on your content structure
        $patterns = [
            '/Question\s*(\d+)[\.:]?\s*(.*?)(?=Answer|Solution|Marks)/is',
            '/(\d+)\.\s*(.*?)(?=Answer|Solution|Marks)/is'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $qaContent[] = [
                        'question_number' => (int)$match[1],
                        'question_text' => trim($match[2]),
                        'full_answer' => $this->extractAnswer($content, $match[1]),
                        'question_type' => 'standard',
                        'position_order' => count($qaContent)
                    ];
                }
                break;
            }
        }

        return $qaContent;
    }

    /**
     * Extract answer for a specific question
     */
    private function extractAnswer($content, $questionNumber) {
        // Simple extraction - can be enhanced
        $patterns = [
            "/Answer\s*{$questionNumber}[\.:]?\s*(.*?)(?=Question\s*" . ($questionNumber + 1) . "|$)/is",
            "/Solution\s*{$questionNumber}[\.:]?\s*(.*?)(?=Question\s*" . ($questionNumber + 1) . "|$)/is"
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $match)) {
                return trim($match[1]);
            }
        }

        return '';
    }

    /**
     * Generate Schema.org markup for SEO page
     */
    private function generateSchemaMarkup($data, $content) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LearningResource',
            'name' => $data['title'],
            'description' => $data['meta_description'] ?? '',
            'educationalLevel' => $data['grade_level'] ?? '',
            'teaches' => $data['topic'] ?? '',
            'inLanguage' => 'en-ZA',
            'audience' => [
                '@type' => 'EducationalAudience',
                'educationalRole' => 'student',
                'geographicArea' => [
                    '@type' => 'Country',
                    'name' => 'South Africa'
                ]
            ]
        ];

        // Add subject-specific type
        if (!empty($data['subject'])) {
            $schema['about'] = [
                '@type' => 'Thing',
                'name' => $data['subject']
            ];
        }

        // Add curriculum info
        if ($data['subject'] === 'Mathematics' || $data['subject'] === 'Mathematical Literacy') {
            $schema['@type'] = 'MathSolver';
            $schema['potentialAction'] = [
                '@type' => 'SolveMathAction',
                'mathExpression' => $data['topic'] ?? ''
            ];
        }

        return $schema;
    }

    /**
     * Create URL-friendly slug
     */
    private function createSlug($text) {
        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $text);
        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');
        // Convert to lowercase
        $slug = strtolower($slug);
        return $slug;
    }

    /**
     * Create unique slug by appending number
     */
    private function createUniqueSlug($slug, $counter = 1) {
        $newSlug = $slug . '-' . $counter;
        $existing = $this->seoModel->findBySlug($newSlug);
        
        if ($existing) {
            return $this->createUniqueSlug($slug, $counter + 1);
        }
        
        return $newSlug;
    }

    /**
     * Show 404 page with suggestions
     */
    private function show404($slug) {
        http_response_code(404);
        
        // Try to find similar pages
        $keywords = explode('-', $slug);
        $suggestions = [];
        
        foreach ($keywords as $keyword) {
            if (strlen($keyword) > 3) {
                $results = $this->seoModel->searchByKeyword($keyword);
                $suggestions = array_merge($suggestions, $results);
            }
        }
        
        $suggestions = array_unique($suggestions, SORT_REGULAR);
        $suggestions = array_slice($suggestions, 0, 5);
        
        include __DIR__ . '/../templates/pages/seo_404.php';
    }

    /**
     * Show empty browse page with info
     */
    private function showBrowseEmpty($subject, $grade) {
        $subjectGradeCombinations = $this->seoModel->getSubjectGradeCombinations();
        
        include __DIR__ . '/../templates/pages/seo_browse_empty.php';
    }

    /**
     * Get default system prompt for AI
     */
    private function getDefaultSystemPrompt() {
        return "You are an expert South African CAPS curriculum educator. Create detailed, accurate memorandum answers and study materials for Grade 12 students. Use clear explanations, show all working steps, and include common mistakes to avoid. Format content in HTML with proper headings, lists, and emphasis on key points.";
    }

    /**
     * Get default user prompt template
     */
    private function getDefaultUserPrompt() {
        return "Create a comprehensive memorandum/study guide for {subject} {grade_level} covering {topic}. Include:\n1. Clear question and answer format\n2. Step-by-step solutions\n3. Marks allocation\n4. Common mistakes students make\n5. Tips for remembering key concepts\n\nTarget keyword: {target_keyword}\nCurriculum: {curriculum} ({country})";
    }

    /**
     * Check if user is admin (redirects to login if not)
     */
    private function requireAdmin() {
        if (!isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        $user = getCurrentUser();
        if (!$user || $user['role'] !== 'admin') {
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Admin: List all SEO pages
     */
    public function adminList() {
        $this->requireAdmin();

        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $seoModel = new SEOPage();
        $pages = $seoModel->getAllPages($limit, $offset);

        include __DIR__ . '/../templates/pages/admin/seo_pages_list.php';
    }

    /**
     * Admin: Delete SEO page
     */
    public function adminDelete($id) {
        $this->requireAdmin();

        $seoModel = new SEOPage();
        $seoModel->delete($id);

        header('Location: /admin/seo/pages?deleted=1');
        exit;
    }

    /**
     * Admin: Show add page form
     */
    public function adminAdd() {
        $this->requireAdmin();
        
        $page = null; // null means add mode
        include __DIR__ . '/../templates/pages/admin/seo_add_edit.php';
    }

    /**
     * Admin: Create new SEO page
     */
    public function adminCreate() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/seo/add');
            exit;
        }

        $seoModel = new SEOPage();
        
        $data = [
            'title' => $_POST['title'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'meta_title' => $_POST['meta_title'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? '',
            'meta_keywords' => $_POST['meta_keywords'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'grade_level' => $_POST['grade_level'] ?? '',
            'topic' => $_POST['topic'] ?? '',
            'target_keyword' => $_POST['target_keyword'] ?? '',
            'full_content' => $_POST['full_content'] ?? '',
            'status' => isset($_POST['save_publish']) ? 'published' : 'draft',
            'content_type' => 'static'
        ];

        $seoModel->create($data);

        header('Location: /admin/seo/pages?created=1');
        exit;
    }

    /**
     * Admin: Show edit page form
     */
    public function adminEdit($id) {
        $this->requireAdmin();

        $seoModel = new SEOPage();
        $page = $seoModel->findById($id);
        
        if (!$page) {
            header('Location: /admin/seo/pages?notfound=1');
            exit;
        }

        include __DIR__ . '/../templates/pages/admin/seo_add_edit.php';
    }

    /**
     * Admin: Update SEO page
     */
    public function adminUpdate($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/seo/edit/' . $id);
            exit;
        }

        $seoModel = new SEOPage();
        
        $data = [
            'title' => $_POST['title'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'meta_title' => $_POST['meta_title'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? '',
            'meta_keywords' => $_POST['meta_keywords'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'grade_level' => $_POST['grade_level'] ?? '',
            'topic' => $_POST['topic'] ?? '',
            'target_keyword' => $_POST['target_keyword'] ?? '',
            'full_content' => $_POST['full_content'] ?? '',
            'status' => isset($_POST['save_publish']) ? 'published' : 'draft',
            'content_type' => 'static'
        ];

        $seoModel->update($id, $data);

        header('Location: /admin/seo/pages?updated=1');
        exit;
    }

    /**
     * Admin: Publish/Unpublish page
     */
    public function adminTogglePublish($id) {
        $this->requireAdmin();

        $seoModel = new SEOPage();
        $page = $seoModel->findById($id);

        if ($page['status'] === 'published') {
            $sql = "UPDATE seo_pages SET status = 'draft' WHERE id = ?";
        } else {
            $sql = "UPDATE seo_pages SET status = 'published', published_at = CURRENT_TIMESTAMP WHERE id = ?";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        header('Location: /admin/seo/pages?updated=1');
        exit;
    }

    /**
     * Generate sitemap XML
     */
    public function sitemap() {
        header('Content-Type: application/xml');
        
        $pages = $this->seoModel->getPublishedPages(1000);
        
        include __DIR__ . '/../templates/pages/seo_sitemap_xml.php';
    }
}

// OpenAI helper class (if not already defined)
if (!class_exists('OpenAI')) {
    class OpenAI {
        private $apiKey;
        private $apiUrl = 'https://api.openai.com/v1/chat/completions';

        public function __construct($apiKey) {
            $this->apiKey = $apiKey;
        }

        public function chat($data) {
            $ch = curl_init($this->apiUrl);
            
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ];

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception("OpenAI API Error: HTTP {$httpCode}");
            }

            return json_decode($response, true);
        }
    }
}
