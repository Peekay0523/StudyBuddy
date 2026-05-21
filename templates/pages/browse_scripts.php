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

/* Custom Select Styles */
.custom-select-wrapper {
    position: relative;
    user-select: none;
    min-width: 180px;
}

.custom-select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    color: #475569;
    font-weight: 600;
    transition: all 0.2s;
}

.custom-select-trigger:hover {
    border-color: #cbd5e1;
    background-color: #f8fafc;
}

.custom-select-wrapper.open .custom-select-trigger {
    border-color: #7c3aed;
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.custom-select-trigger::after {
    content: '\f078';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 12px;
    color: #94a3b8;
    transition: transform 0.2s;
}

.custom-select-wrapper.open .custom-select-trigger::after {
    transform: rotate(180deg);
}

.custom-options {
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    z-index: 100;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    max-height: 250px;
    overflow-y: auto;
}

.custom-select-wrapper.open .custom-options {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.custom-option {
    padding: 10px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background 0.2s;
    font-size: 14px;
    color: #4b5563;
}

.custom-option:hover {
    background: #f1f5f9;
    color: #7c3aed;
}

.custom-option.selected {
    background: #f5f3ff;
    color: #7c3aed;
    font-weight: 600;
}

.custom-option i {
    width: 20px;
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
}

.custom-option.selected i {
    color: #7c3aed;
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
            <div class="custom-select-wrapper" id="subject-select">
                <input type="hidden" id="subject-filter" value="">
                <div class="custom-select-trigger">
                    <span><i class="fas fa-book-open"></i> All Subjects</span>
                </div>
                <div class="custom-options">
                    <div class="custom-option selected" data-value="">
                        <i class="fas fa-book-open"></i> All Subjects
                    </div>
                    <?php foreach ($subjects as $subj): ?>
                        <div class="custom-option" data-value="<?php echo htmlspecialchars($subj['subject']); ?>">
                            <i class="fas fa-book"></i> <?php echo htmlspecialchars($subj['subject']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="custom-select-wrapper" id="year-select">
                <input type="hidden" id="year-filter" value="">
                <div class="custom-select-trigger">
                    <span><i class="fas fa-calendar-day"></i> All Years</span>
                </div>
                <div class="custom-options">
                    <div class="custom-option selected" data-value="">
                        <i class="fas fa-calendar-day"></i> All Years
                    </div>
                    <?php 
                    $years = ['2026', '2025', '2024', '2023', '2022', '2021', '2020'];
                    foreach ($years as $year): ?>
                        <div class="custom-option" data-value="<?php echo $year; ?>">
                            <i class="fas fa-calendar-alt"></i> <?php echo $year; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
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
document.addEventListener("DOMContentLoaded", function() {
    // Custom Select Functionality
    const customWrappers = document.querySelectorAll('.custom-select-wrapper');
    customWrappers.forEach(wrapper => {
        const trigger = wrapper.querySelector('.custom-select-trigger');
        const options = wrapper.querySelectorAll('.custom-option');
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        
        trigger.addEventListener('click', (e) => {
            customWrappers.forEach(other => {
                if (other !== wrapper) other.classList.remove('open');
            });
            wrapper.classList.toggle('open');
            e.stopPropagation();
        });
        
        options.forEach(option => {
            option.addEventListener('click', () => {
                const value = option.getAttribute('data-value');
                const content = option.innerHTML;
                
                hiddenInput.value = value;
                trigger.querySelector('span').innerHTML = content;
                
                options.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                
                wrapper.classList.remove('open');
                
                // Trigger filter
                filterScripts();
            });
        });
    });

    document.addEventListener('click', () => {
        customWrappers.forEach(wrapper => wrapper.classList.remove('open'));
    });
});

function filterScripts() {
    const selectedSubject = document.getElementById('subject-filter').value;
    const selectedYear = document.getElementById('year-filter').value;
    const scriptCards = document.querySelectorAll('.script-card-modern');

    let visibleCount = 0;
    scriptCards.forEach(card => {
        const cardSubject = card.getAttribute('data-subject');
        const cardYear = card.getAttribute('data-year');

        const matchesSubject = !selectedSubject || cardSubject === selectedSubject;
        const matchesYear = !selectedYear || cardYear === selectedYear;

        if (matchesSubject && matchesYear) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Handle no results message if needed
    const grid = document.getElementById('scripts-grid');
    const noResults = document.getElementById('no-filter-results');
    
    if (visibleCount === 0 && scriptCards.length > 0) {
        if (!noResults) {
            const msg = document.createElement('div');
            msg.id = 'no-filter-results';
            msg.style.cssText = 'grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #94a3b8;';
            msg.innerHTML = '<i class="fas fa-search" style="font-size: 40px; margin-bottom: 15px; display: block; color: #e2e8f0;"></i><p>No scripts match your filter selection.</p>';
            grid.appendChild(msg);
        }
    } else if (noResults) {
        noResults.remove();
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

        }
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
