<?php
$pageTitle = htmlspecialchars($group['title']) . ' - StudySmart';
$currentPage = 'study-group';
include __DIR__ . '/../layouts/header.php';
?>

<style>
    .chat-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .chat-messages {
        height: 400px;
        overflow-y: auto;
        padding: 20px;
        background: #f8fafc;
    }
    .chat-message {
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
    }
    .chat-message.own {
        align-items: flex-end;
    }
    .chat-message.other {
        align-items: flex-start;
    }
    .message-bubble {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 12px;
        background: white;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .chat-message.own .message-bubble {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }
    .message-sender {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 500;
    }
    .chat-message.own .message-sender {
        color: rgba(255,255,255,0.8);
    }
    .message-time {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .chat-message.own .message-time {
        color: rgba(255,255,255,0.7);
    }
    .message-delete-btn {
        opacity: 0.6;
        transition: opacity 0.2s;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 4px;
        color: inherit;
    }
    .message-delete-btn:hover {
        opacity: 1;
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
    }
    .chat-message.own .message-delete-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    .chat-input-area {
        padding: 15px;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .chat-input {
        flex: 1;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }
    .chat-input:focus {
        border-color: #667eea;
    }
    .chat-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s;
    }
    .chat-btn:hover {
        transform: scale(1.05);
    }
    .chat-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    .send-btn {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }
    .voice-btn {
        background: #f1f5f9;
        color: #64748b;
    }
    .voice-btn.recording {
        background: #ef4444;
        color: white;
        animation: pulse 1s infinite;
    }
    @keyframes pulse {
        0%, 100% { 
            opacity: 1;
            transform: scale(1);
        }
        50% { 
            opacity: 0.7;
            transform: scale(1.1);
        }
    }
    .voice-note {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 250px;
        max-width: 100%;
        background: rgba(255, 255, 255, 0.15);
        padding: 8px 12px;
        border-radius: 20px;
    }
    .voice-note i {
        font-size: 20px;
        flex-shrink: 0;
        color: inherit;
    }
    .voice-note audio {
        height: 35px;
        max-width: 100%;
        width: 180px;
        flex: 1;
    }
    .chat-message.own .voice-note {
        background: rgba(255, 255, 255, 0.2);
    }
    .chat-message.other .voice-note {
        background: rgba(255, 255, 255, 0.15);
    }
    .scripts-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    .script-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .script-info {
        flex: 1;
    }
    .script-name {
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .script-meta {
        font-size: 12px;
        color: #64748b;
    }
    .script-actions {
        display: flex;
        gap: 8px;
    }
    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-primary-sm {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }
    .btn-danger-sm {
        background: #ef4444;
        color: white;
    }
    .tab-container {
        margin-bottom: 20px;
    }
    .tab-btn {
        padding: 10px 20px;
        border: none;
        background: #f1f5f9;
        color: #64748b;
        cursor: pointer;
        border-radius: 8px 8px 0 0;
        font-weight: 500;
        margin-right: 5px;
    }
    .tab-btn.active {
        background: white;
        color: #667eea;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    .tab-notification-badge {
        background: #ef4444;
        color: white;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 12px;
        margin-left: 6px;
        display: inline-block;
    }
</style>

<style>
    .study-group-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 20px;
    }
    .study-group-title-section {
        flex: 1;
        min-width: 0;
    }
    .study-group-title {
        margin-bottom: 10px;
        font-size: 28px;
        word-wrap: break-word;
    }
    .study-group-meta {
        color: #64748b;
        font-size: 14px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 12px;
        line-height: 1.8;
    }
    .study-group-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }
    .study-group-meta-separator {
        color: #cbd5e1;
        font-weight: 300;
    }
    .study-group-actions {
        display: flex;
        gap: 10px;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .study-group-header {
            flex-direction: column;
            align-items: stretch;
        }
        .study-group-title {
            font-size: 22px;
        }
        .study-group-meta {
            font-size: 13px;
            gap: 6px 10px;
        }
        .study-group-meta-item {
            font-size: 13px;
        }
        .study-group-actions {
            width: 100%;
            justify-content: flex-end;
            margin-top: 15px;
        }
        .study-group-actions .btn-primary {
            padding: 10px 16px;
            font-size: 13px;
        }

        /* Chat mobile styles */
        .chat-container {
            border-radius: 8px;
        }

        .chat-messages {
            height: 350px;
            padding: 15px;
        }

        .chat-message {
            max-width: 85% !important;
        }

        .message-bubble {
            max-width: 95%;
            padding: 10px 12px;
            font-size: 13px;
        }

        .chat-input-area {
            padding: 12px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .chat-input {
            width: calc(100% - 100px);
            order: -1;
            padding: 10px 14px;
            font-size: 13px;
        }

        .chat-btn {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
        }

        .voice-note audio {
            height: 32px;
            width: 100%;
            max-width: 100%;
        }

        /* Voice message bubble adjustments */
        .chat-message.own .voice-note,
        .chat-message.other .voice-note {
            min-width: 150px;
            max-width: 100%;
        }

        /* Tabs mobile */
        .tab-container {
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .tab-btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 13px;
        }

        /* Scripts section mobile */
        .scripts-section {
            padding: 16px;
        }

        .script-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            padding: 12px;
        }

        .script-actions {
            width: 100%;
            justify-content: flex-end;
        }

        .script-name {
            font-size: 14px;
        }

        .script-meta {
            font-size: 11px;
        }

        /* Members grid mobile */
        #members-tab > section > div {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
        }
    }

    @media (max-width: 480px) {
        .study-group-title {
            font-size: 20px;
        }
        .study-group-meta {
            flex-direction: column;
            gap: 4px;
            align-items: flex-start;
        }
        .study-group-meta-item {
            width: 100%;
            padding: 4px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .study-group-meta-item:last-child {
            border-bottom: none;
        }
        .study-group-meta-separator {
            display: none;
        }
        .study-group-actions {
            flex-direction: column;
            gap: 8px;
        }
        .study-group-actions .btn-primary {
            width: 100%;
            text-align: center;
            justify-content: center;
        }

        /* Chat extra small screens */
        .chat-messages {
            height: 300px;
            padding: 10px;
        }

        .chat-message {
            max-width: 90% !important;
        }

        .message-bubble {
            padding: 8px 10px;
            font-size: 12px;
        }

        .message-sender {
            font-size: 11px;
        }

        .message-time {
            font-size: 10px;
        }

        .chat-input-area {
            padding: 10px;
        }

        .chat-input {
            width: 100%;
            font-size: 12px;
            padding: 8px 12px;
        }

        .chat-btn {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
        }

        .voice-note {
            min-width: 220px;
            max-width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 12px;
            border-radius: 20px;
        }

        .voice-note i {
            font-size: 18px;
            flex-shrink: 0;
        }

        .voice-note audio {
            height: 32px;
            width: 160px;
            max-width: 100%;
            flex: 1;
        }

        /* Voice message bubble adjustments for extra small screens */
        .chat-message.own .voice-note,
        .chat-message.other .voice-note {
            min-width: 220px;
            max-width: 100%;
        }

        /* Tabs */
        .tab-btn {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Script cards */
        .script-card {
            padding: 10px;
        }

        .script-name {
            font-size: 13px;
        }

        .script-meta {
            font-size: 10px;
        }

        .btn-sm {
            padding: 4px 10px;
            font-size: 11px;
        }

        /* Members grid */
        #members-tab > section > div {
            grid-template-columns: 1fr !important;
        }

        /* Modals */
        #uploadScriptModal > div,
        #editGroupModal > div,
        #inviteToGroupModal > div {
            width: 95% !important;
            max-width: 95% !important;
            padding: 16px !important;
            max-height: 85vh !important;
            overflow-y: auto !important;
        }

        #uploadScriptModal h2,
        #editGroupModal h2,
        #inviteToGroupModal h2 {
            font-size: 16px !important;
            margin-bottom: 14px !important;
            padding-right: 25px !important;
        }

        #uploadScriptModal input,
        #uploadScriptModal textarea,
        #editGroupModal input,
        #editGroupModal select,
        #editGroupModal textarea,
        #inviteToGroupModal input,
        #inviteToGroupModal textarea {
            font-size: 12px !important;
            padding: 8px !important;
        }

        #uploadScriptModal label,
        #editGroupModal label,
        #inviteToGroupModal label {
            font-size: 12px !important;
        }

        #uploadScriptModal button,
        #editGroupModal button,
        #inviteToGroupModal button {
            padding: 8px 14px !important;
            font-size: 12px !important;
        }

        /* Modal close button */
        #uploadScriptModal > div > button,
        #editGroupModal > div > button,
        #inviteToGroupModal > div > button {
            top: 10px !important;
            right: 10px !important;
            font-size: 20px !important;
        }

        /* Back button on mobile */
        .back-button-container {
            margin-bottom: 15px;
        }

        .back-button-container a {
            font-size: 13px !important;
            padding: 8px 12px !important;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Main container padding */
        .content > div[style*="background: white"] {
            padding: 20px !important;
        }
    }

    @media (max-width: 480px) {
        /* Back button extra small */
        .back-button-container a {
            font-size: 12px !important;
        }

        /* Main container */
        .content > div[style*="background: white"] {
            padding: 16px !important;
        }

        /* Description section */
        .study-group-description {
            font-size: 13px !important;
        }
    }
