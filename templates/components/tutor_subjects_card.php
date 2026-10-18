<!-- ===== Editable Subjects You Teach Card ===== -->
<section class="td-section td-cx" id="subjectsYouTeachSection" tabindex="0" aria-expanded="false">
    <div class="td-cx-top">
    <div class="td-section-head">
        <span class="td-section-icon alt-green"><i class="fas fa-book-open"></i></span>
        <div class="td-section-text">
            <h2>Subjects You Teach</h2>
            <p>
                The subjects and grade levels you offer to students. Click the card to edit grades or add/remove subjects.
                <?php if (!empty($tutorProfile['grades_offered'])): ?>
                    <br><strong>Overall grades/levels offered:</strong> <span id="overallGradesDisplay"><?php echo htmlspecialchars($tutorProfile['grades_offered']); ?></span>
                <?php endif; ?>
            </p>
        </div>
        <button type="button" id="editSubjectsBtn" onclick="enterSubjectsEditMode()" class="td-btn td-btn-save" style="margin-left:auto;align-self:center;">
            <i class="fas fa-pen"></i> Edit Subjects
        </button>
    </div>
        <span class="td-cx-chev"><i class="fas fa-chevron-down"></i></span>
    </div><!-- /.td-cx-top -->
    <div class="td-cx-body"><div class="td-cx-inner">

    <!-- View mode -->
    <div id="subjectsViewMode">
        <?php if (empty($subjects)): ?>
            <div class="td-empty">
                <div class="td-empty-icon" style="color:#10b981;background:rgba(16,185,129,0.12);"><i class="fas fa-book"></i></div>
                <h3>No subjects listed yet</h3>
                <p>Click "Edit Subjects" to add the subjects and grades you teach.</p>
            </div>
        <?php else: ?>
            <div class="td-card td-subjects-card" style="cursor:pointer;" onclick="enterSubjectsEditMode()" title="Click to edit subjects and grades">
                <div class="td-subjects-grid">
                    <?php foreach ($subjects as $subj):
                        $grade = '';
                        $displaySubject = $subj;
                        if (preg_match('/\(([^)]+)\)/', $subj, $m)) {
                            $grade = $m[1];
                            $displaySubject = trim(preg_replace('/\s*\([^)]+\)/', '', $subj));
                        } elseif (preg_match('/-\s*(Grade\s*\d+(?:-\d+)?)/i', $subj, $m)) {
                            $grade = $m[1];
                            $displaySubject = trim(preg_replace('/-\s*Grade\s*\d+(?:-\d+)?/i', '', $subj));
                        }
                    ?>
                        <div class="td-subject-item">
                            <span class="td-subject-icon"><i class="fas fa-graduation-cap"></i></span>
                            <div class="td-subject-details">
                                <p class="td-subject-name"><?php echo htmlspecialchars($displaySubject); ?></p>
                                <?php if ($grade): ?>
                                    <p class="td-subject-grade"><i class="fas fa-layer-group" style="margin-right:4px;"></i><?php echo htmlspecialchars($grade); ?></p>
                                <?php else: ?>
                                    <p class="td-subject-grade">All grades</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit mode -->
    <div id="subjectsEditMode" style="display:none;">
        <div class="td-card" style="padding:22px;">
            <div id="subjectsEditList" style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px;">
                <!-- Rows injected by JS -->
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-size:13.5px;font-weight:600;color:#334155;">Overall Grades / Levels Offered</label>
                <input type="text" id="subjectsGradesOffered" value="<?php echo htmlspecialchars($tutorProfile['grades_offered'] ?? ''); ?>" placeholder="e.g. Grade 10-12, University first year"
                       style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
                <small style="color:#94a3b8;display:block;margin-top:5px;">Shown across your tutor card and public profile.</small>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;">
                <button type="button" onclick="addSubjectRow()" class="td-btn td-btn-save" style="background:#fff;color:#4f46e5;border:1.5px solid #c7d2fe;box-shadow:none;">
                    <i class="fas fa-plus"></i> Add Subject
                </button>
                <div style="display:flex;gap:10px;">
                    <button type="button" onclick="cancelSubjectsEdit()" class="td-btn td-btn-reject">
                        Cancel
                    </button>
                    <button type="button" onclick="saveSubjects()" class="td-btn td-btn-save">
                        <i class="fas fa-save"></i> Save Subjects
                    </button>
                </div>
            </div>
            <div id="subjectsEditMessage" style="margin-top:12px;font-size:13px;font-weight:600;min-height:20px;"></div>
        </div>
    </div>

    </div></div><!-- /.td-cx-body -->
</section>

