<?php
include __DIR__ . '/../../layouts/admin_header.php';

// Get statistics
$totalPages = $seoModel->getAllPages(1000, 0);
$totalCount = count($totalPages);

// Count by status
$publishedCount = 0;
$draftCount = 0;
$archivedCount = 0;
foreach ($totalPages as $page) {
    if ($page['status'] === 'published') $publishedCount++;
    elseif ($page['status'] === 'draft') $draftCount++;
    elseif ($page['status'] === 'archived') $archivedCount++;
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-file-alt"></i> Manage SEO Pages
        </h1>
        <p style="color: #6b7280;">View and manage all SEO pages for your website</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="/admin/seo/add" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i> Add Page
        </a>
        <a href="/admin/seo/generate" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-magic"></i> Generate with AI
        </a>
    </div>
</div>

<!-- Success Messages -->
<?php if (isset($_GET['created'])): ?>
<div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-left: 4px solid #10b981; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <i class="fas fa-check-circle" style="color: #059669; font-size: 20px;"></i>
    <div>
        <strong style="color: #065f46;">Success!</strong>
        <span style="color: #047857;">Page created successfully!</span>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
<div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-left: 4px solid #10b981; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <i class="fas fa-check-circle" style="color: #059669; font-size: 20px;"></i>
    <div>
        <strong style="color: #065f46;">Success!</strong>
        <span style="color: #047857;">Page updated successfully!</span>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
<div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-left: 4px solid #10b981; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
    <i class="fas fa-check-circle" style="color: #059669; font-size: 20px;"></i>
    <div>
        <strong style="color: #065f46;">Success!</strong>
        <span style="color: #047857;">Page deleted successfully!</span>
    </div>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #667eea;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">Total Pages</p>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #667eea;"><?php echo $totalCount; ?></p>
            </div>
            <i class="fas fa-file-alt" style="font-size: 40px; color: #667eea; opacity: 0.3;"></i>
        </div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #10b981;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">Published</p>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #10b981;"><?php echo $publishedCount; ?></p>
            </div>
            <i class="fas fa-check-circle" style="font-size: 40px; color: #10b981; opacity: 0.3;"></i>
        </div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #f59e0b;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">Drafts</p>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #f59e0b;"><?php echo $draftCount; ?></p>
            </div>
            <i class="fas fa-clock" style="font-size: 40px; color: #f59e0b; opacity: 0.3;"></i>
        </div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #ef4444;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">Archived</p>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #ef4444;"><?php echo $archivedCount; ?></p>
            </div>
            <i class="fas fa-archive" style="font-size: 40px; color: #ef4444; opacity: 0.3;"></i>
        </div>
    </div>
</div>

<?php if (empty($totalPages)): ?>
    <div style="background: white; border-radius: 12px; padding: 40px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <i class="fas fa-inbox" style="font-size: 48px; color: #9ca3af; margin-bottom: 15px; display: block;"></i>
        <p style="color: #6b7280; font-size: 16px; margin-bottom: 20px;">No SEO pages found</p>
        <a href="/admin/seo/generate" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-magic"></i> Generate Your First SEO Page
        </a>
    </div>
<?php else: ?>
    <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div class="table-responsive">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <tr>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">ID</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Title & URL</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Subject</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Grade</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Keyword</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Views</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Status</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Created</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($totalPages as $page): ?>
                    <tr style="border-bottom: 1px solid #e5e7eb; transition: background 0.2s;">
                        <td style="padding: 15px; color: #6b7280; font-weight: 600;">#<?php echo $page['id']; ?></td>
                        <td style="padding: 15px;">
                            <div>
                                <strong style="color: #1f2937; display: block; margin-bottom: 4px;"><?php echo htmlspecialchars($page['title']); ?></strong>
                                <span style="color: #9ca3af; font-size: 13px;">
                                    <i class="fas fa-link"></i> /seo/<?php echo htmlspecialchars($page['slug']); ?>
                                </span>
                            </div>
                        </td>
                        <td style="padding: 15px;">
                            <span class="badge basic" style="background: #e0e7ff; color: #667eea; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                <?php echo htmlspecialchars($page['subject']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px;">
                            <span style="color: #6b7280; font-size: 14px;"><?php echo htmlspecialchars($page['grade_level']); ?></span>
                        </td>
                        <td style="padding: 15px;">
                            <span style="color: #6b7280; font-size: 13px;"><?php echo htmlspecialchars($page['target_keyword']); ?></span>
                        </td>
                        <td style="padding: 15px;">
                            <span style="color: #6b7280; font-size: 14px;">
                                <i class="fas fa-eye"></i> <?php echo number_format($page['views']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px;">
                            <?php if ($page['status'] === 'published'): ?>
                                <span class="badge active" style="background: #d1fae5; color: #059669; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-check-circle"></i> Published
                                </span>
                            <?php elseif ($page['status'] === 'draft'): ?>
                                <span class="badge basic" style="background: #fef3c7; color: #d97706; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-clock"></i> Draft
                                </span>
                            <?php else: ?>
                                <span class="badge inactive" style="background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-archive"></i> Archived
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px;">
                            <span style="color: #6b7280; font-size: 13px;">
                                <i class="fas fa-calendar"></i> <?php echo $page['created_at'] ? date('M d, Y', strtotime($page['created_at'])) : 'Unknown date'; ?>
                            </span>
                        </td>
                        <td style="padding: 15px;">
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="/seo/<?php echo urlencode($page['slug']); ?>" target="_blank" class="btn-sm btn-sm-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="/admin/seo/edit/<?php echo $page['id']; ?>" class="btn-sm btn-sm-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="/admin/seo/toggle-publish/<?php echo $page['id']; ?>" class="btn-sm btn-sm-warning" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;" onclick="return confirm('Toggle publish status?');">
                                    <i class="fas fa-thumbtack"></i> <?php echo $page['status'] === 'published' ? 'Unpublish' : 'Publish'; ?>
                                </a>
                                <a href="/admin/seo/delete/<?php echo $page['id']; ?>" class="btn-sm btn-sm-danger" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;" onclick="return confirm('Are you sure you want to delete this page? This cannot be undone.');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.9); display: flex; justify-content: center; align-items: center; z-index: 9999; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease;">
    <div class="loading-spinner" style="width: 60px; height: 60px; border: 4px solid #e9ecef; border-top: 4px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite;"></div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.data-table tbody tr:hover {
    background: #f9fafb;
}
.btn-sm {
    padding: 8px 14px;
    font-size: 13px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}
.btn-sm:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.btn-sm-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.btn-sm-secondary {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
}
.btn-sm-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}
.btn-sm-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}
.table-responsive {
    overflow-x: auto;
}
@media (max-width: 1024px) {
    .data-table {
        font-size: 12px;
    }
    .data-table th, .data-table td {
        padding: 10px 8px;
    }
}
</style>

<script>
// Show loading overlay on page load
window.addEventListener('load', function() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.classList.add('active');
    setTimeout(function() {
        loadingOverlay.classList.remove('active');
    }, 500);
});

// Show loading overlay when clicking links
document.addEventListener('click', function(e) {
    const link = e.target.closest('a[href^="/"], a[href^="http"]');
    if (link && link.target !== '_blank' && !link.href.includes('#')) {
        const href = link.getAttribute('href');
        if (!href.includes('javascript') && !href.includes('mailto')) {
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.add('active');
        }
    }
});
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
