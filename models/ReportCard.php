<?php
/**
 * ReportCard Model
 */

class ReportCard {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($userId, $filePath, $grade = '', $term = '') {
        $stmt = $this->db->prepare("
            INSERT INTO report_cards (user_id, file_path, grade, term)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $filePath, $grade, $term]);

        return $this->db->lastInsertId();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM report_cards WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM report_cards WHERE user_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findByStudentId($studentId) {
        // Keep for backwards compatibility - maps to user_id
        return $this->findByUserId($studentId);
    }
    
    public function updateGradesData($id, $gradesData) {
        $stmt = $this->db->prepare("UPDATE report_cards SET grades_data = ? WHERE id = ?");
        $stmt->execute([json_encode($gradesData), $id]);
        return true;
    }
    
    public function countByStudent($studentId) {
        // Keep for backwards compatibility - maps to user_id
        return $this->countByUserId($studentId);
    }

    public function countByUserId($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM report_cards WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}
