<?php
/**
 * SEO Browse Page Template
 * Shows all pages for a subject/grade combination
 */

$subjectTitle = htmlspecialchars(ucwords(str_replace('-', ' ', $subject)));
$gradeTitle = htmlspecialchars($grade);
$pageTitle = "$subjectTitle $gradeTitle Study Resources & Memorandums";
$metaDescription = "Complete study resources, past papers, and memorandums for $subjectTitle $gradeTitle. CAPS curriculum aligned with detailed answers and explanations.";
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | StudySmart</title>
    <meta name="description" content="<?php echo $metaDescription; ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($subject); ?>, <?php echo htmlspecialchars($grade); ?>, memorandum, past papers, study guide, CAPS">
    <link rel="canonical" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/seo/<?php echo urlencode($subject); ?>/<?php echo urlencode($grade); ?>">
    
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/seo-pages.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    
    <main class="seo-browse-container">
        <header class="browse-header">
            <h1><?php echo $subjectTitle; ?> - <?php echo $gradeTitle; ?></h1>
            <p class="browse-description">
                Comprehensive study resources, memorandums, and practice materials for 
                <?php echo $subjectTitle; ?> students in <?php echo $gradeTitle; ?>. 
                All content aligned with the South African CAPS curriculum.
            </p>
        </header>

        <section class="pages-grid">
            <h2>Available Resources (<?php echo count($pages); ?>)</h2>
            
            <div class="resource-grid">
                <?php foreach ($pages as $page): ?>
                <article class="resource-card">
                    <h3>
                        <a href="/seo/<?php echo urlencode($page['slug']); ?>">
                            <?php echo htmlspecialchars($page['title']); ?>
                        </a>
                    </h3>
                    <p class="resource-excerpt">
                        <?php echo htmlspecialchars(substr(strip_tags($page['full_content']), 0, 150)); ?>...
                    </p>
                    <div class="resource-meta">
                        <span class="views">👁️ <?php echo number_format($page['views']); ?> views</span>
                        <span class="date">📅 <?php echo $page['published_at'] ? date('d M Y', strtotime($page['published_at'])) : 'Not published'; ?></span>
                    </div>
                    <a href="/seo/<?php echo urlencode($page['slug']); ?>" class="btn btn-primary btn-sm">View Resource</a>
                </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Other Grades -->
        <section class="other-grades">
            <h2>Other Grades for <?php echo $subjectTitle; ?></h2>
            <div class="grade-links">
                <a href="/seo/<?php echo urlencode($subject); ?>/Grade 10" class="grade-link">Grade 10</a>
                <a href="/seo/<?php echo urlencode($subject); ?>/Grade 11" class="grade-link">Grade 11</a>
                <a href="/seo/<?php echo urlencode($subject); ?>/Grade 12" class="grade-link">Grade 12</a>
            </div>
        </section>

        <!-- Other Subjects -->
        <section class="other-subjects">
            <h2>Other Subjects for <?php echo $gradeTitle; ?></h2>
            <div class="subject-links">
                <a href="/seo/Mathematics/<?php echo urlencode($grade); ?>" class="subject-link">Mathematics</a>
                <a href="/seo/Mathematical Literacy/<?php echo urlencode($grade); ?>" class="subject-link">Mathematical Literacy</a>
                <a href="/seo/Physical Sciences/<?php echo urlencode($grade); ?>" class="subject-link">Physical Sciences</a>
                <a href="/seo/Life Sciences/<?php echo urlencode($grade); ?>" class="subject-link">Life Sciences</a>
                <a href="/seo/English Home Language/<?php echo urlencode($grade); ?>" class="subject-link">English</a>
                <a href="/seo/Geography/<?php echo urlencode($grade); ?>" class="subject-link">Geography</a>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
