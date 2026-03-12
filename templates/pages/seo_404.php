<?php
/**
 * SEO 404 Page Template
 * Shows when requested page doesn't exist
 */

$pageTitle = "Page Not Found - Resource Unavailable | StudySmart";
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="robots" content="noindex, follow">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/seo-pages.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    
    <main class="seo-404">
        <div class="error-content">
            <div class="error-icon">📭</div>
            <h1>Resource Not Found</h1>
            <h2>404 - Page Missing</h2>
            <p class="error-message">
                The study resource you're looking for (<code><?php echo htmlspecialchars($slug); ?></code>) 
                doesn't exist yet or has been moved.
            </p>

            <?php if (!empty($suggestions)): ?>
            <div class="suggestions">
                <h3>Similar Resources You Might Find Helpful:</h3>
                <ul class="suggestion-list">
                    <?php foreach ($suggestions as $suggestion): ?>
                    <li>
                        <a href="/seo/<?php echo urlencode($suggestion['slug']); ?>">
                            <?php echo htmlspecialchars($suggestion['title']); ?>
                        </a>
                        <?php if (!empty($suggestion['meta_description'])): ?>
                        <p class="suggestion-desc"><?php echo htmlspecialchars(substr($suggestion['meta_description'], 0, 120)); ?>...</p>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="help-section">
                <h3>What can you do?</h3>
                <div class="help-options">
                    <div class="help-option">
                        <h4>📚 Browse All Resources</h4>
                        <p>Explore our complete collection of study materials</p>
                        <a href="/seo" class="btn btn-primary">Browse All</a>
                    </div>
                    <div class="help-option">
                        <h4>🔍 Search</h4>
                        <p>Find what you need using our search</p>
                        <form action="/seo/search" method="GET" class="inline-form">
                            <input type="text" name="q" placeholder="Search..." required>
                            <button type="submit" class="btn btn-secondary">Search</button>
                        </form>
                    </div>
                    <div class="help-option">
                        <h4>💬 Ask AI</h4>
                        <p>Get instant help with your specific question</p>
                        <a href="/ai-chat" class="btn btn-secondary">Try AI Chat</a>
                    </div>
                    <div class="help-option">
                        <h4>📝 Request Topic</h4>
                        <p>Want us to create this resource?</p>
                        <a href="/contact" class="btn btn-outline">Request Now</a>
                    </div>
                </div>
            </div>

            <div class="popular-resources">
                <h3>Popular Study Resources</h3>
                <ul>
                    <li><a href="/seo/mathematics-grade-12-full-memorandum">Mathematics Grade 12 Full Memorandum</a></li>
                    <li><a href="/seo/physical-sciences-grade-12-physics-answers">Physical Sciences Grade 12 Physics Answers</a></li>
                    <li><a href="/seo/life-sciences-grade-12-dna-genetics-guide">Life Sciences Grade 12 DNA & Genetics Guide</a></li>
                    <li><a href="/seo/mathematical-literacy-grade-12-finance-memorandum">Mathematical Literacy Grade 12 Finance Memorandum</a></li>
                </ul>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
