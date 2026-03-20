<?php
$pageTitle = 'Upload Script - StudySmart';
$currentPage = 'scripts';
$canGenerateMemorandum = $canGenerateMemorandum ?? false;
$canGenerateMemorandumJson = json_encode($canGenerateMemorandum);
$extraHead = <<<'SCRIPT'
<script>
// Subscription plan check for memorandum generation
const CAN_GENERATE_MEMORANDUM = <?php echo $canGenerateMemorandumJson; ?>;

document.addEventListener("DOMContentLoaded", function() {
const uploadArea = document.getElementById("upload-area");
const fileInput = document.getElementById("script_file");
const previewSection = document.getElementById("preview-section");
const fileName = document.getElementById("file-name");
const fileSize = document.getElementById("file-size");
const clearBtn = document.getElementById("clear-btn");

console.log('Upload script page loaded');
console.log('uploadArea:', uploadArea);
console.log('fileInput:', fileInput);

// Click to upload
if (uploadArea && fileInput) {
    uploadArea.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('Upload area clicked, triggering file input click');
        
        // Create a temporary file input and trigger it
        const tempInput = document.createElement('input');
        tempInput.type = 'file';
        tempInput.accept = fileInput.accept;
        tempInput.style.display = 'none';
        tempInput.id = 'temp-file-input';
        tempInput.name = 'script_file';
        
        tempInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileInput.files = this.files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
            tempInput.remove();
        });
        
        document.body.appendChild(tempInput);
        tempInput.click();
    });
} else {
    console.error('Upload area or file input not found!');
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
        console.log('File input change event. Files:', e.target.files.length);
        if (e.target.files && e.target.files[0]) {
            handleFile(e.target.files[0]);
        }
    });
}

function handleFile(file) {
    console.log('handleFile called with:', file.name, file.type, file.size);
    
    const allowedTypes = ["application/pdf", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "text/plain"];

    if (!allowedTypes.includes(file.type) && !file.name.endsWith(".pdf") && !file.name.endsWith(".docx") && !file.name.endsWith(".txt")) {
        alert("Invalid file type. Please upload PDF, DOCX, or TXT files.");
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        alert("File size must be less than 10MB.");
        return;
    }

    if (fileName && fileSize && previewSection && uploadArea && fileInput) {
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + " MB";
        previewSection.style.display = "block";
        uploadArea.style.display = "none";
        
        // Create a DataTransfer and set the file to the input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        console.log('File set to input successfully. Files count:', fileInput.files.length);
    } else {
        console.error('Missing elements:', {
            fileName: !!fileName,
            fileSize: !!fileSize,
            previewSection: !!previewSection,
            uploadArea: !!uploadArea,
            fileInput: !!fileInput
        });
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
    console.log('generateMemorandum called with scriptId:', scriptId);
    console.log('CAN_GENERATE_MEMORANDUM:', CAN_GENERATE_MEMORANDUM);

    // Check if user is on free plan
    if (!CAN_GENERATE_MEMORANDUM) {
        alert('Memorandum generation is only available for Basic and Premium subscribers. Please upgrade your plan to use this feature.');
        window.location.href = '/subscription';
        return;
    }

    const btn = document.querySelector(`.gen-memo-btn[data-script-id="${scriptId}"]`);
    const resultDiv = document.getElementById(`memo-result-${scriptId}`);

    if (!btn || !resultDiv) {
        console.error('Button or result div not found for script:', scriptId);
        return;
    }

    btn.disabled = true;
    btn.innerHTML = "<i class=\"fas fa-spinner fa-spin\"></i> Generating...";
    resultDiv.innerHTML = "";

    try {
        const formData = new FormData();
        formData.append("script_id", scriptId);

        console.log('Sending request to /api/generate-memorandum...');

        const response = await fetch("/api/generate-memorandum", {
            method: "POST",
            body: formData
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
            throw new Error(`Server responded with status ${response.status}`);
        }

        const data = await response.json();
        console.log('Response data:', data);

        if (data.success) {
            resultDiv.innerHTML = `
                <div class="memorandum-card" style="margin-top: 15px; padding: 20px; background: #f0f9ff; border-radius: 8px; border: 1px solid #bae6fd;">
                    <div class="memo-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0; color: #0369a1; font-size: 16px;"><i class="fas fa-file-alt"></i> Generated Memorandum</h3>
                        <div class="memo-actions" style="display: flex; gap: 8px;">
                            <button data-action="view-memorandum" data-script-id="${scriptId}" class="btn-view-memo" title="View" style="background: #0ea5e9; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button data-action="download-memorandum" data-script-id="${scriptId}" class="btn-download-memo" title="Download PDF" style="background: #0ea5e9; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="memo-content" style="background: white; padding: 15px; border-radius: 6px; max-height: 400px; overflow-y: auto;">
                        <p style="margin: 0; line-height: 1.6;">${data.memorandum.replace(new RegExp('\\n', 'g'), "<br>")}</p>
                    </div>
                </div>
            `;
            
            // Attach event listeners to view and download buttons
            const viewBtn = resultDiv.querySelector('.btn-view-memo');
            const downloadBtn = resultDiv.querySelector('.btn-download-memo');
            
            if (viewBtn) {
                viewBtn.addEventListener('click', function() {
                    viewMemorandum(scriptId);
                });
            }
            
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function() {
                    showDownloadFormatModal(scriptId);
                });
            }

            // Change button to "View Memorandum" to prevent overuse of AI
            btn.innerHTML = "<i class=\"fas fa-eye\"></i> View Memorandum";
            btn.classList.remove('btn-sm-primary');
            btn.classList.add('btn-sm-secondary');
            btn.onclick = function() {
                viewMemorandum(scriptId);
            };
        } else {
            resultDiv.innerHTML = `<div class="alert alert-error" style="margin-top: 15px; padding: 12px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 6px; color: #dc2626;">Error: ${data.error || "Failed to generate memorandum"}</div>`;
            btn.disabled = false;
        }
    } catch (error) {
        console.error('Error generating memorandum:', error);
        resultDiv.innerHTML = `<div class="alert alert-error" style="margin-top: 15px; padding: 12px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 6px; color: #dc2626;">Error: Failed to connect to server. ${error.message}</div>`;
        btn.disabled = false;
    }
}

