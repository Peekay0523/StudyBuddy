<?php
/**
 * SEO Pages Controller
 * Handles SEO long-tail page generation and display
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SEOPage.php';
require_once __DIR__ . '/../helpers/AIRouter.php';

class SEOController {
    private $seoModel;
    private $aiRouter;

    public function __construct() {
        $this->seoModel = new SEOPage();
        $this->aiRouter = new AIRouter();
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
        
        // Get resources (scripts & memorandums)
        $resources = $this->getResources($page['id']);
        
        // Get AdSense client ID
        $adsenseClientId = getenv('GOOGLE_ADSENSE_CLIENT_ID') ?: $_ENV['GOOGLE_ADSENSE_CLIENT_ID'] ?? '';

        // Render the page
        include __DIR__ . '/../templates/pages/seo_page.php';
    }
    
    /**
     * Get resources for a page
     */
    private function getResources($pageId) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM seo_resources WHERE page_id = ? ORDER BY is_featured DESC, created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$pageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * SEO Pages Home/Browse
     * URL: /seo
     */
    public function index() {
        // Get all available subjects and grades
        $subjects = $this->seoModel->getAllSubjects();
        $grades = $this->seoModel->getAllGrades();
        
        // Get recent pages
        $recentPages = $this->seoModel->getRecentPages(10);
        
        include __DIR__ . '/../templates/pages/seo_index.php';
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
     * Generate content using AI
     */
    private function generateContentWithAI($data) {
        $template = $this->seoModel->getTemplate($data['template_name'] ?? 'Grade 12 Math Memorandum Full');

        $prompt = $this->buildAIPrompt($data, $template);

        $messages = [
            [
                'role' => 'system',
                'content' => $template['ai_system_prompt'] ?? $this->getDefaultSystemPrompt()
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        try {
            // SEO content generation is advanced - use OpenAI
            $response = $this->aiRouter->makeRequest($messages, 4000, 0.7, 'openai');

            if (!$response) {
                return null;
            }

            // Parse content and extract Q&A if present
            $qaContent = $this->parseQAFromContent($response);

            // Generate schema markup
            $schema = $this->generateSchemaMarkup($data, $response);

            return [
                'content' => $response,
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
        return "You are an expert South African CAPS curriculum educator. Create detailed, accurate memorandum answers and study materials for Grade 12 students.

FORMAT REQUIREMENTS:
1. For each question: State the full question, show step-by-step working/solution, then provide the final answer
2. For mathematics: Show ALL calculation steps with explanations. Wrap ALL variables, formulas, and results STRICTLY in [math]...[/math] tags.
   - Example: [math]m = \frac{y_2 - y_1}{x_2 - x_1}[/math], [math]\theta \approx 29.74^\circ[/math], [math](x, y)[/math]
   - DO NOT use \(...\) or \[...\] or $$...$$.
3. For diagrams: Create ASCII art representations with labels
4. Use clear headings, bullet points, and numbered lists
5. Include common mistakes and tips for remembering key concepts

TONE: Educational, encouraging, and suitable for high school students.";
    }

    /**
     * Get default user prompt template
     */
    private function getDefaultUserPrompt() {
        return "Create a comprehensive memorandum/study guide for {subject} {grade_level} covering {topic}.

For EACH question or concept, include:
1. The complete question/text
2. Step-by-step solution/explanation showing how to reach the answer (calculate this FIRST). Wrap all math in [math] tags.
3. Final Answer (must match the calculation above exactly). Wrap all math in [math] tags.
4. For diagrams: ASCII art representation with labels
5. Marks allocation (if applicable)
6. Common mistakes students make
7. Tips for remembering key concepts

Target keyword: {target_keyword}
Curriculum: {curriculum} ({country})";
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
    
    /**
     * Admin: Upload resource to SEO page
     */
    public function adminUploadResource($pageId) {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/seo/edit/' . $pageId);
            exit;
        }
        
        $seoModel = new SEOPage();
        $page = $seoModel->findById($pageId);
        
        if (!$page) {
            header('Location: /admin/seo/pages?notfound=1');
            exit;
        }
        
        // Handle file upload
        if (!isset($_FILES['resource_file']) || $_FILES['resource_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'File upload failed. Please try again.';
            header('Location: /admin/seo/edit/' . $pageId);
            exit;
        }
        
        $file = $_FILES['resource_file'];
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 20 * 1024 * 1024; // 20MB
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error'] = 'Invalid file type. Only PDF and Word documents are allowed.';
            header('Location: /admin/seo/edit/' . $pageId);
            exit;
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            $_SESSION['error'] = 'File is too large. Maximum size is 20MB.';
            header('Location: /admin/seo/edit/' . $pageId);
            exit;
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../uploads/seo-resources/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = uniqid('resource_') . '.' . $extension;
        $filePath = $uploadDir . $safeName;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $_SESSION['error'] = 'Failed to save file.';
            header('Location: /admin/seo/edit/' . $pageId);
            exit;
        }
        
        // Save to database
        $resourceData = [
            'resource_type' => $_POST['resource_type'] ?? 'script',
            'title' => $_POST['title'] ?? $file['name'],
            'description' => $_POST['description'] ?? '',
            'file_path' => 'seo-resources/' . $safeName,
            'file_name' => $file['name'],
            'file_size' => $file['size'],
            'file_mime_type' => $file['type'],
            'subject' => $page['subject'] ?? '',
            'grade_level' => $page['grade_level'] ?? '',
            'is_free' => isset($_POST['is_free']) ? 1 : 0,
            'uploaded_by' => getCurrentUser()['id'] ?? null
        ];
        
        $seoModel->addResource($pageId, $resourceData);
        
        $_SESSION['success'] = 'Resource uploaded successfully!';
        header('Location: /admin/seo/edit/' . $pageId);
        exit;
    }
    
    /**
     * Admin: Delete resource
     */
    public function adminDeleteResource($resourceId) {
        $this->requireAdmin();
        
        $seoModel = new SEOPage();
        
        // Get resource to find page_id
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM seo_resources WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$resourceId]);
        $resource = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$resource) {
            $_SESSION['error'] = 'Resource not found.';
            header('Location: /admin/seo/pages');
            exit;
        }
        
        // Delete file from filesystem
        $filePath = __DIR__ . '/../uploads/' . $resource['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete from database
        $seoModel->deleteResource($resourceId);
        
        $_SESSION['success'] = 'Resource deleted successfully!';
        header('Location: /admin/seo/edit/' . $resource['page_id']);
        exit;
    }
    
    /**
     * Download resource (tracks downloads)
     */
    public function downloadResource($resourceId) {
        $db = Database::getInstance()->getConnection();
        
        // Get resource
        $sql = "SELECT * FROM seo_resources WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$resourceId]);
        $resource = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$resource) {
            http_response_code(404);
            exit('Resource not found');
        }
        
        // Increment download count
        $seoModel = new SEOPage();
        $seoModel->incrementResourceDownloads($resourceId);
        
        // Serve file
        $filePath = __DIR__ . '/../uploads/' . $resource['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('File not found');
        }
        
        header('Content-Type: ' . $resource['file_mime_type']);
        header('Content-Disposition: attachment; filename="' . $resource['file_name'] . '"');
        header('Content-Length: ' . $resource['file_size']);
        readfile($filePath);
        exit;
    }
}