<script>
(function() {
    const initialSubjects = <?php echo json_encode(array_values($subjects)); ?>;
    const initialGradesOffered = <?php echo json_encode($tutorProfile['grades_offered'] ?? ''); ?>;

    function parseSubjectGrade(raw) {
        let name = raw;
        let grade = '';
        if (raw.match(/\(([^)]+)\)/)) {
            grade = raw.match(/\(([^)]+)\)/)[1];
            name = raw.replace(/\s*\([^)]+\)/, '').trim();
        } else if (raw.match(/-\s*(Grade\s*\d+(?:-\d+)?)/i)) {
            const m = raw.match(/-\s*(Grade\s*\d+(?:-\d+)?)/i);
            grade = m[1];
            name = raw.replace(/-\s*Grade\s*\d+(?:-\d+)?/i, '').trim();
        }
        return { name, grade };
    }

    window.enterSubjectsEditMode = function() {
        if (window.expandTdSection) { window.expandTdSection('subjectsYouTeachSection'); }
        document.getElementById('subjectsViewMode').style.display = 'none';
        document.getElementById('subjectsEditMode').style.display = 'block';
        document.getElementById('editSubjectsBtn').style.display = 'none';

        const list = document.getElementById('subjectsEditList');
        list.innerHTML = '';

        if (initialSubjects.length === 0) {
            addSubjectRow();
        } else {
            initialSubjects.forEach(subj => {
                const parsed = parseSubjectGrade(subj);
                addSubjectRow(parsed.name, parsed.grade || 'All grades');
            });
        }
        document.getElementById('subjectsGradesOffered').value = initialGradesOffered;
    };

    window.cancelSubjectsEdit = function() {
        document.getElementById('subjectsEditMode').style.display = 'none';
        document.getElementById('subjectsViewMode').style.display = 'block';
        document.getElementById('editSubjectsBtn').style.display = 'inline-flex';
        document.getElementById('subjectsEditMessage').textContent = '';
    };

    window.addSubjectRow = function(name, grade) {
        const list = document.getElementById('subjectsEditList');
        const row = document.createElement('div');
        row.className = 'subject-edit-row';
        row.style = 'display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:flex-start;background:#f8fafc;border:1px solid var(--sg-line);border-radius:14px;padding:14px;';
        const selected = (val) => grade === val ? 'selected' : '';
        row.innerHTML = `
            <div>
                <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#334155;">Subject</label>
                <input type="text" class="subject-name" value="${name ? name.replace(/"/g, '&quot;') : ''}" placeholder="e.g. Mathematics" style="width:100%;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;">
            </div>
            <div>
                <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#334155;">Grade / Level</label>
                <select class="subject-grade" style="width:100%;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;color:#334155;background:#fff;">
                    <option value="All grades" ${selected('All grades')}>All grades</option>
                    <option value="Grade 1-3" ${selected('Grade 1-3')}>Grade 1-3</option>
                    <option value="Grade 4-6" ${selected('Grade 4-6')}>Grade 4-6</option>
                    <option value="Grade 7-9" ${selected('Grade 7-9')}>Grade 7-9</option>
                    <option value="Grade 10" ${selected('Grade 10')}>Grade 10</option>
                    <option value="Grade 11" ${selected('Grade 11')}>Grade 11</option>
                    <option value="Grade 12" ${selected('Grade 12')}>Grade 12</option>
                    <option value="University/College" ${selected('University/College')}>University/College</option>
                    <option value="Other" ${selected('Other')}>Other</option>
                </select>
            </div>
            <div style="padding-top:22px;">
                <button type="button" onclick="removeSubjectRow(this)" class="td-btn td-btn-remove" style="min-height:36px;padding:8px 12px;"><i class="fas fa-trash"></i></button>
            </div>
        `;
        list.appendChild(row);
    };

    window.removeSubjectRow = function(btn) {
        btn.closest('.subject-edit-row').remove();
    };

    window.saveSubjects = function() {
        const rows = document.querySelectorAll('.subject-edit-row');
        const subjects = [];
        rows.forEach(row => {
            const name = row.querySelector('.subject-name').value.trim();
            const grade = row.querySelector('.subject-grade').value.trim();
            if (name) {
                subjects.push({ name, grade });
            }
        });
        const gradesOffered = document.getElementById('subjectsGradesOffered').value.trim();

        const msg = document.getElementById('subjectsEditMessage');
        msg.textContent = 'Saving...';
        msg.style.color = '#64748b';

        fetch('/tutor/update-subjects', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ subjects: subjects, gradesOffered: gradesOffered })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                msg.textContent = 'Subjects saved. Reloading...';
                msg.style.color = '#16a34a';
                setTimeout(() => window.location.reload(), 700);
            } else {
                msg.textContent = data.message || 'Could not save subjects.';
                msg.style.color = '#dc2626';
            }
        })
        .catch(err => {
            msg.textContent = 'Error: ' + err.message;
            msg.style.color = '#dc2626';
        });
    };
})();
</script>
