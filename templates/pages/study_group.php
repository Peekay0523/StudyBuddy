<?php
$pageTitle = 'Study Groups - StudySmart';
$currentPage = 'study-group';
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Study Groups</h1>
<p class="subtitle">Connect with other students and learn together!</p>

<!-- Create Study Group Button -->
<div style="margin-bottom: 30px;">
    <button onclick="document.getElementById('createGroupModal').style.display='block'" class="btn-primary">
        <i class="fas fa-plus"></i> Create Study Group
    </button>
</div>

<style>
.study-buddies-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.buddy-item {
    transition: all 0.2s ease;
}

.buddy-item:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
}

/* Group Cards Responsive Styles */
.features-section {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.feature-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .features-section {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .feature-card {
        padding: 16px;
    }

    .feature-card h3 {
        font-size: 16px !important;
    }

    .feature-card p {
        font-size: 13px !important;
    }

    .buddy-item {
        padding: 12px 15px !important;
        gap: 15px !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        margin-bottom: 21px !important;
    }

    /* Name and status on mobile */
    .buddy-item > div:first-child {
        min-width: auto !important;
        flex: 0 0 auto !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 12px !important;
    }

    .buddy-item > div:first-child > div:first-child {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        min-height: 40px !important;
        font-size: 16px !important;
        flex-shrink: 0 !important;
    }

    .buddy-item > div:first-child > div:last-child {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 4px !important;
        min-width: 80px !important;
    }

    .buddy-item > div:first-child > div:last-child h3 {
        font-size: 14px !important;
        margin: 0 !important;
        line-height: 1.3 !important;
    }

    .buddy-item > div:first-child > div:last-child p {
        font-size: 11px !important;
        margin: 0 !important;
        line-height: 1.3 !important;
    }

    /* Stats on mobile */
    .buddy-item > div:nth-child(2) {
        width: auto !important;
        order: 3 !important;
        justify-content: flex-start !important;
        gap: 18px !important;
        margin-top: 0 !important;
        display: flex !important;
        flex-direction: row !important;
        margin-left: 14px !important;
    }

    .buddy-item > div:nth-child(2) > div {
        text-align: left !important;
        display: inline-block !important;
    }

    .buddy-item > div:nth-child(2) > div > div:first-child {
        font-size: 15px !important;
        font-weight: 600 !important;
    }

    .buddy-item > div:nth-child(2) > div > div:last-child {
        font-size: 10px !important;
        text-transform: lowercase !important;
    }

    /* Keep actions horizontal on mobile */
    .buddy-item > div:last-child {
        flex-shrink: 0 !important;
        display: flex !important;
        flex-direction: row !important;
        gap: 15px !important;
        margin-left: auto !important;
    }

    /* Icon buttons on mobile */
    .buddy-item > div:last-child a,
    .buddy-item > div:last-child button {
        padding: 0 !important;
        font-size: 18px !important;
        min-width: auto !important;
        background: none !important;
        border: none !important;
        box-shadow: none !important;
    }

    /* Page title and subtitle */
    .title {
        font-size: 24px !important;
    }

    .subtitle {
        font-size: 14px !important;
    }

    /* Buttons */
    .btn-primary {
        padding: 10px 16px !important;
        font-size: 13px !important;
    }

    /* Alert boxes */
    .alert {
        padding: 16px !important;
    }

    .alert h3 {
        font-size: 16px !important;
    }

    .alert p {
        font-size: 13px !important;
    }
}

@media (max-width: 480px) {
    .buddy-item {
        padding: 10px 12px !important;
        gap: 12px !important;
        flex-wrap: nowrap !important;
        margin-bottom: 21px !important;
    }

    /* Avatar and name row layout */
    .buddy-item > div:first-child {
        min-width: auto !important;
        flex-direction: row !important;
        gap: 10px !important;
        align-items: center !important;
    }

    .buddy-item > div:first-child > div:first-child {
        width: 35px !important;
        height: 35px !important;
        min-width: 35px !important;
        min-height: 35px !important;
        font-size: 14px !important;
        flex-shrink: 0 !important;
    }

    .buddy-item > div:first-child > div:last-child {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 3px !important;
        min-width: 70px !important;
    }

    .buddy-item > div:first-child > div:last-child h3 {
        font-size: 13px !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }

    .buddy-item > div:first-child > div:last-child p {
        font-size: 10px !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }

    /* Stats inline */
    .buddy-item > div:nth-child(2) {
        gap: 15px !important;
        margin-left: 12px !important;
    }

    .buddy-item > div:nth-child(2) > div > div:first-child {
        font-size: 13px !important;
    }

    .buddy-item > div:nth-child(2) > div > div:last-child {
        font-size: 9px !important;
    }

    /* Actions inline */
    .buddy-item > div:last-child {
        gap: 12px !important;
    }

    .buddy-item > div:last-child a,
    .buddy-item > div:last-child button {
        font-size: 16px !important;
    }

    /* Feature cards */
    .feature-card {
        padding: 14px !important;
    }

    .feature-card h3 {
        font-size: 15px !important;
    }

    .feature-card p {
        font-size: 12px !important;
    }

    /* Title */
    .title {
        font-size: 22px !important;
    }

    .subtitle {
        font-size: 13px !important;
    }

    /* Section headings */
    section h2 {
        font-size: 18px !important;
    }

    /* Modal responsive styles */
    #createGroupModal > div {
        width: 95% !important;
        max-width: 95% !important;
        padding: 20px !important;
        max-height: 85vh !important;
        overflow-y: auto !important;
    }

    #createGroupModal h2 {
        font-size: 18px !important;
        margin-bottom: 16px !important;
        padding-right: 30px !important;
    }

    #createGroupModal input,
    #createGroupModal select,
    #createGroupModal textarea {
        font-size: 14px !important;
        padding: 8px !important;
    }

    #createGroupModal label {
        font-size: 13px !important;
    }

    #createGroupModal button[type="submit"],
    #createGroupModal button[type="button"] {
        padding: 8px 16px !important;
        font-size: 13px !important;
    }
}

