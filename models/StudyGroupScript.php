<?php
/**
 * StudyGroupScript Model - Shared scripts/resources within study groups
 */

class StudyGroupScript {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Upload a script to a study group
     *
     * Supported resource types:
     *  - 'script'      : a document file (pdf, docx, doc, txt)
     *  - 'video_file'  : an uploaded mp4 video file
     *  - 'video_link'  : a URL pointing to an external tutorial video
     */
    public function upload($studyGroupId, $userId, $fileName, $filePath, $fileSize, $description = '', $resourceType = 'script', $videoUrl = '') {
        $stmt = $this->db->prepare("
            INSERT INTO study_group_scripts (study_group_id, user_id, file_name, file_path, file_size, description, resource_type, video_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$studyGroupId, $userId, $fileName, $filePath, $fileSize, $description, $resourceType, $videoUrl]);
        return $this->db->lastInsertId();
    }

    /**
     * Get all scripts for a study group
     */
    public function findByGroupId($studyGroupId) {
        $stmt = $this->db->prepare("
            SELECT sgs.*, u.username as uploader_name 
            FROM study_group_scripts sgs 
            JOIN users u ON sgs.user_id = u.id 
            WHERE sgs.study_group_id = ? 
            ORDER BY sgs.uploaded_at DESC
        ");
        $stmt->execute([$studyGroupId]);
        return $stmt->fetchAll();
    }

    /**
     * Get a single script by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT sgs.*, u.username as uploader_name 
            FROM study_group_scripts sgs 
            JOIN users u ON sgs.user_id = u.id 
            WHERE sgs.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Delete a script
     */
    public function delete($id, $userId) {
        // Only the uploader can delete
        $stmt = $this->db->prepare("DELETE FROM study_group_scripts WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get scripts uploaded by a user in a group
     */
    public function findByUserInGroup($studyGroupId, $userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM study_group_scripts 
            WHERE study_group_id = ? AND user_id = ? 
            ORDER BY uploaded_at DESC
        ");
        $stmt->execute([$studyGroupId, $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all resources (scripts + videos) from every study group
     * the given user has joined. Used for the "Group Resources" card.
     */
    public function findResourcesByUserGroups($userId) {
        $stmt = $this->db->prepare("
            SELECT sgs.*, u.username as uploader_name, sg.title as group_title
            FROM study_group_scripts sgs
            JOIN study_group_members sgm ON sgm.study_group_id = sgs.study_group_id
            JOIN users u ON sgs.user_id = u.id
            JOIN study_groups sg ON sg.id = sgs.study_group_id
            WHERE sgm.user_id = ? AND sg.is_active = 1
            ORDER BY sgs.uploaded_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all resources (scripts + videos) uploaded by the given user
     * across all study groups. Used for the "My Resources" card.
     */
    public function findResourcesByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT sgs.*, sg.title as group_title
            FROM study_group_scripts sgs
            JOIN study_groups sg ON sg.id = sgs.study_group_id
            WHERE sgs.user_id = ?
            ORDER BY sgs.uploaded_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
