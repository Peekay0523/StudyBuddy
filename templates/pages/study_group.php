<?php
$pageTitle = 'Study Groups - StudySmart';
$currentPage = 'study-group';
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Study Groups</h1>
<p class="subtitle">Connect with other students and learn together!</p>

<!-- Free User Upgrade Notice -->
<?php if ($isFreeUser): ?>
<div class="alert" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
    <div style="display: flex; align-items: flex-start; gap: 15px;">
        <div style="flex-shrink: 0;">
            <i class="fas fa-lock" style="font-size: 24px; color: #f59e0b;"></i>
        </div>
        <div style="flex: 1;">
            <h3 style="margin: 0 0 10px 0; color: #92400e; font-size: 18px;">
                <i class="fas fa-crown"></i> Upgrade to Access Full Features
            </h3>
            <p style="margin: 0 0 15px 0; color: #78350f; font-size: 14px; line-height: 1.6;">
                You're currently on the <strong>Free plan</strong>. To create study groups and unlock all collaborative features, 
                please upgrade to <strong>Basic</strong> or <strong>Premium</strong>.
            </p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="/subscription/checkout?plan=basic" class="btn-primary" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none;">
                    <i class="fas fa-rocket"></i> Upgrade to Basic (R39/mo)
                </a>
                <a href="/subscription/checkout?plan=premium" class="btn-secondary" style="border: 2px solid #f59e0b; color: #f59e0b; text-decoration: none;">
                    <i class="fas fa-star"></i> View Premium (R69/mo)
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Create Study Group Button -->
<?php if (!$isFreeUser): ?>
<div style="margin-bottom: 30px;">
    <button onclick="document.getElementById('createGroupModal').style.display='block'" class="btn-primary">
        <i class="fas fa-plus"></i> Create Study Group
    </button>
</div>
<?php else: ?>
<div style="margin-bottom: 30px;">
    <button disabled class="btn-primary" style="opacity: 0.6; cursor: not-allowed; background: #cbd5e1;" title="Upgrade to create study groups">
        <i class="fas fa-lock"></i> Create Study Group (Upgrade Required)
    </button>
</div>
<?php endif; ?>

<!-- Study Buddies Section -->
<section style="margin-bottom: 40px;">
    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 12px; padding: 25px; margin-bottom: 30px;">
        <h2 style="margin: 0 0 20px 0; color: #166534; font-size: 20px;">
            <i class="fas fa-user-friends" style="color: #22c55e;"></i> Find Study Buddies
        </h2>
        <p style="color: #166534; font-size: 14px; margin-bottom: 20px;">
            Connect with active students who can help you with academic assistance
        </p>

        <?php if (empty($studyBuddies)): ?>
            <div style="text-align: center; padding: 30px; color: #64748b;">
                <i class="fas fa-users-slash" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No study buddies available at the moment. Check back later!</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                <?php foreach ($studyBuddies as $buddy): ?>
                    <div class="feature-card" style="border: 1px solid #86efac; background: white; transition: transform 0.2s; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.1);">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <div style="position: relative;">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 20px;">
                                    <?php echo strtoupper(substr($buddy['username'], 0, 1)); ?>
                                </div>
                                <span style="position: absolute; bottom: 0; right: 0; width: 14px; height: 14px; background: <?php echo $buddy['is_online'] ? '#22c55e' : '#ef4444'; ?>; border: 2px solid white; border-radius: 50%;" title="<?php echo $buddy['is_online'] ? 'Online now' : 'Offline'; ?>"></span>
                            </div>
                            <div style="flex: 1;">
                                <h3 style="margin: 0; font-size: 16px; color: #1e293b;">
                                    <?php echo htmlspecialchars($buddy['username']); ?>
                                </h3>
                                <p style="margin: 3px 0 0 0; font-size: 12px; color: <?php echo $buddy['is_online'] ? '#22c55e' : '#64748b'; ?>; font-weight: 500;">
                                    <i class="fas fa-circle" style="font-size: 8px;"></i> <?php echo $buddy['is_online'] ? 'Active now' : 'Last active: ' . date('M d, H:i', strtotime($buddy['last_active'])); ?>
                                </p>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
                                <span style="background: #f0fdf4; color: #166534; padding: 4px 10px; border-radius: 12px; font-size: 11px; border: 1px solid #86efac;">
                                    <i class="fas fa-book"></i> <?php echo $buddy['scripts_uploaded'] ?? 0; ?> scripts
                                </span>
                                <span style="background: #f0f9ff; color: #0369a1; padding: 4px 10px; border-radius: 12px; font-size: 11px; border: 1px solid #bae6fd;">
                                    <i class="fas fa-users"></i> <?php echo $buddy['groups_count'] ?? 0; ?> groups
                                </span>
                            </div>
                            <?php if (!empty($buddy['grade_level'])): ?>
                                <p style="margin: 0 0 5px 0; font-size: 12px; color: #64748b;">
                                    <i class="fas fa-graduation-cap"></i> Grade <?php echo htmlspecialchars($buddy['grade_level']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($buddy['bio'])): ?>
                                <p style="margin: 0; font-size: 12px; color: #475569; line-height: 1.4;">
                                    <?php echo htmlspecialchars(substr($buddy['bio'], 0, 60)); ?><?php if (strlen($buddy['bio']) > 60): ?>...<?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <a href="mailto:<?php echo htmlspecialchars($buddy['email']); ?>" class="btn-primary" style="flex: 1; text-align: center; padding: 10px 14px; font-size: 13px; text-decoration: none; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; box-shadow: 0 2px 6px rgba(14, 165, 233, 0.3); transition: all 0.3s ease;">
                                <i class="fas fa-envelope"></i> Ask for Help
                            </a>
                            <button onclick="inviteToGroupModal('<?php echo htmlspecialchars($buddy['username']); ?>')" class="btn-secondary" style="flex: 1; padding: 10px 14px; font-size: 13px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #64748b 0%, #475569 100%); color: white; border: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 6px rgba(100, 116, 139, 0.3);" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 10px rgba(100, 116, 139, 0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 6px rgba(100, 116, 139, 0.3)'" onclick="this.blur()">
                                <i class="fas fa-user-plus"></i> Invite
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

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
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <h3 style="font-size: 18px; color: #1e293b; margin: 0; flex: 1;">
                            <?php echo htmlspecialchars($group['title']); ?>
                        </h3>
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
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #94a3b8;">
                        <span>
                            <i class="fas fa-users"></i> <?php echo $group['member_count']; ?>/<?php echo $group['max_members']; ?> members
                        </span>
                        <span>
                            <i class="fas fa-file-upload"></i> <?php echo $group['script_count'] ?? 0; ?> scripts
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
        <i class="fas fa-globe" style="color: #22c55e;"></i> Available Study Groups
    </h2>
    
    <?php if (empty($availableGroups)): ?>

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
                        <span>
                            <i class="fas fa-file-upload"></i> <?php echo $group['script_count'] ?? 0; ?> scripts
                        </span>
                    </div>
                    
                    <?php if ($group['member_count'] >= $group['max_members']): ?>
                        <button disabled style="width: 100%; padding: 10px; background: #cbd5e1; color: #64748b; border: none; border-radius: 6px; cursor: not-allowed;">
                            <i class="fas fa-lock"></i> Group Full
                        </button>
                    <?php elseif ($isFreeUser): ?>
                        <button disabled style="width: 100%; padding: 10px; background: #cbd5e1; color: #64748b; border: none; border-radius: 6px; cursor: not-allowed;" title="Upgrade to join study groups">
                            <i class="fas fa-lock"></i> Join Group (Upgrade Required)
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
