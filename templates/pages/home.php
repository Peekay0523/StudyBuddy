<?php
$pageTitle = 'StudySmart - AI Learning Assistant';
$currentPage = 'home';
include __DIR__ . '/../layouts/header.php';
?>

<div class="hero-section">
    <h1>Welcome to StudySmart</h1>
    <p>An AI-powered educational platform designed to help students learn more effectively.</p>
    <?php if (!isLoggedIn()): ?>
        <a href="/register" class="btn-cta">Get Started</a>
    <?php else: ?>
        <a href="/dashboard" class="btn-cta">Go to Dashboard</a>
    <?php endif; ?>
</div>

<div class="features-section">
    <div class="feature-card">
        <h3><i class="fas fa-file-alt icon"></i> Script Analysis</h3>
        <p>Upload your study scripts and get AI-generated memorandums highlighting key topics and concepts.</p>
        <ul>
            <li><i class="fas fa-check-circle icon-sm"></i> Automatic topic identification</li>
            <li><i class="fas fa-check-circle icon-sm"></i> Challenging topic detection</li>
            <li><i class="fas fa-check-circle icon-sm"></i> Personalized memorandums</li>
        </ul>
    </div>

    <div class="feature-card">
        <h3><i class="fas fa-calendar-check icon"></i> Personalized Study Plans</h3>
        <p>Receive customized study plans focused on areas where you need the most help.</p>
        <ul>
            <li><i class="fas fa-check-circle icon-sm"></i> AI-generated study schedules</li>
            <li><i class="fas fa-check-circle icon-sm"></i> Topic prioritization</li>
            <li><i class="fas fa-check-circle icon-sm"></i> Resource recommendations</li>
        </ul>
    </div>

    <div class="feature-card">
        <h3><i class="fas fa-compass icon"></i> Career Guidance</h3>
        <p>Upload your report cards to get career recommendations based on your academic strengths.</p>
        <ul>
            <li><i class="fas fa-check-circle icon-sm"></i> Career path suggestions</li>
            <li><i class="fas fa-check-circle icon-sm"></i> Strengths analysis</li>
            <li><i class="fas fa-check-circle icon-sm"></i> Improvement areas</li>
        </ul>
    </div>
</div>

<div class="how-it-works">
    <h2><i class="fas fa-cogs icon"></i> How It Works</h2>
    <div class="steps-container">
        <div class="step">
            <div class="step-number"><i class="fas fa-cloud-upload-alt"></i></div>
            <h4>Upload</h4>
            <p>Upload your scripts or report cards</p>
        </div>
        <div class="step">
            <div class="step-number"><i class="fas fa-brain"></i></div>
            <h4>AI Analysis</h4>
            <p>Our AI analyzes your content</p>
        </div>
        <div class="step">
            <div class="step-number"><i class="fas fa-chart-bar"></i></div>
            <h4>Personalized Results</h4>
            <p>Get tailored recommendations</p>
        </div>
        <div class="step">
            <div class="step-number"><i class="fas fa-arrow-up"></i></div>
            <h4>Improve</h4>
            <p>Enhance your learning journey</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
