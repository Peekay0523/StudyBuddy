<?php
/**
 * UserPoints Model - Rewards System
 */

class UserPoints {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get user's points info
     */
    public function getUserPoints($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_points WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        if (!$result) {
            // Initialize points for new user
            $this->initializePoints($userId);
            return ['user_id' => $userId, 'points' => 0, 'free_scans' => 0];
        }

        return $result;
    }

    /**
     * Initialize points for a new user
     */
    public function initializePoints($userId) {
        $stmt = $this->db->prepare("
            INSERT OR IGNORE INTO user_points (user_id, points, free_scans)
            VALUES (?, 0, 0)
        ");
        $stmt->execute([$userId]);
    }

    /**
     * Add points to user
     */
    public function addPoints($userId, $points, $description = '', $referenceId = null, $referenceType = null) {
        $this->db->beginTransaction();
        try {
            // Update user points
            $stmt = $this->db->prepare("
                UPDATE user_points
                SET points = points + ?, updated_at = datetime('now')
                WHERE user_id = ?
            ");
            $stmt->execute([$points, $userId]);

            // Record transaction
            $stmt = $this->db->prepare("
                INSERT INTO points_transactions (user_id, points, transaction_type, description, reference_id, reference_type)
                VALUES (?, ?, 'earned', ?, ?, ?)
            ");
            $stmt->execute([$userId, $points, $description, $referenceId, $referenceType]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Spend points (convert to free scan)
     */
    public function spendPoints($userId, $points) {
        $this->db->beginTransaction();
        try {
            // Deduct points
            $stmt = $this->db->prepare("
                UPDATE user_points
                SET points = points - ?, updated_at = datetime('now')
                WHERE user_id = ? AND points >= ?
            ");
            $stmt->execute([$points, $userId, $points]);

            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            // Add free scan
            $stmt = $this->db->prepare("
                UPDATE user_points
                SET free_scans = free_scans + 1
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);

            // Record transaction
            $stmt = $this->db->prepare("
                INSERT INTO points_transactions (user_id, points, transaction_type, description)
                VALUES (?, ?, 'spent', 'Converted points to free scan')
            ");
            $stmt->execute([$userId, $points]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Use a free scan
     */
    public function useFreeScan($userId) {
        $stmt = $this->db->prepare("
            UPDATE user_points
            SET free_scans = free_scans - 1
            WHERE user_id = ? AND free_scans > 0
        ");
        $stmt->execute([$userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get transaction history
     */
    public function getTransactions($userId, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT * FROM points_transactions
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Check if user has enough points
     */
    public function hasPoints($userId, $points) {
        $userPoints = $this->getUserPoints($userId);
        return $userPoints['points'] >= $points;
    }

    /**
     * Get points stats
     */
    public function getStats($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                points,
                free_scans,
                (SELECT SUM(points) FROM points_transactions WHERE user_id = ? AND transaction_type = 'earned') as total_earned,
                (SELECT SUM(points) FROM points_transactions WHERE user_id = ? AND transaction_type = 'spent') as total_spent
            FROM user_points
            WHERE user_id = ?
        ");
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetch();
    }
}