</style>

<div class="back-button-container" style="margin-bottom: 20px;">
    <a href="/study-group" style="color: #667eea; text-decoration: none; font-size: 14px;">
        <i class="fas fa-arrow-left"></i> Back to Study Groups
    </a>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 30px;">
    <div class="study-group-header">
        <div class="study-group-title-section">
            <h1 class="title study-group-title" style="margin-bottom: 10px; font-size: 28px;">
                <?php echo htmlspecialchars($group['title']); ?>
            </h1>
            <p class="study-group-meta">
                <span class="study-group-meta-item">
                    <i class="fas fa-user"></i>
                    <span>Created by <?php echo htmlspecialchars($group['creator_name']); ?></span>
                </span>
                <span class="study-group-meta-separator">|</span>
                <span class="study-group-meta-item">
                    <i class="fas fa-users"></i>
                    <span><?php echo $group['member_count']; ?>/<?php echo $group['max_members']; ?> members</span>
                </span>
                <?php if (!empty($group['grade_level'])): ?>
                    <span class="study-group-meta-separator">|</span>
                    <span class="study-group-meta-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Grade <?php echo htmlspecialchars($group['grade_level']); ?></span>
                    </span>
                <?php endif; ?>
                <span class="study-group-meta-separator">|</span>
                <span class="study-group-meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo date('M d, Y', strtotime($group['created_at'])); ?></span>
                </span>
            </p>
        </div>

        <div class="study-group-actions">
            <?php if ($isCreator): ?>
                <button onclick="document.getElementById('editGroupModal').style.display='block'"
                        class="btn-primary" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <form method="POST" action="/study-group/delete/<?php echo $group['id']; ?>"
                      onsubmit="return confirm('Are you sure you want to delete this study group? This action cannot be undone.');">
                    <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            <?php elseif ($isMember): ?>
                <form method="POST" action="/study-group/leave/<?php echo $group['id']; ?>"
                      onsubmit="return confirm('Are you sure you want to leave this study group?');">
                    <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-sign-out-alt"></i> Leave Group
                    </button>
                </form>
            <?php elseif (!$isFull): ?>
                <form method="POST" action="/study-group/join/<?php echo $group['id']; ?>">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Join Group
                    </button>
                </form>
            <?php else: ?>
                <button disabled class="btn-primary" style="background: #cbd5e1; cursor: not-allowed;">
                    <i class="fas fa-lock"></i> Group Full
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="border-top: 1px solid #e2e8f0; padding-top: 20px;">
        <h3 style="font-size: 18px; margin-bottom: 10px; color: #1e293b;">Description</h3>
        <p style="color: #64748b; line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($group['description'] ?? 'No description provided.')); ?>
        </p>
    </div>
