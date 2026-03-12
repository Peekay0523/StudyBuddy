<?php
/**
 * SEO Browse Empty Page Template
 * Shows when no pages exist for a subject/grade combination
 */

$subjectTitle = htmlspecialchars(ucwords(str_replace('-', ' ', $subject)));
$gradeTitle = htmlspecialchars($grade);
$pageTitle = "$subjectTitle $gradeTitle Study Resources - Coming Soon";
$metaDescription = "Study resources for $subjectTitle $gradeTitle are being prepared. Check back soon for memorandums, past papers, and study guides.";
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | StudySmart</title>
    <meta name="description" content="<?php echo $metaDescription; ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/seo/<?php echo urlencode($subject); ?>/<?php echo urlencode($grade); ?>">
    
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/seo-pages.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    
    <main class="seo-browse-empty">
        <div class="empty-content">
            <div class="empty-icon">📚</div>
            <h1><?php echo $subjectTitle; ?> - <?php echo $gradeTitle; ?></h1>
            <h2>Study Resources Coming Soon</h2>
            <p class="empty-message">
                We're currently preparing comprehensive study materials, memorandums, and past papers for 
                <strong><?php echo $subjectTitle; ?></strong> students in <strong><?php echo $gradeTitle; ?></strong>.
            </p>
            
            <div class="notify-form">
                <h3>Get Notified When Ready</h3>
                <p>Enter your email to be notified when resources are available:</p>
                <form action="/seo/notify-me.php" method="POST">
                    <input type="hidden" name="subject" value="<?php echo htmlspecialchars($subject); ?>">
                    <input type="hidden" name="grade" value="<?php echo htmlspecialchars($grade); ?>">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-primary">Notify Me</button>
                    </div>
                </form>
            </div>

            <!-- Alternative Resources -->
            <div class="alternative-resources">
                <h3>While You Wait, Check These Out:</h3>
                
                <div class="resource-sections">
                    <div class="resource-section">
                        <h4>📐 Other Grades for <?php echo $subjectTitle; ?></h4>
                        <ul>
                            <li><a href="/seo/<?php echo urlencode($subject); ?>/Grade 10">Grade 10 Resources</a></li>
                            <li><a href="/seo/<?php echo urlencode($subject); ?>/Grade 11">Grade 11 Resources</a></li>
                            <li><a href="/seo/<?php echo urlencode($subject); ?>/Grade 12">Grade 12 Resources</a></li>
                        </ul>
                    </div>

                    <div class="resource-section">
                        <h4>📚 Other Subjects for <?php echo $gradeTitle; ?></h4>
                        <ul>
                            <li><a href="/seo/Mathematics/<?php echo urlencode($grade); ?>">Mathematics</a></li>
                            <li><a href="/seo/Mathematical Literacy/<?php echo urlencode($grade); ?>">Mathematical Literacy</a></li>
                            <li><a href="/seo/Physical Sciences/<?php echo urlencode($grade); ?>">Physical Sciences</a></li>
                            <li><a href="/seo/Life Sciences/<?php echo urlencode($grade); ?>">Life Sciences</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="cta-section">
                <h3>Need Help Now?</h3>
                <p>Our AI-powered chat can help you with specific questions:</p>
                <a href="/ai-chat" class="btn btn-primary btn-lg">Try AI Chat Assistant →</a>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