function showDownloadFormatModal(scriptId) {
    const modal = document.getElementById('download-format-modal');
    if (modal) {
        modal.style.display = 'flex';
        
        // Set up download buttons
        document.getElementById('download-pdf-btn').onclick = function() {
            window.location.href = '/download-memorandum/' + scriptId + '?format=pdf';
            modal.style.display = 'none';
        };
        
        document.getElementById('download-docx-btn').onclick = function() {
            window.location.href = '/download-memorandum/' + scriptId + '?format=docx';
            modal.style.display = 'none';
        };
    }
}

function closeDownloadFormatModal() {
    const modal = document.getElementById('download-format-modal');
    if (modal) {
        modal.style.display = 'none';
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

        console.log('Loaded scripts:', data);
        console.log('CAN_GENERATE_MEMORANDUM:', CAN_GENERATE_MEMORANDUM);

        if (data.scripts && data.scripts.length > 0) {
            const scriptsList = document.getElementById("scripts-list");
            if (scriptsList) {
                scriptsList.innerHTML = data.scripts.map(script => {
                    const hasMemorandum = script.has_memorandum == 1;
                    let buttonHtml = '';
                    
                    if (hasMemorandum) {
                        // Already has memorandum - show view button
                        buttonHtml = `<button data-script-id="${script.id}" class="view-memo-btn btn-sm btn-sm-secondary" style="cursor: pointer;">
                            <i class="fas fa-eye"></i> View Memorandum
                           </button>`;
                    } else if (CAN_GENERATE_MEMORANDUM) {
                        // Paid user - show generate button
                        buttonHtml = `<button data-script-id="${script.id}" class="gen-memo-btn btn-sm btn-sm-primary">
                            <i class="fas fa-magic"></i> Generate Memorandum
                           </button>`;
                    } else {
                        // Free user - show locked button with upgrade tooltip
                        buttonHtml = `<button data-script-id="${script.id}" class="gen-memo-btn btn-sm btn-sm-secondary" style="cursor: not-allowed; opacity: 0.7;" title="Upgrade to Basic or Premium to generate memorandums">
                            <i class="fas fa-lock"></i> Upgrade to Generate
                           </button>`;
                    }

                    return `
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
                                ${buttonHtml}
                                <form method="POST" action="/delete-script/${script.id}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this script?');">
                                    <button type="submit" class="btn-sm btn-sm-danger" style="cursor: pointer;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                            <div id="memo-result-${script.id}" class="memo-result"></div>
                        </div>
                    `;
                }).join("");

                // Attach event listeners to generate memorandum buttons
                document.querySelectorAll('.gen-memo-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        // Skip if this is a locked button (free user)
                        if (this.style.cursor === 'not-allowed') {
                            alert('Memorandum generation is only available for Basic and Premium subscribers. Please upgrade your plan to use this feature.');
                            window.location.href = '/subscription';
                            return;
                        }
                        
                        const scriptId = this.getAttribute('data-script-id');
                        generateMemorandum(scriptId);
                    });
                });

                // Attach event listeners to view memorandum buttons
                document.querySelectorAll('.view-memo-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const scriptId = this.getAttribute('data-script-id');
                        viewMemorandum(scriptId);
                    });
                });
            }
        } else {
            const scriptsList = document.getElementById("scripts-list");
            if (scriptsList) {
                scriptsList.innerHTML = '<p class="no-scripts">No scripts uploaded yet. Upload your first script above.</p>';
            }
        }
    } catch (error) {
        console.error("Failed to load scripts:", error);
        const scriptsList = document.getElementById("scripts-list");
        if (scriptsList) {
            scriptsList.innerHTML = '<p class="error">Failed to load scripts. Please refresh the page.</p>';
        }
    }
}
});
</script>
SCRIPT;
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Upload Script</h1>
<p class="subtitle">Upload your study script for AI analysis and memorandum generation.</p>

