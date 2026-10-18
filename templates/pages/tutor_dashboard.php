<?php
$pageTitle = 'Tutor Dashboard - StudySmart';
$currentPage = 'tutor-dashboard';
include __DIR__ . '/../layouts/header.php';

$subjects = array_filter(array_map('trim', explode(',', $tutorProfile['subjects'] ?? '')));
$billingType = $tutorProfile['billing_type'] ?? 'hourly';

// Parse company team for the dashboard management UI.
$companyTeam = [];
foreach (preg_split('/\r\n|\r|\n/', $tutorProfile['company_team'] ?? '') as $line) {
    $line = trim($line);
    if ($line === '') continue;
    $parts = array_map('trim', explode('|', $line));
    $member = ['name' => $parts[0] ?? '', 'qualifications' => '', 'subjects' => '', 'grades' => ''];
    if (count($parts) >= 4) {
        $member['qualifications'] = $parts[1] ?? '';
        $member['subjects'] = $parts[2] ?? '';
        $member['grades'] = $parts[3] ?? '';
    } elseif (count($parts) === 3) {
        $member['qualifications'] = $parts[1] ?? '';
        $member['subjects'] = $parts[2] ?? '';
    } else {
        $member['subjects'] = $parts[1] ?? '';
    }
    if ($member['name'] === '') continue;
    $companyTeam[] = $member;
}
?>

