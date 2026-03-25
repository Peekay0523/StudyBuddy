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
require_once __DIR__ . '/../controllers/SubscriptionController.php';

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
        $allGroups = $this->studyGroupModel->getAllActive();
        $myGroups = $this->studyGroupModel->findByMember($user['id']);

        // Get IDs of groups user has already joined
        $joinedGroupIds = array_column($myGroups, 'id');

        // Filter out groups user has already joined from available groups
        $availableGroups = array_filter($allGroups, function($group) use ($joinedGroupIds, $user) {
            return !in_array($group['id'], $joinedGroupIds) && $group['creator_user_id'] != $user['id'];
        });

        // Get potential study buddies
        $studyBuddies = $activityModel->getStudyBuddies($user['id'], 5);

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
            $columns = $db->query("PRAGMA table_info(study_group_messages)")->fetchAll(PDO::FETCH_COLUMN);
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
            echo json_encode(['error' => 'Failed to mark messages as viewed']);
        }
    }
}
