<?php
include __DIR__ . '/../../layouts/admin_header.php';

// Group scripts by user
$scriptsByUser = [];
foreach ($scripts as $script) {
    $userId = $script['student_id'];
    if (!isset($scriptsByUser[$userId])) {
        $scriptsByUser[$userId] = [
            'username' => $script['username'],
            'email' => $script['email'],
            'scripts' => []
        ];
    }
    $scriptsByUser[$userId]['scripts'][] = $script;
}

// Separate shared scripts (admin-uploaded)
$sharedScripts = array_filter($scripts, function($script) {
    return !empty($script['is_shared']);
});
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-file-alt"></i> Manage Scripts
        </h1>
        <p style="color: #6b7280;">View and manage all uploaded scripts grouped by user</p>
    </div>
    <a href="/admin" class="btn-secondary" style="text-decoration: none; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<!-- Upload Shared Script Card -->
<div class="admin-section" style="background: white; border-radius: 12px; padding: 24px; margin-bottom: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #667eea;">
    <h2 style="margin: 0 0 20px 0; color: #1f2937; display: flex; align-items: center; gap: 10px; font-size: 20px;">
        <i class="fas fa-upload" style="color: #667eea;"></i>
        Upload Shared Script (Available to All Students)
    </h2>
    <form method="POST" action="/admin/scripts/upload-shared" enctype="multipart/form-data" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; align-items: end;">
        <div style="grid-column: 1 / -1;">
            <h4 style="margin: 0 0 15px 0; color: #374151; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-info-circle" style="color: #667eea;"></i> Script Information
            </h4>
        </div>
        <div>
            <label for="shared_title" style="display: block; margin-bottom: 6px; font-weight: 600; color: #374151; font-size: 14px;">
                <i class="fas fa-heading"></i> Title <span style="color: #ef4444;">*</span>
            </label>
            <input type="text" id="shared_title" name="title" placeholder="e.g., Mathematics Study Guide" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; transition: border-color 0.2s;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e5e7eb'">
        </div>
        <div>
            <label for="shared_subject" style="display: block; margin-bottom: 6px; font-weight: 600; color: #374151; font-size: 14px;">
                <i class="fas fa-book"></i> Subject <span style="color: #ef4444;">*</span>
            </label>
            <input type="text" id="shared_subject" name="subject" placeholder="e.g., Mathematics" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; transition: border-color 0.2s;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e5e7eb'">
        </div>
        <div>
            <label for="shared_grade_level" style="display: block; margin-bottom: 6px; font-weight: 600; color: #374151; font-size: 14px;">
                <i class="fas fa-graduation-cap"></i> Grade Level <span style="color: #ef4444;">*</span>
            </label>
            <select id="shared_grade_level" name="grade_level" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; background: white;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e5e7eb'">
                <option value="">Select Grade</option>
                <option value="8">Grade 8</option>
                <option value="9">Grade 9</option>
                <option value="10">Grade 10</option>
                <option value="11">Grade 11</option>
                <option value="12">Grade 12</option>
            </select>
        </div>
        <div>
            <label for="shared_year" style="display: block; margin-bottom: 6px; font-weight: 600; color: #374151; font-size: 14px;">
                <i class="fas fa-calendar-alt"></i> Year <span style="color: #ef4444;">*</span>
            </label>
            <select id="shared_year" name="year" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; background: white;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e5e7eb'">
                <option value="">Select Year</option>
                <option value="2026">2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
                <option value="2021">2021</option>
                <option value="2020">2020</option>
            </select>
        </div>
        <div>
            <label for="shared_paper" style="display: block; margin-bottom: 6px; font-weight: 600; color: #374151; font-size: 14px;">
                <i class="fas fa-file-alt"></i> Paper Type <span style="color: #ef4444;">*</span>
            </label>
            <select id="shared_paper" name="paper" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; background: white;" onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e5e7eb'">
                <option value="">Select Paper</option>
                <option value="1">Paper 1</option>
                <option value="2">Paper 2</option>
                <option value="3">Paper 3 (if applicable)</option>
            </select>
        </div>
        <div style="grid-column: 1 / -1;">
            <h4 style="margin: 10px 0 15px 0; color: #374151; font-size: 15px; display: flex; align-items: center; gap: 8px; border-top: 1px solid #e5e7eb; padding-top: 15px;">
                <i class="fas fa-file-upload" style="color: #10b981;"></i> File Uploads
            </h4>
        </div>
        <div>
            <label for="shared_script_file" style="display: block; margin-bottom: 6px; font-weight: 600; color: #374151; font-size: 14px;">
                <i class="fas fa-file-pdf"></i> Script File <span style="color: #ef4444;">*</span>
            </label>
            <input type="file" id="shared_script_file" name="script_file" accept=".pdf,.docx,.txt" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; background: white;" onchange="validateFile(this, 10)">
            <small style="color: #6b7280; font-size: 12px; display: block; margin-top: 6px;">
                <i class="fas fa-info-circle"></i> PDF, DOCX, or TXT (Max 10MB)
            </small>
        </div>
        <div>
            <label for="shared_memorandum_file" style="display: block; margin-bottom: 6px; font-weight: 600; color: #374151; font-size: 14px;">
                <i class="fas fa-check-circle"></i> Memorandum File (Optional)
            </label>
            <input type="file" id="shared_memorandum_file" name="memorandum_file" accept=".pdf,.docx,.txt" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; background: white;" onchange="validateFile(this, 10)">
            <small style="color: #6b7280; font-size: 12px; display: block; margin-top: 6px;">
                <i class="fas fa-info-circle"></i> PDF, DOCX, or TXT (Max 10MB)
            </small>
        </div>
        <div style="grid-column: 1 / -1; display: flex; gap: 15px; margin-top: 10px;">
            <button type="submit" class="btn-primary" style="flex: 1; padding: 14px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 10px;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(102,126,234,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                <i class="fas fa-cloud-upload-alt"></i> Upload Shared Script
            </button>
            <button type="reset" class="btn-secondary" style="padding: 14px 24px; background: #f3f4f6; color: #374151; border: 2px solid #e5e7eb; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                <i class="fas fa-undo"></i> Reset
            </button>
        </div>
    </form>
