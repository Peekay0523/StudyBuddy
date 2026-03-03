<?php
/**
 * StudyGroupMessage Model - Chat messages within study groups
 */

class StudyGroupMessage {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Send a message to a study group
     */
    public function send($studyGroupId, $userId, $message, $messageType = 'text', $filePath = null) {
        $stmt = $this->db->prepare("
            INSERT INTO study_group_messages (study_group_id, user_id, message, message_type, file_path) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$studyGroupId, $userId, $message, $messageType, $filePath]);
        return $this->db->lastInsertId();
    }

    /**
     * Get all messages for a study group
     */
    public function findByGroupId($studyGroupId, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT sgm.*, u.username as sender_name 
            FROM study_group_messages sgm 
            JOIN users u ON sgm.user_id = u.id 
            WHERE sgm.study_group_id = ? 
            ORDER BY sgm.created_at ASC 
            LIMIT ?
        ");
        $stmt->execute([$studyGroupId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get recent messages for a study group (for polling)
     */
    public function findRecent($studyGroupId, $afterId = 0, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT sgm.*, u.username as sender_name 
            FROM study_group_messages sgm 
            JOIN users u ON sgm.user_id = u.id 
            WHERE sgm.study_group_id = ? AND sgm.id > ?
            ORDER BY sgm.created_at ASC 
            LIMIT ?
        ");
        $stmt->execute([$studyGroupId, $afterId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get a single message by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT sgm.*, u.username as sender_name 
            FROM study_group_messages sgm 
            JOIN users u ON sgm.user_id = u.id 
            WHERE sgm.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Delete a message
     */
    public function delete($id, $userId) {
        // Only the sender can delete
        $stmt = $this->db->prepare("DELETE FROM study_group_messages WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Count messages in a group
     */
    public function countByGroupId($studyGroupId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM study_group_messages WHERE study_group_id = ?");
        $stmt->execute([$studyGroupId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get latest message timestamp
     */
    public function getLatestTimestamp($studyGroupId) {
        $stmt = $this->db->prepare("SELECT MAX(created_at) as latest FROM study_group_messages WHERE study_group_id = ?");
        $stmt->execute([$studyGroupId]);
        $result = $stmt->fetch();
        return $result['latest'] ?? null;
    }
}
