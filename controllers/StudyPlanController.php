<?php
/**
 * Study Plan Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/StudyPlan.php';
require_once __DIR__ . '/../helpers/AIHelper.php';

class StudyPlanController {
    private $studyPlanModel;
    private $aiHelper;

    public function __construct() {
        $this->studyPlanModel = new StudyPlan();
        $this->aiHelper = new AIHelper();
    }

    public function index() {
        requireStudent();

        $student = getCurrentStudent();
        $studyPlans = $this->studyPlanModel->findByStudentId($student['id'], true);

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
}
