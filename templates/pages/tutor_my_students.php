<?php
/**
 * My Students page (tutor only).
 * Expects: $tutorProfile, $gradeCards (label => ['students'=>[], 'announcements'=>[]]),
 *          $unspecified (students without a grade), $generalAnnouncements, $user
 */
$pageTitle = 'My Students - StudySmart';
$currentPage = 'tutor-dashboard';
include __DIR__ . '/../layouts/header.php';

$gradeColors = [
    'Grade 8'  => ['#0ea5e9', '#2563eb'],
    'Grade 9'  => ['#10b981', '#059669'],
    'Grade 10' => ['#f59e0b', '#f97316'],
    'Grade 11' => ['#667eea', '#764ba2'],
    'Grade 12' => ['#f43f5e', '#ec4899'],
];

$studentCount = 0;
foreach ($gradeCards as $grp) $studentCount += count($grp['students']);
$studentCount += count($unspecified);
?>

<div class="sg-page">
<style>
/* ===== My Students page — scoped premium styles ===== */
.sg-page{
    --sg-brand:#667eea; --sg-brand-2:#764ba2;
    --sg-ink:#1e293b; --sg-muted:#64748b; --sg-faint:#94a3b8; --sg-line:#eef2f7;
    --sg-radius:20px; --sg-shadow:0 10px 30px rgba(30,41,59,.06);
    background:
        radial-gradient(1200px 500px at 100% -10%, rgba(118,75,162,.06), transparent 60%),
        radial-gradient(1000px 500px at -10% 0%, rgba(102,126,234,.07), transparent 55%),
        linear-gradient(180deg,#f8faff 0%,#f5f7fb 100%);
    padding:6px 6px 40px; border-radius:24px; color:var(--sg-ink);
}
.sg-page .ms-title{font-size:30px;margin:0;letter-spacing:-.02em;}
.sg-page .ms-subtitle{color:var(--sg-muted);font-size:15px;margin:6px 0 0;max-width:640px;}
.sg-page .ms-back{display:inline-flex;align-items:center;gap:8px;color:#4f46e5;font-size:13.5px;font-weight:600;text-decoration:none;margin-bottom:16px;padding:8px 14px;border-radius:12px;background:#fff;border:1.5px solid #c7d2fe;transition:transform .2s ease,box-shadow .2s ease;}
.sg-page .ms-back:hover{transform:translateY(-2px);box-shadow:0 10px 22px rgba(102,126,234,.18);}
.sg-page .ms-header{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin:8px 0 28px;}
.sg-page .ms-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(16,185,129,.12);color:#047857;font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;padding:6px 12px;border-radius:999px;margin-bottom:12px;}
.sg-page .ms-grid{display:grid;grid-template-columns:1fr;gap:22px;}
.sg-page .ms-grade-card{background:#fff;border:1px solid var(--sg-line);border-radius:var(--sg-radius);box-shadow:var(--sg-shadow);overflow:hidden;}
.sg-page .ms-grade-head{display:flex;align-items:center;gap:14px;padding:20px 22px;border-bottom:1px solid var(--sg-line);}
.sg-page .ms-grade-icon{width:48px;height:48px;border-radius:14px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;box-shadow:0 8px 18px rgba(30,41,59,.18);}
.sg-page .ms-grade-head h3{font-size:18px;margin:0;color:var(--sg-ink);letter-spacing:-.01em;}
.sg-page .ms-grade-head p{font-size:12.5px;color:var(--sg-muted);margin:3px 0 0;}
.sg-page .ms-grade-count{margin-left:auto;background:rgba(102,126,234,.10);color:#4f46e5;font-weight:700;font-size:13px;padding:6px 14px;border-radius:999px;border:1px solid rgba(102,126,234,.25);white-space:nowrap;}
.sg-page .ms-students{padding:14px 22px 6px;display:flex;flex-direction:column;gap:10px;}
.sg-page .ms-student{display:flex;align-items:center;gap:12px;background:#f8fafc;border:1px solid var(--sg-line);border-radius:14px;padding:12px 14px;transition:border-color .2s ease,background .2s ease;}
.sg-page .ms-student:hover{border-color:#c7d2fe;background:#f5f7ff;}
.sg-page .ms-student-avatar{width:38px;height:38px;border-radius:50%;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;flex-shrink:0;}
.sg-page .ms-student-name{font-size:14.5px;font-weight:700;color:var(--sg-ink);margin:0;}
.sg-page .ms-student-sub{font-size:12px;color:var(--sg-muted);margin:2px 0 0;display:flex;flex-wrap:wrap;gap:10px;}
.sg-page .ms-student-sub span{display:inline-flex;align-items:center;gap:5px;}
.sg-page .ms-badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 10px;border-radius:999px;border:1px solid transparent;white-space:nowrap;}
.sg-page .ms-badge.online{background:rgba(59,130,246,.10);color:#1d4ed8;border-color:rgba(59,130,246,.25);}
.sg-page .ms-badge.contact{background:rgba(16,185,129,.10);color:#047857;border-color:rgba(16,185,129,.25);}
.sg-page .ms-badge.extra{background:rgba(245,158,11,.10);color:#b45309;border-color:rgba(245,158,11,.30);}
.sg-page .ms-ann-block{padding:16px 22px 22px;border-top:1px solid var(--sg-line);margin-top:14px;background:#fbfcfe;}
.sg-page .ms-ann-head{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:12px;}
.sg-page .ms-ann-head h4{font-size:14.5px;margin:0;color:var(--sg-ink);display:inline-flex;align-items:center;gap:8px;}
.sg-page .ms-ann-toggle{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;border:none;cursor:pointer;padding:9px 16px;border-radius:11px;font-size:13px;font-weight:600;font-family:inherit;box-shadow:0 8px 18px rgba(102,126,234,.30);transition:transform .2s ease,filter .2s ease;}
.sg-page .ms-ann-toggle:hover{transform:translateY(-2px);filter:brightness(1.05);}
.sg-page .ms-ann-form{display:none;background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:18px;margin-bottom:16px;}
.sg-page .ms-ann-form.open{display:block;}
.sg-page .ms-ann-list{display:flex;flex-direction:column;gap:10px;}
.sg-page .ms-ann-item{display:flex;gap:12px;align-items:flex-start;background:#fff;border:1px solid var(--sg-line);border-radius:14px;padding:14px 16px;}
.sg-page .ms-ann-icon{width:38px;height:38px;border-radius:11px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:15px;}
.sg-page .ms-ann-item h5{font-size:14px;margin:0 0 3px;color:var(--sg-ink);}
.sg-page .ms-ann-item p{font-size:12.5px;color:var(--sg-muted);margin:0 0 6px;line-height:1.55;}
.sg-page .ms-ann-meta{display:flex;flex-wrap:wrap;gap:8px;font-size:11.5px;font-weight:600;align-items:center;}
.sg-page .ms-ann-meta .pill{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;}
.sg-page .ms-ann-del{margin-left:auto;flex-shrink:0;background:#fff1f2;color:#be123c;border:1px solid #fecdd3;border-radius:10px;padding:7px 12px;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .2s ease;}
.sg-page .ms-ann-del:hover{background:#ffe4e6;}
.sg-page .ms-form-label{display:block;margin-bottom:6px;font-size:12.5px;font-weight:600;color:#334155;}
.sg-page .ms-form-input{width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-family:inherit;color:#334155;background:#fff;box-sizing:border-box;}
.sg-page .ms-type-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;}
.sg-page .ms-type-opt{display:flex;align-items:center;gap:10px;border:1.5px solid #e2e8f0;border-radius:12px;padding:12px 14px;cursor:pointer;transition:border-color .2s ease,background .2s ease;}
.sg-page .ms-type-opt:hover{border-color:#c7d2fe;background:#f8fafc;}
.sg-page .ms-type-opt input{margin:0;}
.sg-page .ms-type-opt.selected{border-color:#667eea;background:#eef2ff;}
.sg-page .ms-type-opt strong{font-size:13.5px;color:var(--sg-ink);display:block;}
.sg-page .ms-type-opt small{font-size:11.5px;color:var(--sg-muted);}
.sg-page .ms-empty{background:#fff;border:1.5px dashed #e2e8f0;border-radius:var(--sg-radius);padding:48px 28px;text-align:center;}
.sg-page .ms-empty-icon{width:64px;height:64px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:14px;}
.sg-page .ms-empty h3{font-size:17px;margin:0 0 8px;color:var(--sg-ink);}
.sg-page .ms-empty p{color:var(--sg-muted);font-size:13.5px;margin:0;}
@media (max-width:640px){
    .sg-page .ms-type-row{grid-template-columns:1fr;}
    .sg-page .ms-grade-head{flex-wrap:wrap;}
    .sg-page .ms-grade-count{margin-left:0;}
}
</style>

<a href="/tutor-dashboard" class="ms-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

<div class="ms-header">
    <div>
        <span class="ms-eyebrow"><i class="fas fa-user-graduate"></i> My Students</span>
        <h1 class="ms-title">My Students</h1>
        <p class="ms-subtitle">Your active students, organised by grade. Post announcements for each grade — online session links or contact lesson dates — and your learners see them on their Tutors page.</p>
    </div>
</div>

<?php if ($studentCount === 0): ?>
    <div class="ms-empty">
        <div class="ms-empty-icon" style="color:#10b981;background:rgba(16,185,129,0.12);"><i class="fas fa-user-friends"></i></div>
        <h3>No active students yet</h3>
        <p>Approve pending requests on your dashboard to start building your roster.</p>
    </div>
<?php else: ?>
<div class="ms-grid">
    <?php foreach ($gradeCards as $gradeLabel => $grp):
        $colors = $gradeColors[$gradeLabel] ?? ['#667eea', '#764ba2'];
        $students = $grp['students'];
        $announcements = $grp['announcements'];
        $annCount = count($announcements) + count($generalAnnouncements);
        $formId = 'annForm_' . preg_replace('/[^A-Za-z0-9]/', '_', $gradeLabel);
    ?>
    <div class="ms-grade-card">
        <div class="ms-grade-head">
            <span class="ms-grade-icon" style="background:linear-gradient(135deg,<?php echo $colors[0]; ?>,<?php echo $colors[1]; ?>);"><i class="fas fa-layer-group"></i></span>
            <div>
                <h3><?php echo htmlspecialchars($gradeLabel); ?></h3>
                <p>Students and announcements for this grade</p>
            </div>
            <span class="ms-grade-count"><i class="fas fa-user-graduate"></i> <?php echo count($students); ?> student<?php echo count($students) === 1 ? '' : 's'; ?></span>
        </div>

        <?php if (count($students) > 0): ?>
        <div class="ms-students">
            <?php foreach ($students as $stu):
                $initial = strtoupper(mb_substr($stu['username'] ?? 'S', 0, 1));
                $mode = ($stu['teaching_mode'] ?? 'online') === 'contact' ? 'contact' : 'online';
            ?>
            <div class="ms-student">
                <span class="ms-student-avatar" style="background:linear-gradient(135deg,<?php echo $colors[0]; ?>,<?php echo $colors[1]; ?>);"><?php echo htmlspecialchars($initial); ?></span>
                <div style="min-width:0;flex:1;">
                    <p class="ms-student-name"><?php echo htmlspecialchars($stu['username'] ?? 'Student'); ?></p>
                    <div class="ms-student-sub">
                        <?php if (!empty($stu['subjects'])): ?><span><i class="fas fa-book" style="color:#1d4ed8;"></i> <?php echo htmlspecialchars($stu['subjects']); ?></span><?php endif; ?>
                        <?php if (!empty($stu['schedule'])): ?><span><i class="fas fa-clock" style="color:#047857;"></i> <?php echo ucfirst(htmlspecialchars($stu['schedule'])); ?></span><?php endif; ?>
                        <?php if (!empty($stu['email'])): ?><span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($stu['email']); ?></span><?php endif; ?>
                    </div>
                </div>
                <span class="ms-badge <?php echo $mode; ?>"><i class="fas <?php echo $mode === 'online' ? 'fa-video' : 'fa-handshake'; ?>"></i> <?php echo $mode === 'online' ? 'Online' : 'Contact'; ?></span>
                <?php if (!empty($stu['extra_classes'])): ?><span class="ms-badge extra"><i class="fas fa-plus-circle"></i> Extra</span><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="ms-ann-block">
            <div class="ms-ann-head">
                <h4><i class="fas fa-bullhorn" style="color:#667eea;"></i> Announcements for this grade</h4>
                <button type="button" class="ms-ann-toggle" onclick="toggleAnnForm('<?php echo $formId; ?>')"><i class="fas fa-plus"></i> New Announcement</button>
            </div>

            <form id="<?php echo $formId; ?>" class="ms-ann-form" method="POST" action="/tutor/announcements/create">
                <input type="hidden" name="grade" value="<?php echo htmlspecialchars($gradeLabel); ?>">

                <div class="ms-type-row">
                    <label class="ms-type-opt selected" onclick="pickType(this)">
                        <input type="radio" name="type" value="online" checked onchange="pickType(this)">
                        <span><strong><i class="fas fa-video" style="color:#1d4ed8;"></i> Online Session</strong><small>Share a link (Zoom, Meet, YouTube…)</small></span>
                    </label>
                    <label class="ms-type-opt" onclick="pickType(this)">
                        <input type="radio" name="type" value="contact" onchange="pickType(this)">
                        <span><strong><i class="fas fa-handshake" style="color:#047857;"></i> Contact Lesson</strong><small>Provide a date for in-person lessons</small></span>
                    </label>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="ms-form-label">Title <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="title" class="ms-form-input" maxlength="120" placeholder="e.g., Maths Online Session — Trigonometry" required>
                </div>

                <div class="ms-online-fields">
                    <div style="margin-bottom:14px;">
                        <label class="ms-form-label">Session Link <span style="color:#ef4444;">*</span></label>
                        <input type="url" name="link" class="ms-form-input" placeholder="https://zoom.us/j/…">
                    </div>
                </div>

                <div class="ms-contact-fields" style="display:none;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <div>
                            <label class="ms-form-label">Lesson Date <span style="color:#ef4444;">*</span></label>
                            <input type="date" name="session_date" class="ms-form-input">
                        </div>
                        <div>
                            <label class="ms-form-label">Time (Optional)</label>
                            <input type="time" name="session_time" class="ms-form-input">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="ms-form-label">Details (Optional)</label>
                    <textarea name="description" rows="2" class="ms-form-input" placeholder="Anything else the learners should know…"></textarea>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="ms-ann-toggle" style="background:#f1f5f9;color:#64748b;box-shadow:none;" onclick="toggleAnnForm('<?php echo $formId; ?>')">Cancel</button>
                    <button type="submit" class="ms-ann-toggle"><i class="fas fa-bullhorn"></i> Post Announcement</button>
                </div>
            </form>

            <div class="ms-ann-list">
                <?php if ($annCount === 0): ?>
                    <div style="text-align:center;padding:18px;color:#64748b;font-size:13px;background:#fff;border:1.5px dashed #e2e8f0;border-radius:14px;">
                        <i class="fas fa-bullhorn" style="color:#cbd5e1;margin-right:6px;"></i> No announcements yet for this grade.
                    </div>
                <?php else:
                    $allForCard = array_merge(
                        array_map(function ($a) { $a['__general'] = 0; return $a; }, $announcements),
                        array_map(function ($a) { $a['__general'] = 1; return $a; }, $generalAnnouncements)
                    );
                    foreach ($allForCard as $ann):
                        $isGeneral = !empty($ann['__general']);
                        $isContact = ($ann['type'] ?? 'online') === 'contact';
                ?>
                    <div class="ms-ann-item">
                        <span class="ms-ann-icon" style="<?php echo $isContact ? 'background:rgba(16,185,129,.12);color:#047857;' : 'background:rgba(59,130,246,.12);color:#1d4ed8;'; ?>">
                            <i class="fas <?php echo $isContact ? 'fa-handshake' : 'fa-video'; ?>"></i>
                        </span>
                        <div style="min-width:0;flex:1;">
                            <h5><?php echo htmlspecialchars($ann['title']); ?></h5>
                            <?php if (!empty($ann['description'])): ?><p><?php echo htmlspecialchars($ann['description']); ?></p><?php endif; ?>
                            <div class="ms-ann-meta">
                                <span class="pill" style="<?php echo $isContact ? 'background:rgba(16,185,129,.10);color:#047857;' : 'background:rgba(59,130,246,.10);color:#1d4ed8;'; ?>">
                                    <i class="fas <?php echo $isContact ? 'fa-handshake' : 'fa-video'; ?>"></i> <?php echo $isContact ? 'Contact Lesson' : 'Online Session'; ?>
                                </span>
                                <?php if ($isContact): ?>
                                    <span class="pill" style="background:rgba(245,158,11,.10);color:#b45309;"><i class="fas fa-calendar"></i> <?php echo htmlspecialchars(date('D, d M Y', strtotime($ann['session_date']))); ?><?php echo !empty($ann['session_time']) ? ' · ' . htmlspecialchars(date('H:i', strtotime($ann['session_time']))) : ''; ?></span>
                                <?php elseif (!empty($ann['link'])): ?>
                                    <a href="<?php echo htmlspecialchars($ann['link']); ?>" target="_blank" rel="noopener" class="pill" style="background:rgba(102,126,234,.10);color:#4f46e5;text-decoration:none;"><i class="fas fa-link"></i> Join Session</a>
                                <?php endif; ?>
                                <?php if ($isGeneral): ?><span class="pill" style="background:rgba(148,163,184,.15);color:#475569;"><i class="fas fa-users"></i> All grades</span><?php endif; ?>
                                <span class="pill" style="background:rgba(148,163,184,.10);color:#64748b;"><i class="fas fa-clock"></i> <?php echo htmlspecialchars(date('d M Y', strtotime($ann['created_at']))); ?></span>
                            </div>
                        </div>
                        <form method="POST" action="/tutor/announcements/delete/<?php echo (int)$ann['id']; ?>" style="margin:0;" onsubmit="return confirm('Delete this announcement?');">
                            <button type="submit" class="ms-ann-del"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <div class='ms-section' style='margin-top:18px;'>
                <h4 class='ms-section-title'><i class='fas fa-question-circle'></i> Student Problems & Questions</h4>
                <?php if (!empty(\[\]['problems'])): ?>
                    <?php foreach ((\[\]['problems'] ?? []) as \): ?>
                    <div class='ms-ann-item' style='background:#f8fafc;'>
                        <div style='min-width:0;flex:1;'>
                            <h5><?php echo htmlspecialchars(\['username'] ?? 'Student'); ?><?php echo !empty(\['grade']) ? ' (Grade '.htmlspecialchars(\['grade']).')' : ''; ?></h5>
                            <?php if (!empty(\['subject'])): ?><p style='margin:4px 0 6px;font-size:12px;color:#334155;'><strong>Subject:</strong> <?php echo htmlspecialchars(\['subject']); ?></p><?php endif; ?>
                            <p style='margin:0 0 6px;font-size:13px;'><?php echo nl2br(htmlspecialchars(\['problem_text'])); ?></p>
                            <div class='ms-ann-meta'>
                                <span class='pill' style='background:rgba(245,158,11,.10);color:#b45309;'><i class='fas fa-circle'></i> <?php echo htmlspecialchars(\['status']); ?></span>
                                <span class='pill' style='background:rgba(148,163,184,.10);color:#64748b;'><i class='fas fa-clock'></i> <?php echo htmlspecialchars(date('d M Y H:i', strtotime(\['created_at']))); ?></span>
                            </div>
                        </div>
                        <?php if (\['status'] !== 'resolved'): ?>
                        <form method='POST' action='/tutor/problems/resolve/<?php echo (int)\['id']; ?>' style='margin:0;'>
                            <button type='submit' class='ms-ann-del' style='background:rgba(16,185,129,.12);color:#047857;'><i class='fas fa-check'></i> Resolve</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style='text-align:center;padding:14px;color:#64748b;font-size:13px;background:#fff;border:1.5px dashed #e2e8f0;border-radius:12px;'>
                        <i class='fas fa-question-circle' style='color:#cbd5e1;margin-right:6px;'></i> No problems submitted yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
        <div class="ms-grade-head">
            <span class="ms-grade-icon" style="background:linear-gradient(135deg,#94a3b8,#64748b);"><i class="fas fa-layer-group"></i></span>
            <div>
                <h3>Grade Not Specified</h3>
                <p>These students did not include a grade in their request</p>
            </div>
            <span class="ms-grade-count"><i class="fas fa-user-graduate"></i> <?php echo count($unspecified); ?> student<?php echo count($unspecified) === 1 ? '' : 's'; ?></span>
        </div>
        <div class="ms-students">
            <?php foreach ($unspecified as $stu): ?>
            <div class="ms-student">
                <span class="ms-student-avatar" style="background:linear-gradient(135deg,#94a3b8,#64748b);"><?php echo htmlspecialchars(strtoupper(mb_substr($stu['username'] ?? 'S', 0, 1))); ?></span>
                <div style="min-width:0;flex:1;">
                    <p class="ms-student-name"><?php echo htmlspecialchars($stu['username'] ?? 'Student'); ?></p>
                    <div class="ms-student-sub">
                        <?php if (!empty($stu['subjects'])): ?><span><i class="fas fa-book" style="color:#1d4ed8;"></i> <?php echo htmlspecialchars($stu['subjects']); ?></span><?php endif; ?>
                    </div>
                </div>
                <span class="ms-badge <?php echo ($stu['teaching_mode'] ?? 'online') === 'contact' ? 'contact' : 'online'; ?>"><i class="fas <?php echo ($stu['teaching_mode'] ?? 'online') === 'contact' ? 'fa-handshake' : 'fa-video'; ?>"></i> <?php echo ($stu['teaching_mode'] ?? 'online') === 'contact' ? 'Contact' : 'Online'; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
function toggleAnnForm(id) {
    var f = document.getElementById(id);
    if (f) f.classList.toggle('open');
}
function pickType(opt) {
    var input = opt.tagName === 'INPUT' ? opt : opt.querySelector('input');
    var form = opt.closest('form');
    if (!form || !input) return;
    var isContact = input.value === 'contact';
    var onlineFields = form.querySelector('.ms-online-fields');
    var contactFields = form.querySelector('.ms-contact-fields');
    form.querySelectorAll('input[name="type"]').forEach(function (r) {
        r.checked = (r.value === input.value);
        var wrap = r.closest('.ms-type-opt');
        if (wrap) wrap.classList.toggle('selected', r.checked);
    });
    if (onlineFields) onlineFields.style.display = isContact ? 'none' : 'block';
    if (contactFields) contactFields.style.display = isContact ? 'block' : 'none';
}
</script>

</div><!-- /.sg-page -->

<?php include __DIR__ . '/../layouts/footer.php'; ?>

