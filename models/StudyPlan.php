<?php
/**
 * StudyPlan Model
 */

class StudyPlan {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($studentId, $title, $content) {
        $stmt = $this->db->prepare("INSERT INTO study_plans (student_id, title, content, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$studentId, $title, $content]);

        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM study_plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByStudentId($studentId, $activeOnly = true) {
        if ($activeOnly) {
            $stmt = $this->db->prepare("SELECT * FROM study_plans WHERE student_id = ? AND is_active = 1 ORDER BY created_at DESC");
        } else {
            $stmt = $this->db->prepare("SELECT * FROM study_plans WHERE student_id = ? ORDER BY created_at DESC");
        }
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
    
    public function countByStudent($studentId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM study_plans WHERE student_id = ? AND is_active = 1");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    public function updateActive($id, $isActive) {
        $stmt = $this->db->prepare("UPDATE study_plans SET is_active = ? WHERE id = ?");
        $stmt->execute([$isActive ? 1 : 0, $id]);
        return true;
    }
}
