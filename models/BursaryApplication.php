<?php
/**
 * BursaryApplication Model
 */

class BursaryApplication {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($studentId, $bursaryName, $bursaryProvider, $bursaryId = null, $status = 'pending', $deadline = null, $notes = null) {
        $stmt = $this->db->prepare("
            INSERT INTO bursary_applications (student_id, bursary_id, bursary_name, bursary_provider, application_status, deadline, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $studentId,
            $bursaryId,
            $bursaryName,
            $bursaryProvider,
            $status,
            $deadline,
            $notes
        ]);

        return $this->db->lastInsertId();
    }

    public function findByStudentId($studentId) {
        $stmt = $this->db->prepare("SELECT * FROM bursary_applications WHERE student_id = ? ORDER BY applied_date DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function countByStudent($studentId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bursary_applications WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    public function countByStatus($studentId, $status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bursary_applications WHERE student_id = ? AND application_status = ?");
        $stmt->execute([$studentId, $status]);
        return (int) $stmt->fetchColumn();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE bursary_applications SET application_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function delete($id, $studentId) {
        $stmt = $this->db->prepare("DELETE FROM bursary_applications WHERE id = ? AND student_id = ?");
        return $stmt->execute([$id, $studentId]);
    }

    public function getRecentApplications($studentId, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT * FROM bursary_applications 
            WHERE student_id = ? 
            ORDER BY applied_date DESC 
            LIMIT ?
        ");
        $stmt->execute([$studentId, $limit]);
        return $stmt->fetchAll();
    }
}
