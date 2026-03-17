<?php
$pageTitle = 'Study Plans - StudySmart';
$currentPage = 'study-plan';

// Add loading overlay styles and share/calendar scripts
$extraHead = <<<'HTML'
<style>
/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}
.loading-overlay.active {
    opacity: 1;
    visibility: visible;
}
.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #e9ecef;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.loading-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, 50px);
    color: #6c757d;
    font-size: 1rem;
    font-weight: 500;
}

/* Calendar Styles */
.calendar-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}
.calendar-header h2 {
    margin: 0;
    color: #1e293b;
    font-size: 1.5rem;
    font-weight: 700;
}
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
}
.calendar-day-header {
    text-align: center;
    font-weight: 600;
    color: #64748b;
    padding: 12px;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.calendar-day {
    min-height: 90px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8fafc;
}
.calendar-day:hover {
    background: #e0e7ff;
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}
.calendar-day.other-month {
    background: #f1f5f9;
    color: #94a3b8;
    opacity: 0.6;
}
.calendar-day.today {
    border-color: #667eea;
    background: linear-gradient(135deg, #e0e7ff 0%, #f0f4ff 100%);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}
.calendar-day.has-events {
    border-color: #10b981;
    background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 100%);
}
.calendar-day-number {
    font-weight: 700;
    margin-bottom: 8px;
    font-size: 1rem;
    color: #1e293b;
}
.calendar-event-count {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border-radius: 12px;
    padding: 3px 10px;
    font-size: 0.75rem;
    display: inline-block;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
}

/* Calendar Navigation Buttons */
.calendar-header .btn-secondary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}
.calendar-header .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}
.calendar-header .btn-secondary:active {
    transform: translateY(0);
}

/* Share Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9998;
}
.modal-content {
    background: white;
    padding: 30px;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

/* Reminder Form */
.reminder-form {
    background: #f8fafc;
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
}

/* Study plan card actions */
.study-plan-actions {
    display: flex;
    gap: 8px;
    margin-top: 10px;
    flex-wrap: wrap;
    width: 100%;
}

/* Button Styles */
.btn-sm {
    padding: 8px 14px;
    font-size: 0.85rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.btn-sm.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
}
.btn-sm.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.4);
}

.btn-sm.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}
.btn-sm.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
}

.btn-sm.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
}
.btn-sm.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
}

.btn-sm.btn-secondary {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
    box-shadow: 0 2px 6px rgba(100, 116, 139, 0.3);
}
.btn-sm.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(100, 116, 139, 0.4);
}

/* Add Reminder Button */
#add-reminder-btn {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 3px 10px rgba(245, 158, 11, 0.3);
}
#add-reminder-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(245, 158, 11, 0.4);
}

/* Upcoming Reminders & Share Sections */
.complete-reminder, .delete-reminder, .accept-share, .decline-share {
    padding: 8px 14px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.complete-reminder {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}
.complete-reminder:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
}

.delete-reminder {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
}
.delete-reminder:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
}

.accept-share {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}
.accept-share:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
}

