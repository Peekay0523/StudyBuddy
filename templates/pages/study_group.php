<?php
$pageTitle = 'Study Groups - StudySmart';
$currentPage = 'study-group';
include __DIR__ . '/../layouts/header.php';

?>

<div class="sg-page">
<style>
/* ===== Study Groups page — scoped premium styles ===== */
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
.sg-page .title{font-size:30px;margin:0;letter-spacing:-.02em;}
.sg-page .subtitle{color:var(--sg-muted);font-size:15px;margin:6px 0 0;max-width:640px;}

.sg-page .sg-header{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin:8px 0 28px;}
.sg-page .sg-header-text{min-width:220px;}
.sg-page .sg-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(102,126,234,.10);color:#4f46e5;font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;padding:6px 12px;border-radius:999px;margin-bottom:12px;}
.sg-page .sg-create-btn{display:inline-flex;align-items:center;gap:10px;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;border:none;cursor:pointer;padding:13px 22px;border-radius:14px;font-size:15px;font-weight:600;box-shadow:0 10px 24px rgba(102,126,234,.35);transition:transform .2s ease,box-shadow .2s ease,filter .2s ease;min-height:48px;font-family:inherit;}
.sg-page .sg-create-btn:hover{transform:translateY(-3px);box-shadow:0 16px 30px rgba(102,126,234,.45);filter:brightness(1.05);}
.sg-page .sg-create-btn:active{transform:translateY(-1px);}
.sg-page .sg-create-btn .fa-plus{background:rgba(255,255,255,.22);width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:11px;}

