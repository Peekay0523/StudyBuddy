<?php
$pageTitle = 'Upload Script - StudySmart';
$currentPage = 'scripts';
$extraScripts = '<script>
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
document.addEventListener("DOMContentLoaded", function() {
    loadUploadedScripts();
});

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
                                <span class="badge blue">${script.subject}</span>
                                <span class="badge orange">Grade ${script.grade_level}</span>
                                <span class="badge green">${new Date(script.uploaded_at).toLocaleDateString()}</span>
                            </p>
                        </div>
                        <div class="script-actions">
                            <button id="gen-memo-btn-${script.id}" onclick="generateMemorandum(${script.id})" class="btn-primary btn-sm">
                                <i class="fas fa-magic"></i> Generate Memorandum
                            </button>
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
            <div class="form-group">
                <label for="script_file">Script File (PDF, DOCX, or TXT)</label>
                <input type="file" id="script_file" name="script_file" accept=".pdf,.docx,.txt" required>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