.decline-share {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
}
.decline-share:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    /* Calendar responsive */
    .calendar-container {
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .calendar-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .calendar-header h2 {
        font-size: 1.2rem;
        order: -1;
        width: 100%;
    }
    
    .calendar-header .btn-secondary {
        padding: 8px 16px;
        font-size: 0.85rem;
        width: 100%;
        justify-content: center;
    }
    
    .calendar-grid {
        gap: 4px;
    }
    
    .calendar-day-header {
        padding: 8px 4px;
        font-size: 0.7rem;
    }
    
    .calendar-day {
        min-height: 60px;
        padding: 6px;
    }
    
    .calendar-day-number {
        font-size: 0.85rem;
        margin-bottom: 4px;
    }
    
    .calendar-event-count {
        font-size: 0.65rem;
        padding: 2px 6px;
    }
    
    /* Upload container */
    .upload-container {
        padding: 0 10px;
    }
    
    .upload-container > div {
        padding: 20px 15px;
    }
    
    /* Form grid to single column */
    .upload-container .form-group,
    .upload-container div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
    }
    
    /* Button sizing */
    #add-reminder-btn,
    .btn-primary[style*="padding: 15px 40px"] {
        padding: 12px 24px !important;
        font-size: 14px !important;
        width: 100%;
    }
    
    /* Study plan cards */
    section.actions {
        grid-template-columns: 1fr !important;
        gap: 15px !important;
    }
    
    .action.orange {
        min-height: auto !important;
    }
    
    .study-plan-actions {
        flex-wrap: wrap;
    }
    
    .study-plan-actions .btn-sm {
        flex: 1;
        min-width: calc(50% - 4px);
        justify-content: center;
    }
    
    /* Modal content */
    .modal-content {
        width: 95%;
        padding: 20px;
    }
    
    .modal-content .form-group {
        margin-bottom: 15px;
    }
    
    /* Reminder form grid */
    #reminder-form div[style*="grid-template-columns: 1fr 1fr"],
    .upload-container div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
    
    /* Modal buttons stack */
    .modal-content form > div[style*="display: flex; gap: 10px"] {
        flex-direction: column;
    }
    
    .modal-content form > div[style*="display: flex; gap: 10px"] button {
        width: 100%;
    }
    
    /* Upcoming reminders */
    div[style*="background: white; border-radius: 12px; padding: 20px"] {
        padding: 15px !important;
    }
    
    div[style*="display: flex; justify-content: space-between; align-items: center; padding: 12px"] {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 10px;
    }
    
    div[style*="display: flex; gap: 8px"] {
        width: 100%;
        justify-content: space-between;
    }
    
    div[style*="display: flex; gap: 8px"] .btn-sm {
        flex: 1;
        justify-content: center;
    }
    
    /* Hide file size on very small screens */
    #file-size {
        font-size: 11px !important;
    }
    
    /* Title and subtitle */
    .title {
        font-size: 1.5rem !important;
    }
    
    .subtitle {
        font-size: 0.9rem !important;
    }
    
    /* Points banner */
    div[style*="background: linear-gradient(135deg, #dbeafe"] {
        padding: 15px !important;
    }
    
    div[style*="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px"] {
        gap: 15px !important;
    }
}

/* Extra small screens */
@media (max-width: 480px) {
    .calendar-day {
        min-height: 50px;
        padding: 4px;
    }
    
    .calendar-day-header {
        font-size: 0.65rem;
        padding: 6px 2px;
    }
    
    .calendar-event-count {
        display: none;
    }
    
    .modal-content {
        padding: 15px;
    }
    
    .btn-sm {
        padding: 6px 10px !important;
        font-size: 0.75rem !important;
    }
}
</style>
HTML;

$extraScripts = '<script>
const uploadArea = document.getElementById("upload-area");
const fileInput = document.getElementById("script_file");
const previewSection = document.getElementById("preview-section");
const fileName = document.getElementById("file-name");
const fileSize = document.getElementById("file-size");
const clearBtn = document.getElementById("clear-btn");
const generateForm = document.getElementById("generate-form");

// Click to upload
uploadArea.addEventListener("click", () => {
    fileInput.click();
});

// Drag and drop
uploadArea.addEventListener("dragover", (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = "#667eea";
    uploadArea.style.background = "#e0e7ff";
});

uploadArea.addEventListener("dragleave", () => {
    uploadArea.style.borderColor = "#cbd5e1";
    uploadArea.style.background = "#f8fafc";
});

uploadArea.addEventListener("drop", (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = "#cbd5e1";
    uploadArea.style.background = "#f8fafc";
    
    const files = Array.from(e.dataTransfer.files);
    if (files.length > 0) {
        handleFile(files[0]);
    }
});

// File input change
fileInput.addEventListener("change", (e) => {
    if (e.target.files && e.target.files[0]) {
        handleFile(e.target.files[0]);
    }
});

function handleFile(file) {
    const allowedTypes = ["application/pdf", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "text/plain"];
    
    if (!allowedTypes.includes(file.type) && !file.name.endsWith(".pdf") && !file.name.endsWith(".docx") && !file.name.endsWith(".txt")) {
        alert("Invalid file type. Please upload PDF, DOCX, or TXT files.");
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
        alert("File size must be less than 10MB.");
        return;
    }
    
    fileName.textContent = file.name;
    fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + " MB";
    previewSection.style.display = "block";
    uploadArea.style.display = "none";
}

// Clear selection
clearBtn.addEventListener("click", () => {
    fileInput.value = "";
    previewSection.style.display = "none";
    uploadArea.style.display = "block";
    document.getElementById("title").value = "";
    document.getElementById("subject").value = "";
    document.getElementById("grade_level").value = "";
});

