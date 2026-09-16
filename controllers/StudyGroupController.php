<?php
/**
 * Study Group Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/StudyGroup.php';
require_once __DIR__ . '/../models/StudyGroupScript.php';
require_once __DIR__ . '/../models/StudyGroupMessage.php';
require_once __DIR__ . '/../models/StudyGroupInvite.php';
require_once __DIR__ . '/../models/Tutor.php';
require_once __DIR__ . '/../models/TutorStudent.php';
require_once __DIR__ . '/../models/MyResource.php';
require_once __DIR__ . '/../controllers/SubscriptionController.php';

class StudyGroupController {
    private $studyGroupModel;

    public function __construct() {
        $this->studyGroupModel = new StudyGroup();
        $this->ensureSchema();
    }

    /**
     * Make sure the study_group_scripts table has the extra resource
     * columns and the tutors table exists. This is idempotent so it
     * is safe to run on every request (existing installs + fresh ones).
     */
    private function ensureSchema() {
        try {
            $db = Database::getInstance()->getConnection();

            // Add resource_type / video_url columns to study_group_scripts if missing
            $columns = $db->query("PRAGMA table_info(study_group_scripts)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (is_array($columns)) {
                if (!in_array('resource_type', $columns)) {
                    $db->exec("ALTER TABLE study_group_scripts ADD COLUMN resource_type TEXT DEFAULT 'script'");
                }
                if (!in_array('video_url', $columns)) {
                    $db->exec("ALTER TABLE study_group_scripts ADD COLUMN video_url TEXT");
                }
            }

            // Create tutors table if it does not exist
            $db->exec("
                CREATE TABLE IF NOT EXISTS tutors (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER UNIQUE NOT NULL,
                    full_name TEXT,
                    subjects TEXT,
                    bio TEXT,
                    hourly_rate TEXT,
                    phone TEXT,
                    email TEXT,
                    is_available INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");

            // Add billing_type and company_name columns to tutors if missing
            $tutorCols = $db->query("PRAGMA table_info(tutors)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (is_array($tutorCols)) {
                if (!in_array('billing_type', $tutorCols)) {
                    $db->exec("ALTER TABLE tutors ADD COLUMN billing_type TEXT DEFAULT 'hourly'");
                }
                if (!in_array('company_name', $tutorCols)) {
                    $db->exec("ALTER TABLE tutors ADD COLUMN company_name TEXT");
                }
            }

            // Create my_resources table (personal resources) if it does not exist
            $db->exec("
                CREATE TABLE IF NOT EXISTS my_resources (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    resource_type TEXT DEFAULT 'script',
                    file_name TEXT NOT NULL,
                    file_path TEXT NOT NULL,
                    file_size INTEGER DEFAULT 0,
                    description TEXT,
                    video_url TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_my_resources_user_id ON my_resources(user_id)");
        } catch (Exception $e) {
            // Silently ignore - queries will simply fail later if columns are missing
        }
    }

    /**
     * Display study groups page
     */
    public function index() {
        requireLogin();

        $user = getCurrentUser();

        // Update user's activity
        require_once __DIR__ . '/../models/UserActivity.php';
        $activityModel = new UserActivity();
        $activityModel->updateActivity($user['id']);

        // Check if user is on free plan
        $subscriptionController = new SubscriptionController();
        $userSubscription = $subscriptionController->getUserSubscription($user['id']);
        $currentPlan = $userSubscription['plan'] ?? 'free';
        $isFreeUser = ($currentPlan === 'free');

        // Free users can view the page but with limited functionality

        // My Resources: personal video tutorials + scripts the user stores
        // directly (independent of any study group)
        $myResourceModel = new MyResource();
        $myResources = $myResourceModel->findByUser($user['id']);

        // Available Tutors: tutors students can choose to learn from
        $tutorModel = new Tutor();
        $availableTutors = $tutorModel->getAllAvailable();

        // My Tutors: tutors who have accepted this student's request
        $tutorStudentModel = new TutorStudent();
        $myTutors = $tutorStudentModel->getActiveForStudent($user['id']);

        include __DIR__ . '/../templates/pages/study_group.php';
    }

    /**
     * Create a new study group
     */
    public function create() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /study-group');
            exit;
        }

        $user = getCurrentUser();

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $gradeLevel = trim($_POST['grade_level'] ?? '');
        $schoolName = trim($_POST['school_name'] ?? '');
        $maxMembers = intval($_POST['max_members'] ?? 10);

        // Validation
        if (empty($title)) {
            setFlashMessage('error', 'Study group title is required.');
            header('Location: /study-group');
            exit;
        }

        if (strlen($title) > 100) {
            setFlashMessage('error', 'Title must be less than 100 characters.');
            header('Location: /study-group');
            exit;
        }

        if ($maxMembers < 2 || $maxMembers > 50) {
            $maxMembers = 10;
        }

        // Create the study group
        $groupId = $this->studyGroupModel->create($user['id'], $title, $description, $gradeLevel, $schoolName, $maxMembers);

        if ($groupId) {
            setFlashMessage('success', 'Study group "' . htmlspecialchars($title) . '" created successfully!');
        } else {
            setFlashMessage('error', 'Failed to create study group. Please try again.');
        }

        header('Location: /study-group');
        exit;
    }

    /**
     * Join a study group
     */
    public function join($groupId) {
        requireLogin();

        $user = getCurrentUser();

        $group = $this->studyGroupModel->findById($groupId);

        if (!$group || !$group['is_active']) {
            setFlashMessage('error', 'Study group not found or is no longer active.');
            header('Location: /study-group');
            exit;
        }

        // Check if already a member
        if ($this->studyGroupModel->isMember($groupId, $user['id'])) {
            setFlashMessage('info', 'You are already a member of this study group.');
            header('Location: /study-group');
            exit;
        }

        // Check if group is full
        if ($this->studyGroupModel->isFull($groupId)) {
            setFlashMessage('error', 'This study group is full.');
            header('Location: /study-group');
            exit;
        }

        // Add member
        $result = $this->studyGroupModel->addMember($groupId, $user['id']);

        if ($result) {
            setFlashMessage('success', 'Successfully joined "' . htmlspecialchars($group['title']) . '"!');
        } else {
            setFlashMessage('error', 'Failed to join study group.');
        }

        header('Location: /study-group');
        exit;
    }

    /**
     * Leave a study group
     */
    public function leave($groupId) {
        requireLogin();

        $user = getCurrentUser();
        $group = $this->studyGroupModel->findById($groupId);

        if (!$group) {
            setFlashMessage('error', 'Study group not found.');
            header('Location: /study-group');
            exit;
        }

        // Check if member
        if (!$this->studyGroupModel->isMember($groupId, $user['id'])) {
            setFlashMessage('info', 'You are not a member of this study group.');
            header('Location: /study-group');
            exit;
        }

        // Prevent creator from leaving (they should delete the group instead)
        if ($group['creator_user_id'] == $user['id']) {
            setFlashMessage('error', 'As the creator, you must delete the group instead of leaving.');
            header('Location: /study-group');
            exit;
        }

        $this->studyGroupModel->removeMember($groupId, $user['id']);
        setFlashMessage('success', 'You have left the study group.');
        header('Location: /study-group');
        exit;
    }

    /**
     * Delete a study group
     */
    public function delete($groupId) {
        requireLogin();

        $user = getCurrentUser();
        $group = $this->studyGroupModel->findById($groupId);

        if (!$group) {
            setFlashMessage('error', 'Study group not found.');
            header('Location: /study-group');
            exit;
        }

        // Only creator can delete
        if ($group['creator_user_id'] != $user['id']) {
            setFlashMessage('error', 'Only the creator can delete this study group.');
            header('Location: /study-group');
            exit;
        }

        $this->studyGroupModel->delete($groupId);
        setFlashMessage('success', 'Study group "' . htmlspecialchars($group['title']) . '" has been deleted.');
        header('Location: /study-group');
        exit;
    }

    /**
     * Update a study group
     */
    public function update($groupId) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /study-group');
            exit;
        }

        $user = getCurrentUser();
        $group = $this->studyGroupModel->findById($groupId);

        if (!$group) {
            setFlashMessage('error', 'Study group not found.');
            header('Location: /study-group');
            exit;
        }

        // Only creator can update
        if ($group['creator_user_id'] != $user['id']) {
            setFlashMessage('error', 'Only the creator can update this study group.');
            header('Location: /study-group');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $gradeLevel = trim($_POST['grade_level'] ?? '');
        $maxMembers = intval($_POST['max_members'] ?? 10);

        // Validation
        if (empty($title)) {
            setFlashMessage('error', 'Study group title is required.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        if ($maxMembers < 2 || $maxMembers > 50) {
            $maxMembers = 10;
        }

        // Update the study group
        $result = $this->studyGroupModel->update($groupId, [
            'title' => $title,
            'description' => $description,
            'grade_level' => $gradeLevel,
            'max_members' => $maxMembers
        ]);

        if ($result) {
            setFlashMessage('success', 'Study group updated successfully!');
        } else {
            setFlashMessage('error', 'Failed to update study group.');
        }

        header('Location: /study-group/view/' . $groupId);
        exit;
    }

    /**
     * View study group details
     */
    public function view($groupId) {
        requireLogin();

        $user = getCurrentUser();
        $group = $this->studyGroupModel->findById($groupId);

        if (!$group || !$group['is_active']) {
            setFlashMessage('error', 'Study group not found or is no longer active.');
            header('Location: /study-group');
            exit;
        }

        // NOTE: We no longer mark notifications as viewed here automatically.
        // Notifications are now cleared when the user views specific tabs (chat, scripts)
        // This allows for granular notification tracking across different sections

        $members = $this->studyGroupModel->getMembers($groupId);
        $isMember = $this->studyGroupModel->isMember($groupId, $user['id']);
        $userRole = $isMember ? $this->studyGroupModel->getUserRole($groupId, $user['id']) : null;
        $isCreator = $group['creator_user_id'] == $user['id'];
        $isFull = $this->studyGroupModel->isFull($groupId);

        // Get shared scripts
        $scriptModel = new StudyGroupScript();
        $scripts = $scriptModel->findByGroupId($groupId);

        // Get chat messages
        $messageModel = new StudyGroupMessage();
        $messages = $messageModel->findByGroupId($groupId, 100);

        include __DIR__ . '/../templates/pages/study_group_detail.php';
    }

    /**
     * Upload a resource (script / video file / video link) to a study group
     */
    public function uploadScript($groupId) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /study-group');
            exit;
        }

        $user = getCurrentUser();
        $group = $this->studyGroupModel->findById($groupId);

        if (!$group || !$group['is_active']) {
            setFlashMessage('error', 'Study group not found.');
            header('Location: /study-group');
            exit;
        }

        if (!$this->studyGroupModel->isMember($groupId, $user['id'])) {
            setFlashMessage('error', 'You must be a member to upload resources.');
            header('Location: /study-group');
            exit;
        }

        $description = trim($_POST['description'] ?? '');
        $resourceType = trim($_POST['resource_type'] ?? 'script');
        $scriptModel = new StudyGroupScript();

        // ---- Video link (external URL) ----
        if ($resourceType === 'video_link') {
            $videoUrl = trim($_POST['video_url'] ?? '');
            if (empty($videoUrl) || !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                setFlashMessage('error', 'Please provide a valid video URL.');
                header('Location: /study-group/view/' . $groupId);
                exit;
            }
            $linkName = trim($_POST['video_title'] ?? '');
            if (empty($linkName)) {
                $linkName = 'Video Tutorial';
            }
            $scriptId = $scriptModel->upload($groupId, $user['id'], $linkName, '', 0, $description, 'video_link', $videoUrl);
            if ($scriptId) {
                setFlashMessage('success', 'Video tutorial link added successfully!');
            } else {
                setFlashMessage('error', 'Failed to save video link.');
            }
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        // ---- File based uploads (script or video file) ----
        if (!isset($_FILES['script']) || $_FILES['script']['error'] !== UPLOAD_ERR_OK) {
            setFlashMessage('error', 'File upload failed. Please try again.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        $file = $_FILES['script'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($resourceType === 'video_file') {
            $allowedVideo = ['mp4', 'webm', 'ogg'];
            if (!in_array($ext, $allowedVideo)) {
                setFlashMessage('error', 'Invalid video file type. Allowed: MP4, WEBM, OGG');
                header('Location: /study-group/view/' . $groupId);
                exit;
            }
            if ($file['size'] > 100 * 1024 * 1024) {
                setFlashMessage('error', 'Video file size must be less than 100MB.');
                header('Location: /study-group/view/' . $groupId);
                exit;
            }
            $finalResourceType = 'video_file';
        } else {
            $allowedTypes = ['pdf', 'docx', 'txt', 'doc'];
            if (!in_array($ext, $allowedTypes)) {
                setFlashMessage('error', 'Invalid file type. Allowed: PDF, DOCX, DOC, TXT');
                header('Location: /study-group/view/' . $groupId);
                exit;
            }
            if ($file['size'] > 10 * 1024 * 1024) {
                setFlashMessage('error', 'File size must be less than 10MB.');
                header('Location: /study-group/view/' . $groupId);
                exit;
            }
            $finalResourceType = 'script';
        }

        // Create upload directory if not exists
        $uploadDir = __DIR__ . '/../public/uploads/study_groups/' . $groupId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . uniqid() . '_' . $file['name'];
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            setFlashMessage('error', 'Failed to save file.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        $scriptId = $scriptModel->upload($groupId, $user['id'], $file['name'], $filePath, $file['size'], $description, $finalResourceType, '');

        if ($scriptId) {
            setFlashMessage('success', $finalResourceType === 'video_file' ? 'Video tutorial uploaded successfully!' : 'Script uploaded successfully!');
        } else {
            setFlashMessage('error', 'Failed to save resource to database.');
            unlink($filePath);
        }

        header('Location: /study-group/view/' . $groupId);
        exit;
    }

    /**
     * Stream a video file inline (for the HTML5 <video> player)
     */
    public function viewVideo($groupId, $scriptId) {
        requireLogin();

        $user = getCurrentUser();
        $scriptModel = new StudyGroupScript();
        $script = $scriptModel->findById($scriptId);

        if (!$script || $script['study_group_id'] != $groupId) {
            http_response_code(404);
            echo 'Video not found.';
            return;
        }

        if (!$this->studyGroupModel->isMember($groupId, $user['id'])) {
            http_response_code(403);
            echo 'You must be a member to view this video.';
            return;
        }

        if (!file_exists($script['file_path'])) {
            http_response_code(404);
            echo 'File not found on server.';
            return;
        }

        $ext = strtolower(pathinfo($script['file_name'], PATHINFO_EXTENSION));
        $mimeTypes = [
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
        ];
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($script['file_name']) . '"');
        header('Content-Length: ' . filesize($script['file_path']));
        header('Accept-Ranges: bytes');
        readfile($script['file_path']);
        exit;
    }

    /**
     * Register the current user as an available tutor
     */
    public function becomeTutor() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /study-group');
            exit;
        }

        $user = getCurrentUser();

        $fullName = trim($_POST['full_name'] ?? '');
        $subjects = trim($_POST['subjects'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $hourlyRate = trim($_POST['rate'] ?? '');
        $billingType = trim($_POST['billing_type'] ?? 'hourly');
        $companyName = trim($_POST['company_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($fullName) || empty($subjects)) {
            setFlashMessage('error', 'Please provide at least your name and the subjects you teach.');
            header('Location: /study-group');
            exit;
        }

        $tutorModel = new Tutor();
        // Preserve the newer profile fields (teaching mode, approach,
        // packages, company team) when upserting via this form
        $existingTutor = $tutorModel->findByUserId($user['id']);
        $result = $tutorModel->register(
            $user['id'],
            $fullName,
            $subjects,
            $bio,
            $hourlyRate,
            $phone,
            $email,
            $billingType,
            $companyName,
            1,
            $existingTutor['teaching_mode'] ?? 'contact',
            $existingTutor['teaching_approach'] ?? '',
            $existingTutor['packages'] ?? '',
            $existingTutor['company_team'] ?? ''
        );

        if ($result) {
            // Promote user's DB role to 'tutor' so they get the tutor experience
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE users SET role = 'tutor' WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Update session so the change takes effect immediately
            $user['role'] = 'tutor';
            $_SESSION['user'] = $user;
            $_SESSION['user_role'] = 'tutor';

            setFlashMessage('success', 'You are now listed as an available tutor! Students can find you in the Available Tutors section.');
        } else {
            setFlashMessage('error', 'Failed to register as a tutor. Please try again.');
        }

        header('Location: /study-group');
        exit;
    }
    /**
     * Upload a personal resource (script / video file / video link)
     * directly into "My Resources" (not tied to any study group).
     */
    public function uploadMyResource() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /study-group');
            exit;
        }

        $user = getCurrentUser();
        $resourceType = trim($_POST['resource_type'] ?? 'script');
        $description = trim($_POST['description'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $myResourceModel = new MyResource();

        // ---- Video link (external URL) ----
        if ($resourceType === 'video_link') {
            $videoUrl = trim($_POST['video_url'] ?? '');
            if (empty($videoUrl) || !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                setFlashMessage('error', 'Please provide a valid video URL.');
                header('Location: /study-group');
                exit;
            }
            if (empty($title)) {
                $title = 'Video Tutorial';
            }
            $id = $myResourceModel->add($user['id'], $title, 'video_link', $title, '', 0, $description, $videoUrl);
            if ($id) {
                setFlashMessage('success', 'Video tutorial link saved to My Resources!');
            } else {
                setFlashMessage('error', 'Failed to save video link.');
            }
            header('Location: /study-group');
            exit;
        }

        // ---- File based uploads (script or video file) ----
        if (!isset($_FILES['resource_file']) || $_FILES['resource_file']['error'] !== UPLOAD_ERR_OK) {
            setFlashMessage('error', 'File upload failed. Please try again.');
            header('Location: /study-group');
            exit;
        }

        $file = $_FILES['resource_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($resourceType === 'video_file') {
            $allowedVideo = ['mp4', 'webm', 'ogg'];
            if (!in_array($ext, $allowedVideo)) {
                setFlashMessage('error', 'Invalid video file type. Allowed: MP4, WEBM, OGG');
                header('Location: /study-group');
                exit;
            }
            if ($file['size'] > 100 * 1024 * 1024) {
                setFlashMessage('error', 'Video file size must be less than 100MB.');
                header('Location: /study-group');
                exit;
            }
            $finalType = 'video_file';
        } else {
            $allowedTypes = ['pdf', 'docx', 'txt', 'doc'];
            if (!in_array($ext, $allowedTypes)) {
                setFlashMessage('error', 'Invalid file type. Allowed: PDF, DOCX, DOC, TXT');
                header('Location: /study-group');
                exit;
            }
            if ($file['size'] > 10 * 1024 * 1024) {
                setFlashMessage('error', 'File size must be less than 10MB.');
                header('Location: /study-group');
                exit;
            }
            $finalType = 'script';
        }

        if (empty($title)) {
            $title = $file['name'];
        }

        // Create upload directory if not exists
        $uploadDir = __DIR__ . '/../public/uploads/my_resources/' . $user['id'] . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . uniqid() . '_' . $file['name'];
        $filePath = $uploadDir . $fileName;


        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            setFlashMessage('error', 'Failed to save file.');
            header('Location: /study-group');
            exit;
        }

        $id = $myResourceModel->add($user['id'], $title, $finalType, $file['name'], $filePath, $file['size'], $description, '');

        if ($id) {
            setFlashMessage('success', $finalType === 'video_file' ? 'Video tutorial saved to My Resources!' : 'Script saved to My Resources!');
        } else {
            setFlashMessage('error', 'Failed to save resource.');
            unlink($filePath);
        }

        header('Location: /study-group');
        exit;
    }



    /**
     * Delete a personal resource from My Resources
     */
    public function deleteMyResource($id) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /study-group');
            exit;
        }

        $user = getCurrentUser();
        $myResourceModel = new MyResource();
        $resource = $myResourceModel->findById($id, $user['id']);

        if (!$resource) {
            setFlashMessage('error', 'Resource not found.');
            header('Location: /study-group');
            exit;
        }

        // Delete the physical file (for file-based resources)
        if ($resource['resource_type'] !== 'video_link' && !empty($resource['file_path']) && file_exists($resource['file_path'])) {
            unlink($resource['file_path']);
        }

        $myResourceModel->delete($id, $user['id']);
        setFlashMessage('success', 'Resource deleted from My Resources.');
        header('Location: /study-group');
        exit;
    }

    /**
     * Stream a personal video file inline (HTML5 <video> player)
     */
    public function viewMyVideo($id) {
        requireLogin();

        $user = getCurrentUser();
        $myResourceModel = new MyResource();
        $resource = $myResourceModel->findById($id, $user['id']);

        if (!$resource || $resource['resource_type'] !== 'video_file') {
            http_response_code(404);
            echo 'Video not found.';
            return;
        }

        if (!file_exists($resource['file_path'])) {
            http_response_code(404);
            echo 'File not found on server.';
            return;
        }

        $ext = strtolower(pathinfo($resource['file_name'], PATHINFO_EXTENSION));
        $mimeTypes = [
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
        ];
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($resource['file_name']) . '"');
        header('Content-Length: ' . filesize($resource['file_path']));
        header('Accept-Ranges: bytes');
        readfile($resource['file_path']);
        exit;
    }

    /**
     * Download a personal resource file
     */
    public function downloadMyResource($id) {
        requireLogin();

        $user = getCurrentUser();
        $myResourceModel = new MyResource();
        $resource = $myResourceModel->findById($id, $user['id']);

        if (!$resource) {
            setFlashMessage('error', 'Resource not found.');
            header('Location: /study-group');
            exit;
        }

        if ($resource['resource_type'] === 'video_link') {
            // Links have no file to download - redirect to the URL
            header('Location: ' . $resource['video_url']);
            exit;
        }

        if (!file_exists($resource['file_path'])) {
            setFlashMessage('error', 'File not found on server.');
            header('Location: /study-group');
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($resource['file_name']) . '"');
        header('Content-Length: ' . filesize($resource['file_path']));
        readfile($resource['file_path']);
        exit;
    }


    /**
     * Delete a script from study group
     */
    public function deleteScript($groupId, $scriptId) {
        requireLogin();

        $user = getCurrentUser();
        $scriptModel = new StudyGroupScript();
        $script = $scriptModel->findById($scriptId);

        if (!$script || $script['study_group_id'] != $groupId) {
            setFlashMessage('error', 'Script not found.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        if ($script['user_id'] != $user['id']) {
            setFlashMessage('error', 'You can only delete your own scripts.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        // Delete file
        if (file_exists($script['file_path'])) {
            unlink($script['file_path']);
        }

        $scriptModel->delete($scriptId, $user['id']);
        setFlashMessage('success', 'Script deleted successfully.');
        header('Location: /study-group/view/' . $groupId);
        exit;
    }

    /**
     * Download a script
     */
    public function downloadScript($groupId, $scriptId) {
        requireLogin();

        $user = getCurrentUser();
        $scriptModel = new StudyGroupScript();
        $script = $scriptModel->findById($scriptId);

        if (!$script || $script['study_group_id'] != $groupId) {
            setFlashMessage('error', 'Script not found.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        if (!$this->studyGroupModel->isMember($groupId, $user['id'])) {
            setFlashMessage('error', 'You must be a member to download scripts.');
            header('Location: /study-group');
            exit;
        }

        if (!file_exists($script['file_path'])) {
            setFlashMessage('error', 'File not found on server.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        // Send file for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($script['file_name']) . '"');
        header('Content-Length: ' . filesize($script['file_path']));
        readfile($script['file_path']);
        exit;
    }

    /**
     * Send a chat message
     */
    public function sendMessage($groupId) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /study-group');
            exit;
        }

        $user = getCurrentUser();
        $group = $this->studyGroupModel->findById($groupId);

        if (!$group || !$group['is_active']) {
            setFlashMessage('error', 'Study group not found.');
            header('Location: /study-group');
            exit;
        }

        if (!$this->studyGroupModel->isMember($groupId, $user['id'])) {
            setFlashMessage('error', 'You must be a member to send messages.');
            header('Location: /study-group');
            exit;
        }

        $message = trim($_POST['message'] ?? '');
        $messageType = $_POST['message_type'] ?? 'text';
        $voiceData = null;

        // Handle voice note upload - store in database as BLOB
        if ($messageType === 'voice' && isset($_FILES['voice_file']) && $_FILES['voice_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['voice_file'];
            
            // Read file content into memory
            $voiceData = file_get_contents($file['tmp_name']);
            
            if ($voiceData === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to read voice note']);
                exit;
            }
            
            $message = 'Voice note';
        }

        if (empty($message) && $voiceData === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Message cannot be empty']);
            exit;
        }

        $messageModel = new StudyGroupMessage();
        
        // Use sendVoiceMessage for voice, regular send for text
        if ($messageType === 'voice' && $voiceData !== null) {
            $messageId = $messageModel->sendVoiceMessage($groupId, $user['id'], $voiceData);
        } else {
            $messageId = $messageModel->send($groupId, $user['id'], $message, $messageType);
        }

        if ($messageId) {
            $newMessage = [
                'id' => $messageId,
                'message' => $message,
                'message_type' => $messageType,
                'file_path' => null,  // No longer using file_path for voice
                'sender_name' => $user['username'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            echo json_encode(['success' => true, 'message' => $newMessage]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send message']);
        }
        exit;
    }

    /**
     * Get chat messages (for polling)
     */
    public function getMessages($groupId) {
        requireLogin();

        // Prevent caching
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Type: application/json');

        $user = getCurrentUser();
        $group = $group = $this->studyGroupModel->findById($groupId);

        if (!$group || !$group['is_active']) {
            http_response_code(404);
            echo json_encode(['error' => 'Group not found']);
            exit;
        }

        if (!$this->studyGroupModel->isMember($groupId, $user['id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }

        $afterId = intval($_GET['after_id'] ?? 0);
        $messageModel = new StudyGroupMessage();
        $messages = $messageModel->findRecent($groupId, $afterId);

        echo json_encode(['messages' => $messages]);
        exit;
    }

    /**
     * Delete a chat message
     */
    public function deleteMessage($groupId, $messageId) {
        requireLogin();

        $user = getCurrentUser();
        $group = $this->studyGroupModel->findById($groupId);
        $messageModel = new StudyGroupMessage();
        $message = $messageModel->findById($messageId);

        if (!$message || $message['study_group_id'] != $groupId) {
            setFlashMessage('error', 'Message not found.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        // Check if user is group creator (admin) or message owner
        $isGroupAdmin = $group['creator_user_id'] == $user['id'];
        $isMessageOwner = $message['user_id'] == $user['id'];
        
        if (!$isGroupAdmin && !$isMessageOwner) {
            setFlashMessage('error', 'You can only delete your own messages.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        // Delete voice file if exists
        if ($message['file_path'] && file_exists($message['file_path'])) {
            unlink($message['file_path']);
        }

        $messageModel->delete($messageId, $user['id']);
        setFlashMessage('success', 'Message deleted.');
        header('Location: /study-group/view/' . $groupId);
        exit;
    }

    /**
     * Remove a member from the study group (admin only)
     */
    public function removeMember($groupId, $userId) {
        requireLogin();

        $currentUser = getCurrentUser();
        $group = $this->studyGroupModel->findById($groupId);

        if (!$group) {
            setFlashMessage('error', 'Study group not found.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        // Only group creator can remove members
        if ($group['creator_user_id'] != $currentUser['id']) {
            setFlashMessage('error', 'Only the group creator can remove members.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        // Cannot remove yourself
        if ($userId == $currentUser['id']) {
            setFlashMessage('error', 'You cannot remove yourself from the group.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        $this->studyGroupModel->removeMember($groupId, $userId);
        setFlashMessage('success', 'Member removed from group.');
        header('Location: /study-group/view/' . $groupId);
        exit;
    }

    /**
     * Send invite to friends
     */
    public function sendInvite() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard');
            exit;
        }

        $user = getCurrentUser();
        $inviteModel = new StudyGroupInvite();

        $friendEmails = $_POST['friend_emails'] ?? '';
        $friendName = trim($_POST['friend_name'] ?? '');
        $studyGroupId = $_POST['study_group_id'] ?? null;
        $message = trim($_POST['invite_message'] ?? '');

        // Split emails by comma, newline, or semicolon
        $emails = preg_split('/[,\n;]+/', $friendEmails);
        $emails = array_map('trim', $emails);
        $emails = array_filter($emails); // Remove empty values

        if (empty($emails)) {
            setFlashMessage('error', 'Please enter at least one email address.');
            header('Location: /dashboard');
            exit;
        }

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($emails as $email) {
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Check if already has pending invite
            if ($inviteModel->hasPendingInvite($email)) {
                $skippedCount++;
                continue;
            }

            // Create invite
            $inviteModel->create($user['id'], $email, $friendName, $studyGroupId, $message);
            $sentCount++;
        }

        if ($sentCount > 0) {
            setFlashMessage('success', "Invitation sent to {$sentCount} friend(s)!" . ($skippedCount > 0 ? " ({$skippedCount} already invited)" : ""));
        } else {
            setFlashMessage('error', 'Failed to send invites. All emails may already have pending invitations.');
        }

        $redirectUrl = $studyGroupId ? '/study-group/view/' . $studyGroupId : '/dashboard';
        header('Location: ' . $redirectUrl);
        exit;
    }

    /**
     * Accept invite
     */
    public function acceptInvite($token) {
        requireLogin();

        $user = getCurrentUser();
        $inviteModel = new StudyGroupInvite();

        $invite = $inviteModel->getByToken($token);

        if (!$invite) {
            setFlashMessage('error', 'Invalid invite token.');
            header('Location: /dashboard');
            exit;
        }

        if ($invite['status'] !== 'pending') {
            setFlashMessage('error', 'This invite has already been ' . $invite['status'] . '.');
            header('Location: /dashboard');
            exit;
        }

        if (strtotime($invite['expires_at']) < time()) {
            setFlashMessage('error', 'This invite has expired.');
            header('Location: /dashboard');
            exit;
        }

        // Accept the invite
        $inviteModel->accept($token, $user['id']);

        // If it's a group invite, add user to the group
        if ($invite['study_group_id']) {
            $this->studyGroupModel->addMember($invite['study_group_id'], $user['id'], 'member');
            setFlashMessage('success', "You've successfully joined the study group: " . htmlspecialchars($invite['group_title']));
        } else {
            setFlashMessage('success', "Welcome to StudySmart! Your friend " . htmlspecialchars($invite['sender_name']) . " invited you.");
        }

        header('Location: /dashboard');
        exit;
    }

    /**
     * View sent invites
     */
    public function viewInvites() {
        requireLogin();

        $user = getCurrentUser();
        $inviteModel = new StudyGroupInvite();

        $invites = $inviteModel->getBySender($user['id']);
        $stats = $inviteModel->getStats($user['id']);

        include __DIR__ . '/../templates/pages/invites.php';
    }

    /**
     * Cancel an invite
     */
    public function cancelInvite($inviteId) {
        requireLogin();

        $user = getCurrentUser();
        $inviteModel = new StudyGroupInvite();

        $invite = $inviteModel->getByToken($inviteId); // Using token as ID for simplicity

        if (!$invite || $invite['user_id'] != $user['id']) {
            setFlashMessage('error', 'Invalid invite or not authorized.');
            header('Location: /invites');
            exit;
        }

        $inviteModel->delete($inviteId);
        setFlashMessage('success', 'Invite cancelled.');
        header('Location: /invites');
        exit;
    }

    /**
     * Mark all messages in a study group as viewed
     */
    public function markMessagesAsViewed($groupId) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $user = getCurrentUser();
        $group = $this->studyGroupModel->findById($groupId);

        if (!$group || !$group['is_active']) {
            http_response_code(404);
            echo json_encode(['error' => 'Group not found']);
            return;
        }

        if (!$this->studyGroupModel->isMember($groupId, $user['id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            // Check if is_viewed column exists
            $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN, 1);
            $hasIsViewed = in_array('is_viewed', $columns);

            if ($hasIsViewed) {
                // Mark all messages from other users as viewed
                $stmt = $db->prepare("
                    UPDATE study_group_messages
                    SET is_viewed = 1
                    WHERE study_group_id = ?
                    AND user_id != ?
                    AND is_viewed = 0
                ");
                $stmt->execute([$groupId, $user['id']]);
            }

            // Update last_visited timestamp to clear script notifications
            $stmt = $db->prepare("
                UPDATE study_group_members
                SET last_visited = CURRENT_TIMESTAMP
                WHERE study_group_id = ? AND user_id = ?
            ");
            $stmt->execute([$groupId, $user['id']]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark messages as viewed']);
        }
    }

    /**
     * Mark all study group notifications as viewed (for main study group page) - Server side version
     */
    private function markAllNotificationsAsViewedServerSide() {
        $user = getCurrentUser();

        try {
            $db = Database::getInstance()->getConnection();

            // Get all study groups the user is a member of
            $stmt = $db->prepare("
                SELECT study_group_id
                FROM study_group_members
                WHERE user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $groupIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($groupIds)) {
                // Check if is_viewed column exists
                $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN, 1);
                $hasIsViewed = in_array('is_viewed', $columns);

                if ($hasIsViewed) {
                    // Mark all messages from other users as viewed in all groups
                    $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
                    $stmt = $db->prepare("
                        UPDATE study_group_messages
                        SET is_viewed = 1
                        WHERE study_group_id IN ($placeholders)
                        AND user_id != ?
                        AND is_viewed = 0
                    ");
                    $params = array_merge($groupIds, [$user['id']]);
                    $stmt->execute($params);
                }

                // Update last_visited timestamp for all groups to clear script notifications
                $stmt = $db->prepare("
                    UPDATE study_group_members
                    SET last_visited = CURRENT_TIMESTAMP
                    WHERE user_id = ?
                ");
                $stmt->execute([$user['id']]);
            }
        } catch (Exception $e) {
            // Silently fail - don't break the page if this fails
            error_log('Error marking study group notifications as viewed: ' . $e->getMessage());
        }
    }

    /**
     * Mark notifications as viewed for a specific study group - Server side version
     */
    private function markGroupNotificationsAsViewedServerSide($groupId) {
        $user = getCurrentUser();

        try {
            $db = Database::getInstance()->getConnection();

            // Check if is_viewed column exists
            $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN, 1);
            $hasIsViewed = in_array('is_viewed', $columns);

            if ($hasIsViewed) {
                // Mark all messages from other users as viewed
                $stmt = $db->prepare("
                    UPDATE study_group_messages
                    SET is_viewed = 1
                    WHERE study_group_id = ?
                    AND user_id != ?
                    AND is_viewed = 0
                ");
                $stmt->execute([$groupId, $user['id']]);
            }

            // Update last_visited timestamp for this group to clear script notifications
            $stmt = $db->prepare("
                UPDATE study_group_members
                SET last_visited = CURRENT_TIMESTAMP
                WHERE study_group_id = ? AND user_id = ?
            ");
            $stmt->execute([$groupId, $user['id']]);
        } catch (Exception $e) {
            // Silently fail - don't break the page if this fails
            error_log('Error marking group notifications as viewed: ' . $e->getMessage());
        }
    }

    /**
     * Mark all study group notifications as viewed (for main study group page)
     */
    public function markAllNotificationsAsViewed() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $user = getCurrentUser();

        try {
            $db = Database::getInstance()->getConnection();

            // Get all study groups the user is a member of
            $stmt = $db->prepare("
                SELECT study_group_id
                FROM study_group_members
                WHERE user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $groupIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($groupIds)) {
                // Check if is_viewed column exists
                $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN, 1);
                $hasIsViewed = in_array('is_viewed', $columns);

                if ($hasIsViewed) {
                    // Mark all messages from other users as viewed in all groups
                    $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
                    $stmt = $db->prepare("
                        UPDATE study_group_messages
                        SET is_viewed = 1
                        WHERE study_group_id IN ($placeholders)
                        AND user_id != ?
                        AND is_viewed = 0
                    ");
                    $params = array_merge($groupIds, [$user['id']]);
                    $stmt->execute($params);
                }

                // Update last_visited timestamp for all groups to clear script notifications
                $stmt = $db->prepare("
                    UPDATE study_group_members
                    SET last_visited = CURRENT_TIMESTAMP
                    WHERE user_id = ?
                ");
                $stmt->execute([$user['id']]);
            }

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark notifications as viewed']);
        }
    }

    /**
     * Mark chat messages as viewed for a specific study group
     */
    public function markChatAsViewed($groupId) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $user = getCurrentUser();

        try {
            $db = Database::getInstance()->getConnection();

            // Check if is_viewed column exists
            $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN, 1);
            $hasIsViewed = in_array('is_viewed', $columns);

            if ($hasIsViewed) {
                // Mark all messages from other users as viewed
                $stmt = $db->prepare("
                    UPDATE study_group_messages
                    SET is_viewed = 1
                    WHERE study_group_id = ?
                    AND user_id != ?
                    AND is_viewed = 0
                ");
                $stmt->execute([$groupId, $user['id']]);
            }

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark chat as viewed']);
        }
    }

    /**
     * Mark scripts as viewed for a specific study group
     */
    public function markScriptsAsViewed($groupId) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $user = getCurrentUser();

        try {
            $db = Database::getInstance()->getConnection();

            // Update last_visited timestamp for this group to clear script notifications
            $stmt = $db->prepare("
                UPDATE study_group_members
                SET last_visited = CURRENT_TIMESTAMP
                WHERE study_group_id = ? AND user_id = ?
            ");
            $stmt->execute([$groupId, $user['id']]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark scripts as viewed']);
        }
    }
}
