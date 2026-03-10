<?php
$pageTitle = 'Upload Script - StudySmart';
$currentPage = 'scripts';
$extraScripts = '<script>
const uploadArea = document.getElementById("upload-area");
const fileInput = document.getElementById("script_file");
const previewSection = document.getElementById("preview-section");
const fileName = document.getElementById("file-name");
const fileSize = document.getElementById("file-size");
const clearBtn = document.getElementById("clear-btn");

// Click to upload
if (uploadArea && fileInput) {
    uploadArea.addEventListener("click", () => {
        fileInput.click();
    });
}

// Drag and drop
if (uploadArea) {
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
}

// File input change
if (fileInput) {
    fileInput.addEventListener("change", (e) => {
        if (e.target.files && e.target.files[0]) {
            handleFile(e.target.files[0]);
        }
    });
}

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

    if (fileName && fileSize && previewSection && uploadArea) {
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + " MB";
        previewSection.style.display = "block";
        uploadArea.style.display = "none";
    }
}

// Clear selection
if (clearBtn && fileInput && previewSection && uploadArea) {
    clearBtn.addEventListener("click", () => {
        fileInput.value = "";
        previewSection.style.display = "none";
        uploadArea.style.display = "block";
    });
}

async function generateMemorandum(scriptId) {
    const btn = document.getElementById("gen-memo-btn-" + scriptId);
    const resultDiv = document.getElementById("memo-result-" + scriptId);

    if (!btn || !resultDiv) return;

    btn.disabled = true;
    btn.innerHTML = "<i class=\"fas fa-spinner fa-spin\"></i> Generating...";
    resultDiv.innerHTML = "";

    try {
        const formData = new FormData();
        formData.append("script_id", scriptId);

        const response = await fetch("/api/generate-memorandum", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            resultDiv.innerHTML = `
                <div class="memorandum-card">
                    <div class="memo-header">
                        <h3><i class="fas fa-file-alt"></i> Generated Memorandum</h3>
                        <div class="memo-actions">
                            <button onclick="viewMemorandum(${scriptId})" class="btn-icon btn-view" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="downloadMemorandum(${scriptId})" class="btn-icon btn-download" title="Download PDF">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="memo-content">
                        <p>${data.memorandum.replace(/\\n/g, "<br>")}</p>
                    </div>
                </div>
            `;
            btn.innerHTML = "<i class=\"fas fa-redo\"></i> Regenerate";
            btn.disabled = false;
        } else {
            resultDiv.innerHTML = `<div class="alert alert-error">Error: ${data.error || "Failed to generate memorandum"}</div>`;
            btn.disabled = false;
        }
    } catch (error) {
        resultDiv.innerHTML = `<div class="alert alert-error">Error: Failed to connect to server</div>`;
        btn.disabled = false;
    }
}

function viewMemorandum(scriptId) {
    window.open("/view-memorandum/" + scriptId, "_blank");
}

function downloadMemorandum(scriptId) {
    window.location.href = "/download-memorandum/" + scriptId;
}

// Load existing scripts on page load
loadUploadedScripts();

async function loadUploadedScripts() {
    try {
        const response = await fetch("/api/get-user-scripts");
        const data = await response.json();

        if (data.scripts && data.scripts.length > 0) {
            const scriptsList = document.getElementById("scripts-list");
            if (scriptsList) {
                scriptsList.innerHTML = data.scripts.map(script => `
                    <div class="script-item">
                        <div class="script-info">
                            <h4>${script.title}</h4>
                            <p class="script-meta">
                                <span class="badge blue">${script.subject || 'No subject'}</span>
                                <span class="badge orange">Grade ${script.grade_level || '-'}</span>
                                <span class="badge green">${new Date(script.uploaded_at).toLocaleDateString()}</span>
                            </p>
                        </div>
                        <div class="script-actions">
                            <button id="gen-memo-btn-${script.id}" onclick="generateMemorandum(${script.id})" class="btn-primary btn-sm">
                                <i class="fas fa-magic"></i> Generate Memorandum
                            </button>
                            <form method="POST" action="/delete-script/${script.id}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this script?');">
                                <button type="submit" class="btn-sm btn-sm-danger" style="cursor: pointer;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                        <div id="memo-result-${script.id}" class="memo-result"></div>
                    </div>
                `).join("");
            }
        }
    } catch (error) {
        console.error("Failed to load scripts:", error);
    }
}
</script>';
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Upload Script</h1>
<p class="subtitle">Upload your study script for AI analysis and memorandum generation.</p>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="upload-container">
    <div class="auth-box" style="max-width: 600px;">
        <form method="post" action="/upload-script" enctype="multipart/form-data">
            <!-- Drag & Drop File Input -->
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Script File (PDF, DOCX, or TXT)</label>
                <div class="upload-area" id="upload-area" style="border: 3px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.3s ease;">
                    <input type="file" id="script_file" name="script_file" accept=".pdf,.docx,.txt" style="display: none;">
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

            <div class="form-group">
                <label for="title">Title (optional)</label>
                <input type="text" id="title" name="title" placeholder="Enter a title for your script">
            </div>

            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" placeholder="e.g., Mathematics, Physics, History" required>
            </div>

            <div class="form-group">
                <label for="grade_level">Grade Level</label>
                <select id="grade_level" name="grade_level" required>
                    <option value="">Select Grade</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Upload Script</button>
        </form>
    </div>

    <div class="scripts-section">
        <h2 class="section-title"><i class="fas fa-file-alt"></i> Your Uploaded Scripts</h2>
        <div id="scripts-list" class="scripts-list">
            <p class="loading">Loading scripts...</p>
        </div>
    </div>
</div>

<script>
// Form validation - check if file is selected before submit
document.querySelector('form')?.addEventListener('submit', function(e) {
    const fileInput = document.getElementById('script_file');
    const selectedScanInput = document.getElementById('selected_scan_file');

    // Check if either a file is selected OR a scan is selected
    const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
    const hasScan = selectedScanInput && selectedScanInput.value && selectedScanInput.value.trim() !== '';

    if (!hasFile && !hasScan) {
        e.preventDefault();
        alert('Please select a file to upload. Click or drag a file into the upload area, or use "Select from My Scans" button.');
        const uploadArea = document.getElementById('upload-area');
        if (uploadArea) {
            uploadArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
            uploadArea.style.borderColor = '#ef4444';
            uploadArea.style.background = '#fef2f2';
            setTimeout(() => {
                uploadArea.style.borderColor = '#cbd5e1';
                uploadArea.style.background = '#f8fafc';
            }, 2000);
        }
        return false;
    }
});
</script>

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
    // Create a hidden input to store the selected scan
    let hiddenInput = document.getElementById('selected_scan_file');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.id = 'selected_scan_file';
        hiddenInput.name = 'selected_scan_file';
        document.querySelector('form').appendChild(hiddenInput);
    }
    hiddenInput.value = filename;

    // Update preview
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
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
