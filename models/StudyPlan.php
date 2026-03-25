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

    /**
     * Share a study plan with a friend
     */
    public function share($studyPlanId, $senderId, $recipientId, $message = '') {
        try {
            // Check if already shared
            $stmt = $this->db->prepare("SELECT id FROM study_plan_shares WHERE study_plan_id = ? AND sender_id = ? AND recipient_id = ?");
            $stmt->execute([$studyPlanId, $senderId, $recipientId]);
            if ($stmt->fetch()) {
                return false; // Already shared
            }

            $stmt = $this->db->prepare("INSERT INTO study_plan_shares (study_plan_id, sender_id, recipient_id, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$studyPlanId, $senderId, $recipientId, $message]);

            // Update shared count
            $stmt = $this->db->prepare("UPDATE study_plans SET shared_count = shared_count + 1 WHERE id = ?");
            $stmt->execute([$studyPlanId]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false; // Table doesn't exist yet
        }
    }

    /**
     * Get shared study plans for a user (received)
     */
    public function getSharedWith($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT sp.*, spr.sender_id, u.username as sender_name, spr.message, spr.status as share_status, spr.created_at as shared_at
                FROM study_plan_shares spr
                JOIN study_plans sp ON spr.study_plan_id = sp.id
                JOIN users u ON spr.sender_id = u.id
                WHERE spr.recipient_id = ? AND spr.status = 'accepted'
                ORDER BY spr.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return []; // Table doesn't exist yet
        }
    }

    /**
     * Get pending share requests for a user
     */
    public function getPendingShares($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT sp.*, spr.sender_id, u.username as sender_name, spr.message, spr.created_at as shared_at
                FROM study_plan_shares spr
                JOIN study_plans sp ON spr.study_plan_id = sp.id
                JOIN users u ON spr.sender_id = u.id
                WHERE spr.recipient_id = ? AND spr.status = 'pending'
                ORDER BY spr.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return []; // Table doesn't exist yet
        }
    }

    /**
     * Accept or decline a shared study plan
     */
    public function updateShareStatus($shareId, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE study_plan_shares SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $shareId]);
        } catch (PDOException $e) {
            return false; // Table doesn't exist yet
        }
    }

    /**
     * Get study plans shared by a user
     */
    public function getSharedBy($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT sp.*, spr.recipient_id, u.username as recipient_name, spr.status, spr.created_at as shared_at
                FROM study_plan_shares spr
                JOIN study_plans sp ON spr.study_plan_id = sp.id
                JOIN users u ON spr.recipient_id = u.id
                WHERE spr.sender_id = ?
                ORDER BY spr.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return []; // Table doesn't exist yet
        }
    }

    /**
     * Mark a study plan as complete
     */
    public function markComplete($planId) {
        try {
            // Check if is_completed column exists
            $columns = $this->db->query("PRAGMA table_info(study_plans)")->fetchAll(PDO::FETCH_COLUMN);
            $hasIsCompleted = in_array('is_completed', $columns);

            if ($hasIsCompleted) {
                $stmt = $this->db->prepare("UPDATE study_plans SET is_completed = 1 WHERE id = ?");
                return $stmt->execute([$planId]);
            } else {
                // If column doesn't exist, just mark as inactive
                return $this->updateActive($planId, 0);
            }
        } catch (PDOException $e) {
            return false;
        }
    }
}
