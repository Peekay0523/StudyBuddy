<?php
/**
 * StudyGroup Model
 */

class StudyGroup {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new study group
     */
    public function create($userId, $title, $description = '', $gradeLevel = '', $schoolName = '', $maxMembers = 10) {
        $stmt = $this->db->prepare("
            INSERT INTO study_groups (creator_user_id, title, description, grade_level, school_name, max_members)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $description, $gradeLevel, $schoolName, $maxMembers]);

        $groupId = $this->db->lastInsertId();

        // Add creator as admin member
        $this->addMember($groupId, $userId, 'admin');

        return $groupId;
    }

    /**
     * Get study group by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT sg.*, u.username as creator_name,
                   (SELECT COUNT(*) FROM study_group_members sgm WHERE sgm.study_group_id = sg.id) as member_count,
                   (SELECT COUNT(*) FROM study_group_scripts sgs WHERE sgs.study_group_id = sg.id) as script_count
            FROM study_groups sg
            JOIN users u ON sg.creator_user_id = u.id
            WHERE sg.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get all active study groups
     */
    public function getAllActive() {
        $stmt = $this->db->query("
            SELECT sg.*, u.username as creator_name,
                   (SELECT COUNT(*) FROM study_group_members sgm WHERE sgm.study_group_id = sg.id) as member_count,
                   (SELECT COUNT(*) FROM study_group_scripts sgs WHERE sgs.study_group_id = sg.id) as script_count
            FROM study_groups sg
            JOIN users u ON sg.creator_user_id = u.id
            WHERE sg.is_active = 1
            ORDER BY sg.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get top active available study groups
     */
    public function getTopActiveAvailable($userId, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT sg.*, u.username as creator_name,
                   (SELECT COUNT(*) FROM study_group_members sgm WHERE sgm.study_group_id = sg.id) as member_count,
                   (SELECT COUNT(*) FROM study_group_scripts sgs WHERE sgs.study_group_id = sg.id) as script_count
            FROM study_groups sg
            JOIN users u ON sg.creator_user_id = u.id
            WHERE sg.is_active = 1 
            AND sg.id NOT IN (SELECT study_group_id FROM study_group_members WHERE user_id = ?)
            AND sg.creator_user_id != ?
            ORDER BY script_count DESC, sg.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get study groups created by a user
     */
    public function findByCreator($userId) {
        $stmt = $this->db->prepare("
            SELECT sg.*,
                   (SELECT COUNT(*) FROM study_group_members sgm WHERE sgm.study_group_id = sg.id) as member_count,
                   (SELECT COUNT(*) FROM study_group_scripts sgs WHERE sgs.study_group_id = sg.id) as script_count
            FROM study_groups sg
            WHERE sg.creator_user_id = ? AND sg.is_active = 1
            ORDER BY sg.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get study groups where user is a member
     */
    public function findByMember($userId) {
        $stmt = $this->db->prepare("
            SELECT sg.*, u.username as creator_name,
                   (SELECT COUNT(*) FROM study_group_members sgm WHERE sgm.study_group_id = sg.id) as member_count,
                   (SELECT COUNT(*) FROM study_group_scripts sgs WHERE sgs.study_group_id = sg.id) as script_count,
                   sgm.role as user_role
            FROM study_groups sg
            JOIN users u ON sg.creator_user_id = u.id
            JOIN study_group_members sgm ON sg.id = sgm.study_group_id
            WHERE sgm.user_id = ? AND sg.is_active = 1
            ORDER BY sg.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Add a member to a study group
     */
    public function addMember($groupId, $userId, $role = 'member') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO study_group_members (study_group_id, user_id, role) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$groupId, $userId, $role]);
            return true;
        } catch (PDOException $e) {
            // Member already exists
            return false;
        }
    }

    /**
     * Remove a member from a study group
     */
    public function removeMember($groupId, $userId) {
        $stmt = $this->db->prepare("
            DELETE FROM study_group_members 
            WHERE study_group_id = ? AND user_id = ?
        ");
        $stmt->execute([$groupId, $userId]);
        return true;
    }

    /**
     * Get all members of a study group
     */
    public function getMembers($groupId) {
        $stmt = $this->db->prepare("
            SELECT sgm.*, u.username, u.email 
            FROM study_group_members sgm 
            JOIN users u ON sgm.user_id = u.id 
            WHERE sgm.study_group_id = ?
        ");
        $stmt->execute([$groupId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if user is a member of a study group
     */
    public function isMember($groupId, $userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM study_group_members 
            WHERE study_group_id = ? AND user_id = ?
        ");
        $stmt->execute([$groupId, $userId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Get user's role in a study group
     */
    public function getUserRole($groupId, $userId) {
        $stmt = $this->db->prepare("
            SELECT role FROM study_group_members 
            WHERE study_group_id = ? AND user_id = ?
        ");
        $stmt->execute([$groupId, $userId]);
        $result = $stmt->fetch();
        return $result ? $result['role'] : null;
    }

    /**
     * Update study group
     */
    public function update($groupId, $data) {
        $fields = [];
        $values = [];

        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $values[] = $data['title'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $values[] = $data['description'];
        }
        if (isset($data['grade_level'])) {
            $fields[] = "grade_level = ?";
            $values[] = $data['grade_level'];
        }
        if (isset($data['max_members'])) {
            $fields[] = "max_members = ?";
            $values[] = $data['max_members'];
        }
        if (isset($data['is_active'])) {
            $fields[] = "is_active = ?";
            $values[] = $data['is_active'] ? 1 : 0;
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $groupId;
        $sql = "UPDATE study_groups SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        return true;
    }

    /**
     * Delete a study group
     */
    public function delete($groupId) {
        $stmt = $this->db->prepare("DELETE FROM study_groups WHERE id = ?");
        $stmt->execute([$groupId]);
        return true;
    }

    /**
     * Get member count for a study group
     */
    public function getMemberCount($groupId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM study_group_members 
            WHERE study_group_id = ?
        ");
        $stmt->execute([$groupId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Check if group is full
     */
    public function isFull($groupId) {
        $group = $this->findById($groupId);
        if (!$group) {
            return true;
        }

        $memberCount = $this->getMemberCount($groupId);
        return $memberCount >= $group['max_members'];
    }
}
