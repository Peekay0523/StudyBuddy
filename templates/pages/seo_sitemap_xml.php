<?php
/**
 * SEO Sitemap XML Template
 * Generates XML sitemap for search engines
 */

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
    
    <!-- Homepage -->
    <url>
        <loc>https://<?php echo $_SERVER['HTTP_HOST']; ?>/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- SEO Pages -->
    <?php foreach ($pages as $page): ?>
    <url>
        <loc>https://<?php echo $_SERVER['HTTP_HOST']; ?>/seo/<?php echo htmlspecialchars(urlencode($page['slug'])); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($page['updated_at'])); ?></lastmod>
        <changefreq><?php echo $page['views'] > 100 ? 'weekly' : 'monthly'; ?></changefreq>
        <priority><?php echo $page['views'] > 500 ? '0.8' : ($page['views'] > 100 ? '0.6' : '0.5'); ?></priority>
    </url>
    <?php endforeach; ?>
    
    <!-- Browse Pages by Subject -->
    <?php
    $subjects = [
        'mathematics',
        'mathematical-literacy',
        'physical-sciences',
        'life-sciences',
        'english-home-language',
        'afrikaans-home-language',
        'geography',
        'history',
        'accounting',
        'business-studies'
    ];
    ?>
    <?php foreach ($subjects as $subject): ?>
    <url>
        <loc>https://<?php echo $_SERVER['HTTP_HOST']; ?>/seo/<?php echo $subject; ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- Browse Pages by Grade -->
    <?php
    $grades = ['Grade 10', 'Grade 11', 'Grade 12'];
    ?>
    <?php foreach ($grades as $grade): ?>
    <url>
        <loc>https://<?php echo $_SERVER['HTTP_HOST']; ?>/seo/grade/<?php echo urlencode($grade); ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>
    
</urlset>
