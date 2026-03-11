<?php
/**
 * StudyGroupInvite Model
 */

class StudyGroupInvite {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new invite
     */
    public function create($userId, $friendEmail, $friendName = '', $studyGroupId = null, $message = '', $expiresInDays = 7) {
        $inviteToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days"));

        $stmt = $this->db->prepare("
            INSERT INTO study_group_invites (user_id, study_group_id, friend_email, friend_name, invite_token, message, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $studyGroupId, $friendEmail, $friendName, $inviteToken, $message, $expiresAt]);

        return $this->db->lastInsertId();
    }

    /**
     * Get invite by token
     */
    public function getByToken($token) {
        $stmt = $this->db->prepare("
            SELECT i.*, u.username as sender_name, u.email as sender_email, sg.title as group_title
            FROM study_group_invites i
            LEFT JOIN users u ON i.user_id = u.id
            LEFT JOIN study_groups sg ON i.study_group_id = sg.id
            WHERE i.invite_token = ?
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    /**
     * Get all invites sent by a user
     */
    public function getBySender($userId) {
        $stmt = $this->db->prepare("
            SELECT i.*, sg.title as group_title
            FROM study_group_invites i
            LEFT JOIN study_groups sg ON i.study_group_id = sg.id
            WHERE i.user_id = ?
            ORDER BY i.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get pending invites sent by a user
     */
    public function getPendingBySender($userId) {
        $stmt = $this->db->prepare("
            SELECT i.*, sg.title as group_title
            FROM study_group_invites i
            LEFT JOIN study_groups sg ON i.study_group_id = sg.id
            WHERE i.user_id = ? AND i.status = 'pending' AND i.expires_at > datetime('now')
            ORDER BY i.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Accept an invite
     */
    public function accept($token, $userId) {
        $stmt = $this->db->prepare("
            UPDATE study_group_invites
            SET status = 'accepted', claimed_at = datetime('now'), claimed_user_id = ?
            WHERE invite_token = ? AND status = 'pending' AND expires_at > datetime('now')
        ");
        $stmt->execute([$userId, $token]);
        
        if ($stmt->rowCount() > 0) {
            // Get the invite sender's user ID
            $invite = $this->getByToken($token);
            if ($invite && $invite['user_id']) {
                // Award 100 points to the sender
                require_once __DIR__ . '/UserPoints.php';
                $pointsModel = new UserPoints();
                $pointsModel->addPoints(
                    $invite['user_id'],
                    100,
                    'Friend accepted invitation to StudySmart',
                    $invite['id'],
                    'invite_accepted'
                );
            }
            return true;
        }
        
        return false;
    }

    /**
     * Reject an invite
     */
    public function reject($token) {
        $stmt = $this->db->prepare("
            UPDATE study_group_invites
            SET status = 'rejected'
            WHERE invite_token = ? AND status = 'pending'
        ");
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if email already has a pending invite
     */
    public function hasPendingInvite($email) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM study_group_invites
            WHERE friend_email = ? AND status = 'pending' AND expires_at > datetime('now')
        ");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Delete an invite
     */
    public function delete($inviteId) {
        $stmt = $this->db->prepare("DELETE FROM study_group_invites WHERE id = ?");
        $stmt->execute([$inviteId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get invite stats for a user
     */
    public function getStats($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' AND expires_at > datetime('now') THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN status = 'rejected' OR expires_at <= datetime('now') THEN 1 ELSE 0 END) as expired_or_rejected
            FROM study_group_invites
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