<?php if (!$canGenerateMemorandum): ?>
    <div class="alert alert-info" style="padding: 12px 16px; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 1px solid #fdba74; border-radius: 8px; color: #9a3412; margin-bottom: 20px;">
        <i class="fas fa-lock" style="margin-right: 8px;"></i>
        <strong>Memorandum generation is locked.</strong> 
        Upgrade to <a href="/subscription" style="color: #c2410c; text-decoration: underline; font-weight: 600;">Basic</a> or <a href="/subscription" style="color: #c2410c; text-decoration: underline; font-weight: 600;">Premium</a> to unlock AI-powered memorandum generation for your scripts.
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="upload-container" style="display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start;">
    <!-- Left Column: Upload Form -->
    <div class="auth-box" style="max-width: 100%;">
        <form method="post" action="/upload-script" enctype="multipart/form-data" id="upload-script-form">
            <!-- Drag & Drop File Input -->
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Script File (PDF, DOCX, or TXT)</label>
                <div class="upload-area" id="upload-area" style="border: 3px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.3s ease; user-select: none;" onmouseover="this.style.borderColor='#667eea';this.style.background='#e0e7ff'" onmouseout="this.style.borderColor='#cbd5e1';this.style.background='#f8fafc'">
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

    <!-- Right Column: Browse Scripts by Grade Card -->
    <div class="browse-grade-card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; position: sticky; top: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #1f2937; display: flex; align-items: center; gap: 10px; font-size: 18px;">
            <i class="fas fa-graduation-cap" style="color: #667eea;"></i> Browse Scripts by Grade
        </h3>
        <p style="color: #6b7280; font-size: 13px; margin-bottom: 20px; line-height: 1.5;">Select your grade to view available scripts and study materials uploaded by your school.</p>
        <div class="grade-links" style="display: grid; gap: 10px;">
            <a href="/browse-scripts/8" class="grade-link-btn" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 2px solid #bae6fd; border-radius: 8px; color: #0369a1; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100)';this.style.borderColor='#7dd3fc';this.style.transform='translateX(5px)'" onmouseout="this.style.background='linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100)';this.style.borderColor='#bae6fd';this.style.transform='translateX(0)'">
                <span><i class="fas fa-chevron-right" style="font-size: 12px; margin-right: 8px;"></i> Grade 8</span>
                <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
            </a>
            <a href="/browse-scripts/9" class="grade-link-btn" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #86efac; border-radius: 8px; color: #15803d; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='linear-gradient(135deg, #dcfce7 0%, #86efac 100)';this.style.borderColor='#4ade80';this.style.transform='translateX(5px)'" onmouseout="this.style.background='linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100)';this.style.borderColor='#86efac';this.style.transform='translateX(0)'">
                <span><i class="fas fa-chevron-right" style="font-size: 12px; margin-right: 8px;"></i> Grade 9</span>
                <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
            </a>
            <a href="/browse-scripts/10" class="grade-link-btn" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); border: 2px solid #fde047; border-radius: 8px; color: #a16207; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='linear-gradient(135deg, #fef08a 0%, #fde047 100)';this.style.borderColor='#facc15';this.style.transform='translateX(5px)'" onmouseout="this.style.background='linear-gradient(135deg, #fef9c3 0%, #fef08a 100)';this.style.borderColor='#fde047';this.style.transform='translateX(0)'">
                <span><i class="fas fa-chevron-right" style="font-size: 12px; margin-right: 8px;"></i> Grade 10</span>
                <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
            </a>
            <a href="/browse-scripts/11" class="grade-link-btn" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%); border: 2px solid #fb923c; border-radius: 8px; color: #c2410c; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='linear-gradient(135deg, #fdba74 0%, #fb923c 100)';this.style.borderColor='#fb7185';this.style.transform='translateX(5px)'" onmouseout="this.style.background='linear-gradient(135deg, #fed7aa 0%, #fdba74 100)';this.style.borderColor='#fb923c';this.style.transform='translateX(0)'">
                <span><i class="fas fa-chevron-right" style="font-size: 12px; margin-right: 8px;"></i> Grade 11</span>
                <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
            </a>
            <a href="/browse-scripts/12" class="grade-link-btn" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); border: 2px solid #c4b5fd; border-radius: 8px; color: #6d28d9; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100)';this.style.borderColor='#a78bfa';this.style.transform='translateX(5px)'" onmouseout="this.style.background='linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100)';this.style.borderColor='#c4b5fd';this.style.transform='translateX(0)'">
                <span><i class="fas fa-chevron-right" style="font-size: 12px; margin-right: 8px;"></i> Grade 12</span>
                <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
            </a>
        </div>
    </div>
