<?php
$pageTitle = 'Upload Report Card - StudySmart';
$currentPage = 'careers';
$extraScripts = '<script>
const uploadArea = document.getElementById("upload-area");
const fileInput = document.getElementById("report_card_file");
const previewSection = document.getElementById("preview-section");
const fileName = document.getElementById("file-name");
const fileSize = document.getElementById("file-size");
const clearBtn = document.getElementById("clear-btn");

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
    const allowedTypes = ["application/pdf", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "image/jpeg", "image/jpg", "image/png"];
    
    if (!allowedTypes.includes(file.type)) {
        alert("Invalid file type. Please upload PDF, DOCX, JPG, or PNG.");
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
});

async function loadUploadedReportCards() {
    try {
        const response = await fetch("/api/get-user-report-cards");
        const data = await response.json();

        const reportCardsList = document.getElementById("report-cards-list");
        if (reportCardsList) {
            if (data.report_cards && data.report_cards.length > 0) {
                reportCardsList.innerHTML = data.report_cards.map(rc => `
                    <div class="report-card-item">
                        <div class="report-card-info">
                            <h4>Report Card - ${rc.grade ? "Grade " + rc.grade : ""} ${rc.term ? "Term " + rc.term : ""}</h4>
                            <p class="report-card-meta">
                                <span class="badge blue">${rc.file_path}</span>
                                <span class="badge green">${new Date(rc.uploaded_at).toLocaleDateString()}</span>
                            </p>
                        </div>
                        <div class="report-card-actions">
                            <a href="/view-career-recommendations/${rc.id}" class="btn-primary btn-sm">
                                <i class="fas fa-compass"></i> View Career Recommendations
                            </a>
                        </div>
                    </div>
                `).join("");
            } else {
                reportCardsList.innerHTML = "<p class=\"no-data\">No report cards uploaded yet. Upload your first report card to get career recommendations!</p>";
            }
        }
    } catch (error) {
        console.error("Failed to load report cards:", error);
    }
}

document.addEventListener("DOMContentLoaded", function() {
    loadUploadedReportCards();
});
</script>';
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Upload Report Card</h1>
<p class="subtitle">Upload your report card to get AI-powered career guidance, course recommendations, and bursary opportunities.</p>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="upload-container">
    <div class="auth-box" style="max-width: 600px;">
        <form method="post" action="/upload-report-card" enctype="multipart/form-data">
            <!-- Drag & Drop File Input -->
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Report Card File (PDF, DOCX, JPG, PNG)</label>
                <div class="upload-area" id="upload-area" style="border: 3px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.3s ease;">
                    <input type="file" id="report_card_file" name="report_card_file" accept=".pdf,.docx,.jpg,.jpeg,.png" style="display: none;" required>
                    <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: #667eea; margin-bottom: 15px;"></i>
                    <h4 style="margin: 0 0 8px 0; color: #1e293b; font-size: 15px;">Click or drag file to upload</h4>
                    <p style="margin: 0; color: #64748b; font-size: 13px;">PDF, DOCX, JPG, PNG (Max 10MB)</p>
                </div>
                <div id="preview-section" style="display: none; margin-top: 15px; padding: 15px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-file-pdf" style="font-size: 28px; color: #ef4444;"></i>
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

            <div class="form-group">
                <label for="grade">Grade Level</label>
                <select id="grade" name="grade" required>
                    <option value="">Select Grade</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
            </div>

            <div class="form-group">
                <label for="term">Term</label>
                <select id="term" name="term" required>
                    <option value="">Select Term</option>
                    <option value="1">Term 1</option>
                    <option value="2">Term 2</option>
                    <option value="3">Term 3</option>
                    <option value="4">Term 4</option>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                <i class="fas fa-upload"></i> Upload Report Card
            </button>
        </form>
    </div>

    <div class="report-cards-section">
        <h2 class="section-title"><i class="fas fa-file-alt"></i> Your Uploaded Report Cards</h2>
        <div id="report-cards-list" class="report-cards-list">
            <p class="loading">Loading report cards...</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
