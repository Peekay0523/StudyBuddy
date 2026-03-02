<?php
$pageTitle = 'View Study Plan - StudySmart';
$currentPage = 'study-plan';
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Study Plan</h1>
<p class="subtitle"><?php echo htmlspecialchars($studyPlan['title']); ?></p>

<a href="/study-plan" class="btn-primary" style="text-decoration: none; display: inline-block; margin-bottom: 20px;">
    <i class="fas fa-arrow-left"></i> Back to Study Plans
</a>

<div class="feature-card" style="max-width: 800px;">
    <h3 style="margin-bottom: 20px;">
        <i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($studyPlan['title']); ?>
    </h3>
    
    <div style="margin-bottom: 20px;">
        <strong>Created:</strong> <?php echo date('M d, Y', strtotime($studyPlan['created_at'])); ?> |
        <strong>Status:</strong> 
        <span style="color: <?php echo $studyPlan['is_active'] ? '#16a34a' : '#6b7280'; ?>;">
            <?php echo $studyPlan['is_active'] ? 'Active' : 'Inactive'; ?>
        </span>
    </div>
    
    <h4 style="margin: 20px 0 10px 0;"><i class="fas fa-clipboard-list"></i> Plan Details</h4>
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; white-space: pre-wrap;">
        <?php echo htmlspecialchars($studyPlan['content']); ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
