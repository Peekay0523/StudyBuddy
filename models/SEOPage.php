<?php
/**
 * SEO Page Model
 * Handles all database operations for SEO pages
 */

require_once __DIR__ . '/../config/database.php';

class SEOPage {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new SEO page
     */
    public function create($data) {
        $sql = "INSERT INTO seo_pages (
            title, slug, meta_title, meta_description, meta_keywords,
            content_type, subject, grade_level, topic, search_intent,
            target_keyword, secondary_keywords, full_content, ai_prompt_template,
            schema_markup, og_image, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        
        $secondary_keywords = is_array($data['secondary_keywords'] ?? []) 
            ? json_encode($data['secondary_keywords']) 
            : '[]';

        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['meta_title'] ?? $data['title'],
            $data['meta_description'] ?? '',
            $data['meta_keywords'] ?? '',
            $data['content_type'] ?? 'static',
            $data['subject'] ?? '',
            $data['grade_level'] ?? '',
            $data['topic'] ?? '',
            $data['search_intent'] ?? 'informational',
            $data['target_keyword'] ?? '',
            $secondary_keywords,
            $data['full_content'] ?? '',
            $data['ai_prompt_template'] ?? '',
            $data['schema_markup'] ?? '',
            $data['og_image'] ?? '',
            $data['status'] ?? 'draft',
            $data['created_by'] ?? null
        ]);
    }

    /**
     * Update an existing SEO page
     */
    public function update($id, $data) {
        $sql = "UPDATE seo_pages SET
            title = ?, slug = ?, meta_title = ?, meta_description = ?,
            content_type = ?, topic = ?, full_content = ?,
            schema_markup = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['meta_title'],
            $data['meta_description'],
            $data['content_type'],
            $data['topic'] ?? '',
            $data['full_content'] ?? '',
            $data['schema_markup'] ?? '',
            $data['status'] ?? 'draft',
            $id
        ]);
    }

    /**
     * Get page by slug
     */
    public function findBySlug($slug) {
        $sql = "SELECT * FROM seo_pages WHERE slug = ? AND status = 'published'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get page by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM seo_pages WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all published pages
     */
    public function getPublishedPages($limit = 100, $offset = 0) {
        $sql = "SELECT * FROM seo_pages WHERE status = 'published'
                ORDER BY published_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all pages (including drafts) for admin
     */
    public function getAllPages($limit = 100, $offset = 0) {
        $sql = "SELECT * FROM seo_pages
                ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get pages by subject and grade
     */
    public function getBySubjectAndGrade($subject, $gradeLevel) {
        $sql = "SELECT * FROM seo_pages 
                WHERE subject = ? AND grade_level = ? AND status = 'published'
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$subject, $gradeLevel]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search pages by keyword
     */
    public function searchByKeyword($keyword) {
        $sql = "SELECT * FROM seo_pages 
                WHERE (target_keyword LIKE ? 
                    OR meta_keywords LIKE ? 
                    OR title LIKE ?
                    OR topic LIKE ?)
                AND status = 'published'
                ORDER BY views DESC";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%{$keyword}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Q&A content for a page
     */
    public function getQAContent($pageId) {
        $sql = "SELECT * FROM seo_qa_content WHERE page_id = ? ORDER BY position_order, question_number";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add Q&A content to a page
     */
    public function addQAContent($pageId, $qaData) {
        $sql = "INSERT INTO seo_qa_content (
            page_id, question_number, question_text, question_type,
            subject_area, grade_level, bloom_taxonomy_level, full_answer,
            step_by_step_solution, marks_allocated, difficulty_level,
            related_concepts, common_mistakes, tips_and_tricks,
            latex_formula, position_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        
        $related_concepts = is_array($qaData['related_concepts'] ?? [])
            ? json_encode($qaData['related_concepts'])
            : '[]';

        return $stmt->execute([
            $pageId,
            $qaData['question_number'] ?? 1,
            $qaData['question_text'],
            $qaData['question_type'] ?? 'standard',
            $qaData['subject_area'] ?? '',
            $qaData['grade_level'] ?? '',
            $qaData['bloom_taxonomy_level'] ?? 'understand',
            $qaData['full_answer'],
            $qaData['step_by_step_solution'] ?? '',
            $qaData['marks_allocated'] ?? null,
            $qaData['difficulty_level'] ?? 'medium',
            $related_concepts,
            $qaData['common_mistakes'] ?? '',
            $qaData['tips_and_tricks'] ?? '',
            $qaData['latex_formula'] ?? '',
            $qaData['position_order'] ?? 0
        ]);
    }

    /**
     * Bulk add Q&A content
     */
    public function bulkAddQAContent($pageId, $qaContents) {
        $this->db->beginTransaction();
        try {
            foreach ($qaContents as $index => $qaData) {
                $qaData['position_order'] = $index;
                $this->addQAContent($pageId, $qaData);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Increment page views
     */
    public function incrementViews($id) {
        $sql = "UPDATE seo_pages SET views = views + 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Get all subject-grade combinations
     */
    public function getSubjectGradeCombinations() {
        $sql = "SELECT * FROM seo_subject_grades WHERE is_active = 1 ORDER BY subject, grade_level";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get content template by name
     */
    public function getTemplate($templateName) {
        $sql = "SELECT * FROM seo_content_templates WHERE template_name = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$templateName]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all active templates
     */
    public function getAllTemplates() {
        $sql = "SELECT * FROM seo_content_templates WHERE is_active = 1";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get low-competition keywords
     */
    public function getLowCompetitionKeywords($limit = 50) {
        $sql = "SELECT * FROM seo_keywords 
                WHERE competition_level = 'low' AND status = 'active'
                ORDER BY keyword_difficulty ASC, search_volume DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a page
     */
    public function delete($id) {
        $sql = "DELETE FROM seo_pages WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Publish a page
     */
    public function publish($id) {
        $sql = "UPDATE seo_pages SET status = 'published', published_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Get related pages
     */
    public function getRelatedPages($pageId, $subject, $gradeLevel, $limit = 5) {
        $sql = "SELECT id, title, slug, meta_description
                FROM seo_pages
                WHERE id != ?
                AND (subject = ? OR grade_level = ?)
                AND status = 'published'
                ORDER BY views DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pageId, $subject, $gradeLevel, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all unique subjects
     */
    public function getAllSubjects() {
        $sql = "SELECT DISTINCT subject FROM seo_pages WHERE status = 'published' AND subject != '' ORDER BY subject";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get all unique grades
     */
    public function getAllGrades() {
        $sql = "SELECT DISTINCT grade_level FROM seo_pages WHERE status = 'published' AND grade_level != '' ORDER BY grade_level";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get recent pages
     */
    public function getRecentPages($limit = 10) {
        $sql = "SELECT id, title, slug, subject, grade_level, meta_description, created_at
                FROM seo_pages
                WHERE status = 'published'
                ORDER BY created_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get resources for a page
     */
    public function getResources($pageId) {
        $sql = "SELECT * FROM seo_resources WHERE page_id = ? ORDER BY is_featured DESC, created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Add a resource to a page
     */
    public function addResource($pageId, $data) {
        $sql = "INSERT INTO seo_resources (
            page_id, resource_type, title, description, file_path, file_name,
            file_size, file_mime_type, subject, grade_level, is_free, uploaded_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $pageId,
            $data['resource_type'],
            $data['title'],
            $data['description'] ?? '',
            $data['file_path'],
            $data['file_name'],
            $data['file_size'] ?? null,
            $data['file_mime_type'] ?? '',
            $data['subject'] ?? '',
            $data['grade_level'] ?? '',
            $data['is_free'] ?? 1,
            $data['uploaded_by'] ?? null
        ]);
    }
    
    /**
     * Delete a resource
     */
    public function deleteResource($id) {
        $sql = "DELETE FROM seo_resources WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Increment resource download count
     */
    public function incrementResourceDownloads($id) {
        $sql = "UPDATE seo_resources SET download_count = download_count + 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