</div>

<!-- Your Uploaded Scripts Section (Full Width Below) -->
<div class="scripts-section" style="margin-top: 40px;">
    <h2 class="section-title" style="font-size: 24px; margin-bottom: 20px; color: #1f2937; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-file-alt"></i> Your Uploaded Scripts
    </h2>
    <div id="scripts-list" class="scripts-list">
        <p class="loading">Loading scripts...</p>
    </div>
</div>

<style>
/* Responsive layout for upload page */
@media (max-width: 1024px) {
    .upload-container {
        grid-template-columns: 1fr !important;
    }
    
    .browse-grade-card {
        position: static !important;
    }
}

@media (max-width: 768px) {
    .browse-grade-card {
        padding: 20px !important;
    }
    
    .grade-link-btn {
        padding: 10px 14px !important;
        font-size: 13px !important;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
// Form validation - check if file is selected before submit
const form = document.getElementById('upload-script-form');
if (form) {
    form.addEventListener('submit', function(e) {
        const fileInput = document.getElementById('script_file');
        const selectedScanInput = document.getElementById('selected_scan_file');

        // Check if either a file is selected OR a scan is selected
        const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
        const hasScan = selectedScanInput && selectedScanInput.value && selectedScanInput.value.trim() !== '';

        console.log('Form submit triggered');
        console.log('Has file:', hasFile, 'Files count:', fileInput?.files?.length);
        console.log('Has scan:', hasScan, 'Scan value:', selectedScanInput?.value);

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
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        }
    });
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
            <p><strong>No saved scans yet.</strong></p>
            <p style="font-size: 13px; margin-top: 10px;">
                To use a scan here, you need to save it first:<br>
                1. Go to <a href="/scan" style="color: #667eea; font-weight: 500;">Scan Documents</a><br>
                2. Upload your document images<br>
                3. Click "Save as PDF" and give it a name<br>
                4. Then come back here to select it
            </p>
        </div>
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb; text-align: center;">
            <button id="close-scans-modal-bottom" class="btn-secondary" style="padding: 10px 30px;">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
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

        console.log('Scans API response:', data);

        if (data.success && data.files && data.files.length > 0) {
            noScans.style.display = 'none';
            scansList.innerHTML = data.files.map((file, index) => `
                <div class="scan-item" data-filename="${escapeHtml(file.name)}" data-fileurl="${escapeHtml(file.url)}" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s;">
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

            // Add click handlers to all scan items
            document.querySelectorAll('.scan-item').forEach(item => {
                item.addEventListener('click', function() {
                    const filename = this.getAttribute('data-filename');
                    const url = this.getAttribute('data-fileurl');
                    selectScan(filename, url);
                });

                // Add hover effects
                item.addEventListener('mouseenter', function() {
                    this.style.background = '#eff6ff';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.background = '#f8fafc';
                });
            });
        } else {
            noScans.style.display = 'block';
            scansList.innerHTML = '';
            // Show helpful message about saving scans first
            noScans.innerHTML = `
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                <p><strong>No saved scans found.</strong></p>
                <p style="font-size: 13px; margin-top: 10px;">
                    To use a scan here, you need to save it first:<br>
                    1. Go to <a href="/scan" style="color: #667eea; font-weight: 500;">Scan Documents</a><br>
                    2. Upload your document images<br>
                    3. Click "Save as PDF" and give it a name<br>
                    4. Then come back here to select it
                </p>
            `;
        }
    } catch (error) {
        console.error('Error loading scans:', error);
        scansList.innerHTML = '<p style="text-align: center; color: #dc2626;">Error loading scans. Please refresh the page.</p>';
    }
}

function selectScan(filename, url) {
    console.log('selectScan called with:', filename, url);

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
    const fileNameEl = document.getElementById('file-name');
    const fileSizeEl = document.getElementById('file-size');
    const previewSection = document.getElementById('preview-section');
    const uploadArea = document.getElementById('upload-area');

    if (fileNameEl && fileSizeEl && previewSection && uploadArea) {
        fileNameEl.textContent = filename;
        fileSizeEl.textContent = 'From saved scans';
        previewSection.style.display = 'block';
        uploadArea.style.display = 'none';

        scansModalOverlay.style.display = 'none';
        console.log('Scan selected successfully:', filename);
    } else {
        console.error('Preview elements not found');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close download format modal when clicking outside
const downloadModal = document.getElementById('download-format-modal');
if (downloadModal) {
    downloadModal.addEventListener('click', function(e) {
        if (e.target === downloadModal) {
            closeDownloadFormatModal();
        }
    });
}
});
</script>

<!-- Download Format Selection Modal -->
<div id="download-format-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb;">
            <h3 style="margin: 0; color: #1f2937; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-download" style="color: #667eea;"></i> Download Memorandum
            </h3>
            <button onclick="closeDownloadFormatModal()" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div style="text-align: center; padding: 20px 0;">
            <p style="color: #6b7280; margin-bottom: 20px;">Choose your preferred format:</p>
            <div style="display: grid; gap: 15px; max-width: 300px; margin: 0 auto;">
                <button id="download-pdf-btn" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(102,126,234,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                    <i class="fas fa-file-pdf"></i> PDF Format
                </button>
                <button id="download-docx-btn" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 30px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(40,167,69,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                    <i class="fas fa-file-word"></i> DOCX Format
                </button>
            </div>
        </div>
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb; text-align: center;">
            <button onclick="closeDownloadFormatModal()" class="btn-secondary" style="padding: 10px 30px; cursor: pointer;">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
