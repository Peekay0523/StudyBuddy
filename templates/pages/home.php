<?php
$pageTitle = 'StudySmart - AI Learning Assistant';
$currentPage = 'home';
include __DIR__ . '/../layouts/header.php';
?>

<div class="hero-section">
    <h1>Welcome to StudyBuddie</h1>
    <p class="hero-tagline"><i class="fas fa-map-marker-alt"></i> Built for South African learners</p>
    <p>An AI-powered educational platform designed to help students learn more effectively.</p>
    <?php if (!isLoggedIn()): ?>
        <a href="/register" class="btn-cta">Get Started</a>
    <?php else: ?>
        <a href="/dashboard" class="btn-cta">Go to Dashboard</a>
    <?php endif; ?>
    
    <!-- PWA Install Button -->
    <button id="installBtn" class="btn-install" style="display: none;">
        <i class="fas fa-mobile-alt"></i> Add to Home Screen
    </button>
</div>

<!-- Sample Results Section -->
<div class="sample-results-section">
    <h2 class="section-heading">
        <i class="fas fa-star" style="color: #fbbf24;"></i> Success Stories
    </h2>
    <p class="section-subheading">
        See how StudySmart is helping South African students excel
    </p>

    <div class="sample-testimonials-grid">
        <!-- Testimonial 1 -->
        <div class="sample-testimonial-card">
            <div class="testimonial-header">
                <div class="testimonial-avatar avatar-T">T</div>
                <div class="testimonial-info">
                    <h4>Thabo M.</h4>
                    <p>Grade 12, Johannesburg</p>
                </div>
            </div>
            <p class="testimonial-text">"StudySmart helped me identify my weak areas in Mathematics. The AI-generated study plan was exactly what I needed!"</p>
            <div class="testimonial-badges">
                <span class="badge-success">
                    <i class="fas fa-arrow-up"></i> Math improved from 58% to 82%
                </span>
            </div>
        </div>

        <!-- Testimonial 2 -->
        <div class="sample-testimonial-card">
            <div class="testimonial-header">
                <div class="testimonial-avatar avatar-P">P</div>
                <div class="testimonial-info">
                    <h4>Precious N.</h4>
                    <p>Grade 11, Cape Town</p>
                </div>
            </div>
            <p class="testimonial-text">"The career recommendations opened my eyes to opportunities I didn't know existed. Now I know exactly what subjects to focus on!"</p>
            <div class="testimonial-badges">
                <span class="badge-info">
                    <i class="fas fa-compass"></i> Discovered 5 career paths
                </span>
            </div>
        </div>

        <!-- Testimonial 3 -->
        <div class="sample-testimonial-card">
            <div class="testimonial-header">
                <div class="testimonial-avatar avatar-L">L</div>
                <div class="testimonial-info">
                    <h4>Lerato K.</h4>
                    <p>Grade 10, Durban</p>
                </div>
            </div>
            <p class="testimonial-text">"I was struggling with Physical Sciences. The memorandum summaries made everything click. Highly recommend!"</p>
            <div class="testimonial-badges">
                <span class="badge-purple">
                    <i class="fas fa-chart-line"></i> Sciences up by 25%
                </span>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="sample-stats-grid">
        <div class="stat-item">
            <div class="stat-value stat-blue">100+</div>
            <div class="stat-label">Students Helped</div>
        </div>
        <div class="stat-item">
            <div class="stat-value stat-gold">100+</div>
            <div class="stat-label">Scripts Analyzed</div>
        </div>
        <div class="stat-item">
            <div class="stat-value stat-green">84%</div>
            <div class="stat-label">Improved Grades</div>
        </div>
    </div>
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
    <h2><i class="fas fa-cogs"></i> How It Works</h2>
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

<style>
.hero-tagline {
    font-size: 18px;
    color: white;
    font-weight: 600;
    margin-bottom: 10px;
}

.hero-tagline i {
    margin-right: 8px;
    color: #fbbf24;
}

.sample-results-section {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    padding: 60px 20px;
    margin: 40px 0;
    border-radius: 16px;
    animation: fadeInUp 0.8s ease-out;
}

.section-heading {
    text-align: center;
    color: #1e293b;
    font-size: 32px;
    margin-bottom: 15px;
}

.section-subheading {
    text-align: center;
    color: #64748b;
    font-size: 16px;
    margin-bottom: 40px;
}

.sample-testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.sample-testimonial-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.sample-testimonial-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.testimonial-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.testimonial-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: bold;
    flex-shrink: 0;
}