.sg-page .sg-section{margin-bottom:40px;}
.sg-page .sg-section-head{display:flex;gap:14px;align-items:flex-start;margin:0 0 18px;}
.sg-page .sg-section-icon{width:44px;height:44px;border-radius:13px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));box-shadow:0 8px 18px rgba(102,126,234,.30);}
.sg-page .sg-section-icon.alt-amber{background:linear-gradient(135deg,#f59e0b,#f97316);box-shadow:0 8px 18px rgba(245,158,11,.30);}
.sg-page .sg-section-icon.alt-rose{background:linear-gradient(135deg,#f43f5e,#ec4899);box-shadow:0 8px 18px rgba(244,63,94,.30);}
.sg-page .sg-section-icon.alt-green{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 18px rgba(16,185,129,.28);}
.sg-page .sg-section-text h2{font-size:19px;margin:2px 0 4px;color:var(--sg-ink);letter-spacing:-.01em;}
.sg-page .sg-section-text p{color:var(--sg-muted);font-size:13.5px;margin:0;}

.sg-page .feature-card{background:#fff;border:1px solid var(--sg-line);border-radius:var(--sg-radius);padding:22px;box-shadow:var(--sg-shadow);transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease;}
.sg-page .feature-card:hover{transform:translateY(-4px);box-shadow:var(--sg-shadow-hover);border-color:rgba(102,126,234,.25);}

.sg-page .sg-groups-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;}

.sg-page .sg-group-card{position:relative;overflow:hidden;padding:22px 22px 20px;display:flex;flex-direction:column;gap:14px;cursor:pointer;}
.sg-page .sg-group-card .sg-decor{position:absolute;right:-34px;top:-34px;width:150px;height:150px;border-radius:50%;}
.sg-page .sg-group-card .sg-decor-icon{position:absolute;right:16px;top:18px;width:58px;height:58px;border-radius:18px;display:inline-flex;align-items:center;justify-content:center;font-size:24px;color:#fff;box-shadow:0 12px 24px rgba(102,126,234,.35);}
.sg-page .sg-group-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;position:relative;z-index:1;padding-right:70px;}
.sg-page .sg-group-name{font-size:19px;font-weight:700;color:var(--sg-ink);margin:0;line-height:1.25;letter-spacing:-.01em;}
.sg-page .sg-group-sub{font-size:13px;color:var(--sg-muted);margin:4px 0 0;}
.sg-page .sg-meta-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px;position:relative;z-index:1;}
.sg-page .sg-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:999px;font-size:12px;font-weight:600;}
.sg-page .sg-pill.grade{background:#eef2ff;color:#4338ca;}
.sg-page .sg-pill.members{background:#ecfdf5;color:#047857;}
.sg-page .sg-pill.activity{background:#fff7ed;color:#c2410c;border:1px solid #ffedd5;}
.sg-page .sg-group-desc{color:var(--sg-muted);font-size:13.5px;line-height:1.55;margin:0;position:relative;z-index:1;}
.sg-page .sg-group-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:2px;padding-top:14px;border-top:1px solid var(--sg-line);position:relative;z-index:1;}
.sg-page .sg-admin-chip{background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;padding:4px 10px;border-radius:999px;font-size:10.5px;font-weight:700;letter-spacing:.05em;}
.sg-page .sg-view-btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:var(--sg-brand);font-weight:600;font-size:14px;padding:9px 16px;border-radius:12px;border:1.5px solid rgba(102,126,234,.35);background:linear-gradient(135deg,rgba(102,126,234,.06),rgba(118,75,162,.06));transition:all .2s ease;}
.sg-page .sg-view-btn:hover{background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;border-color:transparent;transform:translateX(3px);box-shadow:0 10px 20px rgba(102,126,234,.30);}

.sg-page .sg-kebab{position:absolute;right:14px;top:14px;width:34px;height:34px;border-radius:10px;border:none;background:rgba(15,23,42,.04);color:var(--sg-faint);cursor:pointer;display:inline-flex;align-items:center;justify-content:center;z-index:3;transition:background .2s,color .2s;}
.sg-page .sg-kebab:hover{background:rgba(102,126,234,.12);color:var(--sg-brand);}
.sg-page .sg-menu{position:absolute;right:14px;top:50px;min-width:172px;background:#fff;border:1px solid var(--sg-line);border-radius:14px;box-shadow:0 18px 40px rgba(30,41,59,.14);padding:8px;z-index:20;display:none;}
.sg-page .sg-menu.open{display:block;}
.sg-page .sg-menu a,.sg-page .sg-menu button{display:flex;align-items:center;gap:10px;width:100%;text-align:left;background:none;border:none;cursor:pointer;padding:9px 12px;border-radius:10px;font-size:13.5px;color:var(--sg-ink);text-decoration:none;font-family:inherit;}
.sg-page .sg-menu a:hover,.sg-page .sg-menu button:hover{background:rgba(102,126,234,.10);color:var(--sg-brand);}
.sg-page .sg-menu-sep{height:1px;background:var(--sg-line);margin:6px 4px;}
.sg-page .sg-menu-meta{padding:8px 12px;font-size:12px;color:var(--sg-faint);line-height:1.5;}

.sg-page .sg-rank-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px;}
.sg-page .sg-rank-card{position:relative;padding:20px;display:flex;flex-direction:column;gap:14px;}
.sg-page .sg-rank-top{display:flex;align-items:center;gap:12px;}
.sg-page .sg-rank-badge{width:40px;height:40px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;flex-shrink:0;}
.sg-page .sg-rank-icon{width:40px;height:40px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:17px;color:#fff;flex-shrink:0;}
.sg-page .sg-rank-name{min-width:0;padding-right:8px;}
.sg-page .sg-rank-name h3{font-size:16px;margin:0;color:var(--sg-ink);line-height:1.25;}
.sg-page .sg-rank-name .sg-rank-sub{font-size:12.5px;color:var(--sg-muted);margin-top:2px;display:block;}
.sg-page .sg-rank-desc{font-size:13px;color:var(--sg-muted);line-height:1.5;margin:0;}
.sg-page .sg-rank-meta{display:flex;flex-wrap:wrap;gap:8px;}
.sg-page .sg-rank-foot{margin-top:auto;padding-top:14px;border-top:1px solid var(--sg-line);}
.sg-page .sg-join-btn{width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(135deg,var(--sg-brand),var(--sg-brand-2));color:#fff;border:none;cursor:pointer;padding:11px 16px;border-radius:12px;font-size:14px;font-weight:600;box-shadow:0 8px 18px rgba(102,126,234,.28);transition:transform .2s,box-shadow .2s,filter .2s;font-family:inherit;}
.sg-page .sg-join-btn:hover{transform:translateY(-2px);box-shadow:0 12px 24px rgba(102,126,234,.36);filter:brightness(1.05);}
.sg-page .sg-join-btn:disabled{background:#e2e8f0;color:#64748b;box-shadow:none;cursor:not-allowed;transform:none;}
.sg-page .sg-rank-card .sg-kebab{top:16px;right:16px;}

.sg-page .sg-empty{background:#fff;border:1.5px dashed #e2e8f0;border-radius:var(--sg-radius);padding:48px 28px;text-align:center;box-shadow:var(--sg-shadow);}
.sg-page .sg-empty .sg-empty-icon{width:64px;height:64px;border-radius:18px;margin:0 auto 16px;display:inline-flex;align-items:center;justify-content:center;font-size:26px;color:var(--sg-brand);background:rgba(102,126,234,.10);}
.sg-page .sg-empty h3{font-size:17px;margin:0 0 6px;color:var(--sg-ink);}
.sg-page .sg-empty p{color:var(--sg-muted);font-size:14px;margin:0;}

.sg-page .sg-tutor-card{padding:20px;display:flex;flex-direction:column;gap:12px;}

/* ===== Collapsible resource lists (accordion) ===== */
.sg-page .sg-accordion{display:flex;flex-direction:column;gap:14px;}
.sg-page .sg-acc{background:#fff;border:1px solid var(--sg-line);border-radius:var(--sg-radius);box-shadow:var(--sg-shadow);overflow:hidden;transition:box-shadow .2s,border-color .2s;}
.sg-page .sg-acc:hover{box-shadow:0 14px 34px rgba(30,41,59,.08);border-color:rgba(102,126,234,.22);}
.sg-page .sg-acc-head{display:flex;align-items:center;gap:14px;width:100%;background:none;border:none;cursor:pointer;text-align:left;padding:18px 22px;font-family:inherit;transition:background .2s;}
.sg-page .sg-acc-head:hover{background:rgba(102,126,234,.04);}
.sg-page .sg-acc-icon{width:44px;height:44px;border-radius:13px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;flex-shrink:0;box-shadow:0 8px 18px rgba(102,126,234,.25);}
.sg-page .sg-acc-icon.videos{background:linear-gradient(135deg,#7c3aed,#6d28d9);box-shadow:0 8px 18px rgba(124,58,237,.30);}
.sg-page .sg-acc-icon.scripts{background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 8px 18px rgba(239,68,68,.28);}
.sg-page .sg-acc-titles{flex:1;min-width:0;}
.sg-page .sg-acc-titles h3{margin:0;font-size:16px;color:var(--sg-ink);font-weight:700;}
.sg-page .sg-acc-titles p{margin:2px 0 0;font-size:12.5px;color:var(--sg-muted);}
.sg-page .sg-acc-count{display:inline-flex;align-items:center;gap:6px;background:#f1f5f9;color:#475569;font-size:12.5px;font-weight:700;padding:5px 12px;border-radius:999px;flex-shrink:0;}
.sg-page .sg-acc-chev{width:32px;height:32px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;color:var(--sg-faint);flex-shrink:0;transition:transform .25s ease,background .2s,color .2s;}
.sg-page .sg-acc.open .sg-acc-chev{transform:rotate(180deg);color:var(--sg-brand);background:rgba(102,126,234,.10);}
.sg-page .sg-acc-body{display:none;border-top:1px solid var(--sg-line);}
.sg-page .sg-acc.open .sg-acc-body{display:block;}

/* Resource list rows */
.sg-page .sg-rlist{list-style:none;margin:0;padding:0;}
.sg-page .sg-rrow{display:flex;align-items:center;gap:14px;padding:14px 22px;border-bottom:1px solid var(--sg-line);transition:background .18s;}
.sg-page .sg-rrow:last-child{border-bottom:none;}
.sg-page .sg-rrow:hover{background:rgba(102,126,234,.05);}
.sg-page .sg-rrow-ico{width:38px;height:38px;border-radius:11px;display:inline-flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.sg-page .sg-rrow-main{flex:1;min-width:0;}
.sg-page .sg-rrow-title{font-size:14.5px;font-weight:600;color:var(--sg-ink);margin:0;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sg-page .sg-rrow-desc{font-size:12.5px;color:var(--sg-muted);margin:3px 0 0;line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sg-page .sg-rrow-sub{display:flex;flex-wrap:wrap;gap:12px;font-size:12px;color:var(--sg-faint);margin-top:3px;}
.sg-page .sg-rrow-sub span{display:inline-flex;align-items:center;gap:5px;}
.sg-page .sg-rrow-actions{display:flex;align-items:center;gap:8px;flex-shrink:0;}
.sg-page .sg-rrow-actions .btn-primary,.sg-page .sg-rrow-actions .btn-sm{padding:7px 13px;font-size:12.5px;min-height:auto;box-shadow:0 5px 12px rgba(37,99,235,.20);}
.sg-page .sg-rrow-actions .btn-danger-sm{padding:7px 11px;font-size:12.5px;min-height:auto;}
.sg-page .sg-rrow-badge{padding:3px 10px;border-radius:999px;font-size:10.5px;font-weight:700;white-space:nowrap;flex-shrink:0;}
.sg-page .sg-rlist-empty{padding:26px 22px;text-align:center;color:var(--sg-muted);font-size:13.5px;}

@media (max-width:768px){
    .sg-page .sg-rrow{flex-wrap:wrap;}
    .sg-page .sg-rrow-actions{width:100%;justify-content:flex-end;padding-top:6px;}
    .sg-page .sg-acc-head{padding:15px 16px;gap:12px;}
    .sg-page .sg-rrow{padding:13px 16px;}
}

@media (max-width:920px){.sg-page .sg-rank-grid{grid-template-columns:repeat(2,1fr);}}
@media (max-width:768px){.sg-page{padding:6px 2px 30px;}.sg-page .title{font-size:25px;}.sg-page .sg-header{align-items:flex-start;}.sg-page .sg-groups-grid{grid-template-columns:1fr;}.sg-page .sg-rank-grid{grid-template-columns:1fr;}.sg-page .sg-group-card .sg-decor-icon{width:50px;height:50px;font-size:20px;}}
@media (max-width:560px){.sg-page .sg-rank-grid{grid-template-columns:1fr;}.sg-page .sg-create-btn{width:100%;justify-content:center;}}
</style>

<?php
/**
 * Render a single resource as a compact list row (used inside accordions).
 * Preserves all original download/watch/delete routes & handlers.
 */
$renderResourceRow = function($res, $showUploader = true, $isPersonal = false) {
    $type = $res['resource_type'] ?? 'script';
    $isVideoFile = ($type === 'video_file');
    $isVideoLink = ($type === 'video_link');
    $isVideo = $isVideoFile || $isVideoLink;
    $icon = $isVideo ? 'fa-play-circle' : 'fa-file-pdf';
    $iconColor = $isVideo ? '#7c3aed' : '#ef4444';
    $iconBg = $isVideo ? 'rgba(124,58,237,0.10)' : 'rgba(239,68,68,0.10)';
    $badgeBg = $isVideo ? '#ede9fe' : '#fef2f2';
    $badgeColor = $isVideo ? '#6d28d9' : '#dc2626';
    $badgeText = $isVideoLink ? 'Video Link' : ($isVideoFile ? 'Video' : 'Script');
    $dateField = $isPersonal ? ($res['created_at'] ?? '') : ($res['uploaded_at'] ?? '');
    $title = $isPersonal ? ($res['title'] ?? $res['file_name']) : $res['file_name'];
    $desc = trim($res['description'] ?? '');
    ?>
    <li class="sg-rrow">
        <span class="sg-rrow-ico" style="color:<?php echo $iconColor; ?>;background:<?php echo $iconBg; ?>;"><i class="fas <?php echo $icon; ?>"></i></span>
        <div class="sg-rrow-main">
            <p class="sg-rrow-title" title="<?php echo htmlspecialchars($title); ?>"><?php echo htmlspecialchars($title); ?></p>
            <?php if ($desc !== '' && strtolower($desc) !== 'no description'): ?>
                <p class="sg-rrow-desc"><?php echo htmlspecialchars(mb_strimwidth($desc, 0, 110, '…')); ?></p>
            <?php endif; ?>
            <div class="sg-rrow-sub">
                <?php if (!empty($res['group_title'])): ?><span><i class="fas fa-users"></i> <?php echo htmlspecialchars($res['group_title']); ?></span><?php endif; ?>
                <?php if ($showUploader && !empty($res['uploader_name'])): ?><span><i class="fas fa-user"></i> <?php echo htmlspecialchars($res['uploader_name']); ?></span><?php endif; ?>
                <?php if (!$isVideoLink && !empty($res['file_size'])): ?><span><i class="fas fa-hdd"></i> <?php echo round($res['file_size'] / 1024, 1); ?> KB</span><?php endif; ?>
                <?php if (!empty($dateField)): ?><span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($dateField)); ?></span><?php endif; ?>
            </div>
        </div>
        <span class="sg-rrow-badge" style="background:<?php echo $badgeBg; ?>;color:<?php echo $badgeColor; ?>;"><?php echo $badgeText; ?></span>
        <div class="sg-rrow-actions">
            <?php if ($isVideoLink): ?>
                <a href="<?php echo htmlspecialchars($res['video_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn-primary">
                    <i class="fas fa-external-link-alt"></i> Watch Video
                </a>
            <?php elseif ($isVideoFile): ?>
                <?php if ($isPersonal): ?>
                    <a href="/study-group/view-my-video/<?php echo $res['id']; ?>" target="_blank" class="btn-primary">
                        <i class="fas fa-play"></i> Watch
                    </a>
                    <a href="/study-group/download-my-resource/<?php echo $res['id']; ?>" class="btn-sm btn-primary-sm">
                        <i class="fas fa-download"></i> Download
                    </a>
                <?php else: ?>
                    <a href="/study-group/<?php echo $res['study_group_id']; ?>/view-video/<?php echo $res['id']; ?>" target="_blank" class="btn-primary">
                        <i class="fas fa-play"></i> Watch
                    </a>
                    <a href="/study-group/<?php echo $res['study_group_id']; ?>/download-script/<?php echo $res['id']; ?>" class="btn-sm btn-primary-sm">
                        <i class="fas fa-download"></i> Download
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($isPersonal): ?>
                    <a href="/study-group/download-my-resource/<?php echo $res['id']; ?>" class="btn-primary">
                        <i class="fas fa-download"></i> Download
                    </a>
                <?php else: ?>
                    <a href="/study-group/<?php echo $res['study_group_id']; ?>/download-script/<?php echo $res['id']; ?>" class="btn-primary">
                        <i class="fas fa-download"></i> Download
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($isPersonal): ?>
                <form method="POST" action="/study-group/delete-my-resource/<?php echo $res['id']; ?>" style="display:inline;margin-left:auto;">
                    <button type="submit" class="btn-sm btn-danger-sm" onclick="return confirm('Delete this resource from My Resources?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </li>
    <?php
};

/**
 * Render a collapsible accordion that groups resources into Videos / Scripts
 * dropdown lists. Clicking a header expands/collapses its list.
 */
$renderResourceAccordion = function($resources, $showUploader = true, $isPersonal = false) use ($renderResourceRow) {
    $videos = [];
    $scripts = [];
    foreach ($resources as $res) {
        $t = $res['resource_type'] ?? 'script';
        if ($t === 'video_file' || $t === 'video_link') {
            $videos[] = $res;
        } else {
            $scripts[] = $res;
        }
    }
    $vidCount = count($videos);
    $scrCount = count($scripts);
    ?>
    <div class="sg-accordion">
        <div class="sg-acc<?php echo $vidCount > 0 ? ' open' : ''; ?>">
            <button type="button" class="sg-acc-head" onclick="sgToggleAcc(this)">
                <span class="sg-acc-icon videos"><i class="fas fa-play-circle"></i></span>
                <span class="sg-acc-titles">
                    <h3>Videos</h3>
                    <p>Video tutorials &amp; links you can watch.</p>
                </span>
                <span class="sg-acc-count"><?php echo $vidCount; ?> item<?php echo $vidCount === 1 ? '' : 's'; ?></span>
                <span class="sg-acc-chev"><i class="fas fa-chevron-down"></i></span>
            </button>
            <div class="sg-acc-body">
                <?php if ($vidCount > 0): ?>
                    <ul class="sg-rlist">
                        <?php foreach ($videos as $v): ?><?php $renderResourceRow($v, $showUploader, $isPersonal); ?><?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="sg-rlist-empty">No videos uploaded yet.</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="sg-acc<?php echo $scrCount > 0 ? ' open' : ''; ?>">
            <button type="button" class="sg-acc-head" onclick="sgToggleAcc(this)">
                <span class="sg-acc-icon scripts"><i class="fas fa-file-alt"></i></span>
                <span class="sg-acc-titles">
                    <h3>Scripts &amp; Notes</h3>
                    <p>PDF, DOCX &amp; text documents you can download.</p>
                </span>
                <span class="sg-acc-count"><?php echo $scrCount; ?> item<?php echo $scrCount === 1 ? '' : 's'; ?></span>
                <span class="sg-acc-chev"><i class="fas fa-chevron-down"></i></span>
            </button>
            <div class="sg-acc-body">
                <?php if ($scrCount > 0): ?>
                    <ul class="sg-rlist">
                        <?php foreach ($scripts as $s): ?><?php $renderResourceRow($s, $showUploader, $isPersonal); ?><?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="sg-rlist-empty">No scripts uploaded yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
};
?>
<!-- My Resources Section -->
<section class="sg-section">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;margin-bottom:18px;">
        <div class="sg-section-head" style="margin:0;">
            <span class="sg-section-icon alt-green"><i class="fas fa-bookmark"></i></span>
            <div class="sg-section-text">
                <h2>My Resources</h2>
                <p>Store your own video tutorials and scripts here for quick access.</p>
            </div>
        </div>
        <button onclick="document.getElementById('uploadMyResourceModal').style.display='block'" class="sg-create-btn" style="padding:11px 18px;font-size:14px;">
            <i class="fas fa-plus"></i> Add Resource
        </button>
    </div>

    <?php if (empty($myResources)): ?>
        <div class="sg-empty">
            <div class="sg-empty-icon" style="color:#10b981;background:rgba(16,185,129,0.12);"><i class="fas fa-bookmark"></i></div>
            <h3>Your personal collection is empty</h3>
            <p>Add a script or video tutorial (file or link) to get started!</p>
            <div style="margin-top:16px;">
                <button onclick="document.getElementById('uploadMyResourceModal').style.display='block'" class="sg-create-btn" style="padding:11px 18px;font-size:14px;">
                    <i class="fas fa-plus"></i> Add Your First Resource
                </button>
            </div>
        </div>
    <?php else: ?>
        <?php $renderResourceAccordion($myResources, false, true); ?>
    <?php endif; ?>
</section><!-- My Resources -->

<?php if (!isTutor()): ?>
<!-- My Tutors Section (students only) -->
<section class="sg-section">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;margin-bottom:18px;">
        <div class="sg-section-head" style="margin:0;">
            <span class="sg-section-icon alt-green"><i class="fas fa-user-graduate"></i></span>
            <div class="sg-section-text">
                <h2>My Tutors</h2>
                <p>Tutors who have accepted your request and are teaching you.</p>
            </div>
        </div>
    </div>

    <?php if (empty($myTutors)): ?>
        <div class="sg-empty">
            <div class="sg-empty-icon" style="color:#10b981;background:rgba(16,185,129,0.12);"><i class="fas fa-user-graduate"></i></div>
            <h3>You do not have any active tutors yet</h3>
            <p>Choose a tutor below to send a tutoring request.</p>
        </div>
    <?php else: ?>
        <div class="sg-groups-grid">
            <?php foreach ($myTutors as $tutor):
                $tutorName = $tutor['full_name'] ?: ($tutor['username'] ?? 'Tutor');
                $nameParts = preg_split('/\s+/', trim($tutorName));
                $initials = '';
                foreach ($nameParts as $part) {
                    if ($part !== '') $initials .= strtoupper(mb_substr($part, 0, 1));
                    if (mb_strlen($initials) >= 2) break;
                }
                if ($initials === '') $initials = 'T';
                $subjects = array_filter(array_map('trim', explode(',', $tutor['subjects'] ?? '')));
                $contactEmail = $tutor['email'] ?: ($tutor['tutor_account_email'] ?? '');
                $contactPhone = $tutor['phone'] ?: ($tutor['tutor_account_phone'] ?? '');
            ?>
                <div class="feature-card sg-tutor-card">
                    <div style="display:flex;align-items:center;gap:14px;">
                        <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);color:white;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;flex-shrink:0;overflow:hidden;">
                            <?php if (!empty($tutor['profile_image'])): ?>
                                <img src="/tutor/profile-image/<?php echo (int)$tutor['tutor_profile_id']; ?>?v=<?php echo urlencode($tutor['profile_image']); ?>" alt="Profile picture" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';">
                            <?php else: ?>
                                <?php echo htmlspecialchars($initials); ?>
                            <?php endif; ?>
                        </div>
                        <div style="min-width:0;">
                            <h3 style="font-size:16px;color:#1e293b;margin:0;word-break:break-word;"><?php echo htmlspecialchars($tutorName); ?></h3>
                            <?php if (!empty($tutor['username'])): ?><div style="font-size:12px;color:#94a3b8;">@<?php echo htmlspecialchars($tutor['username']); ?></div><?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($subjects)): ?>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            <?php foreach (array_slice($subjects, 0, 4) as $subject): ?><span style="background:#ecfdf5;color:#047857;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;"><?php echo htmlspecialchars($subject); ?></span><?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div style="display:inline-flex;align-items:center;gap:7px;background:#eff6ff;color:#1d4ed8;font-size:12.5px;font-weight:600;padding:5px 12px;border-radius:999px;border:1px solid #bfdbfe;">
                        <i class="fas fa-<?php echo ($tutor['teaching_mode'] ?? 'online') === 'online' ? 'laptop' : 'chalkboard-teacher'; ?>"></i>
                        <?php echo ($tutor['teaching_mode'] ?? 'online') === 'online' ? 'Online' : 'Contact'; ?> Lessons
                    </div>

                    <?php if (!empty($tutor['bio'])): ?><p style="color:#64748b;font-size:13px;margin:0;line-height:1.5;"><?php echo htmlspecialchars(mb_strimwidth($tutor['bio'], 0, 120, '…')); ?></p><?php endif; ?>

                    <div style="display:flex;gap:8px;margin-top:auto;padding-top:12px;border-top:1px solid #e2e8f0;flex-wrap:wrap;">
                        <a href="/tutor/view/<?php echo (int)$tutor['tutor_profile_id']; ?>" class="btn-primary" style="padding:8px 14px;font-size:13px;min-height:auto;background:linear-gradient(135deg,#667eea,#764ba2);text-decoration:none;display:inline-flex;align-items:center;gap:6px;"><i class="fas fa-user"></i> View Tutor</a>
                        <?php if (!empty($contactEmail)): ?><a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>" class="btn-primary" style="padding:8px 14px;font-size:13px;min-height:auto;"><i class="fas fa-envelope"></i> Contact</a><?php endif; ?>
                        <?php if (!empty($contactPhone)): ?><a href="tel:<?php echo htmlspecialchars($contactPhone); ?>" class="btn-sm btn-primary-sm" style="padding:8px 14px;font-size:13px;min-height:auto;"><i class="fas fa-phone"></i> Call</a><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section><!-- My Tutors -->

<!-- Available Tutors Section (students only) -->
<section class="sg-section">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;margin-bottom:18px;">
        <div class="sg-section-head" style="margin:0;">
            <span class="sg-section-icon alt-amber"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="sg-section-text">
                <h2>Available Tutors</h2>
                <p>Choose a tutor to learn from and start mastering your subjects.</p>
            </div>
        </div>
    </div>

    <?php if (empty($availableTutors)): ?>
        <div class="sg-empty">
            <div class="sg-empty-icon" style="color:#f59e0b;background:rgba(245,158,11,0.12);"><i class="fas fa-chalkboard-teacher"></i></div>
            <h3>No tutors available yet</h3>
            <p>Tutors will appear here as they become available. Please check back soon!</p>
        </div>
    <?php else: ?>
        <div class="sg-groups-grid">
            <?php foreach ($availableTutors as $tutor):
                $initials = '';
                $nameParts = preg_split('/\s+/', trim($tutor['full_name'] ?: ($tutor['username'] ?? '')));
                foreach ($nameParts as $p) {
                    if ($p !== '') $initials .= strtoupper(mb_substr($p, 0, 1));
                    if (mb_strlen($initials) >= 2) break;
                }
                if ($initials === '') $initials = 'T';
                $subjects = array_filter(array_map('trim', explode(',', $tutor['subjects'] ?? '')));
            ?>
                <div class="feature-card sg-tutor-card">
                    <div style="display:flex;align-items:center;gap:14px;">
                        <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#f97316);color:white;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;flex-shrink:0;overflow:hidden;">
                            <?php if (!empty($tutor['profile_image'])): ?>
                                <img src="/tutor/profile-image/<?php echo (int)$tutor['id']; ?>?v=<?php echo urlencode($tutor['profile_image']); ?>"
                                     alt="Profile picture" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';">
                            <?php else: ?>
                                <?php echo htmlspecialchars($initials); ?>
                            <?php endif; ?>
                        </div>
                        <div style="min-width:0;">
                            <h3 style="font-size:16px;color:#1e293b;margin:0;word-break:break-word;">
                                <?php echo htmlspecialchars($tutor['full_name'] ?: ($tutor['username'] ?? 'Tutor')); ?>
                            </h3>
                            <?php if (!empty($tutor['username'])): ?>
                                <div style="font-size:12px;color:#94a3b8;">@<?php echo htmlspecialchars($tutor['username']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($subjects)): ?>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            <?php foreach (array_slice($subjects, 0, 4) as $subj): ?>
                                <span style="background:#fef3c7;color:#b45309;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;">
                                    <?php echo htmlspecialchars($subj); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($tutor['company_name'])): ?>
                        <div style="display:inline-flex;align-items:center;gap:7px;background:#fffbeb;color:#92400e;font-size:12.5px;font-weight:600;padding:5px 12px;border-radius:999px;border:1px solid #fde68a;">
                            <i class="fas fa-building"></i> <?php echo htmlspecialchars($tutor['company_name']); ?>
                        </div>
                    <?php endif; ?>

                    <?php $sgTutorMode = $tutor['teaching_mode'] ?? ''; ?>
                    <?php if (in_array($sgTutorMode, ['contact', 'hybrid', 'online'], true)): ?>
                        <div style="display:inline-flex;align-items:center;gap:7px;background:#eff6ff;color:#1d4ed8;font-size:12.5px;font-weight:600;padding:5px 12px;border-radius:999px;border:1px solid #bfdbfe;">
                            <i class="fas fa-<?php echo $sgTutorMode === 'online' ? 'laptop' : ($sgTutorMode === 'hybrid' ? 'random' : 'chalkboard-teacher'); ?>"></i>
                            <?php echo $sgTutorMode === 'online' ? 'Online' : ($sgTutorMode === 'hybrid' ? 'Hybrid' : 'Contact'); ?> Lessons
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($tutor['bio'])): ?>
                        <p style="color:#64748b;font-size:13px;margin:0;line-height:1.5;">
                            <?php echo htmlspecialchars(mb_strimwidth($tutor['bio'], 0, 120, '…')); ?>
                        </p>
                    <?php endif; ?>

                    <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:12px;color:#64748b;">
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
                    </div>

                    <div style="display:flex;gap:8px;margin-top:auto;padding-top:12px;border-top:1px solid #e2e8f0;flex-wrap:wrap;">
                        <?php
                        $sgCurrentViewer = getCurrentUser();
                        $isOwnTutorCard = $sgCurrentViewer && (int)$sgCurrentViewer['id'] === (int)$tutor['user_id'];
                        ?>
                        <?php if (!$isOwnTutorCard): ?>
                            <a href="/tutor/view/<?php echo $tutor['id']; ?>" class="btn-primary" style="padding:8px 14px;font-size:13px;min-height:auto;background:linear-gradient(135deg,#667eea,#764ba2);text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                                <i class="fas fa-user"></i> View Tutor
                            </a>
                        <?php else: ?>
                            <a href="/tutor/view/<?php echo $tutor['id']; ?>" class="btn-primary" style="padding:8px 14px;font-size:13px;min-height:auto;background:linear-gradient(135deg,#667eea,#764ba2);text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                                <i class="fas fa-id-badge"></i> View Profile
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($tutor['email'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($tutor['email']); ?>?subject=Tutoring%20Request" class="btn-primary" style="padding:8px 14px;font-size:13px;min-height:auto;">
                                <i class="fas fa-envelope"></i> Contact
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($tutor['phone'])): ?>
                            <a href="tel:<?php echo htmlspecialchars($tutor['phone']); ?>" class="btn-sm btn-primary-sm" style="padding:8px 14px;font-size:13px;min-height:auto;">
                                <i class="fas fa-phone"></i> Call
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section><!-- Available Tutors -->

</div><!-- /.sg-page -->

<?php endif; ?><!-- end students-only Available Tutors section -->

<!-- Upload My Resource Modal -->
<div id="uploadMyResourceModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,41,0.55);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(2px);">
    <div style="background:#fff;padding:30px;border-radius:20px;width:90%;max-width:520px;position:relative;box-shadow:0 30px 60px rgba(30,41,59,0.3);max-height:90vh;overflow-y:auto;">
        <button onclick="document.getElementById('uploadMyResourceModal').style.display='none'" aria-label="Close"
                style="position:absolute;top:16px;right:16px;background:#f1f5f9;border:none;width:34px;height:34px;border-radius:10px;font-size:20px;cursor:pointer;color:#64748b;display:inline-flex;align-items:center;justify-content:center;">&times;</button>
        <h2 style="margin-bottom:6px;color:#1e293b;font-size:22px;"><i class="fas fa-bookmark" style="color:#10b981;"></i> Add to My Resources</h2>
        <p style="color:#64748b;font-size:13.5px;margin:0 0 22px;">Store a script or video tutorial for your own quick access.</p>
        <form method="POST" action="/study-group/upload-my-resource" enctype="multipart/form-data">
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Resource Type <span style="color:#ef4444;">*</span></label>
                <select name="resource_type" id="myResourceType" onchange="sgToggleMyType()" style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
                    <option value="script">Script (PDF, DOCX, DOC, TXT)</option>
                    <option value="video_file">Video Tutorial File (MP4)</option>
                    <option value="video_link">Video Tutorial Link (URL)</option>
                </select>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Title</label>
                <input type="text" name="title" maxlength="100" placeholder="e.g., Trigonometry Cheat Sheet"
                       style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
            </div>
            <div id="myFileField" style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Select File <span style="color:#ef4444;">*</span></label>
                <input type="file" name="resource_file" id="myResourceFile" accept=".pdf,.docx,.doc,.txt"
                       style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
                <small id="myFileHint" style="color:#94a3b8;display:block;margin-top:5px;">Allowed: PDF, DOCX, DOC, TXT (Max 10MB)</small>
            </div>
            <div id="myLinkFields" style="display:none;margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Video URL <span style="color:#ef4444;">*</span></label>
                <input type="url" name="video_url" placeholder="https://youtube.com/watch?v=..."
                       style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
                <small style="color:#94a3b8;display:block;margin-top:5px;">Paste a link to YouTube, Vimeo, or any video sharing site.</small>
            </div>
            <div style="margin-bottom:22px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Description (Optional)</label>
                <textarea name="description" rows="3" placeholder="Describe what this resource is about..."
                          style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;resize:vertical;font-family:inherit;"></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('uploadMyResourceModal').style.display='none'"
                        style="padding:11px 20px;background:#f1f5f9;color:#64748b;border:none;border-radius:12px;cursor:pointer;font-weight:600;font-family:inherit;font-size:14px;">Cancel</button>
                <button type="submit" class="sg-create-btn" style="padding:11px 20px;font-size:14px;"><i class="fas fa-upload"></i> Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ---- Resource accordions (Videos / Scripts dropdowns) ----
    function sgToggleAcc(btn) {
        var acc = btn.parentElement;
        if (!acc) return;
        acc.classList.toggle('open');
    }

    // ---- My Resource modal: toggle file vs link fields ----
    function sgToggleMyType() {
        var type = document.getElementById('myResourceType').value;
        var fileField = document.getElementById('myFileField');
        var linkFields = document.getElementById('myLinkFields');
        var fileInput = document.getElementById('myResourceFile');
        var hint = document.getElementById('myFileHint');
        if (type === 'video_link') {
            fileField.style.display = 'none';
            linkFields.style.display = 'block';
        } else {
            linkFields.style.display = 'none';
            fileField.style.display = 'block';
            if (type === 'video_file') {
                fileInput.setAttribute('accept', '.mp4,.webm,.ogg');
                hint.textContent = 'Allowed: MP4, WEBM, OGG (Max 100MB)';
            } else {
                fileInput.setAttribute('accept', '.pdf,.docx,.doc,.txt');
                hint.textContent = 'Allowed: PDF, DOCX, DOC, TXT (Max 10MB)';
            }
        }
    }

    // ---- Keep modals centered (inline open handlers use 'block'; switch to 'flex') ----
    ['uploadMyResourceModal'].forEach(function (id) {
        var m = document.getElementById(id);
        if (!m) return;
        new MutationObserver(function () {
            if (m.style.display === 'block') { m.style.display = 'flex'; }
        }).observe(m, { attributes: true, attributeFilter: ['style'] });
    });

    // ---- Close any open modal when clicking its overlay (preserves original behavior) ----
    window.onclick = function (event) {
        ['uploadMyResourceModal'].forEach(function (id) {
            var m = document.getElementById(id);
            if (m && event.target === m) { m.style.display = 'none'; }
        });
    }

    function inviteToGroupModal(username) {
        alert('To invite ' + username + ' to a study group, you can:\n\n1. Click "Ask for Help" to send them an email\n2. Create a study group and share the invite link\n3. Message them directly if you\'re in the same group');
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>













