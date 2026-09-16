<?php
/**
 * MyResource Model - Personal resources (video tutorials + scripts)
 * a user stores for themselves, independent of any study group.
 */

class MyResource {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Add a personal resource.
     *
     * $resourceType: 'script' | 'video_file' | 'video_link'
     * For 'video_link' the $filePath / $fileSize can be empty and $videoUrl holds the link.
     */
    public function add($userId, $title, $resourceType, $fileName, $filePath, $fileSize, $description, $videoUrl = '') {
        $stmt = $this->db->prepare("
            INSERT INTO my_resources (user_id, title, resource_type, file_name, file_path, file_size, description, video_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $resourceType, $fileName, $filePath, $fileSize, $description, $videoUrl]);
        return $this->db->lastInsertId();
    }

    /**
     * Get all personal resources for a user
     */
    public function findByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM my_resources
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get a single resource by id (scoped to the given user)
     */
    public function findById($id, $userId = null) {
        if ($userId !== null) {
            $stmt = $this->db->prepare("SELECT * FROM my_resources WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM my_resources WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    /**
     * Delete a personal resource (only the owner can delete)
     */
    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM my_resources WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }
}
