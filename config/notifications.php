<?php
/**
 * Notification Helper Functions
 * 
 * Functions for counting pending study plans and unread study group messages
 */

/**
 * Get count of pending (not completed) study plans for current user
 * @return int Count of pending study plans
 */
function getPendingStudyPlansCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $user = getCurrentUser();
        
        // Get student_id from students table
        $stmt = $db->prepare("SELECT id FROM students WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $student = $stmt->fetch();
        
        if (!$student) {
            return 0;
        }
        
        // Check if is_completed column exists
        $columns = $db->query("PRAGMA table_info(study_plans)")->fetchAll(PDO::FETCH_COLUMN, 1);
        $hasIsCompleted = in_array('is_completed', $columns);
        
        if ($hasIsCompleted) {
            // Count study plans that are not completed and are active
            $stmt = $db->prepare("
                SELECT COUNT(*) as count
                FROM study_plans
                WHERE student_id = ?
                AND (is_completed = 0 OR is_completed IS NULL)
                AND (is_active = 1 OR is_active IS NULL)
            ");
            $stmt->execute([$student['id']]);
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } else {
            // If is_completed column doesn't exist, count active study plans
            $stmt = $db->prepare("
                SELECT COUNT(*) as count
                FROM study_plans
                WHERE student_id = ?
                AND (is_active = 1 OR is_active IS NULL)
            ");
            $stmt->execute([$student['id']]);
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        }
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get count of unread study group messages for current user
 * @return int Count of unread messages
 */
function getUnreadStudyGroupMessagesCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $user = getCurrentUser();
        
        // Get all study groups the user is a member of
        $stmt = $db->prepare("
            SELECT study_group_id 
            FROM study_group_members 
            WHERE user_id = ?
        ");
        $stmt->execute([$user['id']]);
        $groupIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($groupIds)) {
            return 0;
        }
        
        // Check if is_viewed column exists
        $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN, 1);
        $hasIsViewed = in_array('is_viewed', $columns);
        
        if ($hasIsViewed) {
            // Count messages from OTHER users that haven't been viewed
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM study_group_messages 
                WHERE study_group_id IN ($placeholders)
                AND user_id != ?
                AND (is_viewed = 0 OR is_viewed IS NULL)
            ");
            $params = array_merge($groupIds, [$user['id']]);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } else {
            // If is_viewed column doesn't exist, count messages from last 7 days
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM study_group_messages 
                WHERE study_group_id IN ($placeholders)
                AND user_id != ?
                AND datetime(created_at) > datetime('now', '-7 days')
            ");
            $params = array_merge($groupIds, [$user['id']]);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        }
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get count of new scripts uploaded to study groups for current user
 * @return int Count of new scripts
 */
function getNewStudyGroupScriptsCount() {
    if (!isLoggedIn()) {
        return 0;
    }

    try {
        $db = Database::getInstance()->getConnection();
        $user = getCurrentUser();

        // Get all study groups the user is a member of with their last visited time
        $stmt = $db->prepare("
            SELECT study_group_id, last_visited
            FROM study_group_members
            WHERE user_id = ?
        ");
        $stmt->execute([$user['id']]);
        $groupMemberships = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($groupMemberships)) {
            return 0;
        }

        // Count scripts uploaded by OTHER users after the user's last visit to each group
        $totalCount = 0;
        foreach ($groupMemberships as $membership) {
            $groupId = $membership['study_group_id'];
            $lastVisited = $membership['last_visited'];
            
            $stmt = $db->prepare("
                SELECT COUNT(*) as count
                FROM study_group_scripts
                WHERE study_group_id = ?
                AND user_id != ?
                AND datetime(uploaded_at) > datetime(?)
            ");
            $stmt->execute([$groupId, $user['id'], $lastVisited]);
            $result = $stmt->fetch();
            $totalCount += (int)($result['count'] ?? 0);
        }

        return $totalCount;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get total count of unread study group activity (messages + new scripts)
 * @return int Total count of unread activity
 */
function getStudyGroupActivityCount() {
    $messagesCount = getUnreadStudyGroupMessagesCount();
    $scriptsCount = getNewStudyGroupScriptsCount();
    return $messagesCount + $scriptsCount;
}

/**
 * Get count of unread activity for a specific study group
 * @param int $groupId Study group ID
 * @return int Count of unread messages + new scripts in the group
 */
function getStudyGroupNotificationCount($groupId) {
    if (!isLoggedIn()) {
        return 0;
    }

    try {
        $db = Database::getInstance()->getConnection();
        $user = getCurrentUser();

        // Check if is_viewed column exists
        $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN, 1);
        $hasIsViewed = in_array('is_viewed', $columns);

        if ($hasIsViewed) {
            // Count unread messages from OTHER users
            $stmt = $db->prepare("
                SELECT COUNT(*) as count
                FROM study_group_messages
                WHERE study_group_id = ?
                AND user_id != ?
                AND (is_viewed = 0 OR is_viewed IS NULL)
            ");
            $stmt->execute([$groupId, $user['id']]);
            $result = $stmt->fetch();
            $messagesCount = (int)($result['count'] ?? 0);
        } else {
            // If is_viewed column doesn't exist, count messages from last 7 days
            $stmt = $db->prepare("
                SELECT COUNT(*) as count
                FROM study_group_messages
                WHERE study_group_id = ?
                AND user_id != ?
                AND datetime(created_at) > datetime('now', '-7 days')
            ");
            $stmt->execute([$groupId, $user['id']]);
            $result = $stmt->fetch();
            $messagesCount = (int)($result['count'] ?? 0);
        }

        // Get user's last visited time for this group
        $stmt = $db->prepare("
            SELECT last_visited
            FROM study_group_members
            WHERE study_group_id = ? AND user_id = ?
        ");
        $stmt->execute([$groupId, $user['id']]);
        $membership = $stmt->fetch();
        $lastVisited = $membership ? $membership['last_visited'] : date('Y-m-d H:i:s');

        // Count new scripts from OTHER users uploaded after last visit
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM study_group_scripts
            WHERE study_group_id = ?
            AND user_id != ?
            AND datetime(uploaded_at) > datetime(?)
        ");
        $stmt->execute([$groupId, $user['id'], $lastVisited]);
        $result = $stmt->fetch();
        $scriptsCount = (int)($result['count'] ?? 0);

        return $messagesCount + $scriptsCount;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get count of unread chat messages for a specific study group
 * @param int $groupId Study group ID
 * @return int Count of unread messages in the group
 */
function getStudyGroupChatNotificationCount($groupId) {
    if (!isLoggedIn()) {
        return 0;
    }

    try {
        $db = Database::getInstance()->getConnection();
        $user = getCurrentUser();

        // Check if is_viewed column exists
        $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN, 1);
        $hasIsViewed = in_array('is_viewed', $columns);

        if ($hasIsViewed) {
            // Count unread messages from OTHER users
            $stmt = $db->prepare("
                SELECT COUNT(*) as count
                FROM study_group_messages
                WHERE study_group_id = ?
                AND user_id != ?
                AND (is_viewed = 0 OR is_viewed IS NULL)
            ");
            $stmt->execute([$groupId, $user['id']]);
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } else {
            // If is_viewed column doesn't exist, count messages from last 7 days
            $stmt = $db->prepare("
                SELECT COUNT(*) as count
                FROM study_group_messages
                WHERE study_group_id = ?
                AND user_id != ?
                AND datetime(created_at) > datetime('now', '-7 days')
            ");
            $stmt->execute([$groupId, $user['id']]);
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        }
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get count of new scripts for a specific study group
 * @param int $groupId Study group ID
 * @return int Count of new scripts in the group
 */
function getStudyGroupScriptsNotificationCount($groupId) {
    if (!isLoggedIn()) {
        return 0;
    }

    try {
        $db = Database::getInstance()->getConnection();
        $user = getCurrentUser();

        // Get user's last visited time for this group
        $stmt = $db->prepare("
            SELECT last_visited
            FROM study_group_members
            WHERE study_group_id = ? AND user_id = ?
        ");
        $stmt->execute([$groupId, $user['id']]);
        $membership = $stmt->fetch();
        $lastVisited = $membership ? $membership['last_visited'] : date('Y-m-d H:i:s');

        // Count new scripts from OTHER users uploaded after last visit
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM study_group_scripts
            WHERE study_group_id = ?
            AND user_id != ?
            AND datetime(uploaded_at) > datetime(?)
        ");
        $stmt->execute([$groupId, $user['id'], $lastVisited]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get count of new bursaries available for current user
 * @return int Count of new bursaries
 */
function getBursaryNotificationCount() {
    if (!isLoggedIn()) {
        return 0;
    }

    try {
        $db = Database::getInstance()->getConnection();
        $user = getCurrentUser();

        // Get user's last viewed time for bursaries
        $stmt = $db->prepare("
            SELECT bursaries_last_viewed
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);
        $result = $stmt->fetch();
        $lastViewed = $result ? $result['bursaries_last_viewed'] : date('Y-m-d H:i:s');

        // Count active bursaries created after last view
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM bursaries
            WHERE is_active = 1
            AND deadline >= date('now')
            AND datetime(created_at) > datetime(?)
        ");
        $stmt->execute([$lastViewed]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}
