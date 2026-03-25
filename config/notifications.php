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
        $columns = $db->query("PRAGMA table_info(study_plans)")->fetchAll(PDO::FETCH_COLUMN);
        $hasIsCompleted = in_array('is_completed', $columns);
        
        if ($hasIsCompleted) {
            // Count study plans that are not completed
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM study_plans 
                WHERE student_id = ? 
                AND (is_completed = 0 OR is_completed IS NULL)
            ");
            $stmt->execute([$student['id']]);
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } else {
            // If is_completed column doesn't exist, count all study plans
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM study_plans 
                WHERE student_id = ?
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
        $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN);
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
        
        // Count scripts uploaded by OTHER users in last 7 days
        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM study_group_scripts 
            WHERE study_group_id IN ($placeholders)
            AND user_id != ?
            AND datetime(uploaded_at) > datetime('now', '-7 days')
        ");
        $params = array_merge($groupIds, [$user['id']]);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
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
        $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN);
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

        // Count new scripts from OTHER users in last 7 days
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM study_group_scripts
            WHERE study_group_id = ?
            AND user_id != ?
            AND datetime(uploaded_at) > datetime('now', '-7 days')
        ");
        $stmt->execute([$groupId, $user['id']]);
        $result = $stmt->fetch();
        $scriptsCount = (int)($result['count'] ?? 0);

        return $messagesCount + $scriptsCount;
    } catch (Exception $e) {
        return 0;
    }
}
