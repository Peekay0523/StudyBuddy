<?php
$pageTitle = 'Study Plans - StudySmart';
$currentPage = 'study-plan';
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Your Study Plans</h1>
<p class="subtitle">Personalized study plans to help you master challenging topics.</p>

<div style="margin-bottom: 30px; display: flex; gap: 15px; flex-wrap: wrap;">
    <a href="/upload-script" class="btn-primary">
        <i class="fas fa-magic"></i> Create Study Plan
    </a>
    <a href="/upload-report-card" class="btn-primary" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
        <i class="fas fa-file-alt"></i> Upload Report Card
    </a>
</div>

<?php if (empty($studyPlans)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> You don't have any active study plans yet. 
        <strong>Upload a script</strong> to generate a personalized study plan based on your learning needs!
    </div>
    
    <div class="features-section" style="margin-top: 40px;">
        <div class="feature-card">
            <h3><i class="fas fa-upload" style="color: #667eea;"></i> Step 1: Upload Script</h3>
            <p style="color: #6b7280;">Upload your study notes, textbooks, or any learning material in PDF, DOCX, or TXT format.</p>
        </div>
        <div class="feature-card">
            <h3><i class="fas fa-robot" style="color: #667eea;"></i> Step 2: AI Analysis</h3>
            <p style="color: #6b7280;">Our AI analyzes your content to identify key topics, challenging areas, and learning objectives.</p>
        </div>
        <div class="feature-card">
            <h3><i class="fas fa-clipboard-check" style="color: #667eea;"></i> Step 3: Get Study Plan</h3>
            <p style="color: #6b7280;">Receive a personalized study plan with strategies to master the challenging topics.</p>
        </div>
    </div>
<?php else: ?>
    <section class="actions" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
        <?php foreach ($studyPlans as $plan): ?>
            <a href="/view-study-plan/<?php echo $plan['id']; ?>" class="action orange" style="text-decoration: none; flex-direction: column; align-items: flex-start; height: auto; min-height: 150px;">
                <h3 style="margin: 0 0 10px 0; font-size: 18px;"><?php echo htmlspecialchars($plan['title']); ?></h3>
                <p style="margin: 0; font-size: 14px; opacity: 0.9; flex: 1; overflow: hidden; text-overflow: ellipsis;">
                    <?php echo htmlspecialchars(substr($plan['content'], 0, 100)); ?>...
                </p>
                <small style="margin-top: 10px; opacity: 0.8;">
                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($plan['created_at'])); ?>
                </small>
            </a>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
