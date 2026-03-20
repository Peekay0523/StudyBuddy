<?php
/**
 * Dashboard Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UploadedScript.php';
require_once __DIR__ . '/../models/StudyPlan.php';
require_once __DIR__ . '/../models/ReportCard.php';
require_once __DIR__ . '/../models/UserActivity.php';
require_once __DIR__ . '/../models/BursaryApplication.php';
require_once __DIR__ . '/../models/InstitutionApplication.php';

class DashboardController {
    private $scriptModel;
    private $studyPlanModel;
    private $reportCardModel;
    private $userActivityModel;
    private $bursaryApplicationModel;
    private $institutionApplicationModel;

    public function __construct() {
        $this->scriptModel = new UploadedScript();
        $this->studyPlanModel = new StudyPlan();
        $this->reportCardModel = new ReportCard();
        $this->userActivityModel = new UserActivity();
        $this->bursaryApplicationModel = new BursaryApplication();
        $this->institutionApplicationModel = new InstitutionApplication();
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

        // Get user's login streak
        $userActivity = $this->userActivityModel->getUserActivity($userId);
        $loginStreak = $userActivity['login_streak'] ?? 0;

        // Calculate login streak points (1 point for every 3 consecutive days)
        $loginStreakPoints = floor($loginStreak / 3);

        // Calculate activity score (study plans + scripts + login streak points)
        $activityScore = $plansCount + $scriptsCount + $loginStreakPoints;
        
        // Calculate activity percentage (based on a scale of 0-100)
        // Assuming 50 activities = 100% active
        $activityPercentage = min(100, round(($activityScore / 50) * 100));

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

        // Pass login streak info to the view
        $loginStreak = $loginStreak ?? 0;
        $loginStreakPoints = $loginStreakPoints ?? 0;
        $nextRewardAt = 3 - ($loginStreak % 3);

        // Get bursary applications
        $bursaryApplications = $this->bursaryApplicationModel->findByStudentId($student['id']);
        $bursaryApplicationsCount = $this->bursaryApplicationModel->countByStudent($student['id']);

        // Get institution applications
        $institutionApplications = $this->institutionApplicationModel->findByStudentId($student['id']);
        $institutionApplicationsCount = $this->institutionApplicationModel->countByStudent($student['id']);

        include __DIR__ . '/../templates/pages/dashboard.php';
    }

    /**
     * Get login streak information
     */
    public function getLoginStreakInfo() {
        requireStudent();
        
        $student = getCurrentStudent();
        $userId = $student['user_id'];
        
        $userActivity = $this->userActivityModel->getUserActivity($userId);
        $loginStreak = $userActivity['login_streak'] ?? 0;
        $loginStreakPoints = floor($loginStreak / 3);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'login_streak' => $loginStreak,
            'login_streak_points' => $loginStreakPoints,
            'next_reward_at' => 3 - ($loginStreak % 3)
        ]);
        exit;
    }
}
