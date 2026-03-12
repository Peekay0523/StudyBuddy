<?php
/**
 * SEO Search Results Template
 */

$searchQuery = htmlspecialchars($query);
$pageTitle = "Search Results for '$searchQuery' | StudySmart";
?>
<!DOCTYPE html>
<html lang="en-ZA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/seo-pages.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    
    <main class="seo-search-results">
        <header class="search-header">
            <h1>Search Results</h1>
            <p class="search-query">Results for "<?php echo $searchQuery; ?>"</p>
            <p class="results-count"><?php echo count($results); ?> resource(s) found</p>
        </header>

        <section class="search-form-section">
            <form action="/seo/search" method="GET" class="search-form">
                <input type="text" name="q" value="<?php echo $query; ?>" placeholder="Search study resources..." required>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </section>

        <?php if (empty($results)): ?>
        <div class="no-results">
            <div class="no-results-icon">🔍</div>
            <h2>No Results Found</h2>
            <p>We couldn't find any resources matching "<?php echo $searchQuery; ?>"</p>
            
            <div class="suggestions">
                <h3>Try these instead:</h3>
                <ul>
                    <li>Check your spelling</li>
                    <li>Use more general keywords</li>
                    <li>Browse by subject: <a href="/seo">All Subjects</a></li>
                    <li>Request this topic: <a href="/contact">Contact Us</a></li>
                </ul>
            </div>
        </div>
        <?php else: ?>
        <section class="results-list">
            <?php foreach ($results as $result): ?>
            <article class="search-result">
                <h3>
                    <a href="/seo/<?php echo urlencode($result['slug']); ?>">
                        <?php echo htmlspecialchars($result['title']); ?>
                    </a>
                </h3>
                <p class="result-meta">
                    <span class="subject"><?php echo htmlspecialchars($result['subject']); ?></span>
                    <span class="grade"><?php echo htmlspecialchars($result['grade_level']); ?></span>
                    <span class="views">👁️ <?php echo number_format($result['views']); ?></span>
                </p>
                <p class="result-excerpt">
                    <?php echo htmlspecialchars(substr(strip_tags($result['full_content']), 0, 200)); ?>...
                </p>
                <a href="/seo/<?php echo urlencode($result['slug']); ?>" class="btn btn-sm">View Resource</a>
            </article>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
