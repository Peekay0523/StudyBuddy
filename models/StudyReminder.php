<?php
/**
 * StudyReminder Model
 * Handles study reminders and calendar events
 */

class StudyReminder {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new reminder
     */
    public function create($userId, $title, $reminderDate, $reminderTime = null, $description = '', $studyPlanId = null, $isRecurring = 0, $recurringPattern = null, $isImportant = 0) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO study_reminders
                (user_id, study_plan_id, title, description, reminder_date, reminder_time, is_recurring, recurring_pattern, is_important)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $studyPlanId, $title, $description, $reminderDate, $reminderTime, $isRecurring, $recurringPattern, $isImportant]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("StudyReminder create error: " . $e->getMessage());
            return false; // Table doesn't exist yet or other error
        }
    }

    /**
     * Get reminder by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM study_reminders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get all reminders for a user
     */
    public function findByUser($userId, $filters = []) {
        $where = ["user_id = ?"];
        $params = [$userId];

        // Filter by date range
        if (isset($filters['start_date'])) {
            $where[] = "reminder_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (isset($filters['end_date'])) {
            $where[] = "reminder_date <= ?";
            $params[] = $filters['end_date'];
        }

        // Filter by completion status
        if (isset($filters['is_completed'])) {
            $where[] = "is_completed = ?";
            $params[] = $filters['is_completed'] ? 1 : 0;
        }

        // Filter by study plan
        if (isset($filters['study_plan_id'])) {
            $where[] = "study_plan_id = ?";
            $params[] = $filters['study_plan_id'];
        }

        $whereClause = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT * FROM study_reminders WHERE $whereClause ORDER BY reminder_date ASC, reminder_time ASC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get reminders for a specific date
     */
    public function findByDate($userId, $date) {
        $stmt = $this->db->prepare("SELECT * FROM study_reminders WHERE user_id = ? AND reminder_date = ? ORDER BY reminder_time ASC");
        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming reminders
     */
    public function getUpcoming($userId, $days = 7) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM study_reminders 
                WHERE user_id = ? 
                AND reminder_date >= date('now') 
                AND reminder_date <= date('now', '+' || ? || ' days')
                AND is_completed = 0
                ORDER BY reminder_date ASC, reminder_time ASC
            ");
            $stmt->execute([$userId, $days]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return []; // Table doesn't exist yet
        }
    }

    /**
     * Get overdue reminders
     */
    public function getOverdue($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM study_reminders 
            WHERE user_id = ? 
            AND reminder_date < date('now') 
            AND is_completed = 0
            ORDER BY reminder_date ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Mark reminder as completed
     */
    public function markCompleted($id, $completed = true) {
        $stmt = $this->db->prepare("UPDATE study_reminders SET is_completed = ? WHERE id = ?");
        return $stmt->execute([$completed ? 1 : 0, $id]);
    }

    /**
     * Update a reminder
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'description', 'reminder_date', 'reminder_time', 'is_completed', 'is_recurring', 'recurring_pattern'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE study_reminders SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    /**
     * Delete a reminder
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM study_reminders WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get calendar data for a month
     */
    public function getCalendarData($userId, $year, $month) {
        try {
            $startDate = sprintf('%d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate)); // Last day of month

            $stmt = $this->db->prepare("
                SELECT * FROM study_reminders 
                WHERE user_id = ? 
                AND reminder_date >= ? 
                AND reminder_date <= ?
                ORDER BY reminder_date ASC
            ");
            $stmt->execute([$userId, $startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return []; // Table doesn't exist yet
        }
    }

    /**
     * Get reminders count by date for calendar visualization
     */
    public function getCountByDate($userId, $year, $month) {
        try {
            $data = $this->getCalendarData($userId, $year, $month);
            $countByDate = [];

            foreach ($data as $reminder) {
                $date = $reminder['reminder_date'];
                if (!isset($countByDate[$date])) {
                    $countByDate[$date] = 0;
                }
                $countByDate[$date]++;
            }

            return $countByDate;
        } catch (PDOException $e) {
            return []; // Table doesn't exist yet
        }
    }
}
