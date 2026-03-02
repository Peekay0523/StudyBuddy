<?php
$pageTitle = 'Dashboard - StudySmart';
$currentPage = 'dashboard';
include __DIR__ . '/../layouts/header.php';
?>

<div class="badge">✨ AI-Powered Learning</div>

<h1 class="title">
    Study <span>Smart</span>, Not <span class="hard">Hard</span>
</h1>

<p class="subtitle">
    Upload your scripts, get instant memorandums, personalized study plans,
    and discover your ideal career path.
</p>

<!-- STATS -->
<section class="stats">
    <div class="card blue">
        <p>Scripts Uploaded</p>
        <h2><?php echo $scriptsCount ?? 0; ?></h2>
    </div>

    <div class="card orange">
        <p>Active Plans</p>
        <h2><?php echo $plansCount ?? 0; ?></h2>
    </div>

    <div class="card green">
        <p>Report Cards</p>
        <h2><?php echo $reportsCount ?? 0; ?></h2>
    </div>

    <div class="card purple">
        <p>Topics Mastered</p>
        <h2><?php echo $topicsCount ?? 0; ?></h2>
    </div>
</section>

<!-- ACTIONS -->
<h2 class="section-title">What would you like to do?</h2>

<section class="actions">
    <a href="/upload-script" class="action blue"><i class="fas fa-cloud-upload-alt icon-sm"></i> Upload Script</a>
    <a href="/study-plan" class="action orange"><i class="fas fa-calendar-check icon-sm"></i> Study Planner</a>
    <a href="/upload-report-card" class="action green"><i class="fas fa-compass icon-sm"></i> Career Guide</a>
    <a href="/ai-chat" class="action purple"><i class="fas fa-robot icon-sm"></i> AI Assistant</a>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
