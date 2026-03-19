<?php
/**
 * UploadedScript Model
 */

class UploadedScript {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($studentId, $title, $filePath, $subject = '', $gradeLevel = '') {
        $stmt = $this->db->prepare("
            INSERT INTO uploaded_scripts (student_id, title, subject, grade_level, file_path) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$studentId, $title, $subject, $gradeLevel, $filePath]);
        
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM uploaded_scripts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByStudentId($studentId) {
        $stmt = $this->db->prepare("
            SELECT 
                us.*,
                CASE WHEN m.id IS NOT NULL THEN 1 ELSE 0 END as has_memorandum
            FROM uploaded_scripts us
            LEFT JOIN memorandums m ON us.id = m.script_id
            WHERE us.student_id = ?
            ORDER BY us.uploaded_at DESC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $setParts = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $setParts[] = "$key = ?";
            $values[] = $value;
        }
        
        $setClause = implode(', ', $setParts);
        $values[] = $id;
        
        $stmt = $this->db->prepare("UPDATE uploaded_scripts SET $setClause WHERE id = ?");
        return $stmt->execute($values);
    }

    public function updateProcessedTopics($id, $topics) {
        $stmt = $this->db->prepare("UPDATE uploaded_scripts SET processed_topics = ? WHERE id = ?");
        $stmt->execute([json_encode($topics), $id]);
        return true;
    }
    
    public function updateChallengingTopics($id, $topics) {
        $stmt = $this->db->prepare("UPDATE uploaded_scripts SET challenging_topics = ? WHERE id = ?");
        $stmt->execute([json_encode($topics), $id]);
        return true;
    }
    
    public function countByStudent($studentId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM uploaded_scripts WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    public function getTotalTopicsCount($studentId) {
        $scripts = $this->findByStudentId($studentId);
        $totalCount = 0;
        foreach ($scripts as $script) {
            $topics = json_decode($script['processed_topics'], true) ?? [];
            $totalCount += count($topics);
        }
        return $totalCount;
    }
}