<div class="sg-page">
<style>
/* ===== Tutor Dashboard — scoped premium styles ===== */
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
.sg-page .title{font-size:30px;margin:0;letter-spacing:-.02em;}
.sg-page .subtitle{color:var(--sg-muted);font-size:15px;margin:6px 0 0;max-width:640px;}
.sg-page .sg-header{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin:8px 0 28px;}
.sg-page .sg-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(245,158,11,.12);color:#b45309;font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;padding:6px 12px;border-radius:999px;margin-bottom:12px;}
.sg-page .td-section{margin-bottom:32px;}
.sg-page .td-section-head{display:flex;gap:14px;align-items:flex-start;margin:0 0 18px;}
.sg-page .td-section-icon{width:44px;height:44px;border-radius:13px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));box-shadow:0 8px 18px rgba(102,126,234,.30);}
.sg-page .td-section-icon.alt-amber{background:linear-gradient(135deg,#f59e0b,#f97316);box-shadow:0 8px 18px rgba(245,158,11,.30);}
.sg-page .td-section-icon.alt-green{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 18px rgba(16,185,129,.28);}
.sg-page .td-section-icon.alt-blue{background:linear-gradient(135deg,#0ea5e9,#2563eb);box-shadow:0 8px 18px rgba(14,165,233,.28);}
.sg-page .td-section-text h2{font-size:19px;margin:2px 0 4px;color:var(--sg-ink);letter-spacing:-.01em;border-left:none;padding-left:0;}
.sg-page .td-section-text p{color:var(--sg-muted);font-size:13.5px;margin:0;}
.sg-page .td-card{background:#fff;border:1px solid var(--sg-line);border-radius:var(--sg-radius);box-shadow:var(--sg-shadow);transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease;}
.sg-page .td-card:hover{transform:translateY(-2px);box-shadow:0 14px 34px rgba(30,41,59,.08);}
/* Profile card */
.sg-page .td-profile{display:flex;gap:22px;padding:26px;align-items:flex-start;flex-wrap:wrap;}
.sg-page .td-avatar{width:76px;height:76px;border-radius:20px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:30px;font-weight:800;color:#fff;background:linear-gradient(135deg,#f59e0b,#f97316);box-shadow:0 14px 28px rgba(245,158,11,.35);}
.sg-page .td-profile-info{flex:1;min-width:240px;}
.sg-page .td-profile-info h3{font-size:22px;margin:0;color:var(--sg-ink);}
.sg-page .td-profile-info .td-role{color:var(--sg-muted);font-size:13.5px;margin:3px 0 0;}
.sg-page .td-subject-pills{display:flex;flex-wrap:wrap;gap:6px;margin-top:12px;}
.sg-page .td-subject-pill{background:#fef3c7;color:#b45309;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;}
.sg-page .td-profile-meta{display:flex;flex-wrap:wrap;gap:16px;margin-top:14px;font-size:13px;color:var(--sg-muted);}
.sg-page .td-profile-meta span{display:inline-flex;align-items:center;gap:6px;}
.sg-page .td-billing{display:inline-flex;align-items:center;gap:7px;background:#ecfdf5;color:#047857;font-size:12.5px;font-weight:700;padding:6px 13px;border-radius:999px;border:1px solid #a7f3d0;}
.sg-page .td-company{display:inline-flex;align-items:center;gap:7px;background:#fffbeb;color:#92400e;font-size:12.5px;font-weight:600;padding:5px 12px;border-radius:999px;border:1px solid #fde68a;}
.sg-page .td-edit-btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:var(--sg-brand);font-weight:600;font-size:13.5px;padding:10px 16px;border-radius:12px;border:1.5px solid rgba(102,126,234,.35);background:linear-gradient(135deg,rgba(102,126,234,.06),rgba(118,75,162,.06));transition:all .2s ease;align-self:flex-start;}
.sg-page .td-edit-btn:hover{background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;border-color:transparent;box-shadow:0 10px 20px rgba(102,126,234,.30);}
/* Stats */
.sg-page .td-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px;}
.sg-page .td-stat{background:#fff;border:1px solid var(--sg-line);border-radius:var(--sg-radius);padding:20px;box-shadow:var(--sg-shadow);display:flex;align-items:center;gap:14px;}
.sg-page .td-stat-icon{width:48px;height:48px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:20px;color:#fff;flex-shrink:0;}
.sg-page .td-stat-num{font-size:26px;font-weight:800;color:var(--sg-ink);line-height:1;}
.sg-page .td-stat-label{font-size:12.5px;color:var(--sg-muted);margin-top:4px;}
.sg-page a.td-stat{text-decoration:none;transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease;}
.sg-page a.td-stat:hover{transform:translateY(-3px);box-shadow:0 14px 34px rgba(16,185,129,.16);border-color:rgba(16,185,129,.35);}
.sg-page a.td-stat .td-stat-open{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;color:#047857;margin-top:6px;}
/* My Students clickable banner */
.sg-page .td-ms-banner{display:flex;align-items:center;gap:16px;background:linear-gradient(135deg,rgba(102,126,234,.08),rgba(118,75,162,.08));border:1.5px solid rgba(102,126,234,.25);border-radius:var(--sg-radius);padding:20px 22px;margin-bottom:16px;text-decoration:none;transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease;flex-wrap:wrap;}
.sg-page .td-ms-banner:hover{transform:translateY(-3px);box-shadow:0 14px 34px rgba(102,126,234,.20);border-color:rgba(102,126,234,.45);}
.sg-page .td-ms-banner-icon{width:52px;height:52px;border-radius:15px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:20px;color:#fff;background:linear-gradient(135deg,#667eea,#764ba2);box-shadow:0 10px 22px rgba(102,126,234,.30);}
.sg-page .td-ms-banner h3{font-size:16px;margin:0;color:var(--sg-ink);}
.sg-page .td-ms-banner p{font-size:13px;color:var(--sg-muted);margin:4px 0 0;}
.sg-page .td-ms-banner-cta{margin-left:auto;flex-shrink:0;display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;padding:11px 18px;border-radius:12px;font-size:13.5px;font-weight:600;box-shadow:0 8px 18px rgba(102,126,234,.30);}
/* Rows */
.sg-page .td-row{display:flex;align-items:center;gap:16px;padding:18px 22px;border-bottom:1px solid var(--sg-line);flex-wrap:wrap;}
.sg-page .td-row:last-child{border-bottom:none;}
.sg-page .td-row-avatar{width:44px;height:44px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#fff;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));flex-shrink:0;}
.sg-page .td-row-info{flex:1;min-width:180px;}
.sg-page .td-row-name{font-size:15px;font-weight:700;color:var(--sg-ink);margin:0;}
.sg-page .td-row-sub{font-size:12.5px;color:var(--sg-muted);margin:2px 0 0;display:flex;flex-wrap:wrap;gap:12px;}
.sg-page .td-row-sub span{display:inline-flex;align-items:center;gap:5px;}
/* Buttons */
.sg-page .td-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 15px;border-radius:11px;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:transform .18s,box-shadow .18s,filter .18s;font-family:inherit;}
.sg-page .td-btn:hover{transform:translateY(-2px);}
.sg-page .td-btn-approve{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 6px 16px rgba(16,185,129,.28);}
.sg-page .td-btn-reject,.sg-page .td-btn-remove{background:#fee2e2;color:#dc2626;border:1px solid #fecaca;}
.sg-page .td-btn-reject:hover,.sg-page .td-btn-remove:hover{background:#fecaca;}
.sg-page .td-btn-save{background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;box-shadow:0 6px 16px rgba(102,126,234,.28);}
/* Badges */
.sg-page .td-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:999px;font-size:11px;font-weight:700;white-space:nowrap;}
.sg-page .td-badge.online{background:#dbeafe;color:#1e40af;}
.sg-page .td-badge.contact{background:#fce7f3;color:#be185d;}
.sg-page .td-badge.extra{background:#ede9fe;color:#6d28d9;}
.sg-page .td-badge.eft{background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;}
.sg-page .td-badge.pending{background:#fef3c7;color:#b45309;}
/* Settings mini-form */
.sg-page .td-settings{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.sg-page .td-settings select{padding:8px 11px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:12.5px;font-family:inherit;color:var(--sg-ink);background:#fff;}
.sg-page .td-toggle{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--sg-muted);cursor:pointer;user-select:none;}
.sg-page .td-toggle input{accent-color:var(--sg-brand);width:16px;height:16px;cursor:pointer;}
/* Empty */
.sg-page .td-empty{background:#fff;border:1.5px dashed #e2e8f0;border-radius:var(--sg-radius);padding:40px 26px;text-align:center;box-shadow:var(--sg-shadow);}
.sg-page .td-empty .td-empty-icon{width:60px;height:60px;border-radius:16px;margin:0 auto 14px;display:inline-flex;align-items:center;justify-content:center;font-size:24px;color:var(--sg-brand);background:rgba(102,126,234,.10);}
.sg-page .td-empty h3{font-size:16px;margin:0 0 6px;color:var(--sg-ink);}
.sg-page .td-empty p{color:var(--sg-muted);font-size:13.5px;margin:0;}
/* Subjects card */
.sg-page .td-subjects-card{padding:26px;}
.sg-page .td-subjects-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;}
.sg-page .td-subject-item{background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:18px;display:flex;align-items:center;gap:14px;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease;}
.sg-page .td-subject-item:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(30,41,59,.08);border-color:#c7d2fe;}
.sg-page .td-subject-icon{width:46px;height:46px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;background:linear-gradient(135deg,#667eea,#764ba2);flex-shrink:0;}
.sg-page .td-subject-details{flex:1;min-width:0;}
.sg-page .td-subject-name{font-size:15px;font-weight:700;color:var(--sg-ink);margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sg-page .td-subject-grade{font-size:12.5px;color:var(--sg-muted);margin:3px 0 0;}
@media (max-width:768px){.sg-page .td-profile{flex-direction:column;}.sg-page .td-row{flex-direction:column;align-items:flex-start;}.sg-page .td-settings{width:100%;}.sg-page .td-subjects-grid{grid-template-columns:1fr;}}

/* ===== Entrance reveal ===== */
@keyframes tdRise{from{opacity:0;transform:translateY(26px) scale(.98);}60%{opacity:1;}to{opacity:1;transform:translateY(0) scale(1);}}
@keyframes tdChevNudge{0%,100%{transform:translateY(0);}50%{transform:translateY(3px);}}
.sg-page.td-anim-ready .td-reveal{opacity:0;}
.sg-page.td-anim-ready .td-reveal.td-revealed{animation:tdRise .65s cubic-bezier(.22,.9,.32,1.05) both;animation-delay:var(--td-delay,0ms);}

/* ===== Expandable sections: title only, click header to expand — one open at a time ===== */
.sg-page .td-cx{display:flex;flex-direction:column;gap:18px;transition:gap .4s cubic-bezier(.22,.9,.3,1);}
.sg-page .td-cx:not(.td-expanded){gap:0;}
.sg-page .td-cx-top{display:flex;align-items:center;justify-content:space-between;gap:14px;min-width:0;cursor:pointer;}
.sg-page .td-section.td-cx .td-section-head{flex:1;margin-bottom:0;min-width:0;}
.sg-page .td-cx-chev{width:34px;height:34px;border-radius:11px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:13px;color:#4f46e5;background:rgba(99,102,241,.12);transition:transform .45s cubic-bezier(.34,1.56,.64,1),background .3s ease,color .3s ease,box-shadow .3s ease;}
.sg-page .td-cx-chev i{animation:tdChevNudge 1.9s ease-in-out infinite;}
.sg-page .td-cx.td-expanded .td-cx-chev{transform:rotate(180deg);background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;box-shadow:0 8px 16px rgba(102,126,234,.35);}
.sg-page .td-cx.td-expanded .td-cx-chev i{animation:none;}
.sg-page .td-cx-top:hover .td-section-text h2{color:var(--sg-brand);}
.sg-page .td-cx-body{display:grid;grid-template-rows:0fr;opacity:0;transition:grid-template-rows .45s cubic-bezier(.4,0,.2,1),opacity .3s ease;}
.sg-page .td-cx.td-expanded .td-cx-body{grid-template-rows:1fr;opacity:1;transition:grid-template-rows .55s cubic-bezier(.22,.9,.3,1),opacity .35s ease;}
.sg-page .td-cx-inner{display:flex;flex-direction:column;gap:16px;overflow:hidden;min-height:0;visibility:hidden;transition:visibility 0s .45s;}
.sg-page .td-cx.td-expanded .td-cx-inner{visibility:visible;transition:visibility 0s 0s;}
.sg-page .td-cx-inner>*{opacity:0;transform:translateY(14px);transition:opacity .35s ease,transform .45s cubic-bezier(.22,.9,.3,1);}
.sg-page .td-cx.td-expanded .td-cx-inner>*{opacity:1;transform:none;}
.sg-page .td-cx.td-expanded .td-cx-inner>*:nth-child(1){transition-delay:.14s;}
.sg-page .td-cx.td-expanded .td-cx-inner>*:nth-child(2){transition-delay:.2s;}
.sg-page .td-cx.td-expanded .td-cx-inner>*:nth-child(3){transition-delay:.26s;}
.sg-page .td-cx.td-expanded .td-cx-inner>*:nth-child(4){transition-delay:.32s;}
.sg-page .td-cx.td-expanded .td-cx-inner>*:nth-child(n+5){transition-delay:.38s;}
@media (prefers-reduced-motion:reduce){
    .sg-page.td-anim-ready .td-reveal,.sg-page.td-anim-ready .td-reveal.td-revealed{opacity:1!important;animation:none!important;}
    .sg-page .td-cx,.sg-page .td-cx-chev,.sg-page .td-cx-chev i,.sg-page .td-cx-body,.sg-page .td-cx-inner,.sg-page .td-cx-inner>*{transition-duration:.01ms!important;animation:none!important;}
}
</style>

<!-- Page header -->
<header class="sg-header">
    <div>
        <span class="sg-eyebrow"><i class="fas fa-chalkboard-teacher"></i> Tutor Dashboard</span>
        <h1 class="title">Welcome, <?php echo htmlspecialchars($tutorProfile['full_name']); ?></h1>
        <p class="subtitle">Manage your profile, review student requests, and manage your active students.</p>
    </div>
</header>

<!-- Stats -->
<div class="td-stats">
    <a href="/tutor/my-students" class="td-stat" title="Open My Students">
        <span class="td-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="fas fa-user-check"></i></span>
        <div>
            <div class="td-stat-num"><?php echo $activeCount; ?></div>
            <div class="td-stat-label">Active Students</div>
            <span class="td-stat-open"><i class="fas fa-arrow-right"></i> Open My Students</span>
        </div>
    </a>
    <div class="td-stat">
        <span class="td-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#f97316);"><i class="fas fa-user-clock"></i></span>
        <div>
            <div class="td-stat-num"><?php echo $pendingCount; ?></div>
            <div class="td-stat-label">Pending Requests</div>
        </div>
    </div>
    <div class="td-stat">
        <span class="td-stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);"><i class="fas fa-book-open"></i></span>
        <div>
            <div class="td-stat-num"><?php echo count($subjects); ?></div>
            <div class="td-stat-label">Subjects You Teach</div>
        </div>
    </div>
    <div class="td-stat">
        <span class="td-stat-icon" style="background:linear-gradient(135deg,#0ea5e9,#2563eb);"><i class="fas fa-university"></i></span>
        <div>
            <div class="td-stat-num" style="font-size:18px;">EFT</div>
            <div class="td-stat-label">Payment Method</div>
        </div>
    </div>
</div>

<!-- Tutor Profile -->
<section class="td-section td-cx" tabindex="0" aria-expanded="false">
    <div class="td-cx-top">
    <div class="td-section-head">
        <span class="td-section-icon alt-amber"><i class="fas fa-id-card"></i></span>
        <div class="td-section-text">
            <h2>Your Tutor Profile</h2>
            <p>This is what students see when browsing available tutors.</p>
        </div>
    </div>
        <span class="td-cx-chev"><i class="fas fa-chevron-down"></i></span>
    </div><!-- /.td-cx-top -->
    <div class="td-cx-body"><div class="td-cx-inner">

    <?php if (empty($tutorProfile['is_available'])): ?>
    <div style="display:flex;align-items:center;gap:10px;background:rgba(245,158,11,0.08);border:1.5px solid rgba(245,158,11,0.35);border-radius:14px;padding:12px 16px;">
        <i class="fas fa-triangle-exclamation" style="color:#f59e0b;"></i>
        <span style="color:#92400e;font-size:13.5px;font-weight:600;">Your profile is not yet visible to students. Click "Edit Profile" to complete it — it will be listed under Available Tutors once saved.</span>
    </div>
    <?php endif; ?>

    <div class="td-card td-profile">
        <div class="td-avatar" style="overflow:hidden;<?php echo !empty($tutorProfile['profile_image']) ? 'background:#0f172a;' : ''; ?>">
            <?php if (!empty($tutorProfile['profile_image'])): ?>
                <img src="/tutor/profile-image/<?php echo (int)$tutorProfile['id']; ?>?v=<?php echo urlencode($tutorProfile['profile_image']); ?>"
                     alt="Profile picture" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';">
            <?php else: ?>
                <?php
                $initials = '';
                foreach (preg_split('/\s+/', trim($tutorProfile['full_name'] ?: $tutorProfile['email'])) as $p) {
                    if ($p !== '') $initials .= strtoupper(mb_substr($p, 0, 1));
                    if (mb_strlen($initials) >= 2) break;
                }
                echo htmlspecialchars($initials ?: 'T');
                ?>
            <?php endif; ?>
        </div>
        <div class="td-profile-info">
            <h3><?php echo htmlspecialchars($tutorProfile['full_name']); ?></h3>
            <div class="td-role"><i class="fas fa-chalkboard-teacher"></i> Professional Tutor</div>

            <?php if (!empty($tutorProfile['company_name'])): ?>
                <div style="margin-top:10px;">
                    <span class="td-company"><i class="fas fa-building"></i> <?php echo htmlspecialchars($tutorProfile['company_name']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($subjects)): ?>
                <div class="td-subject-pills">
                    <?php foreach ($subjects as $subj): ?>
                        <span class="td-subject-pill"><?php echo htmlspecialchars($subj); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($tutorProfile['bio'])): ?>
                <p style="color:var(--sg-muted);font-size:13.5px;margin:14px 0 0;line-height:1.6;max-width:640px;">
                    <?php echo nl2br(htmlspecialchars($tutorProfile['bio'])); ?>
                </p>
            <?php endif; ?>

            <div class="td-profile-meta">
                <?php if (!empty($tutorProfile['hourly_rate'])): ?>
                    <span><i class="fas fa-tag" style="color:#10b981;"></i>
                        <?php echo htmlspecialchars($tutorProfile['hourly_rate']); echo $billingType === 'monthly' ? ' / month' : ' / hour'; ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($tutorProfile['email'])): ?>
                    <span><i class="fas fa-envelope" style="color:#667eea;"></i> <?php echo htmlspecialchars($tutorProfile['email']); ?></span>
                <?php endif; ?>
                <?php if (!empty($tutorProfile['phone'])): ?>
                    <span><i class="fas fa-phone" style="color:#667eea;"></i> <?php echo htmlspecialchars($tutorProfile['phone']); ?></span>
                <?php endif; ?>
            </div>

            <div style="margin-top:14px;">
                <span class="td-badge eft"><i class="fas fa-university"></i> Payment via EFT</span>
            </div>
        </div>
        <button type="button" onclick="document.getElementById('tutorEditProfileModal').style.display='block'" class="td-edit-btn" style="border:none;cursor:pointer;font-family:inherit;"><i class="fas fa-pen"></i> Edit Profile</button>
    </div>

    </div></div><!-- /.td-cx-body -->
</section>


<?php include __DIR__ . '/../components/tutor_subjects_card.php'; ?>

<!-- Pending Requests -->
<section class="td-section td-cx" tabindex="0" aria-expanded="false">
    <div class="td-cx-top">
    <div class="td-section-head">
        <span class="td-section-icon alt-amber"><i class="fas fa-user-clock"></i></span>
        <div class="td-section-text">
            <h2>Pending Student Requests</h2>
            <p>Students who have requested you as their tutor. Approve them to add them to your sessions.</p>
        </div>
    </div>
        <span class="td-cx-chev"><i class="fas fa-chevron-down"></i></span>
    </div><!-- /.td-cx-top -->
    <div class="td-cx-body"><div class="td-cx-inner">

    <?php if (empty($pendingRequests)): ?>
        <div class="td-empty">
            <div class="td-empty-icon"><i class="fas fa-inbox"></i></div>
            <h3>No pending requests</h3>
            <p>When students request you as their tutor, they'll appear here for you to approve.</p>
        </div>
    <?php else: ?>
        <div class="td-card" style="overflow:hidden;">
            <?php foreach ($pendingRequests as $req): ?>
                <div class="td-row">
                    <span class="td-row-avatar"><?php echo strtoupper(mb_substr($req['username'], 0, 1)); ?></span>
                    <div class="td-row-info">
                        <p class="td-row-name"><?php echo htmlspecialchars($req['username']); ?></p>
                        <div class="td-row-sub">
                            <?php if (!empty($req['email'])): ?><span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($req['email']); ?></span><?php endif; ?>
                            <?php if (!empty($req['phone'])): ?><span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($req['phone']); ?></span><?php endif; ?>
                            <span><i class="fas fa-calendar"></i> Requested <?php echo date('M d, Y', strtotime($req['requested_at'])); ?></span>
                            <?php if (!empty($req['grade'])): ?><span style="color:#b45309;font-weight:600;"><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($req['grade']); ?></span><?php endif; ?>
                            <?php if (!empty($req['subjects'])): ?><span style="color:#1d4ed8;font-weight:600;"><i class="fas fa-book"></i> <?php echo htmlspecialchars($req['subjects']); ?></span><?php endif; ?>
                            <?php if (!empty($req['teaching_mode'])): ?><span style="color:#7c3aed;font-weight:600;"><i class="fas fa-<?php echo $req['teaching_mode'] === 'online' ? 'video' : 'handshake'; ?>"></i> <?php echo ucfirst(htmlspecialchars($req['teaching_mode'])); ?></span><?php endif; ?>
                            <?php if (!empty($req['schedule'])): ?><span style="color:#047857;font-weight:600;"><i class="fas fa-clock"></i> <?php echo ucfirst(htmlspecialchars($req['schedule'])); ?></span><?php endif; ?>
                            <?php if (!empty($req['estimated_price'])): ?><span style="color:#059669;font-weight:600;"><i class="fas fa-tag"></i> Est. <?php echo htmlspecialchars($req['estimated_price']); ?></span><?php endif; ?>
                        </div>
                    </div>
                    <span class="td-badge pending"><i class="fas fa-clock"></i> Pending</span>
                    <div style="display:flex;gap:8px;">
                        <form method="POST" action="/tutor/approve-student/<?php echo $req['id']; ?>" style="margin:0;">
                            <button type="submit" class="td-btn td-btn-approve"><i class="fas fa-check"></i> Approve</button>
                        </form>
                        <button type="button" class="td-btn td-btn-reject" onclick="openRejectModal(<?php echo (int)$req['id']; ?>)"><i class="fas fa-times"></i> Decline</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    </div></div><!-- /.td-cx-body -->
</section>

<!-- Reject Reason Modal -->
<div id="rejectStudentModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(15,23,42,0.55);align-items:center;justify-content:center;padding:20px;" onclick="if(event.target === this) closeRejectModal();">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:480px;box-shadow:0 25px 50px rgba(15,23,42,0.25);overflow:hidden;">
        <div style="padding:20px 24px;border-bottom:1px solid #eef2f7;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:18px;color:#1e293b;"><i class="fas fa-times-circle" style="color:#ef4444;margin-right:8px;"></i>Decline Request</h3>
            <button type="button" onclick="closeRejectModal()" style="background:none;border:none;font-size:20px;color:#64748b;cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="/tutor/reject-student/" id="rejectStudentForm" style="margin:0;">
            <input type="hidden" name="id" id="rejectStudentId" value="">
            <div style="padding:24px;">
                <p style="margin:0 0 14px;color:#64748b;font-size:13.5px;line-height:1.6;">Please let the student know why you are declining their request. This helps them understand and improve future requests.</p>
                <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:600;color:#334155;">Reason for declining</label>
                <textarea name="rejection_reason" required rows="4" placeholder="e.g. I am fully booked this term, or I do not teach that grade level."
                          style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13.5px;font-family:inherit;resize:vertical;"></textarea>
            </div>
            <div style="padding:16px 24px;border-top:1px solid #eef2f7;background:#f8fafc;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="closeRejectModal()" style="padding:10px 18px;background:#f1f5f9;color:#64748b;border:none;border-radius:11px;cursor:pointer;font-weight:600;font-family:inherit;font-size:13.5px;">Cancel</button>
                <button type="submit" style="padding:10px 20px;background:#ef4444;color:#fff;border:none;border-radius:11px;cursor:pointer;font-weight:600;font-family:inherit;font-size:13.5px;"><i class="fas fa-times"></i> Decline Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id) {
    var form = document.getElementById('rejectStudentForm');
    var input = document.getElementById('rejectStudentId');
    if (form && input) {
        form.action = '/tutor/reject-student/' + id;
        input.value = id;
    }
    var m = document.getElementById('rejectStudentModal');
    if (m) { m.style.display = 'flex'; }
}
function closeRejectModal() {
    var m = document.getElementById('rejectStudentModal');
    if (m) { m.style.display = 'none'; }
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeRejectModal(); }
});
</script>


<!-- Active Students -->
<section class="td-section td-cx" tabindex="0" aria-expanded="false">
    <div class="td-cx-top">
    <div class="td-section-head">
        <span class="td-section-icon alt-green"><i class="fas fa-user-check"></i></span>
        <div class="td-section-text">
            <h2>Active Students</h2>
            <p>Students currently in your sessions. Set how you teach them and manage their enrollment.</p>
        </div>
    </div>
        <span class="td-cx-chev"><i class="fas fa-chevron-down"></i></span>
    </div><!-- /.td-cx-top -->
    <div class="td-cx-body"><div class="td-cx-inner">

    <a href="/tutor/my-students" class="td-ms-banner">
        <span class="td-ms-banner-icon"><i class="fas fa-users-rectangle"></i></span>
        <div style="min-width:200px;">
            <h3>My Students — organised by grade</h3>
            <p>Open the grade cards to see each of your learners, and post announcements: online session links or contact lesson dates.</p>
        </div>
        <span class="td-ms-banner-cta"><i class="fas fa-arrow-right"></i> Open My Students</span>
    </a>

    <?php if (empty($activeStudents)): ?>
        <div class="td-empty">
            <div class="td-empty-icon" style="color:#10b981;background:rgba(16,185,129,0.12);"><i class="fas fa-user-friends"></i></div>
            <h3>No active students yet</h3>
            <p>Approve pending requests above to add students to your sessions.</p>
        </div>
    <?php else: ?>
        <div class="td-card" style="overflow:hidden;">
            <?php foreach ($activeStudents as $stu): ?>
                <div class="td-row">
                    <span class="td-row-avatar" style="background:linear-gradient(135deg,#10b981,#059669);"><?php echo strtoupper(mb_substr($stu['username'], 0, 1)); ?></span>
                    <div class="td-row-info">
                        <p class="td-row-name"><?php echo htmlspecialchars($stu['username']); ?></p>
                        <div class="td-row-sub">
                            <?php if (!empty($stu['email'])): ?><span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($stu['email']); ?></span><?php endif; ?>
                            <?php if (!empty($stu['phone'])): ?><span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($stu['phone']); ?></span><?php endif; ?>
                            <span><i class="fas fa-calendar-check"></i> Joined <?php echo !empty($stu['approved_at']) ? date('M d, Y', strtotime($stu['approved_at'])) : 'recently'; ?></span>
                            <?php if (!empty($stu['grade'])): ?><span style="color:#b45309;font-weight:600;"><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($stu['grade']); ?></span><?php endif; ?>
                            <?php if (!empty($stu['subjects'])): ?><span style="color:#1d4ed8;font-weight:600;"><i class="fas fa-book"></i> <?php echo htmlspecialchars($stu['subjects']); ?></span><?php endif; ?>
                            <?php if (!empty($stu['schedule'])): ?><span style="color:#047857;font-weight:600;"><i class="fas fa-clock"></i> <?php echo ucfirst(htmlspecialchars($stu['schedule'])); ?></span><?php endif; ?>
                        </div>
                    </div>

                    <span class="td-badge <?php echo ($stu['teaching_mode'] ?? 'online') === 'online' ? 'online' : 'contact'; ?>">
                        <i class="fas <?php echo ($stu['teaching_mode'] ?? 'online') === 'online' ? 'fa-video' : 'fa-handshake'; ?>"></i>
                        <?php echo ($stu['teaching_mode'] ?? 'online') === 'online' ? 'Online' : 'Contact'; ?>
                    </span>
                    <?php if (!empty($stu['extra_classes'])): ?>
                        <span class="td-badge extra"><i class="fas fa-plus-circle"></i> Extra Classes</span>
                    <?php endif; ?>
                    <span class="td-badge eft"><i class="fas fa-university"></i> EFT</span>

                    <form method="POST" action="/tutor/update-student-settings/<?php echo $stu['id']; ?>" class="td-settings" style="margin:0;">
                        <select name="teaching_mode" title="Teaching mode">
                            <option value="online" <?php echo ($stu['teaching_mode'] ?? 'online') === 'online' ? 'selected' : ''; ?>>Online</option>
                            <option value="contact" <?php echo ($stu['teaching_mode'] ?? 'online') === 'contact' ? 'selected' : ''; ?>>Contact (In-person)</option>
                        </select>
                        <label class="td-toggle" title="Offer extra classes">
                            <input type="checkbox" name="extra_classes" value="1" <?php echo !empty($stu['extra_classes']) ? 'checked' : ''; ?>>
                            Extra classes
                        </label>
                        <button type="submit" class="td-btn td-btn-save"><i class="fas fa-save"></i> Save</button>
                    </form>

                    <form method="POST" action="/tutor/remove-student/<?php echo $stu['id']; ?>" style="margin:0;" onsubmit="return confirm('Remove <?php echo htmlspecialchars($stu['username'], ENT_QUOTES); ?> from your students?');">
                        <button type="submit" class="td-btn td-btn-remove"><i class="fas fa-user-minus"></i> Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    </div></div><!-- /.td-cx-body -->
</section>

<?php include __DIR__ . '/../components/tutor_company_team.php'; ?>

</div><!-- /.sg-page -->

<?php include __DIR__ . '/../components/tutor_edit_modal.php'; ?>

<script>
// ---- Entrance reveal: staggered rise as sections scroll into view ----
(function () {
    var page = document.querySelector('.sg-page');
    if (!page || !('IntersectionObserver' in window)) return;
    var items = page.querySelectorAll('.td-cx');
    if (!items.length) return;
    page.classList.add('td-anim-ready');
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var el = entry.target;
            io.unobserve(el);
            el.style.setProperty('--td-delay', '0ms');
            el.classList.add('td-revealed');
            var cleaned = false;
            var cleanup = function () {
                if (cleaned) return;
                cleaned = true;
                el.classList.remove('td-reveal', 'td-revealed');
            };
            el.addEventListener('animationend', function (e) {
                if (e.target === el && !e.pseudoElement) cleanup();
            });
            setTimeout(cleanup, 1500);
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    for (var i = 0; i < items.length; i++) { items[i].classList.add('td-reveal'); io.observe(items[i]); }
})();

// ---- Expandable sections: title only, click header to expand — only one open at a time ----
Array.prototype.forEach.call(document.querySelectorAll('.td-cx'), function (sec) {
    var top = sec.querySelector('.td-cx-top');
    var toggle = function () {
        var open = sec.classList.toggle('td-expanded');
        sec.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            Array.prototype.forEach.call(document.querySelectorAll('.td-cx.td-expanded'), function (other) {
                if (other !== sec) {
                    other.classList.remove('td-expanded');
                    other.setAttribute('aria-expanded', 'false');
                }
            });
        }
    };
    if (top) {
        top.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('a, button, form, input, select, textarea, label')) return;
            toggle();
        });
    }
    sec.addEventListener('keydown', function (e) {
        if (e.target !== sec || (e.key !== 'Enter' && e.key !== ' ')) return;
        e.preventDefault();
        toggle();
    });
});

// Expand a section programmatically (e.g. when entering subjects edit mode)
window.expandTdSection = function (id) {
    var sec = document.getElementById(id);
    if (sec && sec.classList.contains('td-cx') && !sec.classList.contains('td-expanded')) {
        sec.classList.add('td-expanded');
        sec.setAttribute('aria-expanded', 'true');
    }
};
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
