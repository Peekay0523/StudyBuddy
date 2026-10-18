<?php
/**
 * Public Tutor Profile page (opened via "View Tutor" on the Available Tutors list).
 * Expects: $tutor (row joined with username), $initials, $subjects, $teachingMode,
 *          $packages (parsed), $teamMembers (parsed), $isOwnProfile, $user
 */
$pageTitle = ($tutor['full_name'] ?: ($tutor['username'] ?? 'Tutor')) . ' - Tutor Profile - StudySmart';
$currentPage = 'study-group';
include __DIR__ . '/../layouts/header.php';

$teachingModeMeta = [
    'contact' => [
        'label' => 'Fully Contact (In-Person)',
        'icon' => 'chalkboard-teacher',
        'color' => '#059669',
        'bg' => 'rgba(16,185,129,0.12)',
        'border' => 'rgba(16,185,129,0.35)',
        'desc' => 'All lessons are taught face-to-face, in person — either at your home, the tutor\'s venue, or a public study space you agree on.',
    ],
    'hybrid' => [
        'label' => 'Hybrid (Online + In-Person)',
        'icon' => 'random',
        'color' => '#b45309',
        'bg' => 'rgba(245,158,11,0.12)',
        'border' => 'rgba(245,158,11,0.35)',
        'desc' => 'A mix of both: lessons can be online or in person, so you can choose what suits your schedule each week.',
    ],
    'online' => [
        'label' => 'Fully Online',
        'icon' => 'laptop',
        'color' => '#1d4ed8',
        'bg' => 'rgba(59,130,246,0.12)',
        'border' => 'rgba(59,130,246,0.35)',
        'desc' => 'All lessons are taught online (video call / virtual classroom) — learn from anywhere with an internet connection.',
    ],
];
$modeMeta = $teachingModeMeta[$teachingMode];

$memberSince = '';
if (!empty($tutor['created_at'])) {
    $ts = strtotime($tutor['created_at']);
    if ($ts) $memberSince = date('M Y', $ts);
}
?>

