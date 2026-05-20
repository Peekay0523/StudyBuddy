<?php
/**
 * Study Plan Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/StudyPlan.php';
require_once __DIR__ . '/../models/StudyReminder.php';
require_once __DIR__ . '/../helpers/AIRouter.php';

class StudyPlanController {
    private $studyPlanModel;
    private $studyReminderModel;
    private $aiRouter;

    public function __construct() {
        $this->studyPlanModel = new StudyPlan();
        $this->studyReminderModel = new StudyReminder();
        $this->aiRouter = new AIRouter();
    }

    public function index() {
        requireStudent();

        $student = getCurrentStudent();
        $studyPlans = $this->studyPlanModel->findByStudentId($student['id'], true);
        $sharedWith = $this->studyPlanModel->getSharedWith($student['id']);
        $pendingShares = $this->studyPlanModel->getPendingShares($student['id']);
        $upcomingReminders = $this->studyReminderModel->getUpcoming($student['id'], 7);

        include __DIR__ . '/../templates/pages/study_plan.php';
    }

    public function view($planId) {
        requireStudent();

        $student = getCurrentStudent();
        $studyPlan = $this->studyPlanModel->findById($planId);

        if (!$studyPlan || $studyPlan['student_id'] != $student['id']) {
            header('Location: /dashboard');
            exit;
        }

        // Check if this study plan has any reminders in the calendar
        $reminders = $this->studyReminderModel->findByUser($student['id'], ['study_plan_id' => $planId]);
        $hasReminders = !empty($reminders);

        include __DIR__ . '/../templates/pages/view_study_plan.php';
    }

    public function recite($planId) {
        requireStudent();

        $student = getCurrentStudent();
        $studyPlan = $this->studyPlanModel->findById($planId);

        if (!$studyPlan || $studyPlan['student_id'] != $student['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Study plan not found']);
            exit;
        }

        $recitation = $this->aiRouter->reciteStudyPlan($studyPlan['title'] . "\n\n" . $studyPlan['content']);

        header('Content-Type: application/json');
        echo json_encode($recitation);
    }

    /**
     * Share a study plan with a friend
     */
    public function share() {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $student = getCurrentStudent();
        $studyPlanId = $_POST['study_plan_id'] ?? null;
        $recipientUsername = $_POST['recipient_username'] ?? null;
        $message = $_POST['message'] ?? '';

        if (!$studyPlanId || !$recipientUsername) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        // Verify ownership
        $studyPlan = $this->studyPlanModel->findById($studyPlanId);
        if (!$studyPlan || $studyPlan['student_id'] != $student['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Study plan not found']);
            return;
        }

        // Find recipient
        $recipient = getUserByUsername($recipientUsername);
        if (!$recipient) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }

        if ($recipient['id'] == $student['id']) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot share with yourself']);
            return;
        }

        $shareId = $this->studyPlanModel->share($studyPlanId, $student['id'], $recipient['id'], $message);

        if ($shareId) {
            echo json_encode(['success' => true, 'message' => 'Study plan shared successfully!']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Already shared with this user']);
        }
    }

    /**
     * Accept or decline a shared study plan
     */
    public function respondToShare($shareId) {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $student = getCurrentStudent();
        $action = $_POST['action'] ?? null;

        if (!in_array($action, ['accept', 'decline'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            return;
        }

        $status = $action === 'accept' ? 'accepted' : 'declined';
        $this->studyPlanModel->updateShareStatus($shareId, $status);

        echo json_encode(['success' => true]);
    }

    /**
     * Delete a study plan (soft delete)
     */
    public function delete($planId) {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $student = getCurrentStudent();
        $studyPlan = $this->studyPlanModel->findById($planId);

        if (!$studyPlan || $studyPlan['student_id'] != $student['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Study plan not found']);
            return;
        }

        $result = $this->studyPlanModel->updateActive($planId, 0);

        if ($result) {
            // Also remove associated reminders
            $reminders = $this->studyReminderModel->findByUser($student['id'], ['study_plan_id' => $planId]);
            foreach ($reminders as $reminder) {
                $this->studyReminderModel->delete($reminder['id']);
            }
            
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete study plan']);
        }
    }

    /**
     * Create a study reminder
     */
    public function createReminder() {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $student = getCurrentStudent();
        $title = $_POST['title'] ?? null;
        $reminderDate = $_POST['reminder_date'] ?? null;
        $reminderTime = $_POST['reminder_time'] ?? null;
        $description = $_POST['description'] ?? '';
        $studyPlanId = $_POST['study_plan_id'] ?? null;
        $isRecurring = isset($_POST['is_recurring']) ? 1 : 0;
        $recurringPattern = $_POST['recurring_pattern'] ?? null;
        $isImportant = isset($_POST['is_important']) ? 1 : 0;

        if (!$title || !$reminderDate) {
            http_response_code(400);
            echo json_encode(['error' => 'Title and date are required']);
            return;
        }

        try {
            $reminderId = $this->studyReminderModel->create(
                $student['id'],
                $title,
                $reminderDate,
                $reminderTime,
                $description,
                $studyPlanId,
                $isRecurring,
                $recurringPattern,
                $isImportant
            );

            if ($reminderId) {
                echo json_encode(['success' => true, 'reminder_id' => $reminderId]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create reminder. Please run the migration first at /run-study-plan-migration.php']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get reminders for calendar
     */
    public function getCalendarData() {
        requireStudent();

        $student = getCurrentStudent();
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('n');

        // Get full reminder data for the calendar
        $reminders = $this->studyReminderModel->getCalendarData($student['id'], $year, $month);
        
        // Debug: Log reminder count
        error_log("Calendar data for user {$student['id']}, year=$year, month=$month: " . count($reminders) . " reminders");
        
        // Group reminders by date
        $calendarData = [];
        foreach ($reminders as $reminder) {
            $date = $reminder['reminder_date'];
            if (!isset($calendarData[$date])) {
                $calendarData[$date] = [];
            }
            $calendarData[$date][] = [
                'id' => $reminder['id'],
                'title' => $reminder['title'],
                'description' => $reminder['description'],
                'reminder_time' => $reminder['reminder_time'],
                'is_completed' => $reminder['is_completed'],
                'is_important' => $reminder['is_important'],
                'study_plan_id' => $reminder['study_plan_id']
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'calendar_data' => $calendarData]);
    }

    /**
     * Mark reminder as completed
     */
    public function completeReminder($reminderId) {
        requireStudent();

        $student = getCurrentStudent();
        $reminder = $this->studyReminderModel->findById($reminderId);

        if (!$reminder || $reminder['user_id'] != $student['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Reminder not found']);
            return;
        }

        $this->studyReminderModel->markCompleted($reminderId, true);
        echo json_encode(['success' => true]);
    }

    /**
     * Delete a reminder
     */
    public function deleteReminder($reminderId) {
        requireStudent();

        $student = getCurrentStudent();
        $reminder = $this->studyReminderModel->findById($reminderId);

        if (!$reminder || $reminder['user_id'] != $student['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Reminder not found']);
            return;
        }

        $this->studyReminderModel->delete($reminderId);
        echo json_encode(['success' => true]);
    }

    /**
     * Mark a study plan as complete
     */
    public function complete($planId) {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $student = getCurrentStudent();
        $studyPlan = $this->studyPlanModel->findById($planId);

        if (!$studyPlan || $studyPlan['student_id'] != $student['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Study plan not found']);
            return;
        }

        $result = $this->studyPlanModel->markComplete($planId);

        if ($result) {
            // Also remove associated reminders when plan is completed
            $reminders = $this->studyReminderModel->findByUser($student['id'], ['study_plan_id' => $planId]);
            foreach ($reminders as $reminder) {
                $this->studyReminderModel->delete($reminder['id']);
            }
            
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark study plan as complete']);
        }
    }

    /**
     * Add study plan dates to calendar
     */
    public function addToCalendar($planId) {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $student = getCurrentStudent();
        $studyPlan = $this->studyPlanModel->findById($planId);
        $startDate = $_POST['start_date'] ?? date('Y-m-d');

        if (!$studyPlan || $studyPlan['student_id'] != $student['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Study plan not found']);
            return;
        }

        // Use AI to generate a structured schedule from the study plan content
        $schedule = $this->aiRouter->generateStudySchedule($studyPlan['title'], $studyPlan['content'], $startDate);

        if (empty($schedule)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to generate schedule from study plan']);
            return;
        }

        $createdReminders = [];
        $conflicts = [];

        foreach ($schedule as $session) {
            $offset = intval($session['date_offset'] ?? 0);
            $targetDate = date('Y-m-d', strtotime($startDate . " + $offset days"));
            
            // Check for ANY existing reminders on this date to avoid direct time conflicts
            $existingOnDate = $this->studyReminderModel->findByDate($student['id'], $targetDate);
            
            // Check if THIS specific study plan already has a reminder on this date
            $alreadyScheduled = false;
            foreach ($existingOnDate as $rem) {
                if ($rem['study_plan_id'] == $planId) {
                    $alreadyScheduled = true;
                    break;
                }
            }

            if ($alreadyScheduled) {
                $conflicts[] = $targetDate;
                continue;
            }

            // Find a free time slot based on day of week (respecting school hours)
            $dayOfWeek = date('w', strtotime($targetDate)); // 0 = Sunday, 1-5 = Weekday, 6 = Saturday
            
            if ($dayOfWeek == 0) { // Sunday: Start from 12:00
                $slots = ["12:00", "14:00", "16:00", "18:00", "20:00", "22:00"];
                $startTime = "12:00:00";
            } elseif ($dayOfWeek == 6) { // Saturday: Start from 10:00 (assume more free time)
                $slots = ["10:00", "12:00", "14:00", "16:00", "18:00", "20:00", "22:00"];
                $startTime = "10:00:00";
            } else { // Weekdays: Start from 15:00 (after school)
                $slots = ["15:00", "17:00", "19:00", "21:00", "22:00"];
                $startTime = "15:00:00";
            }

            if (!empty($existingOnDate)) {
                $busyTimes = array_map(function($r) { return substr($r['reminder_time'], 0, 5); }, $existingOnDate);
                
                foreach ($slots as $slot) {
                    if (!in_array($slot, $busyTimes)) {
                        $startTime = $slot . ":00";
                        break;
                    }
                    // If all slots full, it will just use the last one
                    $startTime = $slot . ":00"; 
                }
            }

            $reminderId = $this->studyReminderModel->create(
                $student['id'],
                $session['title'] ?? $studyPlan['title'],
                $targetDate,
                $startTime,
                $session['description'] ?? '',
                $planId,
                0, // Not recurring by default
                null,
                1 // Mark as important
            );

            if ($reminderId) {
                $createdReminders[] = $reminderId;
            }
        }

        echo json_encode([
            'success' => true, 
            'count' => count($createdReminders),
            'conflicts' => count($conflicts)
        ]);
    }

    /**
     * Remove all reminders associated with a study plan
     */
    public function removeSchedule($planId) {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $student = getCurrentStudent();
        $studyPlan = $this->studyPlanModel->findById($planId);

        if (!$studyPlan || $studyPlan['student_id'] != $student['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Study plan not found']);
            return;
        }

        $reminders = $this->studyReminderModel->findByUser($student['id'], ['study_plan_id' => $planId]);
        foreach ($reminders as $reminder) {
            $this->studyReminderModel->delete($reminder['id']);
        }

        echo json_encode(['success' => true]);
    }

    /**
     * Mark a study plan as viewed (when user views the plan)
     */
    public function markAsViewed($planId) {
        requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $student = getCurrentStudent();
        $studyPlan = $this->studyPlanModel->findById($planId);

        if (!$studyPlan || $studyPlan['student_id'] != $student['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Study plan not found']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            // Check if is_completed column exists
            $columns = $db->query("PRAGMA table_info(study_plans)")->fetchAll(PDO::FETCH_COLUMN);
            $hasIsCompleted = in_array('is_completed', $columns);

            if ($hasIsCompleted) {
                // Mark the study plan as completed (is_completed = 1)
                // This will remove it from the notification count
                $stmt = $db->prepare("
                    UPDATE study_plans
                    SET is_completed = 1
                    WHERE id = ? AND student_id = ? AND (is_completed = 0 OR is_completed IS NULL)
                ");
                $stmt->execute([$planId, $student['id']]);
            }

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark study plan as viewed']);
        }
    }

    /**
     * Get count of pending (not completed) study plans
     */
    public function getPendingCount() {
        requireStudent();

        try {
            $count = getPendingStudyPlansCount();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'count' => $count]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to get pending count']);
        }
    }
}