// Form submission
generateForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const selectedScanInput = document.getElementById("selected_scan_file");
    const hasFile = fileInput.files && fileInput.files[0];
    const hasScan = selectedScanInput && selectedScanInput.value && selectedScanInput.value.trim() !== "";

    if (!hasFile && !hasScan) {
        alert("Please select a file to upload. Click or drag a file into the upload area, or use Select from My Scans button.");
        return;
    }

    const formData = new FormData(generateForm);
    formData.append("for_study_plan", "1");

    const submitBtn = document.getElementById("submit-btn");
    const originalBtnText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = "<i class=\"fas fa-spinner fa-spin\"></i> Generating Study Plan...";
    
    try {
        const response = await fetch("/upload-script", {
            method: "POST",
            body: formData
        });
        
        if (response.ok) {
            window.location.href = "/study-plan?generated=1";
        } else {
            const data = await response.json();
            alert("Error: " + (data.error || "Failed to generate study plan"));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    } catch (error) {
        alert("Error: Failed to connect to server");
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});
</script>';
include __DIR__ . '/../layouts/header.php';
?>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
    <div class="loading-text">Loading...</div>
</div>

<h1 class="title">Your Study Plans</h1>
<p class="subtitle">Personalized study plans to help you master challenging topics.</p>

<!-- Calendar Section -->
<div class="calendar-container">
    <div class="calendar-header">
        <button id="prev-month" class="btn-secondary" style="padding: 8px 16px;">
            <i class="fas fa-chevron-left"></i> Previous
        </button>
        <h2 id="calendar-month-year" style="margin: 0; color: #1e293b;"></h2>
        <button id="next-month" class="btn-secondary" style="padding: 8px 16px;">
            Next <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <div class="calendar-grid" id="calendar-grid">
        <div class="calendar-day-header">Sun</div>
        <div class="calendar-day-header">Mon</div>
        <div class="calendar-day-header">Tue</div>
        <div class="calendar-day-header">Wed</div>
        <div class="calendar-day-header">Thu</div>
        <div class="calendar-day-header">Fri</div>
        <div class="calendar-day-header">Sat</div>
    </div>
    <div style="margin-top: 20px; text-align: right;">
        <button id="add-reminder-btn" class="btn-primary" style="padding: 10px 20px;">
            <i class="fas fa-plus"></i> Add Reminder
        </button>
    </div>
</div>

<!-- Upcoming Reminders -->
<?php if (!empty($upcomingReminders)): ?>
<div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="margin-bottom: 15px; color: #1e293b;">
        <i class="fas fa-bell" style="color: #f59e0b;"></i> Upcoming Reminders
    </h3>
    <div style="display: grid; gap: 10px;">
        <?php foreach ($upcomingReminders as $reminder): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #10b981;">
            <div>
                <strong style="color: #1e293b;"><?php echo htmlspecialchars($reminder['title']); ?></strong>
                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 0.85rem;">
                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($reminder['reminder_date'])); ?>
                    <?php if ($reminder['reminder_time']): ?>
                        at <?php echo date('g:i A', strtotime($reminder['reminder_time'])); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <button class="btn-sm btn-success complete-reminder" data-id="<?php echo $reminder['id']; ?>" style="cursor: pointer;">
                    <i class="fas fa-check"></i> Complete
                </button>
                <button class="btn-sm btn-danger delete-reminder" data-id="<?php echo $reminder['id']; ?>" style="cursor: pointer;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Pending Share Requests -->
<?php if (!empty($pendingShares)): ?>
<div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="margin-bottom: 15px; color: #1e293b;">
        <i class="fas fa-share-alt" style="color: #667eea;"></i> Pending Share Requests
    </h3>
    <div style="display: grid; gap: 10px;">
        <?php foreach ($pendingShares as $share): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #eff6ff; border-radius: 8px;">
            <div>
                <strong style="color: #1e293b;"><?php echo htmlspecialchars($share['title']); ?></strong>
                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 0.85rem;">
                    Shared by: <?php echo htmlspecialchars($share['sender_name']); ?>
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <button class="btn-sm btn-success accept-share" data-id="<?php echo $share['id']; ?>" style="cursor: pointer;">
                    <i class="fas fa-check"></i> Accept
                </button>
                <button class="btn-sm btn-danger decline-share" data-id="<?php echo $share['id']; ?>" style="cursor: pointer;">
                    <i class="fas fa-times"></i> Decline
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Shared Study Plans -->
<?php if (!empty($sharedWith)): ?>
<h2 style="margin-bottom: 20px; color: #1e293b;">
    <i class="fas fa-share-alt" style="color: #667eea;"></i> Shared With You
