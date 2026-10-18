<?php
/**
 * TutorStudent Model
 * Links a tutor to the students they teach, tracking approval status,
 * teaching mode (online/contact), extra classes, and payment method (EFT only).
 */

class TutorStudent {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * A student requests a tutor. Creates a pending link.
     * Returns false if a request/link already exists.
     */
    public function request($tutorId, $studentUserId) {
        $existing = $this->findLink($tutorId, $studentUserId);
        if ($existing) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO tutor_students (tutor_id, student_user_id, status, teaching_mode, payment_method)
            VALUES (?, ?, 'pending', 'online', 'eft')
        ");
        $stmt->execute([$tutorId, $studentUserId]);
        return $this->db->lastInsertId();
    }

    /**
     * Find an existing link between a tutor and a student (any status)
     */
    public function findLink($tutorId, $studentUserId) {
        $stmt = $this->db->prepare("
            SELECT * FROM tutor_students WHERE tutor_id = ? AND student_user_id = ?
        ");
        $stmt->execute([$tutorId, $studentUserId]);
        return $stmt->fetch();
    }

    /**
     * Find a single tutor_students row by its id, scoped to the tutor (ownership check)
     */
    public function findByIdForTutor($id, $tutorId) {
        $stmt = $this->db->prepare("
            SELECT * FROM tutor_students WHERE id = ? AND tutor_id = ?
        ");
        $stmt->execute([$id, $tutorId]);
        return $stmt->fetch();
    }

    /**
     * Get all pending requests for a tutor (with student username/email)
     */
    public function getPendingForTutor($tutorId) {
        $stmt = $this->db->prepare("
            SELECT ts.*, u.username, u.email, u.phone
            FROM tutor_students ts
            JOIN users u ON u.id = ts.student_user_id
            WHERE ts.tutor_id = ? AND ts.status = 'pending'
            ORDER BY ts.requested_at DESC
        ");
        $stmt->execute([$tutorId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all active (approved) students for a tutor
     */
    public function getActiveForTutor($tutorId) {
        $stmt = $this->db->prepare("
            SELECT ts.*, u.username, u.email, u.phone
            FROM tutor_students ts
            JOIN users u ON u.id = ts.student_user_id
            WHERE ts.tutor_id = ? AND ts.status = 'active'
            ORDER BY ts.approved_at DESC
        ");
        $stmt->execute([$tutorId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all tutors currently teaching a student.
     */
    public function getActiveForStudent($studentUserId) {
        $stmt = $this->db->prepare("
            SELECT ts.*, t.*, t.id as tutor_profile_id, tutor_user.username, tutor_user.email as tutor_account_email,
                   tutor_user.phone as tutor_account_phone
            FROM tutor_students ts
            JOIN tutors t ON t.id = ts.tutor_id
            JOIN users tutor_user ON tutor_user.id = t.user_id
            WHERE ts.student_user_id = ? AND ts.status = 'active'
            ORDER BY ts.approved_at DESC, ts.requested_at DESC
        ");
        $stmt->execute([$studentUserId]);
        return $stmt->fetchAll();
    }

    /**
     * Count active students for a tutor
     */
    public function countActiveForTutor($tutorId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM tutor_students WHERE tutor_id = ? AND status = 'active'
        ");
        $stmt->execute([$tutorId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Approve a pending student request
     */
    public function approve($id, $tutorId) {
        $stmt = $this->db->prepare("
            UPDATE tutor_students
            SET status = 'active', approved_at = NOW()
            WHERE id = ? AND tutor_id = ?
        ");
        $stmt->execute([$id, $tutorId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Reject a pending student request
     */
    public function reject($id, $tutorId) {
        $stmt = $this->db->prepare("
            DELETE FROM tutor_students WHERE id = ? AND tutor_id = ? AND status = 'pending'
        ");
        $stmt->execute([$id, $tutorId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Remove an active student from a tutor's roster
     */
    public function remove($id, $tutorId) {
        $stmt = $this->db->prepare("
            DELETE FROM tutor_students WHERE id = ? AND tutor_id = ?
        ");
        $stmt->execute([$id, $tutorId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update teaching mode (online/contact) and extra classes flag for a student
     */
    public function updateSettings($id, $tutorId, $teachingMode, $extraClasses) {
        $stmt = $this->db->prepare("
            UPDATE tutor_students
            SET teaching_mode = ?, extra_classes = ?
            WHERE id = ? AND tutor_id = ?
        ");
        $stmt->execute([$teachingMode, $extraClasses ? 1 : 0, $id, $tutorId]);
        return $stmt->rowCount() > 0;
    }
}
