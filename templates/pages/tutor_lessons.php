<?php
/**
 * Tutor Lessons page (opened via "View Tutor" on the student's "My Tutors" list).
 * Expects: $tutor (row joined with username), $initials, $tutorAnnouncements,
 *          $lessonsByKind (['lessons'=>[], 'recorded'=>[], 'documents'=>[]]),
 *          $teachingMode, $user
 */
$pageTitle = ($tutor['full_name'] ?: ($tutor['username'] ?? 'Tutor')) . ' — Lessons & Materials - StudySmart';
$currentPage = 'study-group';
include __DIR__ . '/../layouts/header.php';

$teachingModeMeta = [
    'contact' => ['label' => 'Fully Contact (In-Person)', 'icon' => 'chalkboard-teacher', 'color' => '#059669', 'bg' => 'rgba(16,185,129,0.12)', 'border' => 'rgba(16,185,129,0.35)'],
    'hybrid' => ['label' => 'Hybrid (Online + In-Person)', 'icon' => 'random', 'color' => '#b45309', 'bg' => 'rgba(245,158,11,0.12)', 'border' => 'rgba(245,158,11,0.35)'],
    'online' => ['label' => 'Fully Online', 'icon' => 'laptop', 'color' => '#1d4ed8', 'bg' => 'rgba(59,130,246,0.12)', 'border' => 'rgba(59,130,246,0.35)'],
];
$modeMeta = $teachingModeMeta[$teachingMode] ?? $teachingModeMeta['contact'];
?>

