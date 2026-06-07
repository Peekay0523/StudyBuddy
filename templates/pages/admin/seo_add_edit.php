<?php
/**
 * Admin: Add/Edit SEO Page Manual Form
 * For manually creating SEO pages without AI
 */

// Determine if we're in edit mode
$editMode = isset($page) && !empty($page);
$pageTitle = $editMode ? 'Edit SEO Page' : 'Add SEO Page';
$currentPage = 'admin-seo';
include __DIR__ . '/../../layouts/admin_header.php';

// Initialize empty page if not editing
if (!$editMode) {
    $page = [
        'title' => '',
        'slug' => '',
        'meta_title' => '',
        'meta_description' => '',
        'meta_keywords' => '',
        'target_keyword' => '',
        'subject' => '',
        'grade_level' => '',
        'topic' => '',
        'full_content' => '',
        'status' => 'draft'
    ];
}
?>

<style>
    .form-container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        width: 100% !important;
    }
    .form-section {
        background: #fff !important;
        padding: 2rem !important;
        margin-bottom: 1.5rem !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        border-left: 4px solid #667eea !important;
    }
    .form-section:last-child {
        margin-bottom: 0 !important;
    }
    .form-section h3 {
        color: #1f2937 !important;
        margin-bottom: 1.5rem !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
        font-size: 1.25rem !important;
        font-weight: 700 !important;
    }
    .form-section h3 i {
        color: #667eea !important;
        font-size: 1.4rem !important;
    }
    .form-row {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
        gap: 1.25rem !important;
        margin-bottom: 1.25rem !important;
        width: 100% !important;
    }
    .form-group {
        margin-bottom: 1.25rem !important;
        width: 100% !important;
    }
    .form-group label {
        display: block !important;
        margin-bottom: 0.5rem !important;
        font-weight: 600 !important;
        color: #374151 !important;
        font-size: 0.95rem !important;
    }
    .form-group label .required {
        color: #ef4444 !important;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100% !important;
        padding: 0.875rem !important;
        border: 2px solid #e5e7eb !important;
        border-radius: 8px !important;
        font-size: 0.95rem !important;
        font-family: inherit !important;
        box-sizing: border-box !important;
        transition: all 0.2s !important;
        background: #f9fafb !important;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none !important;
        border-color: #667eea !important;
        background: white !important;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    }
    .form-group textarea {
        min-height: 250px !important;
        resize: vertical !important;
        font-family: 'Consolas', 'Monaco', monospace !important;
        line-height: 1.6 !important;
    }
    .form-group small {
        display: block !important;
        margin-top: 0.5rem !important;
        color: #6b7280 !important;
        font-size: 0.85rem !important;
    }
    .form-actions {
        display: flex !important;
        gap: 1rem !important;
        margin-top: 2rem !important;
        flex-wrap: wrap !important;
        width: 100% !important;
        padding: 1.5rem 2rem !important;
        background: #f9fafb !important;
        border-radius: 12px !important;
    }
    .btn {
        padding: 0.875rem 1.75rem !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        cursor: pointer !important;
        border: none !important;
        font-size: 0.95rem !important;
        transition: all 0.3s !important;
    }
    .btn:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        opacity: 1 !important;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
    }
    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: white !important;
    }
    .btn-secondary {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%) !important;
        color: white !important;
    }
    .char-count {
        text-align: right !important;
        font-size: 0.8rem !important;
        color: #9ca3af !important;
        margin-top: 0.25rem !important;
    }
    .char-count.warning {
        color: #f59e0b !important;
    }
    .char-count.danger {
        color: #ef4444 !important;
    }
    .resource-item {
        transition: all 0.2s !important;
    }
    .resource-item:hover {
        background: #f3f4f6 !important;
    }
    .btn-sm {
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .loading-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid #e9ecef;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<main class="admin-content" style="max-width: 1600px; margin: 0 auto; padding: 2rem;">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
                <?php echo $editMode ? '✏️' : '➕'; ?> <?php echo $pageTitle; ?>
            </h1>
            <p style="color: #6b7280;">
                <?php echo $editMode ? 'Update and manage your SEO page content' : 'Create a new SEO-optimized page for your website'; ?>
            </p>
        </div>
        <a href="/admin/seo/pages" class="btn btn-secondary" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Pages
        </a>
    </div>

    <!-- Alert for required fields -->
    <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-info-circle" style="color: #3b82f6; font-size: 20px;"></i>
            <div>
                <strong style="color: #1e40af;">Required Fields</strong>
                <p style="margin: 5px 0 0 0; color: #1e3a8a; font-size: 14px;">Fields marked with <span style="color: #ef4444;">*</span> are required</p>
            </div>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="<?php echo $editMode ? '/admin/seo/update/' . $page['id'] : '/admin/seo/create'; ?>" id="seoForm">
            <!-- Hidden field for edit mode -->
            <?php if ($editMode): ?>
            <input type="hidden" name="id" value="<?php echo $page['id']; ?>">
            <?php endif; ?>

            <!-- Basic Info -->
            <div class="form-section">
                <h3><i class="fas fa-edit"></i> Basic Information</h3>

                <div class="form-group">
                    <label for="title">Page Title (H1) <span class="required">*</span></label>
                    <input type="text" name="title" id="title"
                           value="<?php echo htmlspecialchars($page['title'] ?? ''); ?>"
                           placeholder="e.g., Mathematics Memorandum for Grade 12 - Full Answers"
                           required 
                           style="font-size: 1rem !important;">
                    <small>Include your target keyword naturally. This will be the main heading of your page.</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="slug">URL Slug <span class="required">*</span></label>
                        <input type="text" name="slug" id="slug"
                               value="<?php echo htmlspecialchars($page['slug'] ?? ''); ?>"
                               placeholder="e.g., math-memorandum-grade-12-full-answers"
                               required>
                        <small>Use lowercase letters, numbers, and hyphens only. This forms part of your URL.</small>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="draft" <?php echo ($page['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>📝 Draft</option>
                            <option value="published" <?php echo ($page['status'] ?? '') === 'published' ? 'selected' : ''; ?>>✅ Published</option>
                            <option value="archived" <?php echo ($page['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>🗄️ Archived</option>
                        </select>
                        <small>Published pages are visible to the public</small>
                    </div>
                </div>
            </div>

            <!-- SEO Meta -->
            <div class="form-section" style="border-left-color: #10b981 !important;">
                <h3><i class="fas fa-search"></i> SEO Meta Data</h3>

                <div class="form-group">
                    <label for="meta_title">Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title"
                           value="<?php echo htmlspecialchars($page['meta_title'] ?? ''); ?>"
                           placeholder="SEO title for search engines (60 characters max)"
                           maxlength="60"
                           oninput="updateCharCount('meta_title', 'meta_title_count', 60)">
                    <div id="meta_title_count" class="char-count">0 / 60 characters</div>
                    <small>Leave blank to use page title. Optimal length: 50-60 characters.</small>
                </div>

                <div class="form-group">
                    <label for="meta_description">Meta Description <span class="required">*</span></label>
                    <textarea name="meta_description" id="meta_description"
                              placeholder="Brief description for search engines (150-160 characters recommended)"
                              maxlength="160"
                              oninput="updateCharCount('meta_description', 'meta_description_count', 160)"
                              required
                              style="min-height: 100px !important;"><?php echo htmlspecialchars($page['meta_description'] ?? ''); ?></textarea>
                    <div id="meta_description_count" class="char-count">0 / 160 characters</div>
                    <small>This appears in Google search results. Optimal length: 150-160 characters.</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="target_keyword">Target Keyword <span class="required">*</span></label>
                        <input type="text" name="target_keyword" id="target_keyword"
                               value="<?php echo htmlspecialchars($page['target_keyword'] ?? ''); ?>"
                               placeholder="e.g., math memorandum grade 12"
                               required>
                        <small>The main keyword you want this page to rank for</small>
                    </div>
                    <div class="form-group">
                        <label for="meta_keywords">Additional Keywords</label>
                        <input type="text" name="meta_keywords" id="meta_keywords"
                               value="<?php echo htmlspecialchars($page['meta_keywords'] ?? ''); ?>"
                               placeholder="keyword1, keyword2, keyword3">
                        <small>Comma-separated list of related keywords</small>
                    </div>
                </div>
            </div>

            <!-- Resources Section (Scripts & Memorandums) -->
            <?php if ($editMode): ?>
            <div class="form-section" style="border-left-color: #10b981 !important;">
                <h3><i class="fas fa-upload"></i> Upload Scripts & Memorandums</h3>
                
                <form action="/admin/seo/upload-resource/<?php echo $page['id']; ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="resource_type">Resource Type</label>
                            <select name="resource_type" id="resource_type" required>
                                <option value="script">📝 Study Script</option>
                                <option value="memorandum">✅ Memorandum</option>
                                <option value="study_guide">📚 Study Guide</option>
                                <option value="past_paper">📋 Past Exam Paper</option>
                                <option value="checklist">✓ Study Checklist</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="resource_title">Resource Title <span class="required">*</span></label>
                            <input type="text" name="title" id="resource_title" required
                                   placeholder="e.g., Grade 12 Mathematics Memorandum 2024">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="resource_description">Description</label>
                        <textarea name="description" id="resource_description" rows="2"
                                  placeholder="Brief description of this resource..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="resource_file">Upload File <span class="required">*</span></label>
                        <input type="file" name="resource_file" id="resource_file" accept=".pdf,.doc,.docx" required>
                        <small>Allowed: PDF, Word documents. Maximum size: 20MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_free" value="1" checked>
                            Make this resource free to download (not premium)
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Upload Resource
                    </button>
                </form>
                
                <!-- Existing Resources List -->
                <?php
                if ($editMode && isset($page['id'])) {
                    $seoModel = new SEOPage();
                    $resources = $seoModel->getResources($page['id']);
                    if (!empty($resources)):
                ?>
                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 1rem;">Existing Resources</h4>
                    <div class="resources-list">
                        <?php foreach ($resources as $resource): ?>
                        <div class="resource-item" style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong><?php echo htmlspecialchars($resource['title']); ?></strong>
                                <br>
                                <small style="color: #6b7280;">
                                    Type: <?php echo ucfirst($resource['resource_type']); ?> | 
                                    Size: <?php echo number_format($resource['file_size'] / 1024, 1); ?> KB |
                                    Downloads: <?php echo $resource['download_count']; ?>
                                </small>
                            </div>
                            <a href="/admin/seo/delete-resource/<?php echo $resource['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this resource?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; } ?>
            </div>
            <?php endif; ?>

            <!-- Content -->
            <div class="form-section" style="border-left-color: #f59e0b !important;">
                <h3><i class="fas fa-file-alt"></i> Content</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="subject">Subject <span class="required">*</span></label>
                        <select name="subject" id="subject" required>
                            <option value="">Select Subject</option>
                            <option value="Mathematics" <?php echo ($page['subject'] ?? '') === 'Mathematics' ? 'selected' : ''; ?>>Mathematics</option>
                            <option value="Mathematical Literacy" <?php echo ($page['subject'] ?? '') === 'Mathematical Literacy' ? 'selected' : ''; ?>>Mathematical Literacy</option>
                            <option value="Physical Sciences" <?php echo ($page['subject'] ?? '') === 'Physical Sciences' ? 'selected' : ''; ?>>Physical Sciences</option>
                            <option value="Life Sciences" <?php echo ($page['subject'] ?? '') === 'Life Sciences' ? 'selected' : ''; ?>>Life Sciences</option>
                            <option value="English Home Language" <?php echo ($page['subject'] ?? '') === 'English Home Language' ? 'selected' : ''; ?>>English Home Language</option>
                            <option value="Geography" <?php echo ($page['subject'] ?? '') === 'Geography' ? 'selected' : ''; ?>>Geography</option>
                            <option value="History" <?php echo ($page['subject'] ?? '') === 'History' ? 'selected' : ''; ?>>History</option>
                            <option value="Accounting" <?php echo ($page['subject'] ?? '') === 'Accounting' ? 'selected' : ''; ?>>Accounting</option>
                            <option value="Business Studies" <?php echo ($page['subject'] ?? '') === 'Business Studies' ? 'selected' : ''; ?>>Business Studies</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="grade_level">Grade Level <span class="required">*</span></label>
                        <select name="grade_level" id="grade_level" required>
                            <option value="">Select Grade</option>
                            <option value="Grade 10" <?php echo ($page['grade_level'] ?? '') === 'Grade 10' ? 'selected' : ''; ?>>Grade 10</option>
                            <option value="Grade 11" <?php echo ($page['grade_level'] ?? '') === 'Grade 11' ? 'selected' : ''; ?>>Grade 11</option>
                            <option value="Grade 12" <?php echo ($page['grade_level'] ?? '') === 'Grade 12' ? 'selected' : ''; ?>>Grade 12</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="topic">Topic / Chapter</label>
                        <input type="text" name="topic" id="topic"
                               value="<?php echo htmlspecialchars($page['topic'] ?? ''); ?>"
                               placeholder="e.g., Algebra, Calculus, DNA, Photosynthesis">
                        <small>Specific topic or chapter covered</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="full_content">Full Content (HTML) <span class="required">*</span></label>
                    <textarea name="full_content" id="full_content"
                              placeholder="Write your content here using HTML tags like <h2>, <p>, <ul>, <li>, etc."
                              required
                              style="min-height: 400px !important; font-family: 'Consolas', 'Monaco', monospace !important;"><?php echo htmlspecialchars($page['full_content'] ?? ''); ?></textarea>
                    <small>Use HTML tags for formatting: &lt;h2&gt; for subheadings, &lt;p&gt; for paragraphs, &lt;ul&gt;&lt;li&gt; for lists, etc.</small>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" name="save_publish" value="1" class="btn btn-success">
                    <i class="fas fa-rocket"></i> <?php echo $editMode ? 'Update & Publish' : 'Save & Publish'; ?>
                </button>
                <button type="submit" name="save" value="1" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $editMode ? 'Update (Keep Status)' : 'Save as Draft'; ?>
                </button>
                <a href="/admin/seo/pages" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<script>
// Character count for meta fields
function updateCharCount(inputId, countId, max) {
    const input = document.getElementById(inputId);
    const count = document.getElementById(countId);
    const length = input.value.length;
    
    count.textContent = length + ' / ' + max + ' characters';
    
    if (length > max * 0.9) {
        count.className = 'char-count danger';
    } else if (length > max * 0.75) {
        count.className = 'char-count warning';
    } else {
        count.className = 'char-count';
    }
}

// Initialize character counts on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('meta_title')) {
        updateCharCount('meta_title', 'meta_title_count', 60);
    }
    if (document.getElementById('meta_description')) {
        updateCharCount('meta_description', 'meta_description_count', 160);
    }
});

// Show loading overlay on form submit
document.getElementById('seoForm')?.addEventListener('submit', function(e) {
    if (typeof showLoader === 'function') {
        showLoader('Saving SEO page...');
    }
});

// Auto-generate slug from title
document.getElementById('title')?.addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (slugInput && !slugInput.value) {
        const slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
        slugInput.value = slug;
    }
});
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
