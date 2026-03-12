<?php
/**
 * SEO Page Template
 * Main template for displaying SEO long-tail pages
 * 
 * Variables available:
 * - $page: Page data (title, meta_description, full_content, schema_markup, etc.)
 * - $qaContent: Q&A content array
 * - $relatedPages: Related pages array
 */

$pageTitle = htmlspecialchars($page['meta_title'] ?: $page['title']);
$metaDescription = htmlspecialchars($page['meta_description'] ?: substr(strip_tags($page['full_content']), 0, 160));
$canonicalUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/seo/' . urlencode($page['slug']);

// Parse schema markup
$schemaData = !empty($page['schema_markup']) ? json_decode($page['schema_markup'], true) : null;
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary Meta Tags -->
    <title><?php echo $pageTitle; ?> | StudySmart</title>
    <meta name="title" content="<?php echo $pageTitle; ?>">
    <meta name="description" content="<?php echo $metaDescription; ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page['meta_keywords'] ?: ''); ?>">
    <meta name="author" content="StudySmart">
    <link rel="canonical" href="<?php echo $canonicalUrl; ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $canonicalUrl; ?>">
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $metaDescription; ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($page['og_image'] ?: '/assets/images/seo-default.jpg'); ?>">
    <meta property="og:locale" content="en_ZA">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $canonicalUrl; ?>">
    <meta property="twitter:title" content="<?php echo $pageTitle; ?>">
    <meta property="twitter:description" content="<?php echo $metaDescription; ?>">
    <meta property="twitter:image" content="<?php echo htmlspecialchars($page['og_image'] ?: '/assets/images/seo-default.jpg'); ?>">
    
    <!-- Robots -->
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    
    <!-- Schema.org JSON-LD -->
    <?php if ($schemaData): ?>
    <script type="application/ld+json">
    <?php echo json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
    </script>
    <?php endif; ?>
    
    <!-- FAQ Schema (if Q&A content exists) -->
    <?php if (!empty($qaContent)): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            <?php foreach ($qaContent as $index => $qa): ?>
            {
                "@type": "Question",
                "name": <?php echo json_encode(strip_tags($qa['question_text'])); ?>,
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": <?php echo json_encode(strip_tags($qa['full_answer'])); ?>
                }
            }<?php echo $index < count($qaContent) - 1 ? ',' : ''; ?>
            <?php endforeach; ?>
        ]
    }
    </script>
    <?php endif; ?>
    
    <!-- Breadcrumb Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": "https://<?php echo $_SERVER['HTTP_HOST']; ?>"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Study Resources",
                "item": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/seo"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": <?php echo json_encode($page['subject'] ?: 'Subject'); ?>,
                "item": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/seo/<?php echo urlencode($page['subject'] ?: ''); ?>"
            },
            {
                "@type": "ListItem",
                "position": 4,
                "name": <?php echo json_encode($page['title']); ?>,
                "item": "<?php echo $canonicalUrl; ?>"
            }
        ]
    }
    </script>
    
    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/seo-pages.css">
    
    <?php if (!empty($page['latex_formula'])): ?>
    <!-- MathJax for LaTeX formulas -->
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <?php endif; ?>
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    
    <main class="seo-page-container">
        <!-- Breadcrumb Navigation -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <li><a href="/seo">Study Resources</a></li>
                <li><a href="/seo/<?php echo urlencode($page['subject'] ?: ''); ?>"><?php echo htmlspecialchars($page['subject'] ?: 'Subject'); ?></a></li>
                <li><a href="/seo/<?php echo urlencode($page['subject'] ?: ''); ?>/<?php echo urlencode($page['grade_level'] ?: ''); ?>"><?php echo htmlspecialchars($page['grade_level'] ?: 'Grade'); ?></a></li>
                <li aria-current="page"><?php echo htmlspecialchars($page['title']); ?></li>
            </ol>
        </nav>

        <!-- Main Content -->
        <article class="seo-content" itemscope itemtype="https://schema.org/LearningResource">
            <header class="seo-header">
                <h1 itemprop="name"><?php echo htmlspecialchars($page['title']); ?></h1>
                
                <?php if ($page['subject'] && $page['grade_level']): ?>
                <div class="seo-meta-info">
                    <span class="badge badge-subject"><?php echo htmlspecialchars($page['subject']); ?></span>
                    <span class="badge badge-grade"><?php echo htmlspecialchars($page['grade_level']); ?></span>
                    <span class="badge badge-curriculum">CAPS</span>
                    <?php if ($page['search_intent']): ?>
                    <span class="badge badge-intent"><?php echo ucfirst($page['search_intent']); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="seo-description">
                    <?php echo nl2br(htmlspecialchars($page['meta_description'])); ?>
                </div>
            </header>

            <!-- Table of Contents (if multiple sections) -->
            <?php if (count($qaContent) > 3): ?>
            <nav class="table-of-contents">
                <h2>Table of Contents</h2>
                <ul>
                    <?php foreach ($qaContent as $qa): ?>
                    <li>
                        <a href="#question-<?php echo $qa['question_number']; ?>">
                            Question <?php echo $qa['question_number']; ?>: <?php echo htmlspecialchars(substr($qa['question_text'], 0, 60)); ?>...
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <!-- Main Content Body -->
            <div class="seo-body" itemprop="description">
                <?php echo $page['full_content']; ?>
            </div>

            <!-- Q&A Section -->
            <?php if (!empty($qaContent)): ?>
            <section class="qa-section" itemprop="hasPart" itemscope itemtype="https://schema.org/Question">
                <h2>Detailed Questions & Answers</h2>
                
                <?php foreach ($qaContent as $qa): ?>
                <article class="qa-item" id="question-<?php echo $qa['question_number']; ?>" itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">
                    <div class="question-header">
                        <h3 class="question-title">
                            <span class="question-number">Question <?php echo $qa['question_number']; ?></span>
                            <span itemprop="name"><?php echo htmlspecialchars($qa['question_text']); ?></span>
                        </h3>
                        <?php if ($qa['marks_allocated']): ?>
                        <span class="marks-badge"><?php echo $qa['marks_allocated']; ?> marks</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="answer-content" itemprop="text">
                        <?php if ($qa['step_by_step_solution']): ?>
                        <div class="step-by-step">
                            <h4>Step-by-Step Solution:</h4>
                            <?php echo $qa['step_by_step_solution']; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="full-answer">
                            <?php echo $qa['full_answer']; ?>
                        </div>
                        
                        <?php if ($qa['common_mistakes']): ?>
                        <div class="common-mistakes">
                            <h4>⚠️ Common Mistakes to Avoid:</h4>
                            <p><?php echo nl2br(htmlspecialchars($qa['common_mistakes'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($qa['tips_and_tricks']): ?>
                        <div class="tips-tricks">
                            <h4>💡 Tips & Tricks:</h4>
                            <p><?php echo nl2br(htmlspecialchars($qa['tips_and_tricks'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>

            <!-- Download Section -->
            <section class="download-section">
                <h3>Download This Resource</h3>
                <div class="download-options">
                    <a href="/seo/<?php echo urlencode($page['slug']); ?>/pdf" class="btn btn-download" target="_blank">
                        📄 Download PDF
                    </a>
                    <button onclick="window.print()" class="btn btn-print">
                        🖨️ Print Page
                    </button>
                </div>
            </section>

            <!-- Related Pages -->
            <?php if (!empty($relatedPages)): ?>
            <aside class="related-pages">
                <h3>Related Study Resources</h3>
                <ul>
                    <?php foreach ($relatedPages as $related): ?>
                    <li>
                        <a href="/seo/<?php echo urlencode($related['slug']); ?>">
                            <?php echo htmlspecialchars($related['title']); ?>
                        </a>
                        <?php if ($related['meta_description']): ?>
                        <p class="related-description"><?php echo htmlspecialchars(substr($related['meta_description'], 0, 100)); ?>...</p>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            <?php endif; ?>
        </article>

        <!-- Sidebar -->
        <aside class="seo-sidebar">
            <!-- Quick Navigation -->
            <div class="sidebar-widget">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="/seo">All Study Resources</a></li>
                    <li><a href="/seo/mathematics">Mathematics</a></li>
                    <li><a href="/seo/physical-sciences">Physical Sciences</a></li>
                    <li><a href="/seo/life-sciences">Life Sciences</a></li>
                    <li><a href="/uploads">Upload Your Scripts</a></li>
                    <li><a href="/study-groups">Join Study Groups</a></li>
                </ul>
            </div>

            <!-- CTA Widget -->
            <div class="sidebar-widget cta-widget">
                <h3>Need More Help?</h3>
                <p>Get personalized AI-powered study assistance</p>
                <a href="/ai-chat" class="btn btn-primary">Try AI Chat →</a>
            </div>

            <!-- Stats Widget -->
            <div class="sidebar-widget stats-widget">
                <h3>Page Statistics</h3>
                <div class="stat-item">
                    <span class="stat-label">Views:</span>
                    <span class="stat-value"><?php echo number_format($page['views']); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Last Updated:</span>
                    <span class="stat-value"><?php echo date('d M Y', strtotime($page['updated_at'])); ?></span>
                </div>
            </div>
        </aside>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script src="/assets/js/seo-pages.js"></script>
</body>
</html>
