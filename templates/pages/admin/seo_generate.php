<?php
/**
 * Admin: Generate SEO Page
 * Form to create new SEO pages using AI
 */

$seoModel = new SEOPage();
$subjectGrades = $seoModel->getSubjectGradeCombinations();
$templates = $seoModel->getAllTemplates();
$keywords = $seoModel->getLowCompetitionKeywords(20);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate SEO Page - Admin</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #10b981;
            --success-hover: #059669;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        .seo-generate-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-subtitle {
            color: var(--gray-600);
            margin-top: 0.5rem;
            font-size: 1rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: var(--gray-100);
            color: var(--gray-700);
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: var(--gray-200);
        }

        .generate-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid var(--gray-200);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            padding: 1.5rem 2rem;
            color: white;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2.5rem;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--gray-100);
        }

        .section-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-label .required {
            color: var(--danger);
            margin-left: 0.25rem;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            color: var(--gray-800);
            background: white;
            transition: all 0.2s;
            font-family: inherit;
        }

        .form-input:hover,
        .form-select:hover,
        .form-textarea:hover {
            border-color: var(--gray-300);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-hint {
            display: block;
            margin-top: 0.375rem;
            font-size: 0.8125rem;
            color: var(--gray-500);
        }

        .keyword-suggestions {
            background: var(--gray-50);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 0.75rem;
            border: 1px solid var(--gray-200);
        }

        .keyword-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            display: block;
        }

        .keyword-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .keyword-tag {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            background: white;
            border: 1px solid var(--primary);
            color: var(--primary);
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .keyword-tag:hover {
            background: var(--primary);
            color: white;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200);
        }

        .checkbox-input {
            width: 1.25rem;
            height: 1.25rem;
            margin-top: 0.125rem;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .checkbox-label {
            font-size: 0.9375rem;
            color: var(--gray-700);
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--gray-100);
            margin-top: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }

        .btn-secondary {
            background: white;
            color: var(--gray-700);
            border: 2px solid var(--gray-300);
        }

        .btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.show {
            display: flex;
        }

        .loading-box {
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            text-align: center;
            max-width: 400px;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--gray-200);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .loading-text {
            color: var(--gray-600);
            font-size: 0.9375rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../layouts/admin_header.php'; ?>

    <div class="seo-generate-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <span>🚀</span> Generate SEO Long-tail Page
                </h1>
                <p class="page-subtitle">Create SEO-optimized content using AI for low-competition keywords</p>
            </div>
            <a href="/admin/seo/pages" class="back-btn">
                ← Back to Pages
            </a>
        </div>

        <div class="generate-card">
            <div class="card-header">
                <h2>
                    <span>✨</span> AI-Powered Content Generator
                </h2>
            </div>

            <div class="card-body">
                <form action="/admin/seo/generate" method="POST" id="generateForm">
                    <!-- Content Type Selection -->
                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-number">1</span>
                            <h3 class="section-title">Content Type</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="content_type">
                                    Content Type
                                    <span class="required">*</span>
                                </label>
                                <select name="content_type" id="content_type" class="form-select" required>
                                    <option value="ai-generated">🤖 AI-Generated (Full Auto)</option>
                                    <option value="hybrid">🔧 Hybrid (AI + Manual Edit)</option>
                                    <option value="static">📝 Static (Manual Only)</option>
                                </select>
                                <span class="form-hint">Choose AI-generated for automatic content creation</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="template_name">Template (for AI content)</label>
                                <select name="template_name" id="template_name" class="form-select">
                                    <?php foreach ($templates as $template): ?>
                                    <option value="<?php echo htmlspecialchars($template['template_name']); ?>">
                                        <?php echo htmlspecialchars($template['template_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Target Keyword & Topic -->
                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-number">2</span>
                            <h3 class="section-title">Target Keyword & Topic</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label" for="target_keyword">
                                    Target Keyword (Long-tail)
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="target_keyword" id="target_keyword" class="form-input"
                                       placeholder="e.g., math memorandum for grade 12 full answers" required>
                                <span class="form-hint">Use specific, low-competition phrases (3-6 words)</span>

                                <div class="keyword-suggestions">
                                    <span class="keyword-label">💡 Suggested Keywords (click to use):</span>
                                    <div class="keyword-tags">
                                        <?php foreach ($keywords as $keyword): ?>
                                        <span class="keyword-tag" onclick="useKeyword('<?php echo htmlspecialchars($keyword['keyword']); ?>')">
                                            <?php echo htmlspecialchars($keyword['keyword']); ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="subject">
                                    Subject
                                    <span class="required">*</span>
                                </label>
                                <select name="subject" id="subject" class="form-select" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjectGrades as $sg): ?>
                                    <option value="<?php echo htmlspecialchars($sg['subject']); ?>">
                                        <?php echo htmlspecialchars($sg['subject_display_name'] ?: $sg['subject']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="grade_level">
                                    Grade Level
                                    <span class="required">*</span>
                                </label>
                                <select name="grade_level" id="grade_level" class="form-select" required>
                                    <option value="">Select Grade</option>
                                    <option value="Grade 10">Grade 10</option>
                                    <option value="Grade 11">Grade 11</option>
                                    <option value="Grade 12">Grade 12</option>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label" for="topic">
                                    Specific Topic
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="topic" id="topic" class="form-input"
                                       placeholder="e.g., Algebra, Calculus, DNA and Genetics" required>
                            </div>
                        </div>
                    </div>

                    <!-- Page Details -->
                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-number">3</span>
                            <h3 class="section-title">Page Details</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label" for="title">
                                    Page Title (H1)
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="title" id="title" class="form-input"
                                       placeholder="e.g., Mathematics Memorandum for Grade 12 - Full Answers with Steps" required>
                                <span class="form-hint">Include your target keyword naturally</span>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label" for="slug">URL Slug</label>
                                <input type="text" name="slug" id="slug" class="form-input"
                                       placeholder="e.g., math-memorandum-grade-12-full-answers">
                                <span class="form-hint">Leave blank to auto-generate from title</span>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label" for="meta_description">
                                    Meta Description
                                    <span class="required">*</span>
                                </label>
                                <textarea name="meta_description" id="meta_description" class="form-textarea"
                                          placeholder="Brief description for search engines (150-160 characters)" required></textarea>
                                <span class="form-hint">This will appear in search results</span>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label" for="meta_keywords">Additional Keywords</label>
                                <textarea name="meta_keywords" id="meta_keywords" class="form-textarea"
                                          placeholder="keyword1, keyword2, keyword3" style="min-height: 60px;"></textarea>
                                <span class="form-hint">Comma-separated secondary keywords</span>
                            </div>
                        </div>
                    </div>

                    <!-- Publishing Options -->
                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-number">4</span>
                            <h3 class="section-title">Publishing Options</h3>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" name="publish_now" id="publish_now" value="1" class="checkbox-input" checked>
                            <label for="publish_now" class="checkbox-label">
                                <strong>Publish immediately</strong> after generation
                            </label>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            🎯 Generate & Publish
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="saveAsDraft()">
                            💾 Save as Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-box">
            <div class="spinner"></div>
            <h3 class="loading-title">🤖 AI is generating your content...</h3>
            <p class="loading-text">This may take 30-60 seconds. Please don't close this page.</p>
        </div>
    </div>

    <?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>

    <script>
        function useKeyword(keyword) {
            document.getElementById('target_keyword').value = keyword;

            // Auto-generate title from keyword
            const title = keyword.charAt(0).toUpperCase() + keyword.slice(1) + ' | StudySmart';
            document.getElementById('title').value = title;

            // Auto-generate slug
            const slug = keyword.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            document.getElementById('slug').value = slug;
        }

        // Auto-generate meta description from title
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const subject = document.getElementById('subject').value;
            const grade = document.getElementById('grade_level').value;

            if (subject && grade) {
                const description = `Complete ${title.replace(' | StudySmart', '')}. CAPS curriculum aligned for ${grade} ${subject}. Step-by-step solutions, common mistakes, and expert tips.`;
                document.getElementById('meta_description').value = description.substring(0, 160);
            }
        });

        // Form submission with loading state
        document.getElementById('generateForm').addEventListener('submit', function(e) {
            const isPublish = document.querySelector('input[name="publish_now"]').checked;
            if (isPublish) {
                document.getElementById('loadingOverlay').classList.add('show');
            }
        });

        function saveAsDraft() {
            document.querySelector('input[name="publish_now"]').checked = false;
            document.getElementById('generateForm').submit();
        }
    </script>
</body>
</html>
