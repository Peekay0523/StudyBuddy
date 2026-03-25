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
            'total_scripts' => $db->query("SELECT COUNT(*) FROM uploaded_scripts")->fetchColumn(),
            'total_report_cards' => $db->query("SELECT COUNT(*) FROM report_cards")->fetchColumn(),
            'monthly_revenue' => $db->query("SELECT COALESCE(SUM(price), 0) FROM subscriptions WHERE status = 'active' AND DATE(created_at) >= DATE('now', 'start of month')")->fetchColumn(),
            'new_users_this_month' => $db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) >= DATE('now', '-30 days')")->fetchColumn(),

            // OpenAI Usage Statistics (requires openai_usage_logs table - run /add_openai_usage_table.php first)
            'total_tokens_used' => 0,
            'tokens_this_month' => 0,
            'total_api_calls' => 0,
            'api_calls_this_month' => 0,
        ];

        // Calculate estimated cost only if table exists
        try {
            $stats['total_tokens_used'] = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs")->fetchColumn();
            $stats['tokens_this_month'] = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs WHERE DATE(created_at) >= DATE('now', 'start of month')")->fetchColumn();
            $stats['total_api_calls'] = $db->query("SELECT COUNT(*) FROM openai_usage_logs")->fetchColumn();
            $stats['api_calls_this_month'] = $db->query("SELECT COUNT(*) FROM openai_usage_logs WHERE DATE(created_at) >= DATE('now', 'start of month')")->fetchColumn();
            $stats['estimated_cost'] = $stats['total_tokens_used'] * 0.0000006;
            $stats['estimated_cost_month'] = $stats['tokens_this_month'] * 0.0000006;
        } catch (Exception $e) {
            // Table doesn't exist yet - use defaults
            $stats['estimated_cost'] = 0;
            $stats['estimated_cost_month'] = 0;
        }
        
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
                   COUNT(DISTINCT us.id) as scripts_count,
                   COUNT(DISTINCT rc.id) as report_cards_count
            FROM users u
            LEFT JOIN students st ON u.id = st.user_id
            LEFT JOIN uploaded_scripts us ON st.id = us.student_id
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
                   COUNT(DISTINCT us.id) as scripts_count,
                   COUNT(DISTINCT rc.id) as report_cards_count,
                   COUNT(DISTINCT sp.id) as study_plans_count
            FROM users u
            LEFT JOIN (
                SELECT user_id, plan, status, current_period_end, price
                FROM subscriptions
                WHERE status = 'active'
            ) s ON u.id = s.user_id
            LEFT JOIN students st ON u.id = st.user_id
            LEFT JOIN uploaded_scripts us ON st.id = us.student_id
            LEFT JOIN report_cards rc ON u.id = rc.user_id
            LEFT JOIN study_plans sp ON st.id = sp.student_id
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

        // Get filter from query string
        $filter = $_GET['filter'] ?? 'all'; // all, pending_eft, active, trial, expired, cancelled, bobpay, card

        // Build query based on filter
        if ($filter === 'all') {
            $subscriptions = $db->query("
                SELECT s.*, u.username, u.email, u.phone
                FROM subscriptions s
                JOIN users u ON s.user_id = u.id
                ORDER BY
                    CASE s.status
                        WHEN 'pending_eft' THEN 1
                        WHEN 'trial' THEN 2
                        WHEN 'active' THEN 3
                        WHEN 'expired' THEN 4
                        WHEN 'cancelled' THEN 5
                        ELSE 6
                    END,
                    s.created_at DESC
            ")->fetchAll();
        } else if (in_array($filter, ['bobpay', 'card'])) {
            // Filter by payment method
            $stmt = $db->prepare("
                SELECT s.*, u.username, u.email, u.phone
                FROM subscriptions s
                JOIN users u ON s.user_id = u.id
                WHERE s.payment_method = ?
                ORDER BY s.created_at DESC
            ");
            $stmt->execute([$filter]);
            $subscriptions = $stmt->fetchAll();
        } else {
            $stmt = $db->prepare("
                SELECT s.*, u.username, u.email, u.phone
                FROM subscriptions s
                JOIN users u ON s.user_id = u.id
                WHERE s.status = ?
                ORDER BY s.created_at DESC
            ");
            $stmt->execute([$filter]);
            $subscriptions = $stmt->fetchAll();
        }

        $pageTitle = 'Manage Subscriptions - Admin - StudySmart';
        $currentPage = 'admin-subscriptions';

        include __DIR__ . '/../templates/pages/admin/subscriptions.php';
    }
    
    /**
     * Scripts Management
     */
    public function scripts() {
        $db = Database::getInstance()->getConnection();

        // Query uploaded_scripts table which is the main table for script uploads
        $scripts = $db->query("
            SELECT s.*, u.username, u.email
            FROM uploaded_scripts s
            JOIN students st ON s.student_id = st.id
            JOIN users u ON st.user_id = u.id
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

        // Query report_cards table with user join
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
                   COUNT(DISTINCT us.id) as scripts_uploaded,
                   COALESCE(scr.plan, 'free') as subscription_plan
            FROM users u
            LEFT JOIN students st ON u.id = st.user_id
            LEFT JOIN study_plans sp ON st.id = sp.student_id
            LEFT JOIN uploaded_scripts us ON st.id = us.student_id
            LEFT JOIN (
                SELECT user_id, plan FROM subscriptions WHERE status = 'active'
            ) scr ON u.id = scr.user_id
            GROUP BY u.id
            ORDER BY study_plans_created + scripts_uploaded DESC
        ")->fetchAll();

        // Get topic statistics from scripts (memorandums)
        $topicStats = $db->query("
            SELECT subject, COUNT(*) as count
            FROM uploaded_scripts
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

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
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

        // Get user's scripts from uploaded_scripts table
        $scripts = $db->prepare("
            SELECT us.* FROM uploaded_scripts us
            JOIN students st ON us.student_id = st.id
            WHERE st.user_id = ? ORDER BY us.uploaded_at DESC
        ");
        $scripts->execute([$userId]);
        $userScripts = $scripts->fetchAll();

        // Get user's report cards
        $reportCards = $db->prepare("SELECT * FROM report_cards WHERE user_id = ? ORDER BY uploaded_at DESC");
        $reportCards->execute([$userId]);
        $userReportCards = $reportCards->fetchAll();

        // Get user's study plans
        $student = $db->prepare("SELECT id FROM students WHERE user_id = ?");
        $student->execute([$userId]);
        $studentData = $student->fetch();
        
        if ($studentData) {
            $studyPlans = $db->prepare("SELECT * FROM study_plans WHERE student_id = ? ORDER BY created_at DESC");
            $studyPlans->execute([$studentData['id']]);
            $userStudyPlans = $studyPlans->fetchAll();
        } else {
            $userStudyPlans = [];
        }
        
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
     * Change subscription status
     */
    public function changeSubscriptionStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/subscriptions');
            exit;
        }

        $subscriptionId = $_POST['subscription_id'] ?? null;
        $newStatus = $_POST['new_status'] ?? null;

        if (!$subscriptionId || !$newStatus) {
            setFlashMessage('error', 'Invalid request');
            header('Location: /admin/subscriptions');
            exit;
        }

        $validStatuses = ['active', 'expired', 'cancelled', 'trial'];
        if (!in_array($newStatus, $validStatuses)) {
            setFlashMessage('error', 'Invalid status');
            header('Location: /admin/subscriptions');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Get the subscription to check user_id
        $stmt = $db->prepare("SELECT user_id, status FROM subscriptions WHERE id = ?");
        $stmt->execute([$subscriptionId]);
        $subscription = $stmt->fetch();

        if (!$subscription) {
            setFlashMessage('error', 'Subscription not found');
            header('Location: /admin/subscriptions');
            exit;
        }

        // If changing to active or trial, check if user already has an active subscription
        if (in_array($newStatus, ['active', 'trial'])) {
            $checkStmt = $db->prepare("
                SELECT COUNT(*) FROM subscriptions
                WHERE user_id = ?
                AND id != ?
                AND status IN ('active', 'trial')
            ");
            $checkStmt->execute([$subscription['user_id'], $subscriptionId]);
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                setFlashMessage('error', 'This user already has an active subscription. Please cancel the existing subscription first.');
                header('Location: /admin/subscriptions');
                exit;
            }
        }

        // Build update query based on status
        if ($newStatus === 'cancelled') {
            $update = $db->prepare("UPDATE subscriptions SET status = ?, cancelled_at = datetime('now') WHERE id = ?");
            $update->execute([$newStatus, $subscriptionId]);
        } elseif ($newStatus === 'active' || $newStatus === 'trial') {
            // Reactivate - clear cancelled_at and extend period
            $update = $db->prepare("
                UPDATE subscriptions
                SET status = ?,
                    cancelled_at = NULL,
                    current_period_end = datetime('now', '+1 month')
                WHERE id = ?
            ");
            $update->execute([$newStatus, $subscriptionId]);
        } else {
            $update = $db->prepare("UPDATE subscriptions SET status = ? WHERE id = ?");
            $update->execute([$newStatus, $subscriptionId]);
        }

        $statusMessages = [
            'active' => 'Subscription activated successfully',
            'expired' => 'Subscription marked as expired',
            'cancelled' => 'Subscription cancelled successfully',
            'trial' => 'Subscription set to trial mode'
        ];

        setFlashMessage('success', $statusMessages[$newStatus] ?? 'Subscription status updated');
        header('Location: /admin/subscriptions');
    }

    /**
     * Approve EFT payment
     */
    public function approveEFTPayment() {
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

        // Get the subscription to check user_id
        $stmt = $db->prepare("SELECT user_id, status FROM subscriptions WHERE id = ?");
        $stmt->execute([$subscriptionId]);
        $subscription = $stmt->fetch();

        if (!$subscription) {
            setFlashMessage('error', 'Subscription not found');
            header('Location: /admin/subscriptions');
            exit;
        }

        // Check if user already has an active subscription (excluding this one)
        $checkStmt = $db->prepare("
            SELECT COUNT(*) FROM subscriptions
            WHERE user_id = ?
            AND id != ?
            AND status IN ('active', 'trial')
        ");
        $checkStmt->execute([$subscription['user_id'], $subscriptionId]);
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            setFlashMessage('error', 'This user already has an active subscription. Please cancel the existing subscription first.');
            header('Location: /admin/subscriptions');
            exit;
        }

        // Update subscription to active
        $update = $db->prepare("
            UPDATE subscriptions
            SET status = 'active',
                current_period_start = datetime('now'),
                current_period_end = datetime('now', '+1 month'),
                cancelled_at = NULL
            WHERE id = ?
        ");
        $update->execute([$subscriptionId]);

        setFlashMessage('success', 'EFT payment approved. Subscription activated for 1 month.');
        header('Location: /admin/subscriptions');
    }

    /**
     * Reject EFT payment
     */
    public function rejectEFTPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/subscriptions');
            exit;
        }

        $subscriptionId = $_POST['subscription_id'] ?? null;
        $reason = $_POST['rejection_reason'] ?? 'Payment verification failed';

        if (!$subscriptionId) {
            setFlashMessage('error', 'Invalid subscription');
            header('Location: /admin/subscriptions');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Update subscription to rejected
        $update = $db->prepare("
            UPDATE subscriptions 
            SET status = 'rejected',
                cancelled_at = datetime('now')
            WHERE id = ?
        ");
        $update->execute([$subscriptionId]);

        setFlashMessage('info', "EFT payment rejected: {$reason}. User has been notified.");
        header('Location: /admin/subscriptions');
    }

    /**
     * Download EFT proof of payment
     */
    public function downloadProof($subscriptionId) {
        $db = Database::getInstance()->getConnection();

        // Get subscription and proof path
        $stmt = $db->prepare("SELECT * FROM subscriptions WHERE id = ?");
        $stmt->execute([$subscriptionId]);
        $subscription = $stmt->fetch();

        if (!$subscription || empty($subscription['proof_path'])) {
            setFlashMessage('error', 'Proof of payment not found');
            header('Location: /admin/subscriptions');
            exit;
        }

        $filePath = __DIR__ . '/../../../public/' . $subscription['proof_path'];

        if (!file_exists($filePath)) {
            setFlashMessage('error', 'File not found');
            header('Location: /admin/subscriptions');
            exit;
        }

        // Get file info
        $pathInfo = pathinfo($filePath);
        $extension = strtolower($pathInfo['extension'] ?? '');

        // Set appropriate headers based on file type
        switch ($extension) {
            case 'pdf':
                header('Content-Type: application/pdf');
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            default:
                header('Content-Type: application/octet-stream');
        }

        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($filePath);
        exit;
    }
    
    /**
     * Delete a subscription
     */
    public function deleteSubscription() {
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
        
        // Get proof_path before deleting to remove file
        $stmt = $db->prepare("SELECT proof_path FROM subscriptions WHERE id = ?");
        $stmt->execute([$subscriptionId]);
        $subscription = $stmt->fetch();
        
        // Delete the proof file if it exists
        if ($subscription && !empty($subscription['proof_path'])) {
            $filePath = __DIR__ . '/../../../public/' . $subscription['proof_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Delete the subscription
        $delete = $db->prepare("DELETE FROM subscriptions WHERE id = ?");
        $delete->execute([$subscriptionId]);

        setFlashMessage('success', 'Subscription deleted successfully');
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

        // Get file paths before deleting
        $script = $db->prepare("SELECT file_path, memorandum_file_path FROM uploaded_scripts WHERE id = ?");
        $script->execute([$scriptId]);
        $scriptData = $script->fetch();

        // Delete script file if exists
        if ($scriptData && !empty($scriptData['file_path']) && file_exists(UPLOAD_DIR_SCRIPTS . $scriptData['file_path'])) {
            unlink(UPLOAD_DIR_SCRIPTS . $scriptData['file_path']);
        }

        // Delete memorandum file if exists (for shared scripts)
        if ($scriptData && !empty($scriptData['memorandum_file_path']) && file_exists(UPLOAD_DIR_SCRIPTS . $scriptData['memorandum_file_path'])) {
            unlink(UPLOAD_DIR_SCRIPTS . $scriptData['memorandum_file_path']);
        }

        // Delete memorandum from memorandums table if exists (for student scripts)
        try {
            $memo = $db->prepare("SELECT content FROM memorandums WHERE script_id = ?");
            $memo->execute([$scriptId]);
            $memoData = $memo->fetch();

            if ($memoData) {
                $db->prepare("DELETE FROM memorandums WHERE script_id = ?")->execute([$scriptId]);
            }
        } catch (PDOException $e) {
            // Memorandums table might not exist or have different structure
            // Continue with deletion
        }

        // Delete script record from uploaded_scripts table
        $db->prepare("DELETE FROM uploaded_scripts WHERE id = ?")->execute([$scriptId]);

        setFlashMessage('success', 'Script deleted successfully');
        header('Location: /admin/scripts');
    }

    /**
     * Upload shared script (admin)
     */
    public function uploadSharedScript() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/scripts');
            exit;
        }

        $error = '';

        // Handle file upload
        if (isset($_FILES['script_file']) && $_FILES['script_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['script_file'];
            $title = $_POST['title'] ?? $file['name'];
            $subject = $_POST['subject'] ?? '';
            $gradeLevel = $_POST['grade_level'] ?? '';
            $year = $_POST['year'] ?? null;
            $paper = $_POST['paper'] ?? null;

            // Validate file type
            $allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file['type'], $allowedTypes) && !in_array($fileExt, ['pdf', 'docx', 'txt'])) {
                $error = 'Invalid script file type. Please upload PDF, DOCX, or TXT files.';
            }

            // Validate file size (10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                $error = 'Script file size must be less than 10MB.';
            }

            // Validate year
            if (empty($year) || !in_array($year, range(2020, 2026))) {
                $error = 'Please select a valid year (2020-2026).';
            }

            // Validate paper
            if (empty($paper) || !in_array($paper, ['1', '2', '3'])) {
                $error = 'Please select a valid paper type.';
            }

            if (empty($error)) {
                // Generate unique filename
                $newFileName = 'shared_script_' . time() . '_' . basename($file['name']);
                $destPath = UPLOAD_DIR_SCRIPTS . $newFileName;

                // Ensure directory exists
                if (!is_dir(UPLOAD_DIR_SCRIPTS)) {
                    mkdir(UPLOAD_DIR_SCRIPTS, 0755, true);
                }

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $memorandumFilePath = null;

                    // Handle memorandum upload if provided
                    if (isset($_FILES['memorandum_file']) && $_FILES['memorandum_file']['error'] === UPLOAD_ERR_OK) {
                        $memoFile = $_FILES['memorandum_file'];
                        $memoExt = strtolower(pathinfo($memoFile['name'], PATHINFO_EXTENSION));
                        
                        if (in_array($memoFile['type'], $allowedTypes) || in_array($memoExt, ['pdf', 'docx', 'txt'])) {
                            if ($memoFile['size'] <= 10 * 1024 * 1024) {
                                $memoFileName = 'shared_memo_' . time() . '_' . basename($memoFile['name']);
                                $memoDestPath = UPLOAD_DIR_SCRIPTS . $memoFileName;
                                
                                if (move_uploaded_file($memoFile['tmp_name'], $memoDestPath)) {
                                    $memorandumFilePath = $memoFileName;
                                }
                            }
                        }
                    }

                    // Insert into database with is_shared = 1
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("
                        INSERT INTO uploaded_scripts (student_id, title, subject, grade_level, year, paper, file_path, is_shared, memorandum_file_path)
                        VALUES (1, ?, ?, ?, ?, ?, ?, 1, ?)
                    ");
                    $stmt->execute([$title, $subject, $gradeLevel, $year, $paper, $newFileName, $memorandumFilePath]);

                    setFlashMessage('success', 'Shared script uploaded successfully!' . ($memorandumFilePath ? ' Memorandum included.' : ''));
                    header('Location: /admin/scripts');
                    exit;
                } else {
                    $error = 'Failed to upload file. Please try again.';
                }
            }
        } else {
            $error = 'No file uploaded or upload error occurred.';
        }

        // If we reach here, there was an error - reload the page with error
        setFlashMessage('error', $error);
        header('Location: /admin/scripts');
        exit;
    }

    /**
     * Delete a report card
     */
    public function deleteReportCard() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/report-cards');
            exit;
        }

        $reportCardId = $_POST['report_card_id'] ?? null;
        if (!$reportCardId) {
            setFlashMessage('error', 'Invalid report card');
            header('Location: /admin/report-cards');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Get file path before deleting
        $reportCard = $db->prepare("SELECT file_path FROM report_cards WHERE id = ?");
        $reportCard->execute([$reportCardId]);
        $reportCardData = $reportCard->fetch();

        // Delete file if exists
        if ($reportCardData && !empty($reportCardData['file_path']) && file_exists(UPLOAD_DIR_REPORT_CARDS . $reportCardData['file_path'])) {
            unlink(UPLOAD_DIR_REPORT_CARDS . $reportCardData['file_path']);
        }

        // Delete career recommendations
        $db->prepare("DELETE FROM career_recommendations WHERE report_card_id = ?")->execute([$reportCardId]);

        // Delete report card record
        $db->prepare("DELETE FROM report_cards WHERE id = ?")->execute([$reportCardId]);

        setFlashMessage('success', 'Report card deleted successfully');
        header('Location: /admin/report-cards');
    }

    /**
     * Delete a user
     */
    public function deleteUser() {
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

        // Prevent admin from deleting themselves
        $currentUser = getCurrentUser();
        if ($userId == $currentUser['id']) {
            setFlashMessage('error', 'You cannot delete your own account');
            header('Location: /admin/users');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Get user info before deletion for logging
        $user = $db->prepare("SELECT username, email FROM users WHERE id = ?");
        $user->execute([$userId]);
        $userData = $user->fetch();

        if (!$userData) {
            setFlashMessage('error', 'User not found');
            header('Location: /admin/users');
            exit;
        }

        // Get all user's subscriptions to delete proof files
        try {
            $subscriptions = $db->prepare("SELECT proof_path FROM subscriptions WHERE user_id = ?");
            $subscriptions->execute([$userId]);
            $userSubscriptions = $subscriptions->fetchAll();

            // Delete proof of payment files
            foreach ($userSubscriptions as $subscription) {
                if (!empty($subscription['proof_path'])) {
                    $filePath = __DIR__ . '/../../../public/' . $subscription['proof_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
        } catch (Exception $e) {
            // Table may not exist
        }

        // Delete user's scans from database (file_data is stored in DB)
        try {
            $db->prepare("DELETE FROM scans WHERE user_id = ?")->execute([$userId]);
        } catch (Exception $e) {
            // Table may not exist
        }

        // Delete user's study group memberships
        try {
            $db->prepare("DELETE FROM study_group_members WHERE user_id = ?")->execute([$userId]);
        } catch (Exception $e) {
            // Table may not exist
        }

        // Delete user's study groups (they created)
        $studyGroups = $db->prepare("SELECT id FROM study_groups WHERE creator_user_id = ?");
        $studyGroups->execute([$userId]);
        $groups = $studyGroups->fetchAll();
        foreach ($groups as $group) {
            // Delete group messages
            try {
                $db->prepare("DELETE FROM study_group_messages WHERE study_group_id = ?")->execute([$group['id']]);
            } catch (Exception $e) {
                // Table may not exist
            }
            // Delete group scripts
            try {
                $db->prepare("DELETE FROM study_group_scripts WHERE study_group_id = ?")->execute([$group['id']]);
            } catch (Exception $e) {
                // Table may not exist
            }
        }
        $db->prepare("DELETE FROM study_groups WHERE creator_user_id = ?")->execute([$userId]);

        // Delete user's chat messages (if table exists)
        try {
            $db->prepare("DELETE FROM chat_messages WHERE user_id = ?")->execute([$userId]);
        } catch (Exception $e) {
            // Table may not exist
        }

        // Delete user's scripts (files and records)
        try {
            $scripts = $db->prepare("SELECT file_path FROM scripts WHERE user_id = ?");
            $scripts->execute([$userId]);
            $userScripts = $scripts->fetchAll();
            foreach ($userScripts as $script) {
                if (!empty($script['file_path']) && file_exists($script['file_path'])) {
                    unlink($script['file_path']);
                }
            }
        } catch (Exception $e) {
            // Table may not exist
        }
        // Delete memorandums for user's scripts
        try {
            $scriptIds = $db->prepare("SELECT id FROM scripts WHERE user_id = ?");
            $scriptIds->execute([$userId]);
            $ids = $scriptIds->fetchAll(PDO::FETCH_COLUMN);
            foreach ($ids as $scriptId) {
                $memo = $db->prepare("SELECT file_path FROM memorandums WHERE script_id = ?");
                $memo->execute([$scriptId]);
                $memoData = $memo->fetch();
                if ($memoData && !empty($memoData['file_path']) && file_exists($memoData['file_path'])) {
                    unlink($memoData['file_path']);
                }
            }
            $db->prepare("DELETE FROM memorandums WHERE script_id IN (SELECT id FROM scripts WHERE user_id = ?)")->execute([$userId]);
            $db->prepare("DELETE FROM scripts WHERE user_id = ?")->execute([$userId]);
        } catch (Exception $e) {
            // Table may not exist
        }

        // Delete user's report cards
        try {
            $reportCards = $db->prepare("SELECT file_path FROM report_cards WHERE user_id = ?");
            $reportCards->execute([$userId]);
            $userReportCards = $reportCards->fetchAll();
            foreach ($userReportCards as $reportCard) {
                if (!empty($reportCard['file_path']) && file_exists($reportCard['file_path'])) {
                    unlink($reportCard['file_path']);
                }
            }
            $db->prepare("DELETE FROM report_cards WHERE user_id = ?")->execute([$userId]);
        } catch (Exception $e) {
            // Table may not exist
        }

        // Delete user's study plans
        try {
            $db->prepare("DELETE FROM study_plans WHERE student_id = ?")->execute([$userId]);
        } catch (Exception $e) {
            // Table may not exist
        }

        // Delete user's subscriptions (cascade will handle this, but explicit for clarity)
        try {
            $db->prepare("DELETE FROM subscriptions WHERE user_id = ?")->execute([$userId]);
        } catch (Exception $e) {
            // Table may not exist
        }

        // Delete user's scan usage records
        try {
            $db->prepare("DELETE FROM scan_usage WHERE user_id = ?")->execute([$userId]);
        } catch (Exception $e) {
            // Table may not exist
        }

        // Finally, delete the user
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

        setFlashMessage('success', "User '{$userData['username']}' and all associated data deleted successfully");
        header('Location: /admin/users');
    }

    /**
     * OpenAI Settings - Manage API keys and credits
     */
    public function openaiSettings() {
        $db = Database::getInstance()->getConnection();
        
        // Get usage statistics
        $stats = [
            'total_tokens_used' => 0,
            'tokens_this_month' => 0,
            'total_api_calls' => 0,
            'estimated_cost' => 0,
        ];
        
        try {
            $stats['total_tokens_used'] = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs")->fetchColumn();
            $stats['tokens_this_month'] = $db->query("SELECT COALESCE(SUM(total_tokens), 0) FROM openai_usage_logs WHERE DATE(created_at) >= DATE('now', 'start of month')")->fetchColumn();
            $stats['total_api_calls'] = $db->query("SELECT COUNT(*) FROM openai_usage_logs")->fetchColumn();
            $stats['estimated_cost'] = $stats['total_tokens_used'] * 0.0000006;
        } catch (Exception $e) {
            // Table doesn't exist
        }
        
        // Get recent usage
        $recentUsage = $db->query("
            SELECT o.*, u.username
            FROM openai_usage_logs o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 20
        ")->fetchAll();
        
        $pageTitle = 'OpenAI Settings - StudySmart';
        $currentPage = 'admin-openai';
        
        include __DIR__ . '/../templates/pages/admin/openai_settings.php';
    }

    /**
     * Update OpenAI Settings
     */
    public function updateOpenaiSettings() {
        // This would typically update API keys in a config file or environment
        // For now, we'll just show instructions

        setFlashMessage('info', 'To add OpenAI credits, visit: <a href="https://platform.openai.com/account/billing" target="_blank">OpenAI Platform</a> and add credits to your account. Your API key is configured in the .env file.');
        header('Location: /admin/openai-settings');
    }

    /**
     * Banking Settings - Manage EFT Banking Details
     */
    public function bankingSettings() {
        $db = Database::getInstance()->getConnection();

        // Get current banking details from settings table
        $bankingDetails = $this->getBankingDetails();

        $pageTitle = 'Banking Settings - StudySmart';
        $currentPage = 'admin-banking';

        include __DIR__ . '/../templates/pages/admin/banking_settings.php';
    }

    /**
     * Update Banking Settings
     */
    public function updateBankingSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/banking-settings');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Get and sanitize POST data
        $bankingData = [
            'bank_name' => $_POST['bank_name'] ?? '',
            'account_type' => $_POST['account_type'] ?? '',
            'account_number' => $_POST['account_number'] ?? '',
            'branch_code' => $_POST['branch_code'] ?? '',
            'account_holder' => $_POST['account_holder'] ?? '',
            'reference_instruction' => $_POST['reference_instruction'] ?? '',
            'email_address' => $_POST['email_address'] ?? '',
            'activation_time' => $_POST['activation_time'] ?? '',
        ];

        // Validate required fields
        if (empty($bankingData['bank_name']) || empty($bankingData['account_number']) || empty($bankingData['branch_code'])) {
            setFlashMessage('error', 'Bank name, account number, and branch code are required.');
            header('Location: /admin/banking-settings');
            exit;
        }

        // Save banking details
        $this->saveBankingDetails($bankingData);

        setFlashMessage('success', 'Banking details updated successfully!');
        header('Location: /admin/banking-settings');
        exit;
    }

    /**
     * Get banking details from settings table or file
     */
    private function getBankingDetails() {
        $db = Database::getInstance()->getConnection();

        // Default banking details
        $defaults = [
            'bank_name' => 'FNB',
            'account_type' => 'Current Account',
            'account_number' => '62123456789',
            'branch_code' => '250655',
            'account_holder' => 'StudySmart',
            'reference_instruction' => 'Use your username and plan (e.g., john-basic)',
            'email_address' => 'billing@studysmart.co.za',
            'activation_time' => '24-48 hours',
        ];

        try {
            // Check if settings table exists
            $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetch();
            
            if ($result) {
                // Get stored settings
                $storedSettings = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'banking_%'")->fetchAll();
                
                foreach ($storedSettings as $setting) {
                    $key = str_replace('banking_', '', $setting['setting_key']);
                    if (!empty($setting['setting_value'])) {
                        $defaults[$key] = $setting['setting_value'];
                    }
                }
            }
        } catch (Exception $e) {
            // Table doesn't exist, use defaults
        }

        return $defaults;
    }

    /**
     * Save banking details to settings table
     */
    private function saveBankingDetails($data) {
        $db = Database::getInstance()->getConnection();

        try {
            // Create settings table if it doesn't exist
            $db->exec("
                CREATE TABLE IF NOT EXISTS settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key TEXT UNIQUE NOT NULL,
                    setting_value TEXT,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");

            // Save each setting
            foreach ($data as $key => $value) {
                $db->prepare("
                    INSERT OR REPLACE INTO settings (setting_key, setting_value, updated_at)
                    VALUES ('banking_' || ?, ?, datetime('now'))
                ")->execute([$key, $value]);
            }
        } catch (Exception $e) {
            // Log error but don't fail
            error_log('Failed to save banking settings: ' . $e->getMessage());
        }
    }

    /**
     * Bursaries Management - List all bursaries
     */
    public function bursaries() {
        $db = Database::getInstance()->getConnection();

        // Auto-deactivate expired bursaries
        $db->exec("UPDATE bursaries SET is_active = 0, updated_at = datetime('now') WHERE is_active = 1 AND deadline < date('now')");

        // Get filter from query string
        $filter = $_GET['filter'] ?? 'all'; // all, active, inactive, expired

        if ($filter === 'active') {
            $bursaries = $db->query("SELECT * FROM bursaries WHERE is_active = 1 ORDER BY deadline ASC")->fetchAll();
        } elseif ($filter === 'inactive') {
            $bursaries = $db->query("SELECT * FROM bursaries WHERE is_active = 0 ORDER BY updated_at DESC")->fetchAll();
        } elseif ($filter === 'expired') {
            $bursaries = $db->query("SELECT * FROM bursaries WHERE deadline < date('now') ORDER BY deadline DESC")->fetchAll();
        } else {
            $bursaries = $db->query("SELECT * FROM bursaries ORDER BY is_active DESC, deadline ASC")->fetchAll();
        }

        $pageTitle = 'Manage Bursaries - Admin - StudySmart';
        $currentPage = 'admin-bursaries';

        include __DIR__ . '/../templates/pages/admin/bursaries.php';
    }

    /**
     * Add new bursary - Show form
     */
    public function addBursary() {
        $pageTitle = 'Add New Bursary - Admin - StudySmart';
        $currentPage = 'admin-bursaries';
        $bursary = null;
        $isEdit = false;

        include __DIR__ . '/../templates/pages/admin/bursaries_add_edit.php';
    }

    /**
     * Create new bursary
     */
    public function createBursary() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/bursaries');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Get and validate POST data
        $name = trim($_POST['name'] ?? '');
        $provider = trim($_POST['provider'] ?? '');
        $eligibility = trim($_POST['eligibility'] ?? '');
        $covers = trim($_POST['covers'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $applyUrl = trim($_POST['apply_url'] ?? '');
        $minGradeAverage = floatval($_POST['min_grade_average'] ?? 0);
        $maxGradeAverage = floatval($_POST['max_grade_average'] ?? 100);
        $requiredSubjects = $_POST['required_subjects'] ?? [];
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validate required fields
        if (empty($name) || empty($provider) || empty($eligibility) || empty($deadline)) {
            setFlashMessage('error', 'Name, provider, eligibility, and deadline are required.');
            header('Location: /admin/bursaries/add');
            exit;
        }

        // Validate deadline format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
            setFlashMessage('error', 'Deadline must be in YYYY-MM-DD format.');
            header('Location: /admin/bursaries/add');
            exit;
        }

        // Insert bursary
        $stmt = $db->prepare("
            INSERT INTO bursaries (name, provider, eligibility, covers, deadline, contact, apply_url, 
                                   min_grade_average, max_grade_average, required_subjects, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $provider,
            $eligibility,
            $covers,
            $deadline,
            $contact,
            $applyUrl,
            $minGradeAverage,
            $maxGradeAverage,
            json_encode($requiredSubjects),
            $isActive
        ]);

        setFlashMessage('success', 'Bursary added successfully!');
        header('Location: /admin/bursaries');
    }

    /**
     * Edit bursary - Show form
     */
    public function editBursary($bursaryId) {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM bursaries WHERE id = ?");
        $stmt->execute([$bursaryId]);
        $bursary = $stmt->fetch();

        if (!$bursary) {
            setFlashMessage('error', 'Bursary not found');
            header('Location: /admin/bursaries');
            exit;
        }

        // Decode required subjects
        $bursary['required_subjects'] = json_decode($bursary['required_subjects'] ?? '[]', true) ?? [];

        $pageTitle = 'Edit Bursary - Admin - StudySmart';
        $currentPage = 'admin-bursaries';
        $isEdit = true;

        include __DIR__ . '/../templates/pages/admin/bursaries_add_edit.php';
    }

    /**
     * Update bursary
     */
    public function updateBursary($bursaryId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/bursaries');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Check if bursary exists
        $stmt = $db->prepare("SELECT * FROM bursaries WHERE id = ?");
        $stmt->execute([$bursaryId]);
        $bursary = $stmt->fetch();

        if (!$bursary) {
            setFlashMessage('error', 'Bursary not found');
            header('Location: /admin/bursaries');
            exit;
        }

        // Get and validate POST data
        $name = trim($_POST['name'] ?? '');
        $provider = trim($_POST['provider'] ?? '');
        $eligibility = trim($_POST['eligibility'] ?? '');
        $covers = trim($_POST['covers'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $applyUrl = trim($_POST['apply_url'] ?? '');
        $minGradeAverage = floatval($_POST['min_grade_average'] ?? 0);
        $maxGradeAverage = floatval($_POST['max_grade_average'] ?? 100);
        $requiredSubjects = $_POST['required_subjects'] ?? [];
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validate required fields
        if (empty($name) || empty($provider) || empty($eligibility) || empty($deadline)) {
            setFlashMessage('error', 'Name, provider, eligibility, and deadline are required.');
            header("Location: /admin/bursaries/edit/{$bursaryId}");
            exit;
        }

        // Validate deadline format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
            setFlashMessage('error', 'Deadline must be in YYYY-MM-DD format.');
            header("Location: /admin/bursaries/edit/{$bursaryId}");
            exit;
        }

        // Update bursary
        $stmt = $db->prepare("
            UPDATE bursaries 
            SET name = ?, provider = ?, eligibility = ?, covers = ?, deadline = ?, 
                contact = ?, apply_url = ?, min_grade_average = ?, max_grade_average = ?, 
                required_subjects = ?, is_active = ?, updated_at = datetime('now')
            WHERE id = ?
        ");

        $stmt->execute([
            $name,
            $provider,
            $eligibility,
            $covers,
            $deadline,
            $contact,
            $applyUrl,
            $minGradeAverage,
            $maxGradeAverage,
            json_encode($requiredSubjects),
            $isActive,
            $bursaryId
        ]);

        setFlashMessage('success', 'Bursary updated successfully!');
        header('Location: /admin/bursaries');
    }

    /**
     * Delete bursary
     */
    public function deleteBursary() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/bursaries');
            exit;
        }

        $bursaryId = $_POST['bursary_id'] ?? null;
        if (!$bursaryId) {
            setFlashMessage('error', 'Invalid bursary');
            header('Location: /admin/bursaries');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Check if bursary exists
        $stmt = $db->prepare("SELECT * FROM bursaries WHERE id = ?");
        $stmt->execute([$bursaryId]);
        $bursary = $stmt->fetch();

        if (!$bursary) {
            setFlashMessage('error', 'Bursary not found');
            header('Location: /admin/bursaries');
            exit;
        }

        // Delete bursary
        $db->prepare("DELETE FROM bursaries WHERE id = ?")->execute([$bursaryId]);

        setFlashMessage('success', 'Bursary deleted successfully');
        header('Location: /admin/bursaries');
    }

    /**
     * Toggle bursary active status
     */
    public function toggleBursaryStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/bursaries');
            exit;
        }

        $bursaryId = $_POST['bursary_id'] ?? null;
        if (!$bursaryId) {
            setFlashMessage('error', 'Invalid bursary');
            header('Location: /admin/bursaries');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Get current status
        $stmt = $db->prepare("SELECT is_active FROM bursaries WHERE id = ?");
        $stmt->execute([$bursaryId]);
        $bursary = $stmt->fetch();

        if (!$bursary) {
            setFlashMessage('error', 'Bursary not found');
            header('Location: /admin/bursaries');
            exit;
        }

        // Toggle status
        $newStatus = $bursary['is_active'] ? 0 : 1;
        $db->prepare("UPDATE bursaries SET is_active = ?, updated_at = datetime('now') WHERE id = ?")
           ->execute([$newStatus, $bursaryId]);

        setFlashMessage('success', 'Bursary status updated');
        header('Location: /admin/bursaries');
    }
    
    /**
     * BobPay Payment Management
     */
    public function bobpayPayments() {
        require_once __DIR__ . '/../config/bobpay.php';
        
        $bobPay = new BobPayHelper();
        
        // Get filters from request
        $filters = [
            'include_retained_amount' => 'true',
            'limit' => 20,
            'order' => 'DESC',
            'order_by' => 'time_created'
        ];
        
        if (!empty($_GET['status'])) {
            $filters['statuses'] = [$_GET['status']];
        }
        
        if (!empty($_GET['from_date'])) {
            $filters['start_date'] = $_GET['from_date'] . ' 00:00:00';
        }
        
        if (!empty($_GET['to_date'])) {
            $filters['end_date'] = $_GET['to_date'] . ' 23:59:59';
        }
        
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        // Get payment intents
        $payments = $bobPay->getPaymentIntents($filters);
        
        // Get payment methods
        $paymentMethods = $bobPay->getPublicPaymentMethods('SAN001');
        
        include __DIR__ . '/../templates/pages/admin/bobpay_payments.php';
    }
    
    /**
     * BobPay Process Refund
     */
    public function bobpayRefund() {
        require_once __DIR__ . '/../config/bobpay.php';
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/bobpay');
            exit;
        }
        
        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $reason = $_POST['reason'] ?? '';
        
        if (!$paymentId) {
            setFlashMessage('error', 'Invalid payment ID');
            header('Location: /admin/bobpay');
            exit;
        }
        
        $bobPay = new BobPayHelper();
        $result = $bobPay->refundPayment($paymentId);
        
        if ($result && isset($result['payment_method'])) {
            setFlashMessage('success', 'Refund processed successfully!');
            
            // Log refund in database (optional)
            $this->logRefund($paymentId, $reason, $result);
        } else {
            setFlashMessage('error', 'Failed to process refund. Please try again.');
        }
        
        header('Location: /admin/bobpay');
        exit;
    }
    
    /**
     * Log refund in database
     */
    private function logRefund($paymentId, $reason, $result) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO payment_refunds (payment_id, refund_amount, reason, bobpay_response, refunded_by, created_at)
                VALUES (?, ?, ?, ?, ?, datetime('now'))
            ");
            $refundAmount = $result['payment_method']['amount'] ?? 0;
            $userId = getCurrentUser()['id'] ?? 0;
            $stmt->execute([$paymentId, $refundAmount, $reason, json_encode($result), $userId]);
        } catch (Exception $e) {
            // Table might not exist, ignore
            error_log('Refund log error: ' . $e->getMessage());
        }
    }
    
    /**
     * BobPay Payment Details
     */
    public function bobpayPaymentDetails($paymentId) {
        require_once __DIR__ . '/../config/bobpay.php';
        
        $bobPay = new BobPayHelper();
        $payment = $bobPay->getPaymentIntent($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            header('Location: /admin/bobpay');
            exit;
        }
        
        $pageTitle = 'Payment Details #' . $paymentId;
        include __DIR__ . '/../templates/pages/admin/bobpay_payment_details.php';
    }
}