</h2>
<section class="actions" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); margin-bottom: 30px;">
    <?php foreach ($sharedWith as $plan): ?>
        <div class="action orange" style="flex-direction: column; align-items: flex-start; height: auto; min-height: 150px;">
            <h3 style="margin: 0 0 10px 0; font-size: 18px;"><?php echo htmlspecialchars($plan['title']); ?></h3>
            <p style="margin: 0; font-size: 14px; opacity: 0.9; flex: 1; overflow: hidden; text-overflow: ellipsis;">
                <?php echo htmlspecialchars(substr($plan['content'], 0, 100)); ?>...
            </p>
            <small style="margin-top: 10px; opacity: 0.8;">
                <i class="fas fa-user"></i> From: <?php echo htmlspecialchars($plan['sender_name']); ?>
            </small>
            <a href="/view-study-plan/<?php echo $plan['id']; ?>" class="btn-primary btn-sm" style="margin-top: 10px; text-decoration: none;">
                <i class="fas fa-eye"></i> View Plan
            </a>
        </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- Upload & Generate Section -->
<div class="upload-container" style="max-width: 800px; margin: 0 auto 40px auto;">
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 20px; color: #1e293b; font-size: 20px;">
            <i class="fas fa-magic" style="color: #667eea;"></i> Generate New Study Plan
        </h2>
        
        <form method="post" action="/upload-script" enctype="multipart/form-data" id="generate-form">
            <!-- Drag & Drop File Input -->
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Upload Study Material (PDF, DOCX, or TXT)</label>
                <div class="upload-area" id="upload-area" style="border: 3px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.3s ease;">
                    <input type="file" id="script_file" name="script_file" accept=".pdf,.docx,.txt" style="display: none;" required>
                    <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: #667eea; margin-bottom: 15px;"></i>
                    <h4 style="margin: 0 0 8px 0; color: #1e293b; font-size: 15px;">Click or drag file to upload</h4>
                    <p style="margin: 0; color: #64748b; font-size: 13px;">PDF, DOCX, TXT (Max 10MB)</p>
                </div>
                <div style="margin-top: 15px; text-align: center;">
                    <button type="button" id="select-from-scans-btn" class="btn-secondary" style="padding: 10px 20px; font-size: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(102,126,234,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                        <i class="fas fa-folder-open"></i> Select from My Scans
                    </button>
                </div>
                <div id="preview-section" style="display: none; margin-top: 15px; padding: 15px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-file-alt" style="font-size: 28px; color: #667eea;"></i>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #1e293b; font-size: 14px;" id="file-name"></div>
                            <div style="font-size: 12px; color: #64748b;" id="file-size"></div>
                        </div>
                        <button type="button" id="clear-btn" style="background: #fee2e2; color: #ef4444; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px;">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px;">
                <div class="form-group">
                    <label for="subject" style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Subject *</label>
                    <input type="text" id="subject" name="subject" placeholder="e.g., Mathematics" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
                </div>

                <div class="form-group">
                    <label for="grade_level" style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Grade Level *</label>
                    <select id="grade_level" name="grade_level" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
                        <option value="">Select Grade</option>
                        <option value="8">Grade 8</option>
                        <option value="9">Grade 9</option>
                        <option value="10">Grade 10</option>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label for="title" style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Title (optional)</label>
                <input type="text" id="title" name="title" placeholder="e.g., Calculus Chapter 5 Notes" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
            </div>

            <div style="margin-top: 25px; text-align: center;">
                <button type="submit" class="btn-primary" id="submit-btn" style="padding: 15px 40px; font-size: 16px;">
                    <i class="fas fa-magic"></i> Generate Study Plan
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (isset($_GET['generated']) && $_GET['generated'] == 1): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> Study plan generated successfully!
    </div>
<?php endif; ?>

