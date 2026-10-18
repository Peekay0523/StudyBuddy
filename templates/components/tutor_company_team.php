<!-- ===== Our Tutors (Company Team Management) ===== -->
<section class="td-section td-cx" tabindex="0" aria-expanded="false">
    <div class="td-cx-top">
    <div class="td-section-head">
        <span class="td-section-icon alt-rose"><i class="fas fa-users"></i></span>
        <div class="td-section-text">
            <h2>Our Tutors</h2>
            <p>Add, edit or remove the tutors in your company, their qualifications, subjects and grades. This appears on your public tutor profile.</p>
        </div>
    </div>
        <span class="td-cx-chev"><i class="fas fa-chevron-down"></i></span>
    </div><!-- /.td-cx-top -->
    <div class="td-cx-body"><div class="td-cx-inner">

    <div class="td-card" style="padding:22px;">
        <div id="companyTeamList" style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px;">
            <?php foreach ($companyTeam as $index => $member): ?>
                <div class="company-team-row" data-index="<?php echo $index; ?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr)) auto;gap:10px;align-items:flex-start;background:#f8fafc;border:1px solid var(--sg-line);border-radius:14px;padding:14px;">
                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#334155;">Name</label>
                        <input type="text" class="team-name" value="<?php echo htmlspecialchars($member['name']); ?>" placeholder="e.g. Thabo Mokoena" style="width:100%;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#334155;">Qualifications</label>
                        <input type="text" class="team-qualifications" value="<?php echo htmlspecialchars($member['qualifications']); ?>" placeholder="e.g. BSc Mathematics" style="width:100%;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#334155;">Subjects</label>
                        <input type="text" class="team-subjects" value="<?php echo htmlspecialchars($member['subjects']); ?>" placeholder="e.g. Maths, Physical Science" style="width:100%;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#334155;">Grades</label>
                        <input type="text" class="team-grades" value="<?php echo htmlspecialchars($member['grades']); ?>" placeholder="e.g. Grade 10-12" style="width:100%;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;">
                    </div>
                    <div style="padding-top:22px;">
                        <button type="button" onclick="removeTeamRow(this)" class="td-btn td-btn-remove" style="min-height:36px;padding:8px 12px;"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;">
            <button type="button" onclick="addTeamRow()" class="td-btn td-btn-save" style="background:#fff;color:#4f46e5;border:1.5px solid #c7d2fe;box-shadow:none;">
                <i class="fas fa-plus"></i> Add Tutor
            </button>
            <button type="button" onclick="saveCompanyTeam()" class="td-btn td-btn-save">
                <i class="fas fa-save"></i> Save Tutors
            </button>
        </div>
        <div id="companyTeamMessage" style="margin-top:12px;font-size:13px;font-weight:600;min-height:20px;"></div>
    </div>

    </div></div><!-- /.td-cx-body -->
</section>

<script>
function addTeamRow() {
    const list = document.getElementById('companyTeamList');
    const index = list.children.length;
    const row = document.createElement('div');
    row.className = 'company-team-row';
    row.dataset.index = index;
    row.style = 'display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr)) auto;gap:10px;align-items:flex-start;background:#f8fafc;border:1px solid var(--sg-line);border-radius:14px;padding:14px;';
    row.innerHTML = `
        <div>
            <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#334155;">Name</label>
            <input type="text" class="team-name" value="" placeholder="e.g. Thabo Mokoena" style="width:100%;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;">
        </div>
        <div>
            <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#334155;">Qualifications</label>
            <input type="text" class="team-qualifications" value="" placeholder="e.g. BSc Mathematics" style="width:100%;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;">
        </div>
        <div>
            <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#334155;">Subjects</label>
            <input type="text" class="team-subjects" value="" placeholder="e.g. Maths, Physical Science" style="width:100%;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;">
        </div>
        <div>
            <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#334155;">Grades</label>
            <input type="text" class="team-grades" value="" placeholder="e.g. Grade 10-12" style="width:100%;padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:inherit;">
        </div>
        <div style="padding-top:22px;">
            <button type="button" onclick="removeTeamRow(this)" class="td-btn td-btn-remove" style="min-height:36px;padding:8px 12px;"><i class="fas fa-trash"></i></button>
        </div>
    `;
    list.appendChild(row);
}

function removeTeamRow(btn) {
    btn.closest('.company-team-row').remove();
}

function saveCompanyTeam() {
    const rows = document.querySelectorAll('.company-team-row');
    const team = [];
    rows.forEach(row => {
        const name = row.querySelector('.team-name').value.trim();
        const qualifications = row.querySelector('.team-qualifications').value.trim();
        const subjects = row.querySelector('.team-subjects').value.trim();
        const grades = row.querySelector('.team-grades').value.trim();
        if (name) {
            team.push({ name, qualifications, subjects, grades });
        }
    });

    const msg = document.getElementById('companyTeamMessage');
    msg.textContent = 'Saving...';
    msg.style.color = '#64748b';

    fetch('/tutor/update-company-team', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ team: team })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            msg.textContent = 'Tutors saved successfully.';
            msg.style.color = '#16a34a';
        } else {
            msg.textContent = data.message || 'Could not save tutors.';
            msg.style.color = '#dc2626';
        }
    })
    .catch(err => {
        msg.textContent = 'Error: ' + err.message;
        msg.style.color = '#dc2626';
    });
}
</script>
