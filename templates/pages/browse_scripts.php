<?php
$pageTitle = "Grade $grade Scripts - StudySmart";
$currentPage = 'scripts';
include __DIR__ . '/../layouts/header.php';
?>

<style>
.browse-scripts-container {
    padding: 20px;
}

/* modern dropdown styling */
.filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    padding: 2px 8px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.filter-group:focus-within {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.filter-group i {
    color: #94a3b8;
    font-size: 14px;
}

.styled-select {
    padding: 10px 8px;
    border: none;
    background: transparent;
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    outline: none;
    min-width: 150px;
}

/* Header & Back Button */
.back-btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #7c3aed;
    text-decoration: none;
    font-weight: 700;
    padding: 10px 18px;
    background: #f5f3ff;
    border-radius: 10px;
    transition: all 0.2s;
    font-size: 14px;
}

.back-btn-modern:hover {
    background: #ede9fe;
    transform: translateX(-3px);
}

/* Script Card Enhancements */
.script-card-modern {
    background: white;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    animation: fadeIn 0.3s ease-in;
}

.script-card-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #7c3aed;
    opacity: 0;
    transition: opacity 0.2s;
}

.script-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px -8px rgba(102, 126, 234, 0.2);
    border-color: #cbd5e1;
}

.script-card-modern:hover::before {
    opacity: 1;
}

.script-card-title {
    margin: 0 0 12px 0;
    color: #1e293b;
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.4;
}

.badge-modern {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .browse-scripts-container { padding: 10px; }
    .scripts-grid { grid-template-columns: 1fr !important; }
    .filter-group { width: 100%; }
    .styled-select { flex: 1; }
}
</style>

<div class="browse-scripts-container" style="max-width: 1200px; margin: 0 auto;">
    <!-- Header with Back Button -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 35px; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="/upload-script" class="back-btn-modern">
                <i class="fas fa-arrow-left"></i>
                Back to Upload
            </a>
            <h1 style="margin: 0; color: #1e293b; font-size: 26px; font-weight: 800;">
                <i class="fas fa-graduation-cap" style="color: #7c3aed; margin-right: 8px;"></i>
                Grade <?php echo htmlspecialchars($grade); ?> Scripts
            </h1>
        </div>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div class="filter-group">
                <i class="fas fa-book-open"></i>
                <select id="subject-filter" class="styled-select" onchange="filterScripts()">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subj): ?>
                        <option value="<?php echo htmlspecialchars($subj['subject']); ?>"><?php echo htmlspecialchars($subj['subject']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <i class="fas fa-calendar-day"></i>
                <select id="year-filter" class="styled-select" onchange="filterScripts()">
                    <option value="">All Years</option>
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="2021">2021</option>
                    <option value="2020">2020</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Scripts Grid -->
    <div id="scripts-grid" class="scripts-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
        <?php if (empty($scripts)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 80px 20px; background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 20px;">
                <i class="fas fa-inbox" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px; display: block;"></i>
                <h3 style="color: #64748b; margin-bottom: 10px; font-weight: 700;">No Scripts Available</h3>
                <p style="color: #94a3b8; max-width: 400px; margin: 0 auto;">No study scripts have been uploaded for Grade <?php echo htmlspecialchars($grade); ?> yet. Check back later!</p>
            </div>
        <?php else: ?>
            <?php foreach ($scripts as $script): ?>
                <div class="script-card-modern" data-subject="<?php echo htmlspecialchars($script['subject']); ?>" data-year="<?php echo htmlspecialchars($script['year'] ?? ''); ?>">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 18px;">
                        <div style="flex: 1;">
                            <h3 class="script-card-title"><?php echo htmlspecialchars($script['title']); ?></h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <span class="badge-modern" style="background: #f5f3ff; color: #7c3aed;">
                                    <i class="fas fa-book"></i> <?php echo htmlspecialchars($script['subject']); ?>
                                </span>
                                <span class="badge-modern" style="background: #fff7ed; color: #ea580c;">
                                    <i class="fas fa-graduation-cap"></i> Grade <?php echo htmlspecialchars($script['grade_level']); ?>
                                </span>
                                <?php if (!empty($script['year'])): ?>
                                    <span class="badge-modern" style="background: #eff6ff; color: #2563eb;">
                                        <i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($script['year']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($script['paper'])): ?>
                                    <span class="badge-modern" style="background: #fdf2f8; color: #db2777;">
                                        <i class="fas fa-file-alt"></i> P<?php echo htmlspecialchars($script['paper']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($script['memorandum_file_path'])): ?>
                                    <span class="badge-modern" style="background: #f0fdf4; color: #16a34a;">
                                        <i class="fas fa-check-circle"></i> Memo
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="width: 40px; height: 40px; background: #f8fafc; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-left: 15px;">
                            <i class="fas fa-file-pdf" style="font-size: 20px; color: #94a3b8;"></i>
                        </div>
                    </div>
                    
                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; gap: 12px;">
                        <a href="/view-script/<?php echo $script['id']; ?>" target="_blank" style="flex: 1.5; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); color: white; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 14px; transition: all 0.2s;">
                            <i class="fas fa-eye"></i> View Script
                        </a>
                        <?php if (!empty($script['memorandum_file_path'])): ?>
                            <a href="/download-memorandum/<?php echo $script['id']; ?>" style="flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; background: #f8fafc; color: #475569; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 14px; border: 1px solid #e2e8f0; transition: all 0.2s;">
                                <i class="fas fa-file-download"></i> Memo
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top: 15px; font-size: 11px; color: #94a3b8; display: flex; align-items: center; gap: 5px; justify-content: center;">
                        <i class="fas fa-clock-rotate-left"></i>
                        Uploaded <?php echo date('M d, Y', strtotime($script['uploaded_at'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function filterScripts() {
    const selectedSubject = document.getElementById('subject-filter').value;
    const selectedYear = document.getElementById('year-filter').value;
    const scriptCards = document.querySelectorAll('.script-card-modern');

    scriptCards.forEach(card => {
        const cardSubject = card.getAttribute('data-subject');
        const cardYear = card.getAttribute('data-year');

        const matchesSubject = !selectedSubject || cardSubject === selectedSubject;
        const matchesYear = !selectedYear || cardYear === selectedYear;

        if (matchesSubject && matchesYear) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
