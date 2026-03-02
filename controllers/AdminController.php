<?php
/**
 * Admin Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class AdminController {
    
    public function __construct() {
        $this->requireAdmin();
    }
    
    /**
     * Check if user is admin
     */
    private function requireAdmin() {
        if (!isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        $user = getCurrentUser();
        if (!$user || $user['role'] !== 'admin') {
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * Admin Dashboard - Overview
     */
    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Get statistics
        $stats = [
            'total_users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'total_subscriptions' => $db->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'")->fetchColumn(),
            'total_scripts' => $db->query("SELECT COUNT(*) FROM scripts")->fetchColumn(),
            'total_report_cards' => $db->query("SELECT COUNT(*) FROM report_cards")->fetchColumn(),
            'monthly_revenue' => $db->query("SELECT COALESCE(SUM(price), 0) FROM subscriptions WHERE status = 'active'")->fetchColumn(),
            'new_users_this_month' => $db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) >= DATE('now', '-30 days')")->fetchColumn(),
        ];
        
        // Recent users
        $recentUsers = $db->query("
            SELECT u.*, 
                   COALESCE(s.plan, 'free') as subscription_plan,
                   COALESCE(s.status, 'inactive') as subscription_status
            FROM users u
            LEFT JOIN (
                SELECT user_id, plan, status FROM subscriptions 
                WHERE status = 'active' 
                ORDER BY created_at DESC
            ) s ON u.id = s.user_id
            ORDER BY u.created_at DESC 
            LIMIT 10
        ")->fetchAll();
        
        // Subscription breakdown
        $subscriptionBreakdown = $db->query("
            SELECT plan, COUNT(*) as count, SUM(price) as revenue
            FROM subscriptions 
            WHERE status = 'active'
            GROUP BY plan
        ")->fetchAll();
        
        // Top users by activity
        $topUsers = $db->query("
            SELECT u.username, u.email,
                   COUNT(DISTINCT s.id) as scripts_count,
                   COUNT(DISTINCT rc.id) as report_cards_count
            FROM users u
            LEFT JOIN scripts s ON u.id = s.user_id
            LEFT JOIN report_cards rc ON u.id = rc.user_id
            GROUP BY u.id
            ORDER BY scripts_count + report_cards_count DESC
            LIMIT 10
        ")->fetchAll();
        
        $pageTitle = 'Admin Dashboard - StudySmart';
        $currentPage = 'admin-dashboard';
        
        include __DIR__ . '/../templates/pages/admin/dashboard.php';
    }
    
    /**
     * Users Management
     */
    public function users() {
        $db = Database::getInstance()->getConnection();
        
        $search = $_GET['search'] ?? '';
        $filter = $_GET['filter'] ?? 'all'; // all, free, basic, premium
        
        $query = "
            SELECT u.*, 
                   COALESCE(s.plan, 'free') as subscription_plan,
                   COALESCE(s.status, 'inactive') as subscription_status,
                   COALESCE(s.current_period_end, '') as subscription_end,
                   COALESCE(s.price, 0) as subscription_price,
                   COUNT(DISTINCT scr.id) as scripts_count,
                   COUNT(DISTINCT rc.id) as report_cards_count,
                   COUNT(DISTINCT sp.id) as study_plans_count
            FROM users u
            LEFT JOIN (
                SELECT user_id, plan, status, current_period_end, price 
                FROM subscriptions 
                WHERE status = 'active'
            ) s ON u.id = s.user_id
            LEFT JOIN scripts scr ON u.id = scr.user_id
            LEFT JOIN report_cards rc ON u.id = rc.user_id
            LEFT JOIN study_plans sp ON u.id = sp.student_id
        ";
        
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(u.username LIKE ? OR u.email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($filter !== 'all') {
            if ($filter === 'free') {
                $where[] = "(s.plan IS NULL OR s.plan = 'free')";
            } else {
                $where[] = "s.plan = ?";
                $params[] = $filter;
            }
        }
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }
        
        $query .= " GROUP BY u.id ORDER BY u.created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        $pageTitle = 'Manage Users - Admin - StudySmart';
        $currentPage = 'admin-users';
        
        include __DIR__ . '/../templates/pages/admin/users.php';
    }
    
    /**
     * Subscriptions Management
     */
    public function subscriptions() {
        $db = Database::getInstance()->getConnection();
        
        $subscriptions = $db->query("
            SELECT s.*, u.username, u.email
            FROM subscriptions s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
        ")->fetchAll();
        
        $pageTitle = 'Manage Subscriptions - Admin - StudySmart';
        $currentPage = 'admin-subscriptions';
        
        include __DIR__ . '/../templates/pages/admin/subscriptions.php';
    }
    
    /**
     * Scripts Management
     */
    public function scripts() {
        $db = Database::getInstance()->getConnection();
        
        $scripts = $db->query("
            SELECT s.*, u.username, u.email
            FROM scripts s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.uploaded_at DESC
        ")->fetchAll();
        
        $pageTitle = 'Manage Scripts - Admin - StudySmart';
        $currentPage = 'admin-scripts';
        
        include __DIR__ . '/../templates/pages/admin/scripts.php';
    }
    
    /**
     * Report Cards Management
     */
    public function reportCards() {
        $db = Database::getInstance()->getConnection();
        
        $reportCards = $db->query("
            SELECT rc.*, u.username, u.email
            FROM report_cards rc
            JOIN users u ON rc.user_id = u.id
            ORDER BY rc.uploaded_at DESC
        ")->fetchAll();
        
        $pageTitle = 'Manage Report Cards - Admin - StudySmart';
        $currentPage = 'admin-report-cards';
        
        include __DIR__ . '/../templates/pages/admin/report_cards.php';
    }
    
    /**
     * Topics Mastered - Analytics
     */
    public function topicsMastered() {
        $db = Database::getInstance()->getConnection();
        
        // Get all users with their mastered topics (from study plans and scripts)
        $topicsData = $db->query("
            SELECT u.id, u.username, u.email,
                   COUNT(DISTINCT sp.id) as study_plans_created,
                   COUNT(DISTINCT s.id) as scripts_uploaded,
                   COALESCE(scr.plan, 'free') as subscription_plan
            FROM users u
            LEFT JOIN study_plans sp ON u.id = sp.student_id
            LEFT JOIN scripts s ON u.id = s.user_id
            LEFT JOIN (
                SELECT user_id, plan FROM subscriptions WHERE status = 'active'
            ) scr ON u.id = scr.user_id
            GROUP BY u.id
            ORDER BY study_plans_created + scripts_uploaded DESC
        ")->fetchAll();
        
        // Get topic statistics from scripts (memorandums)
        $topicStats = $db->query("
            SELECT subject, COUNT(*) as count
            FROM scripts
            WHERE subject IS NOT NULL AND subject != ''
            GROUP BY subject
            ORDER BY count DESC
        ")->fetchAll();
        
        $pageTitle = 'Topics Mastered - Admin - StudySmart';
        $currentPage = 'admin-topics';
        
        include __DIR__ . '/../templates/pages/admin/topics_mastered.php';
    }
    
    /**
     * View single user details
     */
    public function viewUser($userId) {
        $db = Database::getInstance()->getConnection();
        
        $user = $db->prepare("SELECT * FROM users WHERE id = ?")->execute([$userId]);
        $user = $user->fetch();
        
        if (!$user) {
            header('Location: /admin/users');
            exit;
        }
        
        // Get user's subscriptions
        $subscriptions = $db->prepare("
            SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC
        ");
        $subscriptions->execute([$userId]);
        $userSubscriptions = $subscriptions->fetchAll();
        
        // Get user's scripts
        $scripts = $db->prepare("SELECT * FROM scripts WHERE user_id = ? ORDER BY uploaded_at DESC");
        $scripts->execute([$userId]);
        $userScripts = $scripts->fetchAll();
        
        // Get user's report cards
        $reportCards = $db->prepare("SELECT * FROM report_cards WHERE user_id = ? ORDER BY uploaded_at DESC");
        $reportCards->execute([$userId]);
        $userReportCards = $reportCards->fetchAll();
        
        // Get user's study plans
        $studyPlans = $db->prepare("SELECT * FROM study_plans WHERE student_id = ? ORDER BY created_at DESC");
        $studyPlans->execute([$userId]);
        $userStudyPlans = $studyPlans->fetchAll();
        
        $pageTitle = "View User: {$user['username']} - Admin - StudySmart";
        $currentPage = 'admin-users';
        
        include __DIR__ . '/../templates/pages/admin/user_detail.php';
    }
    
    /**
     * Toggle user role (admin/student)
     */
    public function toggleRole() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/users');
            exit;
        }
        
        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            setFlashMessage('error', 'Invalid user');
            header('Location: /admin/users');
            exit;
        }
        
        $db = Database::getInstance()->getConnection();
        $user = $db->prepare("SELECT role FROM users WHERE id = ?");
        $user->execute([$userId]);
        $userData = $user->fetch();
        
        if (!$userData) {
            setFlashMessage('error', 'User not found');
            header('Location: /admin/users');
            exit;
        }
        
        $newRole = $userData['role'] === 'admin' ? 'student' : 'admin';
        $update = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $update->execute([$newRole, $userId]);
        
        setFlashMessage('success', "User role updated to {$newRole}");
        header('Location: /admin/users');
    }
    
    /**
     * Cancel a subscription
     */
    public function cancelSubscription() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/subscriptions');
            exit;
        }
        
        $subscriptionId = $_POST['subscription_id'] ?? null;
        if (!$subscriptionId) {
            setFlashMessage('error', 'Invalid subscription');
            header('Location: /admin/subscriptions');
            exit;
        }
        
        $db = Database::getInstance()->getConnection();
        $update = $db->prepare("UPDATE subscriptions SET status = 'cancelled', cancelled_at = datetime('now') WHERE id = ?");
        $update->execute([$subscriptionId]);
        
        setFlashMessage('success', 'Subscription cancelled successfully');
        header('Location: /admin/subscriptions');
    }
    
    /**
     * Delete a script
     */
    public function deleteScript() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/scripts');
            exit;
        }
        
        $scriptId = $_POST['script_id'] ?? null;
        if (!$scriptId) {
            setFlashMessage('error', 'Invalid script');
            header('Location: /admin/scripts');
            exit;
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Get file path before deleting
        $script = $db->prepare("SELECT file_path FROM scripts WHERE id = ?");
        $script->execute([$scriptId]);
        $scriptData = $script->fetch();
        
        // Delete file if exists
        if ($scriptData && file_exists($scriptData['file_path'])) {
            unlink($scriptData['file_path']);
        }
        
        // Delete memorandum if exists
        $memo = $db->prepare("SELECT file_path FROM memorandums WHERE script_id = ?");
        $memo->execute([$scriptId]);
        $memoData = $memo->fetch();
        
        if ($memoData && file_exists($memoData['file_path'])) {
            unlink($memoData['file_path']);
            $db->prepare("DELETE FROM memorandums WHERE script_id = ?")->execute([$scriptId]);
        }
        
        // Delete script record
        $db->prepare("DELETE FROM scripts WHERE id = ?")->execute([$scriptId]);
        
        setFlashMessage('success', 'Script deleted successfully');
        header('Location: /admin/scripts');
    }
}