<div class="sg-page">
<style>
/* ===== Tutor Profile page — scoped premium styles ===== */
.sg-page{
    --sg-brand:#667eea; --sg-brand-2:#764ba2;
    --sg-ink:#1e293b; --sg-muted:#64748b; --sg-faint:#94a3b8; --sg-line:#eef2f7;
    --sg-radius:20px; --sg-shadow:0 10px 30px rgba(30,41,59,.06); --sg-shadow-hover:0 18px 40px rgba(102,126,234,.18);
    background:
        radial-gradient(1200px 500px at 100% -10%, rgba(118,75,162,.06), transparent 60%),
        radial-gradient(1000px 500px at -10% 0%, rgba(102,126,234,.07), transparent 55%),
        linear-gradient(180deg,#f8faff 0%,#f5f7fb 100%);
    padding:6px 6px 40px; border-radius:24px; color:var(--sg-ink);
}
.sg-page .tp-section{margin-bottom:32px;}
.sg-page .tp-section-head{display:flex;gap:14px;align-items:flex-start;margin:0 0 18px;}
.sg-page .tp-section-icon{width:44px;height:44px;border-radius:13px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));box-shadow:0 8px 18px rgba(102,126,234,.30);}
.sg-page .tp-section-icon.alt-green{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 18px rgba(16,185,129,.28);}
.sg-page .tp-section-icon.alt-amber{background:linear-gradient(135deg,#f59e0b,#f97316);box-shadow:0 8px 18px rgba(245,158,11,.30);}
.sg-page .tp-section-icon.alt-rose{background:linear-gradient(135deg,#f43f5e,#ec4899);box-shadow:0 8px 18px rgba(244,63,94,.30);}
.sg-page .tp-section-icon.alt-blue{background:linear-gradient(135deg,#0ea5e9,#2563eb);box-shadow:0 8px 18px rgba(14,165,233,.28);}
.sg-page .tp-section-text h2{font-size:19px;margin:2px 0 4px;color:var(--sg-ink);letter-spacing:-.01em;}
.sg-page .tp-section-text p{color:var(--sg-muted);font-size:13.5px;margin:0;}
.sg-page .tp-card{background:#fff;border:1px solid var(--sg-line);border-radius:var(--sg-radius);padding:22px;box-shadow:var(--sg-shadow);}
.sg-page .tp-grid-2{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;}
.sg-page .tp-grid-3{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;}
.sg-page .tp-avatar{width:76px;height:76px;border-radius:20px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:30px;font-weight:800;color:#fff;background:linear-gradient(135deg,#f59e0b,#f97316);box-shadow:0 14px 28px rgba(245,158,11,.35);}
.sg-page .tp-subject-pill{background:#fef3c7;color:#b45309;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;}
.sg-page .tp-grade-pill{background:#f0fdf4;color:#15803d;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;border:1px solid #bbf7d0;}
.sg-page .tp-subject-card{display:flex;align-items:center;gap:14px;background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:18px;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease;}
.sg-page .tp-subject-card:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(30,41,59,.08);border-color:#c7d2fe;}
.sg-page .tp-subject-icon{width:46px;height:46px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;background:linear-gradient(135deg,#667eea,#764ba2);flex-shrink:0;}
.sg-page .tp-subject-name{font-size:15px;font-weight:700;color:var(--sg-ink);margin:0;}
.sg-page .tp-subject-grade{font-size:12.5px;color:var(--sg-muted);margin:4px 0 0;}
.sg-page .tp-cta{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;border:none;cursor:pointer;padding:12px 20px;border-radius:13px;font-size:14px;font-weight:600;box-shadow:0 10px 24px rgba(102,126,234,.35);transition:transform .2s ease,box-shadow .2s ease,filter .2s ease;min-height:46px;font-family:inherit;text-decoration:none;}
.sg-page .tp-cta:hover{transform:translateY(-2px);box-shadow:0 14px 28px rgba(102,126,234,.45);filter:brightness(1.05);}
.sg-page .tp-modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(15,23,42,0.55);align-items:center;justify-content:center;padding:20px;}
.sg-page .tp-modal-box{background:#fff;border-radius:var(--sg-radius);width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px rgba(15,23,42,0.25);}
.sg-page .tp-modal-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 24px;border-bottom:1px solid var(--sg-line);}
.sg-page .tp-modal-head h3{margin:0;font-size:18px;color:var(--sg-ink);}
.sg-page .tp-modal-body{padding:24px;}
.sg-page .tp-modal-foot{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid var(--sg-line);background:#f8fafc;}
.sg-page .tp-form-label{display:block;margin-bottom:6px;font-size:13px;font-weight:600;color:#334155;}
.sg-page .tp-form-select{width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-family:inherit;color:#334155;background:#fff;}
.sg-page .tp-form-input{width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-family:inherit;color:#334155;background:#fff;}
.sg-page .tp-checkbox-row{display:flex;align-items:flex-start;gap:10px;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;cursor:pointer;transition:border-color .2s ease,background .2s ease;}
.sg-page .tp-checkbox-row:hover{border-color:#c7d2fe;background:#f8fafc;}
.sg-page .tp-checkbox-row input{margin-top:3px;}
.sg-page .tp-checkbox-row.selected{border-color:#667eea;background:#eef2ff;}
.sg-page .tp-price-box{background:linear-gradient(135deg,#f0fdf4,#ecfdf5);border:1.5px solid #bbf7d0;border-radius:14px;padding:16px;text-align:center;}
.sg-page .tp-price-box .price{font-size:26px;font-weight:800;color:#047857;}
.sg-page .tp-price-box .note{font-size:12px;color:#065f46;margin-top:4px;}

/* ===== Card entrance animations ===== */
@keyframes tpRise{from{opacity:0;transform:translateY(26px) scale(.98);}60%{opacity:1;}to{opacity:1;transform:translateY(0) scale(1);}}
@keyframes tpSweep{from{transform:translateX(-160%) skewX(-18deg);}to{transform:translateX(360%) skewX(-18deg);}}
@keyframes tpChevNudge{0%,100%{transform:translateY(0);}50%{transform:translateY(3px);}}
.sg-page.tp-anim-ready .tp-reveal{opacity:0;}
.sg-page.tp-anim-ready .tp-reveal.tp-revealed{animation:tpRise .65s cubic-bezier(.22,.9,.32,1.05) both;animation-delay:var(--tp-delay,0ms);}

/* ===== Expandable cards: title/name only, tap to expand — one open at a time ===== */
.sg-page .tp-cx{cursor:pointer;position:relative;overflow:hidden;display:flex;flex-direction:column;gap:14px;transition:transform .25s ease,box-shadow .25s ease,border-color .25s ease,gap .4s cubic-bezier(.22,.9,.3,1);}
.sg-page .tp-cx:not(.tp-expanded){gap:0;}
.sg-page .tp-cx-top{display:flex;align-items:center;justify-content:space-between;gap:14px;min-width:0;}
.sg-page .tp-section.tp-cx .tp-section-head{flex:1;margin-bottom:0;min-width:0;}
.sg-page .tp-cx-chev{width:34px;height:34px;border-radius:11px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:13px;color:#4f46e5;background:rgba(99,102,241,.12);transition:transform .45s cubic-bezier(.34,1.56,.64,1),background .3s ease,color .3s ease,box-shadow .3s ease;}
.sg-page .tp-cx-chev i{animation:tpChevNudge 1.9s ease-in-out infinite;}
.sg-page .tp-cx.tp-expanded .tp-cx-chev{transform:rotate(180deg);background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;box-shadow:0 8px 16px rgba(102,126,234,.35);}
.sg-page .tp-cx.tp-expanded .tp-cx-chev i{animation:none;}
.sg-page .tp-cx:not(.tp-section):hover{transform:translateY(-3px);box-shadow:0 14px 34px rgba(30,41,59,.10);border-color:rgba(102,126,234,.30);}
.sg-page .tp-cx.tp-expanded:not(.tp-section){border-color:rgba(102,126,234,.4);box-shadow:0 18px 42px rgba(102,126,234,.20);}
.sg-page .tp-cx-body{display:grid;grid-template-rows:0fr;opacity:0;transition:grid-template-rows .45s cubic-bezier(.4,0,.2,1),opacity .3s ease;}
.sg-page .tp-cx.tp-expanded .tp-cx-body{grid-template-rows:1fr;opacity:1;transition:grid-template-rows .55s cubic-bezier(.22,.9,.3,1),opacity .35s ease;}
.sg-page .tp-cx-inner{display:flex;flex-direction:column;gap:12px;overflow:hidden;min-height:0;visibility:hidden;transition:visibility 0s .45s;}
.sg-page .tp-cx.tp-expanded .tp-cx-inner{visibility:visible;transition:visibility 0s 0s;}
.sg-page .tp-cx-inner>*{opacity:0;transform:translateY(14px);transition:opacity .35s ease,transform .45s cubic-bezier(.22,.9,.3,1);}
.sg-page .tp-cx.tp-expanded .tp-cx-inner>*{opacity:1;transform:none;}
.sg-page .tp-cx.tp-expanded .tp-cx-inner>*:nth-child(1){transition-delay:.14s;}
.sg-page .tp-cx.tp-expanded .tp-cx-inner>*:nth-child(2){transition-delay:.2s;}
.sg-page .tp-cx.tp-expanded .tp-cx-inner>*:nth-child(3){transition-delay:.26s;}
.sg-page .tp-cx.tp-expanded .tp-cx-inner>*:nth-child(4){transition-delay:.32s;}
.sg-page .tp-cx.tp-expanded .tp-cx-inner>*:nth-child(n+5){transition-delay:.38s;}

/* Light sweep glides across expandable cards on hover (not full sections) */
.sg-page .tp-cx:not(.tp-section)::before{content:"";position:absolute;inset:0;z-index:5;pointer-events:none;background:linear-gradient(105deg,transparent 30%,rgba(255,255,255,.6) 50%,transparent 70%);transform:translateX(-160%) skewX(-18deg);opacity:0;transition:opacity .25s ease;}
.sg-page .tp-cx:not(.tp-section):hover::before{opacity:1;animation:tpSweep .75s ease forwards;}

/* ===== Collapsible section dropdowns (card headers w/ animated border) ===== */
@property --tp-ring-a{syntax:'<angle>';initial-value:0deg;inherits:false;}
@keyframes tpRingSpin{to{--tp-ring-a:360deg;}}
.sg-page .tp-sec-header{
    display:flex;justify-content:space-between;align-items:center;gap:14px;
    margin:0;padding:16px 20px;
    background:linear-gradient(180deg,#ffffff 0%,#fbfcff 100%);
    border:1.5px solid #e8edf5;border-radius:var(--sg-radius);
    box-shadow:0 6px 20px rgba(30,41,59,.07);
    cursor:pointer;position:relative;overflow:hidden;
    transition:transform .25s ease,box-shadow .25s ease,border-color .25s ease,border-radius .2s ease;
}
.sg-page .tp-sec:not(.collapsed) .tp-sec-header{border-radius:var(--sg-radius) var(--sg-radius) 0 0;}
.sg-page .tp-sec-header:hover{transform:translateY(-3px);border-color:transparent;box-shadow:0 16px 38px rgba(102,126,234,.18);}
.sg-page .tp-sec-header:focus-visible{outline:2px solid rgba(102,126,234,.45);outline-offset:2px;}
.sg-page .tp-sec-header .tp-section-head{margin:0;}
.sg-page .tp-sec-header::after{
    content:"";position:absolute;inset:0;border-radius:inherit;padding:2px;
    background:conic-gradient(from var(--tp-ring-a),#667eea,#764ba2,#ec4899,#f59e0b,#10b981,#667eea);
    -webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
    -webkit-mask-composite:xor;
            mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);
            mask-composite:exclude;
    opacity:0;transition:opacity .35s ease;pointer-events:none;z-index:2;
}
.sg-page .tp-sec-header:hover::after,.sg-page .tp-sec-header:focus-visible::after{opacity:1;animation:tpRingSpin 3.5s linear infinite;}
.sg-page .tp-sec-actions{display:flex;align-items:center;gap:10px;flex-shrink:0;margin-left:auto;position:relative;z-index:3;}
.sg-page .tp-sec-chev{width:36px;height:36px;border-radius:11px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:13px;color:#4f46e5;background:rgba(99,102,241,.12);transition:background .2s ease,box-shadow .2s ease;}
.sg-page .tp-sec-header:hover .tp-sec-chev{background:rgba(99,102,241,.20);box-shadow:0 6px 14px rgba(102,126,234,.20);}
.sg-page .tp-sec-chev i{display:inline-block;transition:transform .3s cubic-bezier(.34,1.56,.64,1);}
.sg-page .tp-sec:not(.collapsed) .tp-sec-chev i{transform:rotate(180deg);}
.sg-page .tp-sec-body{
    display:block;
    background:linear-gradient(180deg,#e1e8f5 0%,#d8e1ef 100%);
    border:1.5px solid #d4dded;border-top:none;
    border-radius:0 0 var(--sg-radius) var(--sg-radius);
    padding:20px;margin-top:-1.5px;
    box-shadow:inset 0 10px 18px -12px rgba(30,41,59,.24),0 8px 24px rgba(30,41,59,.07);
}
.sg-page .tp-sec.collapsed .tp-sec-body{display:none;}
@keyframes tpSecIn{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:none;}}
.sg-page .tp-sec-body.tp-sec-anim{animation:tpSecIn .3s ease both;}
@media (max-width:768px){
    .sg-page .tp-sec-header{flex-wrap:nowrap;padding:14px;gap:10px;}
    .sg-page .tp-sec-header .tp-section-text p{display:none;}
    .sg-page .tp-sec-actions{margin-left:auto;}
}

@media (prefers-reduced-motion:reduce){
    .sg-page.tp-anim-ready .tp-reveal,.sg-page.tp-anim-ready .tp-reveal.tp-revealed{opacity:1!important;animation:none!important;}
    .sg-page .tp-cx::before{opacity:0!important;animation:none!important;}
    .sg-page .tp-cx,.sg-page .tp-cx-chev,.sg-page .tp-cx-chev i,.sg-page .tp-cx-body,.sg-page .tp-cx-inner,.sg-page .tp-cx-inner>*{transition-duration:.01ms!important;animation:none!important;}
    .sg-page .tp-sec-header::after{animation:none!important;}
    .sg-page .tp-sec-header:hover{transform:none!important;}
    .sg-page .tp-sec-chev i,.sg-page .tp-sec-body.tp-sec-anim{transition:none!important;animation:none!important;}
}
</style>

<div style="margin:8px 0 18px;">
    <a href="/study-group" style="display:inline-flex;align-items:center;gap:8px;color:#4f46e5;font-weight:600;font-size:13.5px;text-decoration:none;">
        <i class="fas fa-arrow-left"></i> Back to Available Tutors
    </a>
</div>

<!-- ===== Profile header card ===== -->
<section class="tp-section">
    <div class="tp-card" style="padding:26px;display:flex;gap:24px;flex-wrap:wrap;">
        <?php if ($isOwnProfile): ?>
        <form id="tutorAvatarUploadForm" method="POST" action="/tutor/upload-image" enctype="multipart/form-data" style="margin:0;">
            <input type="hidden" name="redirect" value="/tutor/view/<?php echo (int)$tutor['id']; ?>">
        </form>
        <?php endif; ?>
        <label class="tp-avatar" for="tutorQuickPhotoInput" style="overflow:hidden;position:relative;cursor:<?php echo $isOwnProfile ? 'pointer' : 'default'; ?>;">
            <?php if (!empty($tutor['profile_image'])): ?>
                <img src="/tutor/profile-image/<?php echo (int)$tutor['id']; ?>?v=<?php echo urlencode($tutor['profile_image']); ?>"
                     alt="Profile picture" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';">
            <?php else: ?>
                <?php echo htmlspecialchars($initials); ?>
            <?php endif; ?>
            <?php if ($isOwnProfile): ?>
                <span style="position:absolute;bottom:0;left:0;right:0;background:rgba(15,23,41,0.65);color:#fff;font-size:10px;font-weight:600;text-align:center;padding:3px 0;">
                    <i class="fas fa-camera"></i> Photo
                </span>
                <input type="file" id="tutorQuickPhotoInput" name="profile_image" accept=".jpg,.jpeg,.png,.webp,.gif" form="tutorAvatarUploadForm" style="display:none;"
                       onchange="if (this.files && this.files.length) { document.getElementById('tutorAvatarUploadForm').submit(); }">
            <?php endif; ?>
        </label>
        <div style="flex:1;min-width:240px;">
            <h1 style="font-size:26px;margin:0;color:var(--sg-ink);letter-spacing:-.02em;">
                <?php echo htmlspecialchars($tutor['full_name'] ?: ($tutor['username'] ?? 'Tutor')); ?>
            </h1>
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-top:4px;color:var(--sg-muted);font-size:13px;">
                <?php if (!empty($tutor['username'])): ?><span>@<?php echo htmlspecialchars($tutor['username']); ?></span><?php endif; ?>
                <?php if ($memberSince): ?><span><i class="fas fa-calendar-alt" style="color:#94a3b8;"></i> Tutoring since <?php echo htmlspecialchars($memberSince); ?></span><?php endif; ?>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:14px;">
                <?php if (!empty($tutor['company_name'])): ?>
                    <span style="display:inline-flex;align-items:center;gap:7px;background:#fffbeb;color:#92400e;font-size:12.5px;font-weight:600;padding:6px 14px;border-radius:999px;border:1px solid #fde68a;">
                        <i class="fas fa-building"></i> <?php echo htmlspecialchars($tutor['company_name']); ?>
                    </span>
                <?php endif; ?>
                <span style="display:inline-flex;align-items:center;gap:7px;background:<?php echo $modeMeta['bg']; ?>;color:<?php echo $modeMeta['color']; ?>;font-size:12.5px;font-weight:600;padding:6px 14px;border-radius:999px;border:1px solid <?php echo $modeMeta['border']; ?>;">
                    <i class="fas fa-<?php echo $modeMeta['icon']; ?>"></i> <?php echo $modeMeta['label']; ?>
                </span>
            </div>

            <?php if (!empty($subjects)): ?>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:14px;">
                    <?php foreach ($subjects as $subj): ?>
                        <span class="tp-subject-pill"><?php echo htmlspecialchars($subj); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="display:flex;flex-wrap:wrap;gap:16px;margin-top:14px;font-size:13px;color:var(--sg-muted);">
                <?php if (!empty($tutor['hourly_rate'])): ?>
                    <span><i class="fas fa-tag" style="color:#10b981;"></i>
                        <?php
                        $billingType = $tutor['billing_type'] ?? 'hourly';
                        echo htmlspecialchars($tutor['hourly_rate']);
                        echo $billingType === 'monthly' ? '/month' : '/hour';
                        ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($tutor['phone'])): ?>
                    <span><i class="fas fa-phone" style="color:#667eea;"></i> <?php echo htmlspecialchars($tutor['phone']); ?></span>
                <?php endif; ?>
                <?php if (!empty($tutor['email'])): ?>
                    <span><i class="fas fa-envelope" style="color:#667eea;"></i> <?php echo htmlspecialchars($tutor['email']); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;min-width:240px;justify-content:center;">
            <?php if (!$isOwnProfile): ?>
                <button type="button" onclick="openTutorRequestModal()" class="tp-cta" style="width:100%;justify-content:center;">
                    <i class="fas fa-user-plus"></i> Request Tutor
                </button>
            <?php else: ?>
                <button type="button" onclick="document.getElementById('tutorEditProfileModal').style.display='block'" class="tp-cta" style="width:100%;justify-content:center;">
                    <i class="fas fa-pen"></i> Edit Profile
                </button>
                <div style="background:rgba(16,185,129,0.10);border:1px solid rgba(16,185,129,0.30);color:#047857;font-size:12.5px;font-weight:600;padding:12px 16px;border-radius:13px;text-align:center;">
                    <i class="fas fa-id-badge"></i> This is your tutor profile.<br>
                    <span style="font-weight:500;">Students see this page when they click &quot;View Tutor&quot;.</span>
                </div>
            <?php endif; ?>
            <?php if (!empty($tutor['email'])): ?>
                <a href="mailto:<?php echo htmlspecialchars($tutor['email']); ?>?subject=Tutoring%20Request" class="tp-cta" style="background:#fff;color:#4f46e5;border:1.5px solid #c7d2fe;box-shadow:none;justify-content:center;">
                    <i class="fas fa-envelope"></i> Contact
                </a>
            <?php endif; ?>
            <?php if (!empty($tutor['phone'])): ?>
                <a href="tel:<?php echo htmlspecialchars($tutor['phone']); ?>" class="tp-cta" style="background:#fff;color:#4f46e5;border:1.5px solid #c7d2fe;box-shadow:none;justify-content:center;">
                    <i class="fas fa-phone"></i> Call
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===== Subjects & Grades ===== -->
<section class="tp-section tp-sec collapsed" id="tpSecSubjects">
    <div class="tp-sec-header" role="button" tabindex="0" aria-expanded="false" aria-controls="tpSecSubjectsBody" onclick="tpToggleSection(this)" onkeydown="tpSectionKey(event, this)">
    <div class="tp-section-head">
        <span class="tp-section-icon alt-blue"><i class="fas fa-book-open"></i></span>
        <div class="tp-section-text">
            <h2>Subjects &amp; Grades</h2>
            <p>The subjects this tutor teaches and the grades or levels they cover.</p>
        </div>
    </div>
        <div class="tp-sec-actions"><span class="tp-sec-chev" aria-hidden="true"><i class="fas fa-chevron-down"></i></span></div>
    </div><!-- /.tp-sec-header -->
    <div class="tp-sec-body" id="tpSecSubjectsBody">

    <?php
    // Build structured subject + grade list from the comma-separated subjects string.
    // Format examples: "Mathematics (Grade 10-12)", "Physical Sciences", "English (All Grades)"
    $subjectsWithGrades = [];
    foreach ($subjects as $subj) {
        $name = $subj;
        $grade = '';
        if (preg_match('/^(.+?)\s*\(([^)]+)\)\s*$/', $subj, $m)) {
            $name = trim($m[1]);
            $grade = trim($m[2]);
        }
        $subjectsWithGrades[] = ['name' => $name, 'grade' => $grade];
    }
    ?>

    <?php if (!empty($subjectsWithGrades)): ?>
        <div class="tp-grid-3" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
            <?php foreach ($subjectsWithGrades as $item):
                $subjectIcons = [
                    'mathematics' => 'fa-square-root-alt',
                    'math' => 'fa-square-root-alt',
                    'maths' => 'fa-square-root-alt',
                    'physical sciences' => 'fa-flask',
                    'physics' => 'fa-atom',
                    'chemistry' => 'fa-flask',
                    'biology' => 'fa-dna',
                    'life sciences' => 'fa-dna',
                    'english' => 'fa-language',
                    'afrikaans' => 'fa-language',
                    'isizulu' => 'fa-language',
                    'history' => 'fa-landmark',
                    'geography' => 'fa-globe-africa',
                    'accounting' => 'fa-calculator',
                    'economics' => 'fa-chart-line',
                    'business studies' => 'fa-briefcase',
                    'computer' => 'fa-laptop-code',
                    'coding' => 'fa-laptop-code',
                    'programming' => 'fa-laptop-code',
                    'music' => 'fa-music',
                    'art' => 'fa-palette',
                ];
                $lcName = strtolower($item['name']);
                $icon = 'fa-book';
                foreach ($subjectIcons as $key => $val) {
                    if (strpos($lcName, $key) !== false) {
                        $icon = $val;
                        break;
                    }
                }
            ?>
                <div class="tp-subject-card">
                    <div class="tp-subject-icon">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <div style="min-width:0;">
                        <p class="tp-subject-name"><?php echo htmlspecialchars($item['name']); ?></p>
                        <?php if ($item['grade'] !== ''): ?>
                            <p class="tp-subject-grade"><i class="fas fa-graduation-cap" style="color:#667eea;margin-right:5px;"></i><?php echo htmlspecialchars($item['grade']); ?></p>
                        <?php else: ?>
                            <p class="tp-subject-grade" style="font-style:italic;">Grade not specified</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="tp-card" style="text-align:center;padding:34px 22px;">
            <div style="width:56px;height:56px;border-radius:50%;background:rgba(99,102,241,0.12);color:#6366f1;display:inline-flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:12px;"><i class="fas fa-book-open"></i></div>
            <h3 style="font-size:16px;margin:0 0 6px;color:var(--sg-ink);">No subjects listed yet</h3>
            <p style="color:var(--sg-muted);font-size:13.5px;margin:0;">This tutor hasn't added the subjects they teach.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($gradesOffered)): ?>
        <div class="tp-card" style="margin-top:20px;">
            <h3 style="font-size:15px;margin:0 0 12px;color:var(--sg-ink);"><i class="fas fa-layer-group" style="color:#8b5cf6;"></i> All Grades / Levels Covered</h3>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach (array_filter(array_map('trim', explode(',', $gradesOffered))) as $g): ?>
                    <span class="tp-grade-pill"><i class="fas fa-check" style="margin-right:5px;"></i><?php echo htmlspecialchars($g); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    </div><!-- /.tp-sec-body -->
</section>

<!-- ===== About + How they teach ===== -->
<section class="tp-section tp-sec collapsed" id="tpSecAbout">
    <div class="tp-sec-header" role="button" tabindex="0" aria-expanded="false" aria-controls="tpSecAboutBody" onclick="tpToggleSection(this)" onkeydown="tpSectionKey(event, this)">
        <div class="tp-section-head">
            <span class="tp-section-icon alt-green"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="tp-section-text">
                <h2>About &amp; How They Teach</h2>
                <p>Who this tutor is, how lessons are delivered, and what to expect.</p>
            </div>
        </div>
        <div class="tp-sec-actions"><span class="tp-sec-chev" aria-hidden="true"><i class="fas fa-chevron-down"></i></span></div>
    </div><!-- /.tp-sec-header -->

    <div class="tp-sec-body" id="tpSecAboutBody">
    <div class="tp-grid-2" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
        <div class="tp-card tp-cx" tabindex="0" aria-expanded="false">
            <div class="tp-cx-top">
                <h3 style="font-size:15px;margin:0;color:var(--sg-ink);"><i class="fas fa-user" style="color:#10b981;"></i> About the Tutor</h3>
                <span class="tp-cx-chev"><i class="fas fa-chevron-down"></i></span>
            </div>
            <div class="tp-cx-body"><div class="tp-cx-inner">
            <?php if (!empty($tutor['bio'])): ?>
                <p style="color:var(--sg-muted);font-size:13.5px;line-height:1.7;margin:0;white-space:pre-line;"><?php echo htmlspecialchars($tutor['bio']); ?></p>
            <?php else: ?>
                <p style="color:var(--sg-faint);font-size:13.5px;margin:0;font-style:italic;">This tutor hasn't written a bio yet.</p>
            <?php endif; ?>
            </div></div>
        </div>

        <div class="tp-card tp-cx" tabindex="0" aria-expanded="false">
            <div class="tp-cx-top">
                <h3 style="font-size:15px;margin:0;color:var(--sg-ink);"><i class="fas fa-lightbulb" style="color:#f59e0b;"></i> Teaching Approach</h3>
                <span class="tp-cx-chev"><i class="fas fa-chevron-down"></i></span>
            </div>
            <div class="tp-cx-body"><div class="tp-cx-inner">
            <?php if (!empty($tutor['teaching_approach'])): ?>
                <p style="color:var(--sg-muted);font-size:13.5px;line-height:1.7;margin:0 0 14px;white-space:pre-line;"><?php echo htmlspecialchars($tutor['teaching_approach']); ?></p>
            <?php else: ?>
                <p style="color:var(--sg-faint);font-size:13.5px;margin:0 0 14px;font-style:italic;">No teaching approach description provided yet.</p>
            <?php endif; ?>
            <div style="border-top:1px solid var(--sg-line);padding-top:14px;">
                <div style="display:inline-flex;align-items:center;gap:8px;background:<?php echo $modeMeta['bg']; ?>;color:<?php echo $modeMeta['color']; ?>;font-size:12.5px;font-weight:700;padding:6px 14px;border-radius:999px;border:1px solid <?php echo $modeMeta['border']; ?>;margin-bottom:10px;">
                    <i class="fas fa-<?php echo $modeMeta['icon']; ?>"></i> <?php echo $modeMeta['label']; ?>
                </div>
                <p style="color:var(--sg-muted);font-size:13px;line-height:1.6;margin:0;"><?php echo $modeMeta['desc']; ?></p>
            </div>
            </div></div>
        </div>
    </div>
    </div><!-- /.tp-sec-body -->
</section>
<!-- ===== Our Tutors ===== -->
<section class="tp-section tp-sec collapsed" id="tpSecTeam">
    <div class="tp-sec-header" role="button" tabindex="0" aria-expanded="false" aria-controls="tpSecTeamBody" onclick="tpToggleSection(this)" onkeydown="tpSectionKey(event, this)">
        <div class="tp-section-head">
            <span class="tp-section-icon alt-rose"><i class="fas fa-users"></i></span>
            <div class="tp-section-text">
                <h2><?php echo !empty($tutor['company_name']) ? 'Our Tutors at ' . htmlspecialchars($tutor['company_name']) : 'Our Tutors'; ?></h2>
                <p>Meet the tutors behind this profile, their qualifications and the subjects they teach.</p>
            </div>
        </div>
        <div class="tp-sec-actions"><span class="tp-sec-chev" aria-hidden="true"><i class="fas fa-chevron-down"></i></span></div>
    </div><!-- /.tp-sec-header -->

    <div class="tp-sec-body" id="tpSecTeamBody">
    <?php if (!empty($teamMembers)): ?>
        <div class="tp-grid-3">
            <?php foreach ($teamMembers as $member): ?>
                <div class="tp-card tp-cx" tabindex="0" aria-expanded="false">
                    <div class="tp-cx-top">
                    <div style="display:flex;align-items:center;gap:12px;min-width:0;">
                        <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#f43f5e,#ec4899);color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;flex-shrink:0;">
                            <?php
                            $mInitials = '';
                            foreach (preg_split('/\s+/', trim($member['name'])) as $mp) {
                                if ($mp !== '') $mInitials .= strtoupper(mb_substr($mp, 0, 1));
                                if (mb_strlen($mInitials) >= 2) break;
                            }
                            echo htmlspecialchars($mInitials !== '' ? $mInitials : 'T');
                            ?>
                        </div>
                        <div style="font-weight:700;font-size:14px;color:var(--sg-ink);"><?php echo htmlspecialchars($member['name']); ?></div>
                    </div>
                        <span class="tp-cx-chev"><i class="fas fa-chevron-down"></i></span>
                    </div><!-- /.tp-cx-top -->
                    <div class="tp-cx-body"><div class="tp-cx-inner">
                    <?php if (!empty($member['qualifications'])): ?>
                        <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(59,130,246,0.10);color:#1d4ed8;font-size:12px;font-weight:600;padding:5px 10px;border-radius:999px;border:1px solid rgba(59,130,246,0.25);width:fit-content;">
                            <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($member['qualifications']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($member['grades'])): ?>
                        <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(139,92,246,0.10);color:#7c3aed;font-size:12px;font-weight:600;padding:5px 10px;border-radius:999px;border:1px solid rgba(139,92,246,0.25);width:fit-content;">
                            <i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($member['grades']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($member['subjects'])): ?>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            <?php foreach ($member['subjects'] as $ms): ?>
                                <span class="tp-subject-pill"><?php echo htmlspecialchars($ms); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <span style="color:var(--sg-faint);font-size:12.5px;font-style:italic;">Subjects not listed</span>
                    <?php endif; ?>
                    </div></div><!-- /.tp-cx-body -->
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="tp-card" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
            <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#f43f5e,#ec4899);color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:700;flex-shrink:0;">
                <?php echo htmlspecialchars($initials); ?>
            </div>
            <div style="flex:1;min-width:200px;">
                <div style="font-weight:700;font-size:14.5px;color:var(--sg-ink);">
                    <?php echo htmlspecialchars($tutor['full_name'] ?: ($tutor['username'] ?? 'Tutor')); ?>
                    <span style="color:#ec4899;font-weight:600;">(Lead Tutor)</span>
                </div>
                <div style="color:var(--sg-muted);font-size:12.5px;margin-top:2px;">Teaches all lessons personally<?php echo !empty($tutor['company_name']) ? ' at ' . htmlspecialchars($tutor['company_name']) : ''; ?>.</div>
            </div>
            <?php if (!empty($subjects)): ?>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <?php foreach ($subjects as $subj): ?>
                        <span class="tp-subject-pill"><?php echo htmlspecialchars($subj); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </div><!-- /.tp-sec-body -->
</section>

<!-- ===== Tutoring packages ===== -->
<section class="tp-section tp-sec collapsed" id="tpSecPackages">
    <div class="tp-sec-header" role="button" tabindex="0" aria-expanded="false" aria-controls="tpSecPackagesBody" onclick="tpToggleSection(this)" onkeydown="tpSectionKey(event, this)">
        <div class="tp-section-head">
            <span class="tp-section-icon alt-blue"><i class="fas fa-box-open"></i></span>
            <div class="tp-section-text">
                <h2>Tutoring Packages</h2>
                <p>Single sessions, group sessions and bundled packages on offer.</p>
            </div>
        </div>
        <div class="tp-sec-actions"><span class="tp-sec-chev" aria-hidden="true"><i class="fas fa-chevron-down"></i></span></div>
    </div><!-- /.tp-sec-header -->

    <div class="tp-sec-body" id="tpSecPackagesBody">
    <?php if (!empty($packages)): ?>
        <div class="tp-grid-3">
            <?php foreach ($packages as $pkg):
                $pkgTypeMeta = [
                    'Group Session' => ['bg' => 'rgba(245,158,11,0.12)', 'color' => '#b45309', 'border' => 'rgba(245,158,11,0.35)', 'icon' => 'fa-users'],
                    'Single Session' => ['bg' => 'rgba(16,185,129,0.12)', 'color' => '#059669', 'border' => 'rgba(16,185,129,0.35)', 'icon' => 'fa-user'],
                    'Package' => ['bg' => 'rgba(59,130,246,0.12)', 'color' => '#1d4ed8', 'border' => 'rgba(59,130,246,0.35)', 'icon' => 'fa-cube'],
                ][$pkg['type']];
            ?>
                <div class="tp-card tp-cx" tabindex="0" aria-expanded="false">
                    <div class="tp-cx-top">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex:1;min-width:0;">
                        <h3 style="font-size:15px;margin:0;color:var(--sg-ink);"><?php echo htmlspecialchars($pkg['name']); ?></h3>
                        <span style="display:inline-flex;align-items:center;gap:6px;background:<?php echo $pkgTypeMeta['bg']; ?>;color:<?php echo $pkgTypeMeta['color']; ?>;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;border:1px solid <?php echo $pkgTypeMeta['border']; ?>;white-space:nowrap;">
                            <i class="fas <?php echo $pkgTypeMeta['icon']; ?>"></i> <?php echo $pkg['type']; ?>
                        </span>
                    </div>
                        <span class="tp-cx-chev"><i class="fas fa-chevron-down"></i></span>
                    </div><!-- /.tp-cx-top -->
                    <div class="tp-cx-body"><div class="tp-cx-inner">
                    <?php if ($pkg['price'] !== ''): ?>
                        <div style="font-size:22px;font-weight:800;color:#10b981;letter-spacing:-.02em;"><?php echo htmlspecialchars($pkg['price']); ?></div>
                    <?php endif; ?>
                    <?php if ($pkg['description'] !== ''): ?>
                        <p style="color:var(--sg-muted);font-size:13px;line-height:1.6;margin:0;flex:1;"><?php echo htmlspecialchars($pkg['description']); ?></p>
                    <?php endif; ?>
                    <div style="border-top:1px solid var(--sg-line);padding-top:12px;margin-top:auto;">
                        <span style="color:var(--sg-muted);font-size:12.5px;"><i class="fas fa-check-circle" style="color:#10b981;"></i> Request this tutor and mention "<?php echo htmlspecialchars($pkg['name']); ?>" when they contact you.</span>
                    </div>
                    </div></div><!-- /.tp-cx-body -->
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="tp-card" style="text-align:center;padding:34px 22px;">
            <div style="width:56px;height:56px;border-radius:50%;background:rgba(14,165,233,0.12);color:#0ea5e9;display:inline-flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:12px;"><i class="fas fa-box-open"></i></div>
            <h3 style="font-size:16px;margin:0 0 6px;color:var(--sg-ink);">No packages listed yet</h3>
            <p style="color:var(--sg-muted);font-size:13.5px;margin:0;">Use the contact details above to ask about single or group session rates.</p>
        </div>
    <?php endif; ?>
    </div><!-- /.tp-sec-body -->
</section>

<?php include __DIR__ . '/../components/tutor_request_modal.php'; ?>

</div><!-- /.sg-page -->

<?php if ($isOwnProfile): ?>
    <?php include __DIR__ . '/../components/tutor_edit_modal.php'; ?>
<?php endif; ?>

<script>
    // ---- Entrance reveal: staggered rise as cards scroll into view ----
    (function () {
        var page = document.querySelector('.sg-page');
        if (!page || !('IntersectionObserver' in window)) return;
        var cards = page.querySelectorAll('.tp-card, .tp-subject-card');
        if (!cards.length) return;
        page.classList.add('tp-anim-ready');
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                io.unobserve(el);
                var idx = 0, n = 0, sibs = el.parentElement ? el.parentElement.children : [];
                for (var i = 0; i < sibs.length; i++) {
                    if (sibs[i].classList && sibs[i].classList.contains('tp-reveal')) {
                        if (sibs[i] === el) idx = n;
                        n++;
                    }
                }
                el.style.setProperty('--tp-delay', (Math.min(idx, 7) * 75) + 'ms');
                el.classList.add('tp-revealed');
                var cleaned = false;
                var cleanup = function () {
                    if (cleaned) return;
                    cleaned = true;
                    el.classList.remove('tp-reveal', 'tp-revealed');
                };
                el.addEventListener('animationend', function (e) {
                    if (e.target === el && !e.pseudoElement) cleanup();
                });
                setTimeout(cleanup, 1500);
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        for (var i = 0; i < cards.length; i++) cards[i].classList.add('tp-reveal');
        for (var j = 0; j < cards.length; j++) io.observe(cards[j]);
    })();

    // ---- Expandable cards: title/name only, click to expand — only one open at a time ----
    Array.prototype.forEach.call(document.querySelectorAll('.tp-cx'), function (card) {
        var toggle = function () {
            var open = card.classList.toggle('tp-expanded');
            card.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                Array.prototype.forEach.call(document.querySelectorAll('.tp-cx.tp-expanded'), function (other) {
                    if (other !== card) {
                        other.classList.remove('tp-expanded');
                        other.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        };
        card.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('a, button, form, input, select, textarea, label')) return;
            toggle();
        });
        card.addEventListener('keydown', function (e) {
            if (e.target !== card || (e.key !== 'Enter' && e.key !== ' ')) return;
            e.preventDefault();
            toggle();
        });
    });

    // ---- Section dropdowns (Subjects / About / Team / Packages) ----
    function tpToggleSection(header) {
        var sec = header.closest('.tp-sec');
        if (!sec) return;
        var collapsed = sec.classList.toggle('collapsed');
        header.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        var body = sec.querySelector('.tp-sec-body');
        if (body && !collapsed) {
            body.classList.remove('tp-sec-anim');
            void body.offsetWidth;
            body.classList.add('tp-sec-anim');
        }
    }

    function tpSectionKey(e, header) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            tpToggleSection(header);
        }
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>




