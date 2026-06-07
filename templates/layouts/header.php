<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6C63FF">
    <meta name="description" content="StudyBuddie - Your AI-powered learning assistant">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <title><?php echo $pageTitle ?? 'StudySmart - AI Learning Assistant'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php echo $extraHead ?? ''; ?>
</head>
<body>

<!-- Global 3D Tower Loader -->
<div class="global-loader-overlay active" id="globalLoader">
    <div class="loader-container">
        <div class="loader-tower">
            <div class="box box-1">
                <div class="side-left"></div>
                <div class="side-right"></div>
                <div class="side-top"></div>
            </div>
            <div class="box box-2">
                <div class="side-left"></div>
                <div class="side-right"></div>
                <div class="side-top"></div>
            </div>
            <div class="box box-3">
                <div class="side-left"></div>
                <div class="side-right"></div>
                <div class="side-top"></div>
            </div>
            <div class="box box-4">
                <div class="side-left"></div>
                <div class="side-right"></div>
                <div class="side-top"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function showLoader(text = null) {
        const loader = document.getElementById('globalLoader');
        if (loader) {
            loader.classList.add('active');
        }
    }

    function hideLoader() {
        const loader = document.getElementById('globalLoader');
        if (loader) {
            // Add a small delay for smoother transition
            setTimeout(() => {
                loader.classList.remove('active');
            }, 500);
        }
    }

    // Hide loader when page is fully loaded
    window.addEventListener('load', function() {
        hideLoader();
    });

    // Show loader on page transitions (when clicking links)
    window.addEventListener('beforeunload', function() {
        showLoader();
    });

    // Handle back/forward cache
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            hideLoader();
        }
    });
</script>

<div class="app-container">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-graduation-cap icon-lg"></i> <span>StudyBuddie</span>
            <small>AI Learning Assistant</small>
        </div>

        <nav class="menu">
            <a href="/" class="<?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>"><i class="fas fa-home icon-sm"></i> Home</a>
            <?php if (isLoggedIn()): ?>
                <?php 
                $currentUser = getCurrentUser();
                if (isset($currentUser['role']) && $currentUser['role'] === 'admin'): 
                ?>
                    <!-- Admin users only see Admin Panel link -->
                    <a href="/admin" class="<?php echo strpos($currentPage ?? '', 'admin-') === 0 ? 'active' : ''; ?>" style="background: rgba(251, 191, 36, 0.1); border: 1px solid #fbbf24;">
                        <i class="fas fa-shield-halved icon-sm"></i> Admin Panel
                    </a>
                <?php else: ?>
                    <!-- Parent users see limited navigation -->
                    <?php if (isParent()): ?>
                        <a href="/parent-dashboard" class="<?php echo ($currentPage ?? '') === 'parent-dashboard' ? 'active' : ''; ?>"><i class="fas fa-chart-line icon-sm"></i> Parent Dashboard</a>
                        <a href="/parent/track-progress" class="<?php echo ($currentPage ?? '') === 'track-progress' ? 'active' : ''; ?>"><i class="fas fa-chart-bar icon-sm"></i> Track Progress</a>
                        <a href="/subscription" class="<?php echo ($currentPage ?? '') === 'subscription' ? 'active' : ''; ?>"><i class="fas fa-crown icon-sm"></i> Subscription</a>
                    <?php else: ?>
                        <!-- Regular users see full navigation -->
                        <a href="/dashboard" class="<?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-chart-line icon-sm"></i> Dashboard</a>
                        <a href="/upload-script" class="<?php echo ($currentPage ?? '') === 'scripts' ? 'active' : ''; ?>"><i class="fas fa-file-alt icon-sm"></i> Scripts</a>
                        <a href="/study-plan" class="<?php echo ($currentPage ?? '') === 'study-plan' ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt icon-sm"></i> Study Plan
                            <?php
                            $pendingPlans = getPendingStudyPlansCount();
                            if ($pendingPlans > 0):
                            ?>
                                <span class="notification-badge"><?php echo $pendingPlans > 99 ? '99+' : $pendingPlans; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/study-group" class="<?php echo ($currentPage ?? '') === 'study-group' ? 'active' : ''; ?>" id="study-group-link">
                            <i class="fas fa-users icon-sm"></i> Study Group
                            <?php
                            $groupActivity = getStudyGroupActivityCount();
                            if ($groupActivity > 0):
                            ?>
                                <span class="notification-badge"><?php echo $groupActivity > 99 ? '99+' : $groupActivity; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/scan" class="<?php echo ($currentPage ?? '') === 'scan' ? 'active' : ''; ?>"><i class="fas fa-camera icon-sm"></i> Scan to PDF</a>
                        <a href="/upload-report-card" class="<?php echo ($currentPage ?? '') === 'careers' ? 'active' : ''; ?>" id="careers-link">
                            <i class="fas fa-bullseye icon-sm"></i> Careers
                            <?php
                            $bursaryCount = getBursaryNotificationCount();
                            if ($bursaryCount > 0):
                            ?>
                                <span class="notification-badge"><?php echo $bursaryCount > 99 ? '99+' : $bursaryCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/ai-chat" class="<?php echo ($currentPage ?? '') === 'ai-chat' ? 'active' : ''; ?>"><i class="fas fa-comments icon-sm"></i> AI Chat</a>
                        <a href="/simulate" class="<?php echo ($currentPage ?? '') === 'simulate' ? 'active' : ''; ?>"><i class="fas fa-vial icon-sm"></i> Simulate</a>
                        <a href="/subscription" class="<?php echo ($currentPage ?? '') === 'subscription' ? 'active' : ''; ?>"><i class="fas fa-crown icon-sm"></i> Subscription</a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (isset($currentUser['role']) && $currentUser['role'] === 'admin'): ?>
                    <a href="/profile" class="<?php echo ($currentPage ?? '') === 'profile' ? 'active' : ''; ?>"><i class="fas fa-user-circle icon-sm"></i> My Profile</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="/login" class="<?php echo ($currentPage ?? '') === 'login' ? 'active' : ''; ?>"><i class="fas fa-sign-in-alt icon-sm"></i> Login</a>
                <a href="/register" class="<?php echo ($currentPage ?? '') === 'register' ? 'active' : ''; ?>"><i class="fas fa-user-plus icon-sm"></i> Register</a>
            <?php endif; ?>
        </nav>

        <?php 
        $currentUser = getCurrentUser();
        if (!isset($currentUser['role']) || $currentUser['role'] !== 'admin'): 
        ?>
        <div class="help-box">
            <h4><i class="fas fa-question-circle icon-sm"></i> Need Help?</h4>
            <p>Ask our AI assistant anything about your studies!</p>
            <a href="/ai-chat" style="text-decoration: none;"><button><i class="fas fa-robot icon-sm"></i> Start Chat</button></a>
        </div>
        <?php endif; ?>
    </aside>

    <!-- OVERLAY for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- MAIN CONTENT -->
    <main class="content">

        <!-- TOP NAV BAR (Mobile Hamburger) -->
        <nav class="top-nav">
            <div class="top-nav-left">
                <button class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="top-logo">
                    <i class="fas fa-graduation-cap"></i> StudyBuddie
                </div>
            </div>
            <div class="top-nav-right">
                <?php if (isLoggedIn()): ?>
                    <span class="user-greeting"><?php echo htmlspecialchars(getCurrentUser()['username']); ?></span>
                    <a href="/logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <span class="user-greeting">Welcome</span>
                <?php endif; ?>
            </div>
        </nav>

        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
