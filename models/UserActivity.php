<?php
/**
 * UserActivity Model - Track online status and study buddies
 */

class UserActivity {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Update user's last active time and track login streak
     */
    public function updateActivity($userId) {
        $today = date('Y-m-d');

        // Get current streak info
        $stmt = $this->db->prepare("SELECT last_login_date, login_streak FROM user_activity WHERE user_id = ?");
        $stmt->execute([$userId]);
        $activity = $stmt->fetch();

        if ($activity) {
            $lastLoginDate = $activity['last_login_date'];
            $currentStreak = $activity['login_streak'];

            // If last login was before today, check if we should update streak
            if ($lastLoginDate !== $today) {
                $yesterday = date('Y-m-d', strtotime('-1 day'));

                if ($lastLoginDate === $yesterday) {
                    // Consecutive login - increment streak
                    $currentStreak++;
                } elseif ($lastLoginDate < $yesterday) {
                    // Streak broken - reset to 1
                    $currentStreak = 1;
                }

                // Update the streak and last login date
                $stmt = $this->db->prepare("
                    UPDATE user_activity
                    SET last_active = datetime('now'), is_online = 1, login_streak = ?, last_login_date = ?
                    WHERE user_id = ?
                ");
                $stmt->execute([$currentStreak, $today, $userId]);

                // Award 1 point for every 3 consecutive days of login
                if ($currentStreak > 0 && $currentStreak % 3 === 0) {
                    require_once __DIR__ . '/UserPoints.php';
                    $pointsModel = new UserPoints();
                    $pointsModel->addPoints(
                        $userId,
                        1,
                        "Login streak reward - {$currentStreak} days",
                        null,
                        'login_streak'
                    );
                }
            } else {
                // Already logged in today - just update activity
                $stmt = $this->db->prepare("
                    UPDATE user_activity
                    SET last_active = datetime('now'), is_online = 1
                    WHERE user_id = ?
                ");
                $stmt->execute([$userId]);
            }
        } else {
            // First time activity - initialize with streak of 1
            $stmt = $this->db->prepare("
                INSERT OR IGNORE INTO user_activity (user_id, last_active, is_online, login_streak, last_login_date)
                VALUES (?, datetime('now'), 1, 1, ?)
            ");
            $stmt->execute([$userId, $today]);
        }
    }

    /**
     * Mark user as offline
     */
    public function setOffline($userId) {
        $stmt = $this->db->prepare("
            UPDATE user_activity
            SET is_online = 0
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
    }

    /**
     * Get online students available for study help
     */
    public function getStudyBuddies($currentUserId, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT 
                u.id,
                u.username,
                u.email,
                ua.last_active,
                ua.is_online,
                ua.subjects_interested,
                ua.grade_level,
                ua.bio,
                (SELECT COUNT(*) FROM scripts s WHERE s.user_id = u.id) as scripts_uploaded,
                (SELECT COUNT(*) FROM study_group_members sgm WHERE sgm.user_id = u.id) as groups_count
            FROM users u
            LEFT JOIN user_activity ua ON u.id = ua.user_id
            LEFT JOIN students st ON u.id = st.user_id
            WHERE u.id != ?
            AND u.role = 'student'
            AND u.id NOT IN (
                SELECT sgm.user_id FROM study_group_members sgm 
                WHERE sgm.study_group_id IN (
                    SELECT sg.id FROM study_groups sg WHERE sg.creator_user_id = ?
                )
            )
            ORDER BY 
                ua.is_online DESC,
                ua.last_active DESC
            LIMIT ?
        ");
        $stmt->execute([$currentUserId, $currentUserId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Check if user is online (active within last 5 minutes)
     */
    public function isOnline($userId) {
        $stmt = $this->db->prepare("
            SELECT is_online FROM user_activity
            WHERE user_id = ? AND datetime(last_active) > datetime('now', '-5 minutes')
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetchColumn();
        return $result > 0;
    }

    /**
     * Get user's activity info
     */
    public function getUserActivity($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_activity WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Update user's study profile
     */
    public function updateStudyProfile($userId, $subjects, $gradeLevel, $bio) {
        $stmt = $this->db->prepare("
            INSERT OR IGNORE INTO user_activity (user_id, last_active, is_online)
            VALUES (?, datetime('now'), 1)
        ");
        $stmt->execute([$userId]);

        $stmt = $this->db->prepare("
            UPDATE user_activity
            SET subjects_interested = ?, grade_level = ?, bio = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$subjects, $gradeLevel, $bio, $userId]);
    }
}
