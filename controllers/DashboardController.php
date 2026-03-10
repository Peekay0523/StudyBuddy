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
        requireStudent();

        $student = getCurrentStudent();
        $userId = $student['user_id'];

        $scripts = $this->scriptModel->findByStudentId($student['id']);
        $studyPlans = $this->studyPlanModel->findByStudentId($student['id'], true);
        $reportCards = $this->reportCardModel->findByUserId($userId);

        $scriptsCount = $this->scriptModel->countByStudent($student['id']);
        $plansCount = $this->studyPlanModel->countByStudent($student['id']);
        $reportsCount = $this->reportCardModel->countByUserId($userId);
        $topicsCount = $this->scriptModel->getTotalTopicsCount($student['id']);

        $user = getCurrentUser();
        
        // Get user's subscription
        $db = Database::getInstance()->getConnection();
        $subscription = $db->prepare("
            SELECT * FROM subscriptions 
            WHERE user_id = ? AND status IN ('active', 'trial') 
            AND datetime(current_period_end) > datetime('now')
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $subscription->execute([$user['id']]);
        $userSubscription = $subscription->fetch();
        
        // Determine plan name and features
        $planName = 'Free';
        $planBadge = 'free';
        $planFeatures = [];
        $trialEnds = null;
        
        if ($userSubscription) {
            if ($userSubscription['status'] === 'trial') {
                $planName = 'Basic (Free Trial)';
                $planBadge = 'trial';
                $trialEnds = $userSubscription['current_period_end'];
                $planFeatures = [
                    '50 script uploads per month',
                    'Unlimited AI chat',
                    'AI study plan recitation',
                    'Priority email support',
                    'Ad-free experience'
                ];
            } elseif ($userSubscription['status'] === 'active') {
                $plan = $userSubscription['plan'];
                $planName = ucfirst($plan);
                $planBadge = $plan;
                
                if ($plan === 'basic') {
                    $planFeatures = [
                        '50 script uploads per month',
                        'Unlimited AI chat',
                        'AI study plan recitation',
                        'Priority email support',
                        'Ad-free experience'
                    ];
                } elseif ($plan === 'premium') {
                    $planFeatures = [
                        'Unlimited script uploads',
                        'Unlimited AI chat with GPT-4',
                        'Voice recitation for all content',
                        '24/7 priority support',
                        'Ad-free experience',
                        'Advanced analytics & insights'
                    ];
                }
            }
        }

        include __DIR__ . '/../templates/pages/dashboard.php';
    }
}