<?php if (!empty($studyPlans)): ?>
    <h2 style="margin-bottom: 20px; color: #1e293b;">
        <i class="fas fa-book" style="color: #667eea;"></i> Your Study Plans
    </h2>
    <section class="actions" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
        <?php foreach ($studyPlans as $plan): ?>
            <div class="action orange" style="flex-direction: column; align-items: flex-start; height: auto; min-height: 150px; position: relative;">
                <a href="/view-study-plan/<?php echo $plan['id']; ?>" style="text-decoration: none; color: inherit; flex: 1;">
                    <h3 style="margin: 0 0 10px 0; font-size: 18px;"><?php echo htmlspecialchars($plan['title']); ?></h3>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars(substr($plan['content'], 0, 100)); ?>...
                    </p>
                </a>
                <div style="margin-top: 10px; opacity: 0.8; font-size: 0.85rem;">
                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($plan['created_at'])); ?>
                    <?php if (isset($plan['shared_count']) && $plan['shared_count'] > 0): ?>
                        | <i class="fas fa-share-alt"></i> Shared <?php echo $plan['shared_count']; ?> time(s)
                    <?php endif; ?>
                </div>
                <div class="study-plan-actions">
                    <button class="btn-sm btn-primary share-plan-btn" data-id="<?php echo $plan['id']; ?>" data-title="<?php echo htmlspecialchars($plan['title']); ?>" style="cursor: pointer;">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                    <button class="btn-sm btn-success add-reminder-to-plan" data-id="<?php echo $plan['id']; ?>" data-title="<?php echo htmlspecialchars($plan['title']); ?>" style="cursor: pointer;">
                        <i class="fas fa-bell"></i> Add Reminder
                    </button>
                    <a href="/view-study-plan/<?php echo $plan['id']; ?>" class="btn-sm btn-secondary" style="text-decoration: none;">
                        <i class="fas fa-eye"></i> View
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<!-- Share Study Plan Modal -->
<div id="share-modal-overlay" class="modal-overlay">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #1f2937;">
                <i class="fas fa-share-alt" style="color: #667eea;"></i> Share Study Plan
            </h3>
            <button id="close-share-modal" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="share-form">
            <input type="hidden" id="share-study-plan-id" name="study_plan_id">
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Study Plan</label>
                <input type="text" id="share-plan-title" readonly style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #f8fafc;">
            </div>
            <div class="form-group">
                <label for="share-recipient-username" style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Friend's Username *</label>
                <input type="text" id="share-recipient-username" name="recipient_username" placeholder="Enter username" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
            </div>
            <div class="form-group">
                <label for="share-message" style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Message (optional)</label>
                <textarea id="share-message" name="message" rows="3" placeholder="Add a personal note..." style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"></textarea>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    <i class="fas fa-paper-plane"></i> Send
                </button>
                <button type="button" id="cancel-share-btn" class="btn-secondary" style="flex: 1;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Reminder Modal -->
<div id="reminder-modal-overlay" class="modal-overlay">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #1f2937;">
                <i class="fas fa-bell" style="color: #f59e0b;"></i> Add Study Reminder
            </h3>
            <button id="close-reminder-modal" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="reminder-form">
            <input type="hidden" id="reminder-study-plan-id" name="study_plan_id">
            <div class="form-group">
                <label for="reminder-title" style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Reminder Title *</label>
                <input type="text" id="reminder-title" name="title" placeholder="e.g., Review Chapter 5" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
            </div>
            <div class="form-group">
                <label for="reminder-description" style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Description</label>
                <textarea id="reminder-description" name="description" rows="2" placeholder="What do you need to study?" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="reminder-date" style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Date *</label>
                    <input type="date" id="reminder-date" name="reminder_date" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
                </div>
                <div class="form-group">
                    <label for="reminder-time" style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Time</label>
                    <input type="time" id="reminder-time" name="reminder_time" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
                </div>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" id="reminder-recurring" name="is_recurring" style="width: auto;">
                    <span style="color: #1e293b; font-weight: 500;">Recurring reminder</span>
                </label>
            </div>
            <div class="form-group" id="recurring-pattern-group" style="display: none;">
                <label for="reminder-recurring-pattern" style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Repeat</label>
                <select id="reminder-recurring-pattern" name="recurring_pattern" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="weekdays">Weekdays (Mon-Fri)</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    <i class="fas fa-bell"></i> Create Reminder
                </button>
                <button type="button" id="cancel-reminder-btn" class="btn-secondary" style="flex: 1;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Day Detail Modal -->
<div id="day-detail-modal-overlay" class="modal-overlay">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="day-detail-date" style="margin: 0; color: #1f2937;">
                <i class="fas fa-calendar-day" style="color: #10b981;"></i> Study Schedule
            </h3>
            <button id="close-day-detail-modal" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="day-detail-reminders" style="max-height: 400px; overflow-y: auto;">
            <p style="text-align: center; color: #6b7280; padding: 20px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i><br>Loading...
            </p>
        </div>
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb; text-align: center;">
            <button id="close-day-detail-bottom" class="btn-secondary" style="padding: 10px 30px;">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Select from Scans Modal -->
<div id="scans-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb;">
            <h3 style="margin: 0; color: #1f2937; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-folder-open" style="color: #667eea;"></i> Select from My Scans
            </h3>
            <button id="close-scans-modal" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="scans-list" style="display: grid; gap: 15px;">
            <p style="text-align: center; color: #6b7280; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i><br>Loading scans...
            </p>
        </div>
        <div id="no-scans" style="display: none; text-align: center; padding: 40px; color: #6b7280;">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            <p>No saved scans yet. <a href="/scan" style="color: #667eea;">Create one first</a></p>
        </div>
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb; text-align: center;">
            <button id="close-scans-modal-bottom" class="btn-secondary" style="padding: 10px 30px;">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<script>
