<?php
/**
 * Study Plan Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/StudyPlan.php';
require_once __DIR__ . '/../models/StudyReminder.php';
require_once __DIR__ . '/../helpers/AIHelper.php';

class StudyPlanController {
    private $studyPlanModel;
    private $studyReminderModel;
    private $aiHelper;

    public function __construct() {
        $this->studyPlanModel = new StudyPlan();
        $this->studyReminderModel = new StudyReminder();
        $this->aiHelper = new AIHelper();
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

        $recitation = $this->aiHelper->reciteStudyPlan($studyPlan['title'], $studyPlan['content']);

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
                $recurringPattern
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

        $calendarData = $this->studyReminderModel->getCountByDate($student['id'], $year, $month);

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
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark study plan as complete']);
        }
    }
}
