<?php
$pageTitle = 'SEO Resources - StudySmart';
$currentPage = 'seo';
include __DIR__ . '/../layouts/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <h1 class="title" style="text-align: center; margin-bottom: 1rem;">
        <i class="fas fa-search" style="color: #667eea;"></i> Study Resources
    </h1>
    <p class="subtitle" style="text-align: center; color: #64748b; margin-bottom: 3rem;">
        Browse our collection of study materials by subject and grade level
    </p>

    <!-- Browse by Subject -->
    <?php if (!empty($subjects)): ?>
    <section style="margin-bottom: 3rem;">
        <h2 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-book" style="color: #667eea;"></i> Browse by Subject
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
            <?php foreach ($subjects as $subject): ?>
                <a href="/seo/<?php echo urlencode($subject); ?>/Grade%2012" 
                   style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); 
                          padding: 1.25rem; 
                          border-radius: 12px; 
                          text-decoration: none; 
                          color: #0369a1; 
                          font-weight: 600;
                          text-align: center;
                          border: 2px solid #bae6fd;
                          transition: all 0.3s ease;
                          box-shadow: 0 2px 4px rgba(14, 165, 233, 0.1);">
                    <i class="fas fa-book-open" style="display: block; font-size: 2rem; margin-bottom: 0.5rem; color: #0ea5e9;"></i>
                    <?php echo htmlspecialchars($subject); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Browse by Grade -->
    <?php if (!empty($grades)): ?>
    <section style="margin-bottom: 3rem;">
        <h2 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-graduation-cap" style="color: #10b981;"></i> Browse by Grade
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
            <?php foreach ($grades as $grade): ?>
                <a href="/seo/Mathematics/<?php echo urlencode($grade); ?>" 
                   style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); 
                          padding: 1rem; 
                          border-radius: 10px; 
                          text-decoration: none; 
                          color: #166534; 
                          font-weight: 600;
                          text-align: center;
                          border: 2px solid #86efac;
                          transition: all 0.3s ease;">
                    <i class="fas fa-user-graduate" style="display: block; font-size: 1.5rem; margin-bottom: 0.5rem; color: #22c55e;"></i>
                    <?php echo htmlspecialchars($grade); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Recent Pages -->
    <?php if (!empty($recentPages)): ?>
    <section style="margin-bottom: 3rem;">
        <h2 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-clock" style="color: #f59e0b;"></i> Recently Added
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            <?php foreach ($recentPages as $page): ?>
                <a href="/seo/<?php echo urlencode($page['slug']); ?>" 
                   style="background: white; 
                          padding: 1.5rem; 
                          border-radius: 12px; 
                          text-decoration: none; 
                          color: inherit;
                          border: 1px solid #e2e8f0;
                          transition: all 0.3s ease;
                          box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <h3 style="margin: 0 0 0.75rem 0; color: #1e293b; font-size: 1.1rem; line-height: 1.4;">
                        <?php echo htmlspecialchars($page['title']); ?>
                    </h3>
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                        <span style="background: #e0e7ff; color: #4f46e5; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                            <?php echo htmlspecialchars($page['subject']); ?>
                        </span>
                        <span style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                            <?php echo htmlspecialchars($page['grade_level']); ?>
                        </span>
                    </div>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin: 0 0 0.75rem 0;">
                        <?php echo htmlspecialchars(substr($page['meta_description'] ?? $page['title'], 0, 120)); ?>...
                    </p>
                    <small style="color: #94a3b8; font-size: 0.8rem;">
                        <i class="fas fa-calendar"></i> <?php echo $page['created_at'] ? date('M d, Y', strtotime($page['created_at'])) : 'Unknown date'; ?>
                    </small>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Search Box -->
    <section style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                    padding: 3rem; 
                    border-radius: 16px; 
                    text-align: center;
                    margin-bottom: 3rem;">
        <h2 style="color: white; font-size: 1.75rem; margin-bottom: 1rem;">
            <i class="fas fa-search"></i> Can't find what you're looking for?
        </h2>
        <p style="color: rgba(255,255,255,0.9); margin-bottom: 2rem; font-size: 1.1rem;">
            Search through all our study resources
        </p>
        <form action="/seo/search" method="GET" style="display: flex; max-width: 500px; margin: 0 auto; gap: 0.5rem;">
            <input type="text" 
                   name="q" 
                   placeholder="Search for topics, subjects, or keywords..." 
                   required
                   style="flex: 1; 
                          padding: 0.875rem 1.25rem; 
                          border: none; 
                          border-radius: 8px; 
                          font-size: 1rem;
                          background: white;
                          box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <button type="submit" 
                    style="background: #f59e0b; 
                           color: white; 
                           border: none; 
                           padding: 0.875rem 1.5rem; 
                           border-radius: 8px; 
                           font-weight: 600; 
                           cursor: pointer;
                           transition: all 0.3s ease;">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </section>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
