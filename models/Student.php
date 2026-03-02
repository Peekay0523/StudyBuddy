<?php
/**
 * Student Model
 */

class Student {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($userId, $gradeLevel = '') {
        $studentId = $this->generateStudentId();
        
        $stmt = $this->db->prepare("INSERT INTO students (user_id, student_id, grade_level) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $studentId, $gradeLevel]);
        
        return $this->db->lastInsertId();
    }
    
    private function generateStudentId() {
        return bin2hex(random_bytes(16));
    }
    
    public function findByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateGradeLevel($id, $gradeLevel) {
        $stmt = $this->db->prepare("UPDATE students SET grade_level = ? WHERE id = ?");
        $stmt->execute([$gradeLevel, $id]);
        return true;
    }
}