// Select from scans functionality
const selectFromScansBtn = document.getElementById('select-from-scans-btn');
const scansModalOverlay = document.getElementById('scans-modal-overlay');
const closeScansModal = document.getElementById('close-scans-modal');
const closeScansModalBottom = document.getElementById('close-scans-modal-bottom');
const scansList = document.getElementById('scans-list');
const noScans = document.getElementById('no-scans');

if (selectFromScansBtn) {
    selectFromScansBtn.addEventListener('click', async () => {
        scansModalOverlay.style.display = 'flex';
        await loadScans();
    });
}

if (closeScansModal) {
    closeScansModal.addEventListener('click', () => {
        scansModalOverlay.style.display = 'none';
    });
}

if (closeScansModalBottom) {
    closeScansModalBottom.addEventListener('click', () => {
        scansModalOverlay.style.display = 'none';
    });
}

scansModalOverlay.addEventListener('click', (e) => {
    if (e.target === scansModalOverlay) {
        scansModalOverlay.style.display = 'none';
    }
});

async function loadScans() {
    try {
        const response = await fetch('/api/scan-saved-list');
        const data = await response.json();
        
        if (data.success && data.files.length > 0) {
            noScans.style.display = 'none';
            scansList.innerHTML = data.files.map(file => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#f8fafc'" onclick="selectScan('${file.name}', '${file.url}')">
                    <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                        <i class="fas fa-file-pdf" style="font-size: 32px; color: #dc2626;"></i>
                        <div>
                            <h4 style="margin: 0; color: #1f2937; font-size: 15px;">${escapeHtml(file.name)}</h4>
                            <small style="color: #6b7280;">${file.size} • ${file.date}</small>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                </div>
            `).join('');
        } else {
            noScans.style.display = 'block';
            scansList.innerHTML = '';
        }
    } catch (error) {
        console.error('Error loading scans:', error);
        scansList.innerHTML = '<p style="text-align: center; color: #dc2626;">Error loading scans</p>';
    }
}

function selectScan(filename, url) {
    let hiddenInput = document.getElementById('selected_scan_file');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.id = 'selected_scan_file';
        hiddenInput.name = 'selected_scan_file';
        document.querySelector('#generate-form').appendChild(hiddenInput);
    }
    hiddenInput.value = filename;
    
    document.getElementById('file-name').textContent = filename;
    document.getElementById('file-size').textContent = 'From saved scans';
    document.getElementById('preview-section').style.display = 'block';
    document.getElementById('upload-area').style.display = 'none';
    
    scansModalOverlay.style.display = 'none';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Calendar functionality
let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth() + 1;
let calendarData = {};

function showLoading(message) {
    const overlay = document.getElementById('loadingOverlay');
    const text = overlay.querySelector('.loading-text');
    if (text) text.textContent = message;
    if (overlay) overlay.classList.add('active');
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.remove('active');
}

async function loadCalendarData(year, month) {
    try {
        const response = await fetch(`/study-plan/calendar?year=${year}&month=${month}`);
        const data = await response.json();
        if (data.success) {
            calendarData = data.calendar_data || {};
        }
    } catch (error) {
        console.error('Error loading calendar data:', error);
    }
}

function renderCalendar() {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('calendar-month-year').textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;

    const firstDay = new Date(currentYear, currentMonth - 1, 1);
    const lastDay = new Date(currentYear, currentMonth, 0);
    const startingDay = firstDay.getDay();
    const totalDays = lastDay.getDate();

    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];

    const grid = document.getElementById('calendar-grid');
    // Clear existing days (keep headers)
    while (grid.children.length > 7) {
        grid.removeChild(grid.children[7]);
    }

    // Previous month days
    const prevMonthLastDay = new Date(currentYear, currentMonth - 1, 0).getDate();
    for (let i = startingDay - 1; i >= 0; i--) {
        const day = document.createElement('div');
        day.className = 'calendar-day other-month';
        day.innerHTML = `<div class="calendar-day-number">${prevMonthLastDay - i}</div>`;
        grid.appendChild(day);
    }

    // Current month days
    for (let day = 1; day <= totalDays; day++) {
        const dayEl = document.createElement('div');
        dayEl.className = 'calendar-day';
        const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        if (dateStr === todayStr) {
            dayEl.classList.add('today');
        }

        const eventCount = calendarData[dateStr] || 0;
        if (eventCount > 0) {
            dayEl.classList.add('has-events');
        }

        dayEl.innerHTML = `
            <div class="calendar-day-number">${day}</div>
            ${eventCount > 0 ? `<span class="calendar-event-count">${eventCount}</span>` : ''}
        `;
        dayEl.addEventListener('click', () => showDayDetail(dateStr));
        grid.appendChild(dayEl);
    }

    // Next month days to fill grid (ensure 6 rows = 42 cells total)
    const totalCells = startingDay + totalDays;
    const remainingCells = 42 - totalCells;
    for (let i = 1; i <= remainingCells; i++) {
        const day = document.createElement('div');
        day.className = 'calendar-day other-month';
        day.innerHTML = `<div class="calendar-day-number">${i}</div>`;
        grid.appendChild(day);
    }
}

async function showDayDetail(dateStr) {
    const modal = document.getElementById('day-detail-modal-overlay');
    const remindersDiv = document.getElementById('day-detail-reminders');

    modal.style.display = 'flex';
    document.getElementById('day-detail-date').textContent = `Study Schedule - ${dateStr}`;

    try {
        const response = await fetch(`/study-plan/calendar?year=${dateStr.split('-')[0]}&month=${parseInt(dateStr.split('-')[1])}`);
        const data = await response.json();

        // For simplicity, show a message - in production, fetch actual reminders
        remindersDiv.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <p style="color: #6b7280;">Click "Add Reminder" to schedule study sessions for this day.</p>
                <button class="btn-primary" onclick="openReminderModal('${dateStr}')" style="margin-top: 10px;">
                    <i class="fas fa-plus"></i> Add Reminder
                </button>
            </div>
        `;
    } catch (error) {
        remindersDiv.innerHTML = '<p style="text-align: center; color: #dc2626;">Error loading reminders</p>';
    }
}

// Share functionality
const shareModalOverlay = document.getElementById('share-modal-overlay');
const closeShareModal = document.getElementById('close-share-modal');
const cancelShareBtn = document.getElementById('cancel-share-btn');
const shareForm = document.getElementById('share-form');

document.querySelectorAll('.share-plan-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const planId = this.dataset.id;
        const planTitle = this.dataset.title;
        document.getElementById('share-study-plan-id').value = planId;
        document.getElementById('share-plan-title').value = planTitle;
        shareModalOverlay.style.display = 'flex';
    });
});