</div>

<?php if ($isMember): ?>
<!-- Tabs -->
<div class="tab-container">
    <button class="tab-btn active" onclick="switchTab('chat')">
        <i class="fas fa-comments"></i> Chat
        <?php
        $chatNotificationCount = getStudyGroupChatNotificationCount($group['id']);
        if ($chatNotificationCount > 0):
        ?>
            <span class="tab-notification-badge"><?php echo $chatNotificationCount > 99 ? '99+' : $chatNotificationCount; ?></span>
        <?php endif; ?>
    </button>
    <button class="tab-btn" onclick="switchTab('scripts')">
        <i class="fas fa-file-alt"></i> Shared Scripts
        <?php
        $scriptsNotificationCount = getStudyGroupScriptsNotificationCount($group['id']);
        if ($scriptsNotificationCount > 0):
        ?>
            <span class="tab-notification-badge"><?php echo $scriptsNotificationCount > 99 ? '99+' : $scriptsNotificationCount; ?></span>
        <?php endif; ?>
    </button>
    <button class="tab-btn" onclick="switchTab('members')">
        <i class="fas fa-users"></i> Members
    </button>
</div>

<!-- Chat Tab -->
<div id="chat-tab" class="tab-content active">
    <div class="chat-container">
        <div class="chat-messages" id="chatMessages">
            <?php foreach ($messages as $msg): ?>
                <?php 
                    $isOwn = $msg['user_id'] == $user['id'];
                    $isAdmin = $group['creator_user_id'] == $user['id'];
                    $canDelete = $isOwn || $isAdmin;
                ?>
                <div class="chat-message <?php echo $isOwn ? 'own' : 'other'; ?>" data-message-id="<?php echo $msg['id']; ?>">
                    <div class="message-sender"><?php echo htmlspecialchars($msg['sender_name']); ?></div>
                    <div class="message-bubble">
                        <?php if ($msg['message_type'] === 'voice'): ?>
                            <div class="voice-note">
                                <i class="fas fa-microphone"></i>
                                <audio controls src="/study-group/<?php echo $group['id']; ?>/voice/<?php echo $msg['id']; ?>"></audio>
                            </div>
                        <?php else: ?>
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                        <?php endif; ?>
                    </div>
                    <div class="message-time">
                        <?php echo date('M d, H:i', strtotime($msg['created_at'])); ?>
                        <?php if ($canDelete): ?>
                            <button type="button" class="message-delete-btn" onclick="if(confirm('Delete this message?')) document.getElementById('delete-form-<?php echo $msg['id']; ?>').submit();" title="Delete message">
                                <i class="fas fa-trash"></i>
                            </button>
                            <form id="delete-form-<?php echo $msg['id']; ?>" method="POST" action="/study-group/<?php echo $group['id']; ?>/delete-message/<?php echo $msg['id']; ?>" style="display: none;"></form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($messages)): ?>
                <div style="text-align: center; color: #94a3b8; padding: 40px;">
                    <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>No messages yet. Start the conversation!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="chat-input-area">
            <form id="chatForm" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center; flex: 1;">
                <input type="text" id="messageInput" name="message" class="chat-input" placeholder="Type a message..." autocomplete="off">
                <input type="hidden" name="message_type" id="messageType" value="text">
                <input type="file" id="voiceFile" name="voice_file" accept="audio/*" style="display: none;">
                
                <button type="button" id="voiceBtn" class="chat-btn voice-btn" title="Record voice note">
                    <i class="fas fa-microphone"></i>
                </button>
                <button type="submit" class="chat-btn send-btn" title="Send message">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Scripts Tab -->
