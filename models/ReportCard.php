<?php
/**
 * ReportCard Model
 */

class ReportCard {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($studentId, $filePath, $grade = '', $term = '') {
        $stmt = $this->db->prepare("
            INSERT INTO report_cards (student_id, file_path, grade, term) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$studentId, $filePath, $grade, $term]);
        
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM report_cards WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByStudentId($studentId) {
        $stmt = $this->db->prepare("SELECT * FROM report_cards WHERE student_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
    
    public function updateGradesData($id, $gradesData) {
        $stmt = $this->db->prepare("UPDATE report_cards SET grades_data = ? WHERE id = ?");
        $stmt->execute([json_encode($gradesData), $id]);
        return true;
    }
    
    public function countByStudent($studentId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM report_cards WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}
