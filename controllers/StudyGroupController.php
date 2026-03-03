<?php
/**
 * Study Group Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/StudyGroup.php';
require_once __DIR__ . '/../models/StudyGroupScript.php';
require_once __DIR__ . '/../models/StudyGroupMessage.php';

class StudyGroupController {
    private $studyGroupModel;

    public function __construct() {
        $this->studyGroupModel = new StudyGroup();
    }

    /**
     * Display study groups page
     */
    public function index() {
        requireLogin();

        $user = getCurrentUser();
        $allGroups = $this->studyGroupModel->getAllActive();
        $myGroups = $this->studyGroupModel->findByMember($user['id']);

        // Get IDs of groups user has already joined
        $joinedGroupIds = array_column($myGroups, 'id');

        // Filter out groups user has already joined from available groups
        $availableGroups = array_filter($allGroups, function($group) use ($joinedGroupIds, $user) {
            return !in_array($group['id'], $joinedGroupIds) && $group['creator_user_id'] != $user['id'];
        });

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
        $groupId = $this->studyGroupModel->create($user['id'], $title, $description, $gradeLevel, $maxMembers);

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
     * Upload a script to study group
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
            setFlashMessage('error', 'You must be a member to upload scripts.');
            header('Location: /study-group');
            exit;
        }

        // Handle file upload
        if (!isset($_FILES['script']) || $_FILES['script']['error'] !== UPLOAD_ERR_OK) {
            setFlashMessage('error', 'File upload failed. Please try again.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        $file = $_FILES['script'];
        $description = trim($_POST['description'] ?? '');

        // Validate file type
        $allowedTypes = ['pdf', 'docx', 'txt', 'doc'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            setFlashMessage('error', 'Invalid file type. Allowed: PDF, DOCX, DOC, TXT');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        // Validate file size (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            setFlashMessage('error', 'File size must be less than 10MB.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        // Create upload directory if not exists
        $uploadDir = __DIR__ . '/../uploads/study_groups/' . $groupId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $fileName = time() . '_' . uniqid() . '_' . $file['name'];
        $filePath = $uploadDir . $fileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            setFlashMessage('error', 'Failed to save file.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        // Save to database
        $scriptModel = new StudyGroupScript();
        $scriptId = $scriptModel->upload($groupId, $user['id'], $file['name'], $filePath, $file['size'], $description);

        if ($scriptId) {
            setFlashMessage('success', 'Script uploaded successfully!');
        } else {
            setFlashMessage('error', 'Failed to save script to database.');
            unlink($filePath);
        }

        header('Location: /study-group/view/' . $groupId);
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
        $filePath = null;

        // Handle voice note upload
        if ($messageType === 'voice' && isset($_FILES['voice_file']) && $_FILES['voice_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['voice_file'];
            $uploadDir = __DIR__ . '/../uploads/study_groups/' . $groupId . '/voice/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_' . uniqid() . '.webm';
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to save voice note']);
                exit;
            }

            // Store relative path for web access
            $relativePath = 'uploads/study_groups/' . $groupId . '/voice/' . $fileName;
            $message = 'Voice note';
        }

        if (empty($message) && !$filePath) {
            http_response_code(400);
            echo json_encode(['error' => 'Message cannot be empty']);
            exit;
        }

        $messageModel = new StudyGroupMessage();
        $messageId = $messageModel->send($groupId, $user['id'], $message, $messageType, $relativePath ?? null);

        if ($messageId) {
            $newMessage = [
                'id' => $messageId,
                'message' => $message,
                'message_type' => $messageType,
                'file_path' => $relativePath ?? null,
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
        $messageModel = new StudyGroupMessage();
        $message = $messageModel->findById($messageId);

        if (!$message || $message['study_group_id'] != $groupId) {
            setFlashMessage('error', 'Message not found.');
            header('Location: /study-group/view/' . $groupId);
            exit;
        }

        if ($message['user_id'] != $user['id']) {
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
}
