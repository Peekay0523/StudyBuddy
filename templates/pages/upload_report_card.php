<?php
$pageTitle = 'Upload Report Card - StudySmart';
$currentPage = 'careers';
$extraScripts = '<script>
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
            <div class="form-group">
                <label for="report_card_file">Report Card File (PDF, DOCX, JPG, PNG)</label>
                <input type="file" id="report_card_file" name="report_card_file" accept=".pdf,.docx,.jpg,.jpeg,.png" required>
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
