<?php
$pageTitle = "Grade $grade Scripts - StudySmart";
$currentPage = 'scripts';
include __DIR__ . '/../layouts/header.php';
?>

<div class="browse-scripts-container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <!-- Header with Back Button -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="/upload-script" style="display: inline-flex; align-items: center; gap: 8px; color: #667eea; text-decoration: none; font-weight: 500; padding: 10px 16px; background: #f0f9ff; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='#e0f2fe'" onmouseout="this.style.background='#f0f9ff'">
                <i class="fas fa-arrow-left"></i>
                Back to Upload
            </a>
            <h1 style="margin: 0; color: #1f2937; font-size: 28px;">
                <i class="fas fa-graduation-cap" style="color: #667eea;"></i>
                Grade <?php echo htmlspecialchars($grade); ?> Scripts
            </h1>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <select id="subject-filter" style="padding: 10px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; background: white; cursor: pointer; min-width: 180px;" onchange="filterScripts()">
                <option value="">All Subjects</option>
                <?php foreach ($subjects as $subj): ?>
                    <option value="<?php echo htmlspecialchars($subj['subject']); ?>"><?php echo htmlspecialchars($subj['subject']); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="year-filter" style="padding: 10px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; background: white; cursor: pointer; min-width: 120px;" onchange="filterScripts()">
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

    <!-- Scripts Grid -->
    <div id="scripts-grid" class="scripts-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php if (empty($scripts)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: 12px;">
                <i class="fas fa-inbox" style="font-size: 64px; color: #d1d5db; margin-bottom: 20px; display: block;"></i>
                <h3 style="color: #6b7280; margin-bottom: 10px;">No Scripts Available</h3>
                <p style="color: #9ca3af;">No study scripts have been uploaded for Grade <?php echo htmlspecialchars($grade); ?> yet. Check back later!</p>
            </div>
        <?php else: ?>
            <?php foreach ($scripts as $script): ?>
                <div class="script-card" data-subject="<?php echo htmlspecialchars($script['subject']); ?>" data-year="<?php echo htmlspecialchars($script['year'] ?? ''); ?>" style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e5e7eb; transition: all 0.2s; display: flex; flex-direction: column;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.1)';this.style.borderColor='#667eea'" onmouseout="this.style.boxShadow='none';this.style.borderColor='#e5e7eb'">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 8px 0; color: #1f2937; font-size: 18px; line-height: 1.4;"><?php echo htmlspecialchars($script['title']); ?></h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
                                <span class="badge" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #dbeafe; color: #1e40af; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                    <i class="fas fa-book"></i>
                                    <?php echo htmlspecialchars($script['subject']); ?>
                                </span>
                                <span class="badge" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #fef3c7; color: #92400e; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                    <i class="fas fa-graduation-cap"></i>
                                    Grade <?php echo htmlspecialchars($script['grade_level']); ?>
                                </span>
                                <?php if (!empty($script['year'])): ?>
                                    <span class="badge" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #e0e7ff; color: #3730a3; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo htmlspecialchars($script['year']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($script['paper'])): ?>
                                    <span class="badge" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #fce7f3; color: #9d174d; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        <i class="fas fa-file-alt"></i>
                                        Paper <?php echo htmlspecialchars($script['paper']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($script['memorandum_file_path'])): ?>
                                    <span class="badge" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #d1fae5; color: #065f46; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        <i class="fas fa-check-circle"></i>
                                        Memo Included
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <i class="fas fa-file-alt" style="font-size: 32px; color: #667eea; flex-shrink: 0; margin-left: 15px;"></i>
                    </div>
                    
                    <div style="margin-top: auto; padding-top: 15px; border-top: 1px solid #e5e7eb; display: flex; gap: 10px;">
                        <a href="/view-script/<?php echo $script['id']; ?>" target="_blank" class="btn-view" style="flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.opacity='0.9';this.style.transform='translateY(-2px)'" onmouseout="this.style.opacity='1';this.style.transform='translateY(0)'">
                            <i class="fas fa-eye"></i>
                            View
                        </a>
                        <?php if (!empty($script['memorandum_file_path'])): ?>
                            <a href="/download-memorandum/<?php echo $script['id']; ?>" class="btn-memo" style="flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.opacity='0.9';this.style.transform='translateY(-2px)'" onmouseout="this.style.opacity='1';this.style.transform='translateY(0)'">
                                <i class="fas fa-file-download"></i>
                                Memo
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top: 12px; font-size: 12px; color: #9ca3af; text-align: center;">
                        <i class="fas fa-calendar"></i>
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
    const scriptCards = document.querySelectorAll('.script-card');

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

<style>
.browse-scripts-container {
    padding: 20px;
}

.script-card {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.badge {
    white-space: nowrap;
}

@media (max-width: 768px) {
    .browse-scripts-container {
        padding: 10px;
    }
    
    .scripts-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