<div class="sg-page">
<style>
/* ===== Tutor Lessons page — scoped premium styles ===== */
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
.sg-page .tl-title{font-size:30px;margin:0;letter-spacing:-.02em;}
.sg-page .tl-subtitle{color:var(--sg-muted);font-size:15px;margin:6px 0 0;max-width:640px;}
.sg-page .tl-back{display:inline-flex;align-items:center;gap:8px;color:#4f46e5;font-size:13.5px;font-weight:600;text-decoration:none;margin-bottom:16px;padding:8px 14px;border-radius:12px;background:#fff;border:1.5px solid #c7d2fe;transition:transform .2s ease,box-shadow .2s ease;}
.sg-page .tl-back:hover{transform:translateY(-2px);box-shadow:0 10px 22px rgba(102,126,234,.18);}
.sg-page .tl-header{display:flex;align-items:center;gap:22px;margin:8px 0 28px;flex-wrap:wrap;}
.sg-page .tl-avatar{width:88px;height:88px;border-radius:22px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:34px;font-weight:800;color:#fff;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));box-shadow:0 14px 30px rgba(102,126,234,.30);}
.sg-page .tl-section{margin-bottom:32px;}
.sg-page .tl-section-head{display:flex;gap:14px;align-items:flex-start;margin-bottom:18px;}
.sg-page .tl-section-icon{width:44px;height:44px;border-radius:13px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));box-shadow:0 8px 18px rgba(102,126,234,.28);}
.sg-page .tl-section-icon.alt-blue{background:linear-gradient(135deg,#3b82f6,#1d4ed8);box-shadow:0 8px 18px rgba(59,130,246,.28);}
.sg-page .tl-section-icon.alt-green{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 18px rgba(16,185,129,.28);}
.sg-page .tl-section-icon.alt-purple{background:linear-gradient(135deg,#8b5cf6,#6d28d9);box-shadow:0 8px 18px rgba(139,92,246,.28);}
.sg-page .tl-section-text h2{font-size:19px;margin:2px 0 4px;color:var(--sg-ink);letter-spacing:-.01em;}
.sg-page .tl-section-text p{color:var(--sg-muted);font-size:13.5px;margin:0;}
.sg-page .tl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
.sg-page .tl-card{background:#fff;border:1px solid var(--sg-line);border-radius:var(--sg-radius);box-shadow:var(--sg-shadow);padding:18px;display:flex;flex-direction:column;gap:10px;transition:transform .22s ease,box-shadow .22s ease;}
.sg-page .tl-card:hover{transform:translateY(-3px);box-shadow:0 14px 34px rgba(30,41,59,.10);}
.sg-page .tl-empty{background:#fff;border:1.5px dashed #e2e8f0;border-radius:var(--sg-radius);padding:40px 26px;text-align:center;box-shadow:var(--sg-shadow);}
.sg-page .tl-empty-icon{width:60px;height:60px;border-radius:16px;margin:0 auto 14px;display:inline-flex;align-items:center;justify-content:center;font-size:24px;color:var(--sg-brand);background:rgba(102,126,234,.10);}
.sg-page .tl-empty h3{font-size:16px;margin:0 0 6px;color:var(--sg-ink);}
.sg-page .tl-empty p{color:var(--sg-muted);font-size:13.5px;margin:0;}
.sg-page .ms-form-label{display:block;margin-bottom:6px;font-size:12.5px;font-weight:600;color:#334155;}
.sg-page .ms-form-input{width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-family:inherit;color:#334155;background:#fff;box-sizing:border-box;transition:border-color .2s ease,box-shadow .2s ease;}
.sg-page textarea.ms-form-input{resize:vertical;line-height:1.55;min-height:96px;}
.sg-page .ms-form-input:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,.15);}
.sg-page .ms-form-input::placeholder{color:var(--sg-faint);}
.sg-page .ms-ann-toggle{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;border:none;cursor:pointer;padding:9px 16px;border-radius:11px;font-size:13px;font-weight:600;font-family:inherit;box-shadow:0 8px 18px rgba(102,126,234,.30);transition:transform .2s ease,filter .2s ease;}
.sg-page .ms-ann-toggle:hover{transform:translateY(-2px);filter:brightness(1.05);}

/* ===== Collapsible section dropdowns (card headers w/ animated border) ===== */
@property --tl-ring-a{syntax:'<angle>';initial-value:0deg;inherits:false;}
@keyframes tlRingSpin{to{--tl-ring-a:360deg;}}
@keyframes tlSecIn{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:none;}}
.sg-page .tl-sec-header{
    display:flex;justify-content:space-between;align-items:center;gap:14px;
    margin:0;padding:16px 20px;
    background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);
    border:1.5px solid #e8edf5;border-radius:var(--sg-radius);
    box-shadow:0 6px 20px rgba(30,41,59,.07);
    cursor:pointer;position:relative;overflow:hidden;
    transition:transform .25s ease,box-shadow .25s ease,border-color .25s ease,border-radius .2s ease;
}
.sg-page .tl-sec:not(.collapsed) .tl-sec-header{border-radius:var(--sg-radius) var(--sg-radius) 0 0;}
.sg-page .tl-sec-header:hover{transform:translateY(-3px);border-color:transparent;box-shadow:0 16px 38px rgba(102,126,234,.18);}
.sg-page .tl-sec-header:focus-visible{outline:2px solid rgba(102,126,234,.45);outline-offset:2px;}
.sg-page .tl-sec-header .tl-section-head{margin:0;}
.sg-page .tl-sec-header::after{
    content:"";position:absolute;inset:0;border-radius:inherit;padding:2px;
    background:conic-gradient(from var(--tl-ring-a),#667eea,#764ba2,#ec4899,#f59e0b,#10b981,#667eea);
    -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
    -webkit-mask-composite:xor;
            mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
            mask-composite:exclude;
    opacity:0;transition:opacity .35s ease;pointer-events:none;z-index:2;
}
.sg-page .tl-sec-header:hover::after,.sg-page .tl-sec-header:focus-visible::after{opacity:1;animation:tlRingSpin 3.5s linear infinite;}
.sg-page .tl-sec-actions{display:flex;align-items:center;gap:10px;flex-shrink:0;margin-left:auto;position:relative;z-index:3;}
.sg-page .tl-sec-chev{width:36px;height:36px;border-radius:11px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:13px;color:#4f46e5;background:rgba(99,102,241,.12);transition:background .2s ease,box-shadow .2s ease;}
.sg-page .tl-sec-header:hover .tl-sec-chev{background:rgba(99,102,241,.20);box-shadow:0 6px 14px rgba(102,126,234,.20);}
.sg-page .tl-sec-chev i{display:inline-block;transition:transform .3s cubic-bezier(.34,1.56,.64,1);}
.sg-page .tl-sec:not(.collapsed) .tl-sec-chev i{transform:rotate(180deg);}
.sg-page .tl-sec-body{
    display:block;
    background:linear-gradient(180deg,#e1e8f5 0%,#d8e1ef 100%);
    border:1.5px solid #d4dded;border-top:none;
    border-radius:0 0 var(--sg-radius) var(--sg-radius);
    padding:20px;margin-top:-1.5px;
    box-shadow:inset 0 10px 18px -12px rgba(30,41,59,.24),0 8px 24px rgba(30,41,59,.07);
}
.sg-page .tl-sec.collapsed .tl-sec-body{display:none;}
.sg-page .tl-sec-body.tl-sec-anim{animation:tlSecIn .3s ease both;}
@media (max-width:768px){
    .sg-page .tl-sec-header{flex-wrap:nowrap;padding:14px;gap:10px;}
    .sg-page .tl-sec-header .tl-section-text p{display:none;}
    .sg-page .tl-sec-actions{margin-left:auto;}
}
@media (prefers-reduced-motion:reduce){
    .sg-page .tl-sec-header::after{animation:none!important;}
    .sg-page .tl-sec-header:hover{transform:none!important;}
    .sg-page .tl-sec-chev i,.sg-page .tl-sec-body.tl-sec-anim{transition:none!important;animation:none!important;}
}

@media (max-width:768px){.sg-page .tl-header{flex-direction:column;align-items:flex-start;}}
</style>

<a href="/study-group" class="tl-back"><i class="fas fa-arrow-left"></i> Back to My Tutors</a>

<div class="tl-header">
    <span class="tl-avatar"><?php echo htmlspecialchars($initials); ?></span>
    <div>
        <h1 class="tl-title"><?php echo htmlspecialchars($tutor['full_name'] ?: ($tutor['username'] ?? 'Tutor')); ?></h1>
        <p class="tl-subtitle">Announcements, lessons, recorded sessions and documents your tutor shares with you.</p>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;align-items:center;">
            <span style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;padding:5px 12px;border-radius:999px;background:<?php echo $modeMeta['bg']; ?>;color:<?php echo $modeMeta['color']; ?>;border:1px solid <?php echo $modeMeta['border']; ?>;">
                <i class="fas fa-<?php echo $modeMeta['icon']; ?>"></i> <?php echo $modeMeta['label']; ?>
            </span>
            <a href="/tutor/view/<?php echo (int)$tutor['id']; ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:5px 12px;border-radius:999px;background:#fff;border:1.5px solid #c7d2fe;color:#4f46e5;text-decoration:none;">
                <i class="fas fa-user"></i> View Profile
            </a>
        </div>
    </div>
</div>

<!-- ===== Announcements ===== -->
<section class="tl-section tl-sec collapsed" id="tlSecAnnouncements">
    <div class="tl-sec-header" role="button" tabindex="0" aria-expanded="false" aria-controls="tlSecAnnouncementsBody" onclick="tlToggleSection(this)" onkeydown="tlSectionKey(event, this)">
        <div class="tl-section-head">
            <span class="tl-section-icon"><i class="fas fa-bullhorn"></i></span>
            <div class="tl-section-text">
                <h2>Announcements</h2>
                <p>Announcements your tutor has posted for you.</p>
            </div>
        </div>
        <div class="tl-sec-actions"><span class="tl-sec-chev" aria-hidden="true"><i class="fas fa-chevron-down"></i></span></div>
    </div><!-- /.tl-sec-header -->
    <div class="tl-sec-body" id="tlSecAnnouncementsBody">
    <div class="tl-grid">
        <?php if (count($tutorAnnouncements) === 0): ?>
            <div class="tl-empty" style="grid-column:1 / -1;">
                <div class="tl-empty-icon"><i class="fas fa-bullhorn"></i></div>
                <h3>No announcements yet</h3>
                <p>Your tutor has not posted any announcements for you yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tutorAnnouncements as $ann):
                $isContactAnn = ($ann['type'] ?? 'online') === 'contact';
            ?>
            <div class="tl-card" style="display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="width:36px;height:36px;border-radius:10px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:14px;<?php echo $isContactAnn ? 'background:rgba(16,185,129,.12);color:#047857;' : 'background:rgba(59,130,246,.12);color:#1d4ed8;'; ?>"><i class="fas <?php echo $isContactAnn ? 'fa-handshake' : 'fa-video'; ?>"></i></span>
                    <p style="font-size:13px;font-weight:700;color:var(--sg-ink);margin:0;"><?php echo htmlspecialchars($ann['title']); ?></p>
                </div>
                <?php if (!empty($ann['description'])): ?><p style="color:var(--sg-muted);font-size:12.5px;margin:0;line-height:1.5;"><?php echo htmlspecialchars($ann['description']); ?></p><?php endif; ?>
                <div style="border-top:1px solid var(--sg-line);padding-top:12px;margin-top:auto;display:flex;flex-direction:column;gap:8px;">
                    <p style="font-size:12px;color:var(--sg-muted);margin:0;"><?php echo htmlspecialchars(date('D, d M Y', strtotime($ann['created_at']))); ?></p>
                    <?php if ($isContactAnn): ?>
                        <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(245,158,11,.10);color:#b45309;padding:6px 12px;border-radius:10px;font-size:12.5px;font-weight:600;align-self:flex-start;"><i class="fas fa-calendar"></i> <?php echo htmlspecialchars(date('D, d M Y', strtotime($ann['session_date']))); ?><?php echo !empty($ann['session_time']) ? ' · ' . htmlspecialchars(date('H:i', strtotime($ann['session_time']))) : ''; ?></span>
                    <?php elseif (!empty($ann['link'])): ?>
                        <a href="<?php echo htmlspecialchars($ann['link']); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:6px 12px;border-radius:10px;font-size:12.5px;font-weight:600;text-decoration:none;align-self:flex-start;"><i class="fas fa-link"></i> Join Session</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    </div><!-- /.tl-sec-body -->
</section>

<!-- ===== Lessons ===== -->
<section class="tl-section tl-sec collapsed" id="tlSecLessons">
    <div class="tl-sec-header" role="button" tabindex="0" aria-expanded="false" aria-controls="tlSecLessonsBody" onclick="tlToggleSection(this)" onkeydown="tlSectionKey(event, this)">
        <div class="tl-section-head">
            <span class="tl-section-icon alt-blue"><i class="fas fa-calendar"></i></span>
            <div class="tl-section-text">
                <h2>Lessons</h2>
                <p>Scheduled lessons from your tutor, with links and times.</p>
            </div>
        </div>
        <div class="tl-sec-actions"><span class="tl-sec-chev" aria-hidden="true"><i class="fas fa-chevron-down"></i></span></div>
    </div><!-- /.tl-sec-header -->
    <div class="tl-sec-body" id="tlSecLessonsBody">
    <div class="tl-grid">
        <?php if (count($lessonsByKind['lessons']) === 0): ?>
            <div class="tl-empty" style="grid-column:1 / -1;">
                <div class="tl-empty-icon"><i class="fas fa-calendar"></i></div>
                <h3>No lessons yet</h3>
                <p>Your tutor has not scheduled any lessons yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($lessonsByKind['lessons'] as $lesson): ?>
            <div class="tl-card" style="display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="width:36px;height:36px;border-radius:10px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:14px;background:rgba(16,185,129,.12);color:#047857;"><i class="fas fa-check-circle"></i></span>
                    <p style="font-size:13px;font-weight:700;color:var(--sg-ink);margin:0;"><?php echo htmlspecialchars($lesson['title']); ?></p>
                </div>
                <?php if (!empty($lesson['description'])): ?><p style="color:var(--sg-muted);font-size:12.5px;margin:0;line-height:1.5;"><?php echo htmlspecialchars($lesson['description']); ?></p><?php endif; ?>
                <div style="border-top:1px solid var(--sg-line);padding-top:12px;margin-top:auto;display:flex;flex-direction:column;gap:8px;">
                    <p style="font-size:12px;color:var(--sg-muted);margin:0;"><?php echo htmlspecialchars(date('D, d M Y', strtotime($lesson['created_at']))); ?></p>
                    <a href="<?php echo htmlspecialchars($lesson['link']); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:8px 14px;border-radius:10px;font-size:12.5px;font-weight:600;text-decoration:none;align-self:flex-start;"><i class="fas fa-link"></i> Open Lesson</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    </div><!-- /.tl-sec-body -->
</section>

<!-- ===== Recorded Sessions ===== -->
<section class="tl-section tl-sec collapsed" id="tlSecRecorded">
    <div class="tl-sec-header" role="button" tabindex="0" aria-expanded="false" aria-controls="tlSecRecordedBody" onclick="tlToggleSection(this)" onkeydown="tlSectionKey(event, this)">
        <div class="tl-section-head">
            <span class="tl-section-icon alt-purple"><i class="fas fa-video"></i></span>
            <div class="tl-section-text">
                <h2>Recorded Sessions</h2>
                <p>Recorded sessions from your tutor, with links.</p>
            </div>
        </div>
        <div class="tl-sec-actions"><span class="tl-sec-chev" aria-hidden="true"><i class="fas fa-chevron-down"></i></span></div>
    </div><!-- /.tl-sec-header -->
    <div class="tl-sec-body" id="tlSecRecordedBody">
    <div class="tl-grid">
        <?php if (count($lessonsByKind['recorded']) === 0): ?>
            <div class="tl-empty" style="grid-column:1 / -1;">
                <div class="tl-empty-icon"><i class="fas fa-video"></i></div>
                <h3>No recorded sessions yet</h3>
                <p>Your tutor has not shared any recorded sessions yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($lessonsByKind['recorded'] as $lesson): ?>
            <div class="tl-card" style="display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="width:36px;height:36px;border-radius:10px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:14px;background:rgba(139,92,246,.12);color:#6d28d9;"><i class="fas fa-play-circle"></i></span>
                    <p style="font-size:13px;font-weight:700;color:var(--sg-ink);margin:0;"><?php echo htmlspecialchars($lesson['title']); ?></p>
                </div>
                <?php if (!empty($lesson['description'])): ?><p style="color:var(--sg-muted);font-size:12.5px;margin:0;line-height:1.5;"><?php echo htmlspecialchars($lesson['description']); ?></p><?php endif; ?>
                <div style="border-top:1px solid var(--sg-line);padding-top:12px;margin-top:auto;display:flex;flex-direction:column;gap:8px;">
                    <p style="font-size:12px;color:var(--sg-muted);margin:0;"><?php echo htmlspecialchars(date('D, d M Y', strtotime($lesson['created_at']))); ?></p>
                    <a href="<?php echo htmlspecialchars($lesson['link']); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:8px 14px;border-radius:10px;font-size:12.5px;font-weight:600;text-decoration:none;align-self:flex-start;"><i class="fas fa-play"></i> Watch Session</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    </div><!-- /.tl-sec-body -->
</section>

<!-- ===== Documents ===== -->
<section class="tl-section tl-sec collapsed" id="tlSecDocuments">
    <div class="tl-sec-header" role="button" tabindex="0" aria-expanded="false" aria-controls="tlSecDocumentsBody" onclick="tlToggleSection(this)" onkeydown="tlSectionKey(event, this)">
        <div class="tl-section-head">
            <span class="tl-section-icon alt-green"><i class="fas fa-file"></i></span>
            <div class="tl-section-text">
                <h2>Documents</h2>
                <p>Documents your tutor shares with you.</p>
            </div>
        </div>
        <div class="tl-sec-actions"><span class="tl-sec-chev" aria-hidden="true"><i class="fas fa-chevron-down"></i></span></div>
    </div><!-- /.tl-sec-header -->
    <div class="tl-sec-body" id="tlSecDocumentsBody">
    <div class="tl-grid">
        <?php if (count($lessonsByKind['documents']) === 0): ?>
            <div class="tl-empty" style="grid-column:1 / -1;">
                <div class="tl-empty-icon"><i class="fas fa-file"></i></div>
                <h3>No documents yet</h3>
                <p>Your tutor has not shared any documents yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($lessonsByKind['documents'] as $lesson): ?>
            <div class="tl-card" style="display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="width:36px;height:36px;border-radius:10px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:14px;background:rgba(16,185,129,.12);color:#047857;"><i class="fas fa-file"></i></span>
                    <p style="font-size:13px;font-weight:700;color:var(--sg-ink);margin:0;"><?php echo htmlspecialchars($lesson['title']); ?></p>
                </div>
                <?php if (!empty($lesson['description'])): ?><p style="color:var(--sg-muted);font-size:12.5px;margin:0;line-height:1.5;"><?php echo htmlspecialchars($lesson['description']); ?></p><?php endif; ?>
                <div style="border-top:1px solid var(--sg-line);padding-top:12px;margin-top:auto;display:flex;flex-direction:column;gap:8px;">
                    <p style="font-size:12px;color:var(--sg-muted);margin:0;"><?php echo htmlspecialchars(date('D, d M Y', strtotime($lesson['created_at']))); ?></p>
                    <a href="<?php echo htmlspecialchars($lesson['link']); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:8px 14px;border-radius:10px;font-size:12.5px;font-weight:600;text-decoration:none;align-self:flex-start;"><i class="fas fa-download"></i> Download Document</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    </div><!-- /.tl-sec-body -->
</section>

<section class="tl-section tl-sec collapsed" id="tlSecProblems">
    <div class="tl-sec-header" role="button" tabindex="0" aria-expanded="false" aria-controls="tlSecProblemsBody" onclick="tlToggleSection(this)" onkeydown="tlSectionKey(event, this)">
        <div class="tl-section-head">
            <span class="tl-section-icon"><i class="fas fa-question-circle"></i></span>
            <div class="tl-section-text">
                <h2>My Problems/Questions</h2>
                <p>State where you are facing problems. Your tutor will see them.</p>
            </div>
        </div>
        <div class="tl-sec-actions"><span class="tl-sec-chev" aria-hidden="true"><i class="fas fa-chevron-down"></i></span></div>
    </div><!-- /.tl-sec-header -->
    <div class="tl-sec-body" id="tlSecProblemsBody">
    <div class="tl-card">
        <form method="POST" action="/tutor/problems/create">
            <input type="hidden" name="tutor_id" value="<?php echo (int)$tutor["id"]; ?>">
            <div style="margin-bottom:12px;">
                <label class="ms-form-label">Subject (Optional)</label>
                <input type="text" name="subject" class="ms-form-input" placeholder="e.g., Mathematics">
            </div>
            <div style="margin-bottom:12px;">
                <label class="ms-form-label">Describe your problem <span style="color:#ef4444;">*</span></label>
                <textarea name="problem_text" class="ms-form-input" rows="4" required placeholder="Explain where you're stuck..."></textarea>
            </div>
            <button type="submit" class="ms-ann-toggle"><i class="fas fa-paper-plane"></i> Submit Problem</button>
        </form>
        <?php if (!empty($studentProblems)): ?>
        <div style="margin-top:16px;">
            <h4 style="font-size:14px;margin:0 0 10px;">Your submitted problems</h4>
            <?php foreach ($studentProblems as $sp): ?>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px;margin-bottom:8px;">
                <p style="margin:0 0 6px;font-size:13px;"><?php echo htmlspecialchars($sp["problem_text"]); ?></p>
                <div style="font-size:11px;color:#475569;"><?php echo ($sp["subject"] ? htmlspecialchars($sp["subject"]) : "-") . " � " . htmlspecialchars($sp["status"]) . " � " . htmlspecialchars($sp["created_at"]); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    </div><!-- /.tl-sec-body -->
</section>

</div><!-- /.sg-page -->

<script>
    // ---- Section dropdowns (Announcements / Lessons / Recorded / Documents / Problems) ----
    function tlToggleSection(header) {
        var sec = header.closest('.tl-sec');
        if (!sec) return;
        var collapsed = sec.classList.toggle('collapsed');
        header.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        var body = sec.querySelector('.tl-sec-body');
        if (body && !collapsed) {
            body.classList.remove('tl-sec-anim');
            void body.offsetWidth;
            body.classList.add('tl-sec-anim');
        }
    }

    function tlSectionKey(e, header) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            tlToggleSection(header);
        }
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