if (closeShareModal) {
    closeShareModal.addEventListener('click', () => {
        shareModalOverlay.style.display = 'none';
    });
}

if (cancelShareBtn) {
    cancelShareBtn.addEventListener('click', () => {
        shareModalOverlay.style.display = 'none';
    });
}

if (shareForm) {
    shareForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        showLoading('Sharing study plan...');

        const formData = new FormData(shareForm);

        try {
            const response = await fetch('/study-plan/share', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                shareModalOverlay.style.display = 'none';
                shareForm.reset();
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Error: ' + data.error);
            }
        } catch (error) {
            alert('Error sharing study plan');
        } finally {
            hideLoading();
        }
    });
}

// Reminder functionality
const reminderModalOverlay = document.getElementById('reminder-modal-overlay');
const closeReminderModal = document.getElementById('close-reminder-modal');
const cancelReminderBtn = document.getElementById('cancel-reminder-btn');
const reminderForm = document.getElementById('reminder-form');
const reminderRecurring = document.getElementById('reminder-recurring');
const recurringPatternGroup = document.getElementById('recurring-pattern-group');

// Show/hide recurring pattern based on checkbox
if (reminderRecurring) {
    reminderRecurring.addEventListener('change', function() {
        recurringPatternGroup.style.display = this.checked ? 'block' : 'none';
    });
}

// Add reminder buttons
document.querySelectorAll('.add-reminder-to-plan').forEach(btn => {
    btn.addEventListener('click', function() {
        const planId = this.dataset.id;
        const planTitle = this.dataset.title;
        openReminderModal(null, planId, planTitle);
    });
});

document.getElementById('add-reminder-btn')?.addEventListener('click', () => {
    openReminderModal(null);
});

function openReminderModal(date = null, planId = '', planTitle = '') {
    if (planId) {
        document.getElementById('reminder-study-plan-id').value = planId;
        document.getElementById('reminder-title').value = `Study: ${planTitle}`;
    }
    if (date) {
        document.getElementById('reminder-date').value = date;
    } else {
        document.getElementById('reminder-date').value = new Date().toISOString().split('T')[0];
    }
    reminderModalOverlay.style.display = 'flex';
}