@media (max-width: 480px) {
    /* Modal on small screens */
    #createGroupModal > div {
        padding: 16px !important;
    }

    #createGroupModal h2 {
        font-size: 16px !important;
    }

    #createGroupModal .form-group {
        margin-bottom: 12px !important;
    }

    #createGroupModal input,
    #createGroupModal select,
    #createGroupModal textarea {
        font-size: 13px !important;
        padding: 8px 10px !important;
    }

    #createGroupModal button[type="submit"],
    #createGroupModal button[type="button"] {
        width: 100%;
        margin-bottom: 8px;
    }

    #createGroupModal > div > button:last-child {
        width: 100%;
    }
}
</style>

<!-- My Study Groups Section -->
<?php if (!empty($myGroups)): ?>
    <section style="margin-bottom: 40px;">
        <h2 style="font-size: 20px; margin-bottom: 20px; color: #1e293b;">
            <i class="fas fa-users" style="color: #667eea;"></i> My Study Groups
        </h2>
        <div class="features-section" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            <?php foreach ($myGroups as $group): ?>
                <div class="feature-card" style="border: 1px solid #e2e8f0; transition: transform 0.2s; cursor: pointer;"
                     onclick="window.location.href='/study-group/view/<?php echo $group['id']; ?>'">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px; position: relative;">
                        <h3 style="font-size: 18px; color: #1e293b; margin: 0; flex: 1; padding-right: 60px;">
                            <?php echo htmlspecialchars($group['title']); ?>
                        </h3>
                        <?php
                        $groupNotificationCount = getStudyGroupNotificationCount($group['id']);
                        if ($groupNotificationCount > 0):
                        ?>
                            <span class="notification-badge"><?php echo $groupNotificationCount > 99 ? '99+' : $groupNotificationCount; ?></span>
                        <?php endif; ?>
                        <?php if ($group['user_role'] === 'admin'): ?>
                            <span style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                ADMIN
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($group['grade_level'])): ?>
                        <div style="margin-bottom: 10px;">
                            <span style="background: #e0e7ff; color: #4f46e5; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                <i class="fas fa-graduation-cap"></i> Grade <?php echo htmlspecialchars($group['grade_level']); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 15px; line-height: 1.5;">
                        <?php echo htmlspecialchars(substr($group['description'] ?? 'No description', 0, 100)); ?>
                        <?php if (strlen($group['description'] ?? '') > 100): ?>...<?php endif; ?>
                    </p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #94a3b8; margin-bottom: 15px;">
                        <span>
                            <i class="fas fa-users"></i> <?php echo $group['member_count']; ?>/<?php echo $group['max_members']; ?> members
                        </span>
                        <span style="background: #fff7ed; color: #c2410c; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #ffedd5;">
                            <i class="fas fa-file-upload" style="color: #f97316;"></i> <?php echo $group['script_count'] ?? 0; ?> activity
                        </span>
                    </div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                        <a href="/study-group/view/<?php echo $group['id']; ?>" 
                           style="color: #667eea; text-decoration: none; font-weight: 500; font-size: 14px;">
                            View Group <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- Available Study Groups Section -->