.avatar-T {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.avatar-P {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
}

.avatar-L {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.testimonial-info h4 {
    margin: 0;
    color: #1e293b;
    font-size: 18px;
}

.testimonial-info p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.testimonial-text {
    color: #475569;
    font-style: italic;
    margin-bottom: 15px;
    line-height: 1.6;
}

.testimonial-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.badge-success {
    background: #dcfce7;
    color: #16a34a;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.badge-info {
    background: #dbeafe;
    color: #2563eb;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.badge-purple {
    background: #fae8ff;
    color: #d946ef;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.sample-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    max-width: 1000px;
    margin: 50px auto 0;
    text-align: center;
}

.stat-item {
    padding: 20px;
}

.stat-value {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 10px;
}

.stat-blue {
    color: #667eea;
}

.stat-gold {
    color: #fbbf24;
}

.stat-green {
    color: #10b981;
}

/* Features Section Enhancements */
.features-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 60px auto;
    padding: 0 20px;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    opacity: 0;
    transition: opacity 0.3s;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px -10px rgba(102, 126, 234, 0.15);
    border-color: #e2e8f0;
}

.feature-card:hover::before {
    opacity: 1;
}

.feature-card h3 {
    color: #1e293b;
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.feature-card .icon {
    width: 60px;
    height: 60px;
    background: #f5f3ff;
    color: #7c3aed;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    font-size: 24px;
    transition: all 0.3s;
}

.feature-card:hover .icon {
    background: #7c3aed;
    color: white;
    transform: scale(1.1) rotate(5deg);
}

.feature-card p {
    color: #64748b;
    line-height: 1.7;
    font-size: 15px;
    margin-bottom: 25px;
}

.feature-card ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.feature-card li {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
}

.feature-card li i {
    color: #10b981;
    font-size: 16px;
}

/* How It Works Section */
.how-it-works {
    background: #f8fafc;
    padding: 80px 20px;
    text-align: center;
    border-radius: 24px;
    margin: 60px 20px;
    border: 1px solid #e2e8f0;
}

.how-it-works h2 {
    color: #1e293b;
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.how-it-works h2 i {
    color: #7c3aed;
    background: #f5f3ff;
    width: 50px;
    height: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 24px;
}

.steps-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 40px;
    max-width: 1200px;
    margin: 0 auto;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

.step-number {
    width: 80px;
    height: 80px;
    background: white;
    color: #7c3aed;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 28px;
    box-shadow: 0 10px 20px -5px rgba(124, 58, 237, 0.2);
    border: 2px solid #f5f3ff;
    transition: all 0.3s;
}

.step:hover .step-number {
    background: #7c3aed;
    color: white;
    transform: scale(1.1);
}

.step h4 {
    color: #1e293b;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
}

.step p {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .sample-results-section {
        padding: 40px 15px;
        margin: 25px 0;
        border-radius: 12px;
    }

    .section-heading {
        font-size: 26px;
    }

    .section-subheading {
        font-size: 14px;
        margin-bottom: 25px;
    }

    .sample-testimonials-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .sample-testimonial-card {
        padding: 20px;
    }

    .testimonial-avatar {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }

    .testimonial-info h4 {
        font-size: 16px;
    }

    .testimonial-info p {
        font-size: 13px;
    }

    .testimonial-text {
        font-size: 14px;
        line-height: 1.5;
    }

    .sample-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 35px;
    }

    .stat-item {
        padding: 15px 10px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .stat-value {
        font-size: 32px;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .hero-section {
        padding: 40px 15px;
    }

    .hero-section h1 {
        font-size: 28px !important;
    }

    .hero-section p {
        font-size: 15px !important;
    }

    .sample-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left;
        padding: 12px 20px;
    }
    
    .stat-value {
        font-size: 28px;
        margin-bottom: 0;
    }
}

/* PWA Install Button Styling */
.btn-install {
    display: none;
    margin: 20px auto;
    padding: 14px 32px;
    background: linear-gradient(135deg, #6C63FF 0%, #5a52d5 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(108, 99, 255, 0.3);
    transition: all 0.3s ease;
    animation: pulse 2s infinite;
}

.btn-install:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(108, 99, 255, 0.4);
}

.btn-install i {
    margin-right: 8px;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 4px 12px rgba(108, 99, 255, 0.3);
    }
    50% {
        box-shadow: 0 4px 20px rgba(108, 99, 255, 0.6);
    }
}

@media (max-width: 768px) {
    .btn-install {
        padding: 12px 24px;
        font-size: 14px;
        margin: 15px auto;
    }
}
</style>