<div id="scripts-tab" class="tab-content">
    <div class="scripts-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 18px; color: #1e293b; margin: 0;">
                <i class="fas fa-file-alt" style="color: #667eea;"></i> Shared Resources
            </h2>
            <button onclick="document.getElementById('uploadScriptModal').style.display='block'" class="btn-primary">
                <i class="fas fa-upload"></i> Upload Script
            </button>
        </div>
        
        <?php if (empty($scripts)): ?>
            <div style="text-align: center; color: #94a3b8; padding: 40px;">
                <i class="fas fa-file-upload" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No scripts shared yet. Be the first to share!</p>
            </div>
        <?php else: ?>
            <?php foreach ($scripts as $script): ?>
                <div class="script-card">
                    <div class="script-info">
                        <div class="script-name">
                            <i class="fas fa-file-pdf" style="color: #ef4444;"></i>
                            <?php echo htmlspecialchars($script['file_name']); ?>
                        </div>
                        <div class="script-meta">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($script['uploader_name']); ?>
                            &nbsp;&nbsp;|&nbsp;&nbsp;
                            <i class="fas fa-hdd"></i> <?php echo round($script['file_size'] / 1024, 1); ?> KB
                            &nbsp;&nbsp;|&nbsp;&nbsp;
                            <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($script['uploaded_at'])); ?>
                        </div>
                        <?php if (!empty($script['description'])): ?>
                            <div style="margin-top: 8px; font-size: 13px; color: #64748b;">
                                <?php echo htmlspecialchars($script['description']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="script-actions">
                        <a href="/study-group/<?php echo $group['id']; ?>/download-script/<?php echo $script['id']; ?>" class="btn-sm btn-primary-sm">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <?php if ($script['user_id'] == $user['id']): ?>
                            <form method="POST" action="/study-group/<?php echo $group['id']; ?>/delete-script/<?php echo $script['id']; ?>" style="display: inline;">
                                <button type="submit" class="btn-sm btn-danger-sm" onclick="return confirm('Delete this script?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Members Tab -->
<div id="members-tab" class="tab-content">
    <section style="margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 20px; margin: 0; color: #1e293b;">
                <i class="fas fa-users" style="color: #667eea;"></i> Members (<?php echo $group['member_count']; ?>)
            </h2>
            <button onclick="document.getElementById('inviteToGroupModal').style.display='flex'" class="btn-primary" style="padding: 10px 20px; font-size: 14px;">
                <i class="fas fa-user-plus"></i> Invite Friends
            </button>
        </div>
        
        <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                <?php foreach ($members as $member): ?>
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px; position: relative;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                            <?php echo strtoupper(substr($member['username'], 0, 1)); ?>
                        </div>
                        <div style="flex: 1; overflow: hidden;">
                            <div style="font-weight: 500; color: #1e293b; font-size: 14px;">
                                <?php echo htmlspecialchars($member['username']); ?>
                                <?php if ($member['role'] === 'admin'): ?>
                                    <span style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin-left: 6px; font-weight: 600;">ADMIN</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 11px; color: #94a3b8;">
                                <?php echo strtoupper($member['role']); ?>
                            </div>
                        </div>
                        <?php if ($isCreator && $member['user_id'] != $user['id']): ?>
                            <form method="POST" action="/study-group/<?php echo $group['id']; ?>/remove-member/<?php echo $member['user_id']; ?>" style="position: absolute; top: 10px; right: 10px;">
                                <button type="submit" class="btn-sm btn-danger-sm" onclick="return confirm('Remove <?php echo htmlspecialchars($member['username']); ?> from the group?')" title="Remove member" style="padding: 4px 8px; font-size: 11px;">
                                    <i class="fas fa-user-minus"></i> Remove
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<!-- Upload Script Modal -->
<div id="uploadScriptModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; position: relative;">
        <button onclick="document.getElementById('uploadScriptModal').style.display='none'" 
                style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">
            &times;
        </button>
        
        <h2 style="margin-bottom: 20px; color: #1e293b;">
            <i class="fas fa-upload" style="color: #667eea;"></i> Upload Script
        </h2>
        
        <form method="POST" action="/study-group/<?php echo $group['id']; ?>/upload-script" enctype="multipart/form-data">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Select File <span style="color: #ef4444;">*</span>
                </label>
                <input type="file" name="script" required accept=".pdf,.docx,.doc,.txt"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
                <small style="color: #64748b; display: block; margin-top: 5px;">Allowed: PDF, DOCX, DOC, TXT (Max 10MB)</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Description (Optional)
                </label>
                <textarea name="description" rows="3" 
                          placeholder="Describe what this script is about..."
                          style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('uploadScriptModal').style.display='none'" 
                        style="padding: 10px 20px; background: #f1f5f9; color: #64748b; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-upload"></i> Upload
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let lastMessageId = <?php echo !empty($messages) ? end($messages)['id'] : 0; ?>;
    let isRecording = false;
    let mediaRecorder;
    let audioChunks = [];

    // Track which tabs have been viewed
    let chatViewed = false;
    let scriptsViewed = false;

    // Initialize: Mark chat as viewed on page load since Chat tab is active by default
    // This ensures chat notifications are cleared when user enters the study group page
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded - checking active tab');
        
        // Check initial badge counts for debugging
        const chatBadge = document.querySelector('.tab-btn[onclick*="chat"] .tab-notification-badge');
        const scriptsBadge = document.querySelector('.tab-btn[onclick*="scripts"] .tab-notification-badge');
        console.log('Initial chat badge:', chatBadge ? chatBadge.textContent : 'none');
        console.log('Initial scripts badge:', scriptsBadge ? scriptsBadge.textContent : 'none');
        
        // Chat tab is active by default, so mark chat notifications as viewed
        const chatTab = document.querySelector('.tab-btn[onclick*="chat"]');
        if (chatTab && chatTab.classList.contains('active')) {
            chatViewed = true;
            console.log('Chat tab is active on load, marking chat as viewed');
            markChatAsViewed();
        }
    });

    // Tab switching
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        event.target.closest('.tab-btn').classList.add('active');
        document.getElementById(tabName + '-tab').classList.add('active');

        console.log('Switched to tab:', tabName, 'chatViewed:', chatViewed, 'scriptsViewed:', scriptsViewed);

        // Mark notifications as read when viewing tab (only on click, not on page load)
        if (tabName === 'chat' && !chatViewed) {
            chatViewed = true;
            console.log('Marking chat as viewed');
            markChatAsViewed();
        } else if (tabName === 'scripts' && !scriptsViewed) {
            scriptsViewed = true;
            console.log('Marking scripts as viewed');
            markScriptsAsViewed();
        }
    }

    // Mark chat messages as viewed
    async function markChatAsViewed() {
        console.log('markChatAsViewed called for group <?php echo $group['id']; ?>');
        try {
            await fetch('/study-group/<?php echo $group['id']; ?>/mark-chat-viewed', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            console.log('Chat marked as viewed, removing badge');
            // Remove chat notification badge
            const chatTab = document.querySelector('.tab-btn[onclick*="chat"]');
            if (chatTab) {
                const badge = chatTab.querySelector('.tab-notification-badge');
                if (badge) {
                    badge.remove();
                    console.log('Chat badge removed from DOM');
                }
            }
            // Update sidebar notification count
            updateSidebarNotificationCount();
        } catch (error) {
            console.error('Error marking chat as viewed:', error);
        }
    }

    // Mark scripts as viewed
    async function markScriptsAsViewed() {
        console.log('markScriptsAsViewed called for group <?php echo $group['id']; ?>');
        try {
            await fetch('/study-group/<?php echo $group['id']; ?>/mark-scripts-viewed', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            console.log('Scripts marked as viewed, removing badge');
            // Remove scripts notification badge
            const scriptsTab = document.querySelector('.tab-btn[onclick*="scripts"]');
            if (scriptsTab) {
                const badge = scriptsTab.querySelector('.tab-notification-badge');
                if (badge) {
                    badge.remove();
                    console.log('Scripts badge removed from DOM');
                }
            }
            // Update sidebar notification count
            updateSidebarNotificationCount();
        } catch (error) {
            console.error('Error marking scripts as viewed:', error);
        }
    }

    // Update sidebar notification count
    async function updateSidebarNotificationCount() {
        try {
            const response = await fetch('/api/study-group-notification-count');
            const data = await response.json();
            console.log('Sidebar notification count updated:', data.count);
            
            const sidebarBadge = document.querySelector('.sidebar a[href="/study-group"] .notification-badge');

            if (data.count <= 0) {
                // Remove badge if count is 0
                if (sidebarBadge) sidebarBadge.remove();
                console.log('Removed sidebar badge (count is 0)');
            } else {
                // Update badge count
                if (sidebarBadge) {
                    sidebarBadge.textContent = data.count > 99 ? '99+' : data.count;
                    console.log('Updated sidebar badge to:', data.count);
                } else if (data.count > 0) {
                    // Create badge if it doesn't exist
                    const studyGroupLink = document.querySelector('.sidebar a[href="/study-group"]');
                    if (studyGroupLink) {
                        const badge = document.createElement('span');
                        badge.className = 'notification-badge';
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        studyGroupLink.appendChild(badge);
                        console.log('Created sidebar badge with count:', data.count);
                    }
                }
            }
        } catch (error) {
            console.error('Error updating sidebar notification count:', error);
        }
    }

    // Chat form submission
    document.getElementById('chatForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const messageInput = document.getElementById('messageInput');
        const messageType = document.getElementById('messageType');
        const voiceFile = document.getElementById('voiceFile');
        const message = messageInput.value.trim();
        const sendBtn = document.querySelector('.send-btn');

        if (!message && messageType.value !== 'voice') return;

        // Disable send button and show loading state
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const formData = new FormData();
        formData.append('message', message);
        formData.append('message_type', messageType.value);

        if (voiceFile.files[0]) {
            formData.append('voice_file', voiceFile.files[0]);
        }

        try {
            const response = await fetch('/study-group/<?php echo $group['id']; ?>/send-message', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                addMessage(result.message);
                messageInput.value = '';
                messageType.value = 'text';
                voiceFile.value = '';
                scrollToBottom();
            } else {
                alert('Failed to send message: ' + result.error);
            }
        } catch (error) {
            alert('Failed to send message');
            console.error(error);
        } finally {
            // Re-enable send button
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        }
    });

    // Voice recording
    document.getElementById('voiceBtn').addEventListener('click', async function() {
        const voiceBtn = this;
        const sendBtn = document.querySelector('.send-btn');

        if (!isRecording) {
            // Start recording
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                audioChunks = [];

                mediaRecorder.ondataavailable = event => {
                    if (event.data.size > 0) {
                        audioChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = async () => {
                    if (audioChunks.length === 0) {
                        alert('No audio recorded. Please try again.');
                        voiceBtn.classList.remove('recording');
                        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
                        isRecording = false;
                        return;
                    }

                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const voiceFile = document.getElementById('voiceFile');

                    // Create a file from the blob
                    const file = new File([audioBlob], 'voice-note.webm', { type: 'audio/webm' });

                    // Set the file to the input
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    voiceFile.files = dataTransfer.files;

                    console.log('Voice file created:', file.name, file.size, 'bytes');

                    // Set message type to voice and submit
                    document.getElementById('messageType').value = 'voice';
                    
                    // Show sending state
                    voiceBtn.classList.remove('recording');
                    voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
                    isRecording = false;
                    
                    // Disable send button during upload
                    sendBtn.disabled = true;
                    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    document.getElementById('chatForm').dispatchEvent(new Event('submit'));

                    // Stop all tracks
                    stream.getTracks().forEach(track => track.stop());
                };

                mediaRecorder.onerror = (event) => {
                    console.error('MediaRecorder error:', event.error);
                    alert('Recording error: ' + event.error);
                    voiceBtn.classList.remove('recording');
                    voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
                    isRecording = false;
                };

                mediaRecorder.start();
                isRecording = true;
                voiceBtn.classList.add('recording');
                voiceBtn.innerHTML = '<i class="fas fa-stop"></i>';
                console.log('Recording started...');
            } catch (error) {
                console.error('Microphone access error:', error);
                alert('Microphone access denied or not available. Please allow microphone access and try again.');
            }
        } else {
            // Stop recording
            console.log('Stopping recording...');
            mediaRecorder.stop();
            isRecording = false;
            this.classList.remove('recording');
            this.innerHTML = '<i class="fas fa-microphone"></i>';
        }
    });

    // Add message to chat
    function addMessage(msg) {
        const chatMessages = document.getElementById('chatMessages');
        const isOwn = true;
        const isAdmin = <?php echo $isCreator ? 'true' : 'false'; ?>;
        const canDelete = isOwn || isAdmin;

        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message own';
        messageDiv.dataset.messageId = msg.id;

        let messageContent = msg.message;
        if (msg.message_type === 'voice') {
            // Voice recordings are now stored in database - use message ID as URL
            const voiceSrc = '/study-group/<?php echo $group['id']; ?>/voice/' + msg.id;

            messageContent = `
                <div class="voice-note">
                    <i class="fas fa-microphone"></i>
                    <audio controls src="${voiceSrc}"></audio>
                </div>
            `;
        } else {
            messageContent = msg.message.replace(/\n/g, '<br>');
        }

        const time = new Date().toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

        const deleteButton = canDelete ? `
            <button type="button" class="message-delete-btn" onclick="if(confirm('Delete this message?')) document.getElementById('delete-form-${msg.id}').submit();" title="Delete message">
                <i class="fas fa-trash"></i>
            </button>
            <form id="delete-form-${msg.id}" method="POST" action="/study-group/<?php echo $group['id']; ?>/delete-message/${msg.id}" style="display: none;"></form>
        ` : '';

        messageDiv.innerHTML = `
            <div class="message-sender">${msg.sender_name}</div>
            <div class="message-bubble">${messageContent}</div>
            <div class="message-time">
                ${time}
                ${deleteButton}
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        scrollToBottom();
        lastMessageId = msg.id;
    }

    // Scroll to bottom of chat
    function scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Poll for new messages
    async function pollMessages() {
        try {
            const response = await fetch('/study-group/<?php echo $group['id']; ?>/get-messages?after_id=' + lastMessageId);
            const result = await response.json();

            if (result.messages && result.messages.length > 0) {
                result.messages.forEach(msg => {
                    if (msg.id > lastMessageId) {
                        addMessageFromOther(msg);
                        lastMessageId = msg.id;
                    }
                });
                // Update notification badge if chat tab is not active
                const chatTab = document.querySelector('.tab-btn[onclick*="chat"]');
                const chatTabActive = chatTab && chatTab.classList.contains('active');
                if (!chatTabActive) {
                    // Add or update chat notification badge
                    let badge = chatTab.querySelector('.tab-notification-badge');
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'tab-notification-badge';
                        chatTab.appendChild(badge);
                    }
                    let count = parseInt(badge.textContent) || 0;
                    count += result.messages.length;
                    badge.textContent = count > 99 ? '99+' : count;
                }
            }
        } catch (error) {
            console.error('Error polling messages:', error);
        }
    }

    function addMessageFromOther(msg) {
        const chatMessages = document.getElementById('chatMessages');
        const currentUsername = '<?php echo addslashes($user['username']); ?>';
        const isAdmin = <?php echo $isCreator ? 'true' : 'false'; ?>;
        const isOwn = msg.sender_name === currentUsername;
        const canDelete = isOwn || isAdmin;

        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message ' + (isOwn ? 'own' : 'other');
        messageDiv.dataset.messageId = msg.id;

        let messageContent = msg.message;
        if (msg.message_type === 'voice') {
            // Voice recordings are now stored in database - use message ID as URL
            const voiceSrc = '/study-group/<?php echo $group['id']; ?>/voice/' + msg.id;

            messageContent = `
                <div class="voice-note">
                    <i class="fas fa-microphone"></i>
                    <audio controls src="${voiceSrc}"></audio>
                </div>
            `;
        } else {
            messageContent = msg.message.replace(/\n/g, '<br>');
        }
        
        const time = new Date(msg.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

        const deleteButton = isOwn ? `
            <button type="button" class="message-delete-btn" onclick="if(confirm('Delete this message?')) document.getElementById('delete-form-${msg.id}').submit();" title="Delete message">
                <i class="fas fa-trash"></i>
            </button>
            <form id="delete-form-${msg.id}" method="POST" action="/study-group/<?php echo $group['id']; ?>/delete-message/${msg.id}" style="display: none;"></form>
        ` : '';

        messageDiv.innerHTML = `
            <div class="message-sender">${msg.sender_name}</div>
            <div class="message-bubble">${messageContent}</div>
            <div class="message-time">
                ${time}
                ${deleteButton}
            </div>
        `;

        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }

    // Initial scroll to bottom
    scrollToBottom();

    // Poll for new messages every 3 seconds
    setInterval(pollMessages, 3000);
</script>
<?php endif; ?>

<!-- Members Section (for non-members) -->
<?php if (!$isMember): ?>
<section style="margin-bottom: 30px;">
    <h2 style="font-size: 20px; margin-bottom: 20px; color: #1e293b;">
        <i class="fas fa-users" style="color: #667eea;"></i> Members (<?php echo $group['member_count']; ?>)
    </h2>
    
    <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
            <?php foreach ($members as $member): ?>
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px;">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                        <?php echo strtoupper(substr($member['username'], 0, 1)); ?>
                    </div>
                    <div style="flex: 1; overflow: hidden;">
                        <div style="font-weight: 500; color: #1e293b; font-size: 14px;">
                            <?php echo htmlspecialchars($member['username']); ?>
                        </div>
                        <div style="font-size: 11px; color: #94a3b8;">
                            <?php echo strtoupper($member['role']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Edit Group Modal -->
<div id="editGroupModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; position: relative;">
        <button onclick="document.getElementById('editGroupModal').style.display='none'" 
                style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">
            &times;
        </button>
        
        <h2 style="margin-bottom: 20px; color: #1e293b;">
            <i class="fas fa-edit" style="color: #667eea;"></i> Edit Study Group
        </h2>
        
        <form method="POST" action="/study-group/update/<?php echo $group['id']; ?>">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Group Title
                </label>
                <input type="text" name="title" required maxlength="100" 
                       value="<?php echo htmlspecialchars($group['title']); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Grade Level
                </label>
                <select name="grade_level" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
                    <option value="">Select Grade (Optional)</option>
                    <option value="8" <?php echo $group['grade_level'] == '8' ? 'selected' : ''; ?>>Grade 8</option>
                    <option value="9" <?php echo $group['grade_level'] == '9' ? 'selected' : ''; ?>>Grade 9</option>
                    <option value="10" <?php echo $group['grade_level'] == '10' ? 'selected' : ''; ?>>Grade 10</option>
                    <option value="11" <?php echo $group['grade_level'] == '11' ? 'selected' : ''; ?>>Grade 11</option>
                    <option value="12" <?php echo $group['grade_level'] == '12' ? 'selected' : ''; ?>>Grade 12</option>
                    <option value="College" <?php echo $group['grade_level'] == 'College' ? 'selected' : ''; ?>>College/University</option>
                    <option value="Other" <?php echo $group['grade_level'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Description
                </label>
                <textarea name="description" rows="4" 
                          style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; resize: vertical;"><?php echo htmlspecialchars($group['description']); ?></textarea>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Maximum Members
                </label>
                <select name="max_members" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
                    <option value="5" <?php echo $group['max_members'] == 5 ? 'selected' : ''; ?>>5 members</option>
                    <option value="10" <?php echo $group['max_members'] == 10 ? 'selected' : ''; ?>>10 members</option>
                    <option value="15" <?php echo $group['max_members'] == 15 ? 'selected' : ''; ?>>15 members</option>
                    <option value="20" <?php echo $group['max_members'] == 20 ? 'selected' : ''; ?>>20 members</option>
                    <option value="30" <?php echo $group['max_members'] == 30 ? 'selected' : ''; ?>>30 members</option>
                    <option value="50" <?php echo $group['max_members'] == 50 ? 'selected' : ''; ?>>50 members</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('editGroupModal').style.display='none'" 
                        style="padding: 10px 20px; background: #f1f5f9; color: #64748b; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Invite to Group Modal -->
<div id="inviteToGroupModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; position: relative; max-height: 90vh; overflow-y: auto;">
        <button onclick="document.getElementById('inviteToGroupModal').style.display='none'"
                style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">
            &times;
        </button>

        <h2 style="margin-bottom: 20px; color: #1e293b;">
            <i class="fas fa-user-plus" style="color: #667eea;"></i> Invite Friends to <?php echo htmlspecialchars($group['title']); ?>
        </h2>

        <form method="POST" action="/study-group/send-invite">
            <input type="hidden" name="study_group_id" value="<?php echo $group['id']; ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Friend's Email <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="friend_emails" required rows="3"
                          placeholder="Enter email addresses (separate multiple emails with commas)"
                          style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
                <small style="color: #64748b; display: block; margin-top: 5px;">Example: friend@example.com, buddy@example.com</small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Friend's Name (Optional)
                </label>
                <input type="text" name="friend_name" maxlength="100"
                       placeholder="e.g., John"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Personal Message (Optional)
                </label>
                <textarea name="invite_message" rows="3"
                          placeholder="e.g., Join our study group for Mathematics!"
                          style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #667eea;">
                <p style="margin: 0; color: #0369a1; font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Your friends will receive an email invitation to join this study group. Invites expire after 7 days.
                </p>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('inviteToGroupModal').style.display='none'"
                        style="padding: 10px 20px; background: #f1f5f9; color: #64748b; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #667eea, #764ba2); border: none;">
                    <i class="fas fa-paper-plane"></i> Send Invitation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('editGroupModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
        var modal2 = document.getElementById('uploadScriptModal');
        if (event.target == modal2) {
            modal2.style.display = 'none';
        }
        var modal3 = document.getElementById('inviteToGroupModal');
        if (event.target == modal3) {
            modal3.style.display = 'none';
        }
    }

    // NOTE: We no longer mark messages as viewed on page load.
    // Messages are only marked as viewed when the user clicks on the Chat tab.
    // This ensures notifications persist until the user actually views the chat.
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
