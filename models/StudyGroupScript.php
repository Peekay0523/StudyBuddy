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
     */
    public function upload($studyGroupId, $userId, $fileName, $filePath, $fileSize, $description = '') {
        $stmt = $this->db->prepare("
            INSERT INTO study_group_scripts (study_group_id, user_id, file_name, file_path, file_size, description) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$studyGroupId, $userId, $fileName, $filePath, $fileSize, $description]);
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
}