if (closeReminderModal) {
    closeReminderModal.addEventListener('click', () => {
        reminderModalOverlay.style.display = 'none';
    });
}

if (cancelReminderBtn) {
    cancelReminderBtn.addEventListener('click', () => {
        reminderModalOverlay.style.display = 'none';
    });
}

if (reminderForm) {
    reminderForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        showLoading('Creating reminder...');

        const formData = new FormData(reminderForm);

        try {
            const response = await fetch('/study-plan/reminders', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Reminder created successfully!');
                reminderModalOverlay.style.display = 'none';
                reminderForm.reset();
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Error: ' + (data.error || 'Failed to create reminder'));
            }
        } catch (error) {
            alert('Error creating reminder: ' + error.message);
        } finally {
            hideLoading();
        }
    });
}

// Complete reminder
document.querySelectorAll('.complete-reminder').forEach(btn => {
    btn.addEventListener('click', async function() {
        const reminderId = this.dataset.id;
        if (confirm('Mark this reminder as completed?')) {
            showLoading('Completing reminder...');
            try {
                const response = await fetch(`/study-plan/reminder-complete/${reminderId}`, {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
                    setTimeout(() => location.reload(), 500);
                }
            } catch (error) {
                alert('Error completing reminder');
            } finally {
                hideLoading();
            }
        }
    });
});

// Delete reminder
document.querySelectorAll('.delete-reminder').forEach(btn => {
    btn.addEventListener('click', async function() {
        const reminderId = this.dataset.id;
        if (confirm('Delete this reminder?')) {
            showLoading('Deleting reminder...');
            try {
                const response = await fetch(`/study-plan/reminder-delete/${reminderId}`, {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
                    setTimeout(() => location.reload(), 500);
                }
            } catch (error) {
                alert('Error deleting reminder');
            } finally {
                hideLoading();
            }
        }
    });
});

// Accept/Decline share
document.querySelectorAll('.accept-share').forEach(btn => {
    btn.addEventListener('click', async function() {
        const shareId = this.dataset.id;
        showLoading('Accepting share...');
        const formData = new FormData();
        formData.append('action', 'accept');
        try {
            const response = await fetch(`/study-plan/share-respond/${shareId}`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                setTimeout(() => location.reload(), 500);
            }
        } catch (error) {
            alert('Error accepting share');
        } finally {
            hideLoading();
        }
    });
});

document.querySelectorAll('.decline-share').forEach(btn => {
    btn.addEventListener('click', async function() {
        const shareId = this.dataset.id;
        showLoading('Declining share...');
        const formData = new FormData();
        formData.append('action', 'decline');
        try {
            const response = await fetch(`/study-plan/share-respond/${shareId}`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                setTimeout(() => location.reload(), 500);
            }
        } catch (error) {
            alert('Error declining share');
        } finally {
            hideLoading();
        }
    });
});

// Day detail modal
const dayDetailModal = document.getElementById('day-detail-modal-overlay');
const closeDayDetailModal = document.getElementById('close-day-detail-modal');
const closeDayDetailBottom = document.getElementById('close-day-detail-bottom');

if (closeDayDetailModal) {
    closeDayDetailModal.addEventListener('click', () => {
        dayDetailModal.style.display = 'none';
    });
}

if (closeDayDetailBottom) {
    closeDayDetailBottom.addEventListener('click', () => {
        dayDetailModal.style.display = 'none';
    });
}

// Calendar navigation
document.getElementById('prev-month')?.addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 1) {
        currentMonth = 12;
        currentYear--;
    }
    loadCalendarData(currentYear, currentMonth);
    renderCalendar();
});

document.getElementById('next-month')?.addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 12) {
        currentMonth = 1;
        currentYear++;
    }
    loadCalendarData(currentYear, currentMonth);
    renderCalendar();
});

// Close modals on outside click
window.addEventListener('click', (e) => {
    if (e.target === shareModalOverlay) shareModalOverlay.style.display = 'none';
    if (e.target === reminderModalOverlay) reminderModalOverlay.style.display = 'none';
    if (e.target === dayDetailModal) dayDetailModal.style.display = 'none';
});

// Initialize calendar
loadCalendarData(currentYear, currentMonth);
renderCalendar();

// Hide loading overlay on page load
window.addEventListener('load', () => {
    setTimeout(() => {
        hideLoading();
    }, 500);
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
