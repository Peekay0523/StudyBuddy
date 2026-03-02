<?php
/**
 * Study Plan Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/StudyPlan.php';

class StudyPlanController {
    private $studyPlanModel;
    
    public function __construct() {
        $this->studyPlanModel = new StudyPlan();
    }
    
    public function index() {
        requireLogin();
        
        $student = getCurrentStudent();
        $studyPlans = $this->studyPlanModel->findByStudentId($student['id'], true);
        
        include __DIR__ . '/../templates/pages/study_plan.php';
    }
    
    public function view($planId) {
        requireLogin();
        
        $student = getCurrentStudent();
        $studyPlan = $this->studyPlanModel->findById($planId);
        
        if (!$studyPlan || $studyPlan['student_id'] != $student['id']) {
            header('Location: /dashboard');
            exit;
        }
        
        include __DIR__ . '/../templates/pages/view_study_plan.php';
    }
}
