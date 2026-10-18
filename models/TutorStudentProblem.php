<?php
class TutorStudentProblem {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureSchema();
    }
    private function ensureSchema() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS tutor_student_problems (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tutor_id INT NOT NULL,
                    student_user_id INT NOT NULL,
                    lesson_id INT NULL,
                    grade VARCHAR(50),
                    subject VARCHAR(255),
                    problem_text TEXT NOT NULL,
                    status VARCHAR(20) DEFAULT \"pending\",
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
        } catch (Exception $e) {}
    }
    public function create($tutorId, $studentUserId, $grade, $subject, $problemText, $lessonId = null) {
        $stmt = $this->db->prepare("INSERT INTO tutor_student_problems (tutor_id, student_user_id, lesson_id, grade, subject, problem_text) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tutorId, $studentUserId, $lessonId, $grade, $subject, $problemText]);
        return (int)$this->db->lastInsertId();
    }
    public function getForTutor($tutorId) {
        $stmt = $this->db->prepare("SELECT p.*, u.username as student_username FROM tutor_student_problems p JOIN users u ON u.id = p.student_user_id WHERE p.tutor_id = ? ORDER BY p.created_at DESC");
        $stmt->execute([$tutorId]);
        return $stmt->fetchAll();
    }
    public function getForStudentAndTutor($studentUserId, $tutorId) {
        $stmt = $this->db->prepare("SELECT * FROM tutor_student_problems WHERE student_user_id = ? AND tutor_id = ? ORDER BY created_at DESC");
        $stmt->execute([$studentUserId, $tutorId]);
        return $stmt->fetchAll();
    }
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tutor_student_problems WHERE id = ?");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public function resolve($id) {
        $stmt = $this->db->prepare("UPDATE tutor_student_problems SET status = \"resolved\" WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->rowCount() > 0;
    }
    public function markResolved($id, $tutorId) {
        $stmt = $this->db->prepare("UPDATE tutor_student_problems SET status = \"resolved\" WHERE id = ? AND tutor_id = ?");
        $stmt->execute([$id, $tutorId]);
        return $stmt->rowCount() > 0;
    }
}
