<?php
/**
 * Dashboard Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UploadedScript.php';
require_once __DIR__ . '/../models/StudyPlan.php';
require_once __DIR__ . '/../models/ReportCard.php';

class DashboardController {
    private $scriptModel;
    private $studyPlanModel;
    private $reportCardModel;
    
    public function __construct() {
        $this->scriptModel = new UploadedScript();
        $this->studyPlanModel = new StudyPlan();
        $this->reportCardModel = new ReportCard();
    }
    
    public function index() {
        requireLogin();
        
        $student = getCurrentStudent();
        $studentId = $student['id'];
        
        $scripts = $this->scriptModel->findByStudentId($studentId);
        $studyPlans = $this->studyPlanModel->findByStudentId($studentId, true);
        $reportCards = $this->reportCardModel->findByStudentId($studentId);
        
        $scriptsCount = $this->scriptModel->countByStudent($studentId);
        $plansCount = $this->studyPlanModel->countByStudent($studentId);
        $reportsCount = $this->reportCardModel->countByStudent($studentId);
        $topicsCount = $this->scriptModel->getTotalTopicsCount($studentId);
        
        $user = getCurrentUser();
        
        include __DIR__ . '/../templates/pages/dashboard.php';
    }
}
