<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin - StudySmart'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Hide ALL regular app layout elements - aggressive reset */
        .app-container,
        .app-container > .sidebar,
        .app-container > .content,
        .app-container > .top-nav,
        .sidebar,
        .sidebar-overlay,
        .top-nav,
        body > nav,
        body > aside,
        body > main,
        .alert {
            display: none !important;
            visibility: hidden !important;
            position: absolute !important;
            left: -9999px !important;
        }

        /* Reset body margin/padding for admin layout */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
            position: relative;
            overflow-x: hidden;
        }
        .admin-sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 9999;
            left: 0;
            top: 0;
            transition: transform 0.3s ease;
        }
        .hamburger-menu {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 10000;
            background: #1e293b;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
            transition: background 0.2s;
        }
        .hamburger-menu:hover {
            background: #2d3f54;
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
        }
        .sidebar-overlay.active {
            display: block;
        }
        .admin-sidebar .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 0;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .admin-sidebar .logo i {
            color: #fbbf24;
        }
        .admin-sidebar nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.2s;
        }
        .admin-sidebar nav a:hover,
        .admin-sidebar nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .admin-sidebar nav a i {
            width: 20px;
            text-align: center;
        }
        .admin-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
            background: #f1f5f9;
            min-height: 100vh;
            overflow-x: hidden;
            width: 100%;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }
        .stat-card .icon.blue { background: #dbeafe; color: #2563eb; }
        .stat-card .icon.green { background: #dcfce7; color: #16a34a; }
        .stat-card .icon.purple { background: #f3e8ff; color: #9333ea; }
        .stat-card .icon.orange { background: #ffedd5; color: #ea580c; }
        .stat-card .icon.pink { background: #fce7f3; color: #db2777; }
        .stat-card .icon.yellow { background: #fef3c7; color: #d97706; }
        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
        }
        .stat-card .label {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }
        .stat-card .change {
            font-size: 13px;
            margin-top: 8px;
        }
        .stat-card .change.positive { color: #16a34a; }
        .stat-card .change.negative { color: #dc2626; }
        .admin-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        .admin-section h3 {
            margin-bottom: 20px;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th,
        .data-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .data-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .data-table tr:hover {
            background: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge.free { background: #f3f4f6; color: #374151; }
        .badge.basic { background: #dbeafe; color: #1d4ed8; }
        .badge.premium { background: #fef3c7; color: #92400e; }
        .badge.active { background: #dcfce7; color: #16a34a; }
        .badge.inactive { background: #f3f4f6; color: #6b7280; }
        .badge.cancelled { background: #fee2e2; color: #dc2626; }
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-sm-primary { background: #2563eb; color: white; }
        .btn-sm-danger { background: #dc2626; color: white; }
        .btn-sm-warning { background: #f59e0b; color: white; }
        .chart-container {
            display: flex;
            gap: 20px;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 10px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
            position: relative;
        }
        .chart-container::-webkit-scrollbar {
            height: 8px;
        }
        .chart-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .chart-container::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
        }
        .chart-container::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        .chart-box {
            flex: 0 0 250px;
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
        }
        .progress-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 8px;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s;
        }

        /* Table wrapper for responsive scrolling */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 15px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        .table-responsive .data-table {
            min-width: 700px;
        }

        /* Hide mobile close button on desktop by default */
        .mobile-close-btn {
            display: none;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .hamburger-menu {
                display: block;
            }
            .mobile-close-btn {
                display: block !important;
            }
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-sidebar.active {
                transform: translateX(0);
            }
            .admin-content {
                margin-left: 0;
                padding: 70px 15px 15px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .stat-card {
                padding: 20px;
            }
            .stat-card .value {
                font-size: 28px;
            }
            .admin-section {
                padding: 20px;
            }
            .admin-section h3 {
                font-size: 18px;
                margin-bottom: 15px;
            }
            .chart-container {
                flex-direction: column;
                gap: 15px;
                overflow-x: visible;
                padding-bottom: 0;
            }
            .chart-box {
                width: 100%;
                padding: 15px;
                box-sizing: border-box;
            }
            .progress-bar {
                height: 10px;
            }
            .table-responsive {
                margin: 0 -10px;
                padding: 0 10px;
                overflow-x: scroll;
            }
            .table-responsive .data-table {
                font-size: 13px;
            }
            .table-responsive .data-table th,
            .table-responsive .data-table td {
                padding: 10px 6px;
            }
            .btn-sm {
                padding: 5px 10px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .stat-card .value {
                font-size: 24px;
            }
            .stat-card .icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
            .admin-content {
                padding: 60px 10px 10px;
            }
            .hamburger-menu {
                top: 10px;
                left: 10px;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>

<!-- Hamburger Menu Button (Mobile) -->
<button class="hamburger-menu" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="admin-layout">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div class="logo" style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;">
                <i class="fas fa-shield-halved"></i> Admin Panel
            </div>
            <button onclick="toggleSidebar()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 5px;" class="mobile-close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav>
            <a href="/admin" class="<?php echo ($currentPage ?? '') === 'admin-dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="/admin/users" class="<?php echo ($currentPage ?? '') === 'admin-users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="/admin/subscriptions" class="<?php echo ($currentPage ?? '') === 'admin-subscriptions' ? 'active' : ''; ?>">
                <i class="fas fa-crown"></i> Subscriptions
            </a>
            <a href="/admin/scripts" class="<?php echo ($currentPage ?? '') === 'admin-scripts' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> Scripts
            </a>
            <a href="/admin/report-cards" class="<?php echo ($currentPage ?? '') === 'admin-report-cards' ? 'active' : ''; ?>">
                <i class="fas fa-file-upload"></i> Report Cards
            </a>
            <a href="/admin/topics" class="<?php echo ($currentPage ?? '') === 'admin-topics' ? 'active' : ''; ?>">
                <i class="fas fa-brain"></i> Topics Mastered
            </a>
            <a href="/admin/seo/pages" class="<?php echo ($currentPage ?? '') === 'admin-seo' ? 'active' : ''; ?>">
                <i class="fas fa-search"></i> SEO Pages
            </a>
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 11px; text-transform: uppercase; color: rgba(255,255,255,0.5); margin-bottom: 10px; padding: 0 16px;">Settings</div>
                <a href="/admin/openai-settings" class="<?php echo ($currentPage ?? '') === 'admin-openai' ? 'active' : ''; ?>">
                    <i class="fas fa-robot"></i> OpenAI Settings
                </a>
                <a href="/admin/banking-settings" class="<?php echo ($currentPage ?? '') === 'admin-banking' ? 'active' : ''; ?>">
                    <i class="fas fa-university"></i> Banking Settings
                </a>
            </div>
            <a href="/" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <i class="fas fa-arrow-left"></i> Back to Site
            </a>
        </nav>
    </aside>

    <!-- Admin Content -->
    <main class="admin-content">
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" style="padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; background: <?php echo $flash['type'] === 'success' ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $flash['type'] === 'success' ? '#16a34a' : '#dc2626'; ?>;">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