</div>

<script>
function validateFile(input, maxSizeMB) {
    const file = input.files[0];
    if (file) {
        const sizeMB = file.size / (1024 * 1024);
        if (sizeMB > maxSizeMB) {
            alert('File size must be less than ' + maxSizeMB + 'MB. Your file is ' + sizeMB.toFixed(2) + 'MB.');
            input.value = '';
        }
    }
}
</script>

<!-- Shared Scripts Section -->
<?php if (!empty($sharedScripts)): ?>
<div class="admin-section" style="background: white; border-radius: 12px; padding: 24px; margin-bottom: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h2 style="margin: 0 0 20px 0; color: #1f2937; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-share-alt" style="color: #22c55e;"></i>
        Shared Scripts (Available to All Students)
    </h2>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Grade</th>
                    <th>Year</th>
                    <th>Paper</th>
                    <th>Memorandum</th>
                    <th>File</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sharedScripts as $script): ?>
                    <tr>
                        <td style="color: #6b7280;">#<?php echo $script['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($script['title']); ?></strong></td>
                        <td>
                            <?php if (!empty($script['subject'])): ?>
                                <span class="badge basic"><?php echo htmlspecialchars($script['subject']); ?></span>
                            <?php else: ?>
                                <span style="color: #9ca3af;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($script['grade_level'])): ?>
                                <span style="color: #6b7280; font-size: 13px;">Grade <?php echo htmlspecialchars($script['grade_level']); ?></span>
                            <?php else: ?>
                                <span style="color: #9ca3af;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($script['year'])): ?>
                                <span class="badge" style="background: #dbeafe; color: #1e40af; font-size: 12px; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                                    <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($script['year']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #9ca3af;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($script['paper'])): ?>
                                <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 12px; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                                    Paper <?php echo htmlspecialchars($script['paper']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #9ca3af;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($script['memorandum_file_path'])): ?>
                                <span style="color: #10b981; font-size: 13px;" title="Memorandum attached">
                                    <i class="fas fa-check-circle"></i> Yes
                                </span>
                            <?php else: ?>
                                <span style="color: #9ca3af; font-size: 13px;" title="No memorandum">
                                    <i class="fas fa-times-circle"></i> No
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="color: #6b7280; font-size: 13px;"><?php echo htmlspecialchars(basename($script['file_path'])); ?></span>
                        </td>
                        <td style="color: #6b7280;"><?php echo date('M d, Y H:i', strtotime($script['uploaded_at'])); ?></td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="/view-script/<?php echo $script['id']; ?>" target="_blank" class="btn-sm btn-sm-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="/download-script/<?php echo $script['id']; ?>" class="btn-sm btn-sm-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <?php if (!empty($script['memorandum_file_path'])): ?>
                                    <a href="/download-memorandum/<?php echo $script['id']; ?>" class="btn-sm btn-sm-success" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600;">
                                        <i class="fas fa-file-download"></i> Memo
                                    </a>
                                <?php endif; ?>
                                <form method="POST" action="/admin/scripts/delete" style="display: inline;" onsubmit="return confirm('Delete this shared script? This cannot be undone.');">
                                    <input type="hidden" name="script_id" value="<?php echo $script['id']; ?>">
                                    <button type="submit" class="btn-sm btn-sm-danger" style="cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (empty($scriptsByUser)): ?>
    <div class="admin-section">
        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            No scripts found
        </div>
    </div>
<?php else: ?>
    <div class="admin-section">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Total Scripts</th>
                        <th>Processed</th>
                        <th>Pending</th>
                        <th>Last Upload</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scriptsByUser as $userId => $userData): ?>
                        <?php
                        $processedCount = 0;
                        $pendingCount = 0;
                        $lastUpload = null;
                        
                        foreach ($userData['scripts'] as $script) {
                            if ($script['processed']) {
                                $processedCount++;
                            } else {
                                $pendingCount++;
                            }
                            if (!$lastUpload || strtotime($script['uploaded_at']) > strtotime($lastUpload)) {
                                $lastUpload = $script['uploaded_at'];
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <strong style="color: #667eea;">
                                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($userData['username'] ?? 'Unknown'); ?>
                                </strong>
                            </td>
                            <td>
                                <span style="color: #6b7280;"><?php echo htmlspecialchars($userData['email'] ?? ''); ?></span>
                            </td>
                            <td>
                                <span class="badge basic"><?php echo count($userData['scripts']); ?></span>
                            </td>
                            <td>
                                <span style="color: #22c55e;"><i class="fas fa-check"></i> <?php echo $processedCount; ?></span>
                            </td>
                            <td>
                                <span style="color: #f97316;"><i class="fas fa-clock"></i> <?php echo $pendingCount; ?></span>
                            </td>
                            <td>
                                <?php echo $lastUpload ? date('M d, Y H:i', strtotime($lastUpload)) : '-'; ?>
                            </td>
                            <td>
                                <button onclick="toggleUserScripts(<?php echo $userId; ?>)" class="btn-sm btn-sm-primary" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i> View Scripts
                                </button>
                            </td>
                        </tr>
                        <tr id="user-scripts-<?php echo $userId; ?>" style="display: none;">
                            <td colspan="7" style="padding: 0; background: #f9fafb;">
                                <div style="padding: 20px;">
                                    <h4 style="margin: 0 0 15px 0; color: #667eea;">
                                        <i class="fas fa-folder-open"></i> Scripts by <?php echo htmlspecialchars($userData['username']); ?>
                                    </h4>
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead style="background: #e5e7eb;">
                                            <tr>
                                                <th style="padding: 10px; text-align: left;">ID</th>
                                                <th style="padding: 10px; text-align: left;">File Name</th>
                                                <th style="padding: 10px; text-align: left;">Subject</th>
                                                <th style="padding: 10px; text-align: left;">Grade Level</th>
                                                <th style="padding: 10px; text-align: left;">Status</th>
                                                <th style="padding: 10px; text-align: left;">Uploaded</th>
                                                <th style="padding: 10px; text-align: left;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userData['scripts'] as $script): ?>
                                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                                    <td style="padding: 10px; color: #6b7280;">#<?php echo $script['id']; ?></td>
                                                    <td style="padding: 10px;">
                                                        <strong><?php echo htmlspecialchars(basename($script['file_path']) ?? 'Unknown'); ?></strong>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php if (!empty($script['subject'])): ?>
                                                            <span class="badge basic"><?php echo htmlspecialchars($script['subject']); ?></span>
                                                        <?php else: ?>
                                                            <span style="color: #9ca3af;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php if (!empty($script['grade_level'])): ?>
                                                            <span style="color: #6b7280; font-size: 13px;"><?php echo htmlspecialchars($script['grade_level']); ?></span>
                                                        <?php else: ?>
                                                            <span style="color: #9ca3af;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php if ($script['processed']): ?>
                                                            <span class="badge active"><i class="fas fa-check"></i> Processed</span>
                                                        <?php else: ?>
                                                            <span class="badge inactive">Not processed</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 10px;"><?php echo date('M d, Y H:i', strtotime($script['uploaded_at'])); ?></td>
                                                    <td style="padding: 10px;">
                                                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                                            <a href="/download-memorandum/<?php echo $script['id']; ?>" class="btn-sm btn-sm-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                            <form method="POST" action="/admin/scripts/delete" style="display: inline;" onsubmit="return confirm('Delete this script? This cannot be undone.');">
                                                                <input type="hidden" name="script_id" value="<?php echo $script['id']; ?>">
                                                                <button type="submit" class="btn-sm btn-sm-danger" style="cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script>
function toggleUserScripts(userId) {
    const row = document.getElementById('user-scripts-' + userId);
    if (row.style.display === 'none') {
        row.style.display = '';
    } else {
        row.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
