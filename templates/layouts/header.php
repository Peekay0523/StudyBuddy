<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'StudySmart - AI Learning Assistant'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php echo $extraHead ?? ''; ?>
</head>
<body>

<div class="app-container">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-graduation-cap icon-lg"></i> <span>StudySmart</span>
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
                    <!-- Regular users see full navigation -->
                    <a href="/dashboard" class="<?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-chart-line icon-sm"></i> Dashboard</a>
                    <a href="/upload-script" class="<?php echo ($currentPage ?? '') === 'scripts' ? 'active' : ''; ?>"><i class="fas fa-file-alt icon-sm"></i> Scripts</a>
                    <a href="/study-plan" class="<?php echo ($currentPage ?? '') === 'study-plan' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt icon-sm"></i> Study Plan</a>
                    <a href="/study-group" class="<?php echo ($currentPage ?? '') === 'study-group' ? 'active' : ''; ?>"><i class="fas fa-users icon-sm"></i> Study Group</a>
                    <a href="/upload-report-card" class="<?php echo ($currentPage ?? '') === 'careers' ? 'active' : ''; ?>"><i class="fas fa-bullseye icon-sm"></i> Careers</a>
                    <a href="/ai-chat" class="<?php echo ($currentPage ?? '') === 'ai-chat' ? 'active' : ''; ?>"><i class="fas fa-comments icon-sm"></i> AI Chat</a>
                    <a href="/subscription" class="<?php echo ($currentPage ?? '') === 'subscription' ? 'active' : ''; ?>"><i class="fas fa-crown icon-sm"></i> Subscription</a>
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
                    <i class="fas fa-graduation-cap"></i> StudySmart
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
