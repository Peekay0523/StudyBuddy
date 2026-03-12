<?php
/**
 * Admin: Generate SEO Page
 * Form to create new SEO pages using AI
 * 
 * Note: This template expects the following variables from the controller:
 * - $seoModel: SEOPage model instance
 * - $subjectGrades: Array of subject-grade combinations
 * - $templates: Array of content templates
 * - $keywords: Array of low-competition keywords
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
        .seo-generate-form {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #e9ecef;
        }
        .form-section h3 {
            color: #007bff;
            margin-bottom: 1rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #212529;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-group small {
            display: block;
            margin-top: 0.25rem;
            color: #6c757d;
        }
        .keyword-suggestions {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 0.5rem;
        }
        .keyword-tag {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            margin: 0.25rem;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .keyword-tag:hover {
            background: #bbdefb;
        }
        .btn-generate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-generate:hover {
            transform: translateY(-2px);
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        .loading-spinner.show {
            display: block;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../layouts/admin_header.php'; ?>
    
    <main class="admin-content">
        <div class="seo-generate-form">
            <h1>🚀 Generate SEO Long-tail Page</h1>
            <p class="text-muted">Create SEO-optimized content using AI for low-competition keywords</p>
            
            <form action="/admin/seo/generate" method="POST" id="generateForm">
                <!-- Content Type Selection -->
                <div class="form-section">
                    <h3>1. Content Type</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="content_type">Content Type *</label>
                            <select name="content_type" id="content_type" required>
                                <option value="ai-generated">AI-Generated (Full Auto)</option>
                                <option value="hybrid">Hybrid (AI + Manual Edit)</option>
                                <option value="static">Static (Manual Only)</option>
                            </select>
                            <small>Choose AI-generated for automatic content creation</small>
                        </div>
                        <div class="form-group">
                            <label for="template_name">Template (for AI content)</label>
                            <select name="template_name" id="template_name">
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
                    <h3>2. Target Keyword & Topic</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="target_keyword">Target Keyword (Long-tail) *</label>
                            <input type="text" name="target_keyword" id="target_keyword" 
                                   placeholder="e.g., math memorandum for grade 12 full answers" required>
                            <small>Use specific, low-competition phrases (3-6 words)</small>
                            
                            <div class="keyword-suggestions">
                                <strong>Suggested Keywords (click to use):</strong><br>
                                <?php foreach ($keywords as $keyword): ?>
                                <span class="keyword-tag" onclick="useKeyword('<?php echo htmlspecialchars($keyword['keyword']); ?>')">
                                    <?php echo htmlspecialchars($keyword['keyword']); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select name="subject" id="subject" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjectGrades as $sg): ?>
                                <option value="<?php echo htmlspecialchars($sg['subject']); ?>">
                                    <?php echo htmlspecialchars($sg['subject_display_name'] ?: $sg['subject']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="grade_level">Grade Level *</label>
                            <select name="grade_level" id="grade_level" required>
                                <option value="">Select Grade</option>
                                <option value="Grade 10">Grade 10</option>
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="topic">Specific Topic *</label>
                        <input type="text" name="topic" id="topic" 
                               placeholder="e.g., Algebra, Calculus, DNA and Genetics" required>
                    </div>
                </div>

                <!-- Page Details -->
                <div class="form-section">
                    <h3>3. Page Details</h3>
                    <div class="form-group">
                        <label for="title">Page Title (H1) *</label>
                        <input type="text" name="title" id="title" 
                               placeholder="e.g., Mathematics Memorandum for Grade 12 - Full Answers with Steps" required>
                        <small>Include your target keyword naturally</small>
                    </div>
                    <div class="form-group">
                        <label for="slug">URL Slug</label>
                        <input type="text" name="slug" id="slug" 
                               placeholder="e.g., math-memorandum-grade-12-full-answers">
                        <small>Leave blank to auto-generate from title</small>
                    </div>
                    <div class="form-group">
                        <label for="meta_description">Meta Description *</label>
                        <textarea name="meta_description" id="meta_description" 
                                  placeholder="Brief description for search engines (150-160 characters)" required></textarea>
                        <small>This will appear in search results</small>
                    </div>
                    <div class="form-group">
                        <label for="meta_keywords">Additional Keywords</label>
                        <textarea name="meta_keywords" id="meta_keywords" 
                                  placeholder="keyword1, keyword2, keyword3"></textarea>
                        <small>Comma-separated secondary keywords</small>
                    </div>
                </div>

                <!-- Publishing Options -->
                <div class="form-section">
                    <h3>4. Publishing Options</h3>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="publish_now" value="1" checked>
                            Publish immediately after generation
                        </label>
                    </div>
                </div>

                <!-- Submit -->
                <div class="form-actions">
                    <button type="submit" class="btn-generate">
                        🎯 Generate & Publish
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="saveAsDraft()">
                        💾 Save as Draft
                    </button>
                </div>
            </form>

            <!-- Loading State -->
            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner"></div>
                <h3>🤖 AI is generating your content...</h3>
                <p>This may take 30-60 seconds. Please don't close this page.</p>
            </div>
        </div>
    </main>

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
                document.getElementById('loadingSpinner').classList.add('show');
            }
        });

        function saveAsDraft() {
            document.querySelector('input[name="publish_now"]').checked = false;
            document.getElementById('generateForm').submit();
        }
    </script>
</body>
</html>