<section style="margin-bottom: 40px;">
    <h2 style="font-size: 20px; margin-bottom: 20px; color: #1e293b;">
        <i class="fas fa-fire" style="color: #ef4444;"></i> Top 5 Most Active Study Groups
    </h2>
    <p style="color: #64748b; font-size: 14px; margin-bottom: 20px; margin-top: -15px;">
        Join these highly active groups to access more shared study materials and discussions!
    </p>
    
    <?php if (empty($availableGroups)): ?>
        <div style="background: #f8fafc; border-radius: 12px; padding: 40px; text-align: center; border: 1px dashed #cbd5e1;">
            <i class="fas fa-users-slash" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"></i>
            <p style="color: #64748b; font-size: 16px;">No other study groups available right now.</p>
            <p style="color: #94a3b8; font-size: 14px; margin-top: 5px;">Why not create your own study group?</p>
        </div>
    <?php else: ?>
        <div class="features-section" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            <?php foreach ($availableGroups as $group): ?>
                <div class="feature-card" style="border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 18px; color: #1e293b; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($group['title']); ?>
                    </h3>
                    <?php if (!empty($group['grade_level'])): ?>
                        <div style="margin-bottom: 10px;">
                            <span style="background: #e0e7ff; color: #4f46e5; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                <i class="fas fa-graduation-cap"></i> Grade <?php echo htmlspecialchars($group['grade_level']); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 15px; line-height: 1.5;">
                        <?php echo htmlspecialchars(substr($group['description'] ?? 'No description', 0, 100)); ?>
                        <?php if (strlen($group['description'] ?? '') > 100): ?>...<?php endif; ?>
                    </p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #94a3b8; margin-bottom: 15px;">
                        <span>
                            <i class="fas fa-users"></i> <?php echo $group['member_count']; ?>/<?php echo $group['max_members']; ?> members
                        </span>
                        <span style="background: #fff7ed; color: #c2410c; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #ffedd5;">
                            <i class="fas fa-file-upload" style="color: #f97316;"></i> <?php echo $group['script_count'] ?? 0; ?> activity
                        </span>
                    </div>
                    
                    <?php if ($group['member_count'] >= $group['max_members']): ?>
                        <button disabled style="width: 100%; padding: 10px; background: #cbd5e1; color: #64748b; border: none; border-radius: 6px; cursor: not-allowed;">
                            <i class="fas fa-lock"></i> Group Full
                        </button>
                    <?php else: ?>
                        <form method="POST" action="/study-group/join/<?php echo $group['id']; ?>" style="margin: 0;">
                            <button type="submit" class="btn-primary" style="width: 100%;">
                                <i class="fas fa-sign-in-alt"></i> Join Group
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Create Group Modal -->
<div id="createGroupModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; position: relative; max-height: 90vh; overflow-y: auto;">
        <button onclick="document.getElementById('createGroupModal').style.display='none'" 
                style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">
            &times;
        </button>
        
        <h2 style="margin-bottom: 20px; color: #1e293b;">
            <i class="fas fa-plus-circle" style="color: #667eea;"></i> Create Study Group
        </h2>
        
        <form method="POST" action="/study-group/create">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Group Title <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="title" required maxlength="100" 
                       placeholder="e.g., Mathematics Study Group"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Grade Level
                </label>
                <select name="grade_level" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
                    <option value="">Select Grade (Optional)</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                    <option value="College">College/University</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    School Name
                </label>
                <input type="text" name="school_name" maxlength="100"
                       placeholder="e.g., Springfield High School"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Description
                </label>
                <textarea name="description" rows="4" 
                          placeholder="Describe what your study group will focus on..."
                          style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">
                    Maximum Members
                </label>
                <select name="max_members" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
                    <option value="5">5 members</option>
                    <option value="10" selected>10 members</option>
                    <option value="15">15 members</option>
                    <option value="20">20 members</option>
                    <option value="30">30 members</option>
                    <option value="50">50 members</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('createGroupModal').style.display='none'" 
                        style="padding: 10px 20px; background: #f1f5f9; color: #64748b; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus"></i> Create Group
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('createGroupModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    function inviteToGroupModal(username) {
        alert('To invite ' + username + ' to a study group, you can:\n\n1. Click "Ask for Help" to send them an email\n2. Create a study group and share the invite link\n3. Message them directly if you\'re in the same group');
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
