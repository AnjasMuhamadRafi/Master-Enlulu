<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Enlulu Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --enlulu-orange: #FF6B35;
            --enlulu-dark: #1a1a1a;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--enlulu-orange) 0%, #ff8c4a 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding-right: 2px;
            margin-right: -2px;
        }
        
        .sidebar.collapsed .sidebar-content {
            padding: 0px;
            margin: 0px;
        }
        
        .sidebar-footer {
            flex-shrink: 0;
            border-top: 2px solid rgba(255,255,255,0.2);
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .logout-link {
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .logout-link:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .logout-link i {
            font-size: 18px;
            width: 25px;
            text-align: center;
            flex-shrink: 0;
            order: 1;
        }
        
        .logout-link .menu-text {
            margin-left: 0;
            order: 2;
        }
        
        .sidebar.collapsed .logout-link .menu-text {
            display: none;
        }
        
        /* Label untuk collapsed state */
        .sidebar.collapsed .logout-link {
            justify-content: center;
            padding: 8px 2px;
            flex-direction: column;
            gap: 3px;
            height: auto;
            min-height: 60px;
        }
        
        .sidebar.collapsed .logout-link::before {
            content: attr(data-label);
            font-size: 10px;
            text-align: center;
            line-height: 1.2;
            word-spacing: 9999px;
            max-width: 42px;
            order: 3;
            min-height: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
            padding: 15px 0px;
            align-items: center;
        }
        
        .sidebar-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255,255,255,0.2);
        }
        
        .sidebar.collapsed .sidebar-brand-text {
            display: none;
        }
        
        .sidebar-brand-logo {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .sidebar.collapsed .sidebar-brand-logo {
            width: 40px;
        }
        
        .sidebar-brand-text {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
        }
        
        .sidebar.collapsed .sidebar-brand {
            padding: 10px 5px;
            margin-bottom: 8px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .sidebar-menu a i {
            font-size: 18px;
            width: 25px;
            text-align: center;
            flex-shrink: 0;
            order: 1;
        }
        
        .sidebar.collapsed .sidebar-menu a i {
            font-size: 20px;
            width: 25px;
            text-align: center;
        }
        
        .sidebar-menu .menu-text {
            margin-left: 0;
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            order: 2;
            flex: 1;
            word-wrap: break-word;
            min-width: 0;
        }
        
        .sidebar.collapsed .menu-text {
            display: none;
        }
        
        /* Label untuk collapsed state */
        .sidebar.collapsed .sidebar-menu a {
            justify-content: center;
            padding: 8px 2px;
            flex-direction: column;
            gap: 3px;
            height: auto;
            min-height: 60px;
        }
        
        .sidebar.collapsed .sidebar-menu a::before {
            content: attr(data-label);
            font-size: 10px;
            text-align: center;
            line-height: 1.2;
            word-spacing: 9999px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            max-width: 42px;
            order: 3;
            min-height: 20px;
        }
        
        /* Chevron styling untuk menu toggle */
        .sidebar-menu .menu-toggle i:last-child {
            order: 4;
            margin-left: auto;
            flex-shrink: 0;
        }
        
        .sidebar.collapsed .menu-toggle i:last-child {
            display: none;
        }
        
        /* Submenu */
        .sidebar-menu li {
            margin-bottom: 10px;
            position: relative;
        }
        
        .sidebar.collapsed .sidebar-menu li {
            margin-bottom: 2px;
        }
        
        .menu-divider {
            margin: 10px 5px !important;
            padding: 0 !important;
        }
        
        .sidebar.collapsed .menu-divider {
            margin: 5px 5px !important;
        }
        
        .sidebar.collapsed .sidebar-menu li {
            margin-bottom: 2px;
        }
        
        /* Handle divider line dalam collapsed state */
        .sidebar.collapsed .sidebar-menu li:has(> [style*="border-top"]) {
            margin: 5px 0;
            padding: 0 5px;
        }
        
        .sidebar-menu .submenu {
            display: none;
            list-style: none;
            padding-left: 0;
            margin-top: 5px;
        }
        
        .sidebar-menu .submenu.show {
            display: block;
        }
        
        .sidebar-menu .submenu a {
            padding-left: 50px;
            font-size: 14px;
            background: rgba(255,255,255,0.1);
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .sidebar.collapsed .sidebar-menu .submenu {
            display: none !important;
        }
        
        .sidebar.collapsed .menu-toggle i:last-child {
            display: none !important;
        }
        
        /* Menu toggle chevron positioning */
        .menu-toggle {
            align-items: flex-start !important;
        }
        
        .menu-toggle .menu-text {
            padding-top: 2px;
        }
        
        .submenu-chevron {
            margin-left: auto !important;
            font-size: 12px !important;
            flex-shrink: 0;
            transition: transform 0.3s ease;
            transform: rotate(0deg);
        }
        
        .sidebar.collapsed .submenu-chevron {
            display: none !important;
        }
        
        .content {
            margin-left: var(--sidebar-width);
            flex: 1;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }
        
        .content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        @media (max-width: 1024px) {
            .content.expanded {
                margin-left: 65px;
            }
            
            .sidebar.collapsed {
                width: 65px;
                padding: 15px 5px;
            }
        }
        
        @media (max-width: 992px) {
            .content {
                margin-left: 0;
            }
            
            .content.expanded {
                margin-left: 0;
            }
        }
        
        .topbar {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--enlulu-orange);
            transition: all 0.3s ease;
        }
        
        .toggle-sidebar:hover {
            color: #ff8c4a;
        }
        
        .topbar-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--enlulu-dark);
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--enlulu-orange);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--enlulu-dark);
            font-size: 14px;
        }
        
        .user-role {
            font-size: 12px;
            color: #999;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0 0 20px 0;
            margin: 0;
        }
        
        .breadcrumb-item.active {
            color: var(--enlulu-orange);
            font-weight: 600;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 2px solid #f5f5f5;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        
        .btn-primary {
            background: var(--enlulu-orange);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #ff8c4a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }
        
        @media (max-width: 1024px) {
            :root {
                --sidebar-width: 200px;
                --sidebar-collapsed-width: 65px;
            }
            
            .sidebar {
                width: var(--sidebar-width);
                padding: 18px;
            }
            
            .sidebar.collapsed {
                width: var(--sidebar-collapsed-width);
                padding: 15px 0px;
                align-items: center;
            }
            
            .sidebar-brand {
                padding: 0px;
                margin-bottom: 20px;
                border-bottom: none;
            }
            
            .sidebar.collapsed .sidebar-brand {
                border-bottom: 1px solid rgba(255,255,255,0.2);
                padding: 10px 5px;
                margin-bottom: 8px;
            }
            
            .sidebar-brand-logo {
                width: 85px;
            }
            
            .sidebar.collapsed .sidebar-brand-logo {
                width: 35px;
            }
            
            .sidebar-brand-text {
                font-size: 14px;
            }
            
            .sidebar-menu li {
                margin-bottom: 8px;
            }
            
            .sidebar.collapsed .sidebar-menu li {
                margin-bottom: 2px;
            }
            
            .sidebar-menu a {
                padding: 10px 12px;
                font-size: 13px;
            }
            
            .sidebar.collapsed .sidebar-menu a {
                padding: 8px 2px;
                font-size: 13px;
                height: auto;
                min-height: 60px;
            }
            
            .sidebar-menu a i {
                font-size: 16px;
                width: 22px;
            }
            
            .sidebar.collapsed .sidebar-menu a i {
                font-size: 18px;
                width: 25px;
            }
            
            .user-name {
                font-size: 13px;
            }
            
            .user-role {
                font-size: 11px;
            }
            
            .sidebar-footer {
                margin-top: 10px;
                padding: 10px 0px;
                border-top: 1px solid rgba(255,255,255,0.2);
            }
            
            .logout-link {
                padding: 8px 12px;
            }
            
            .sidebar.collapsed .logout-link {
                padding: 8px 2px;
                min-height: 60px;
            }
            
            .sidebar.collapsed .sidebar-menu a {
                padding: 8px 2px;
                min-height: 60px;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                z-index: 1050;
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                transform: translateX(0);
            }
            
            .sidebar.mobile-hidden {
                transform: translateX(-100%);
            }
            
            .content {
                margin-left: 0 !important;
            }
            
            .topbar {
                z-index: 1000;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 1049;
                transition: opacity 0.3s ease;
                opacity: 0;
            }
            
            .sidebar-overlay.show {
                display: block;
                opacity: 1;
            }
            
            .topbar-left {
                gap: 12px;
            }
            
            .topbar-title {
                font-size: 16px;
            }
            
            .toggle-sidebar {
                font-size: 22px;
            }
            
            :root {
                --sidebar-width: 250px;
                --sidebar-collapsed-width: 70px;
            }
        }
        
        @media (max-width: 768px) {
            :root {
                --sidebar-width: 240px;
            }
            
            .topbar {
                padding: 14px 18px;
            }
            
            .topbar-title {
                font-size: 15px;
            }
            
            .toggle-sidebar {
                font-size: 20px;
            }
            
            .user-info {
                display: none;
            }
            
            .user-avatar {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }
            
            .topbar-right {
                gap: 10px;
            }
            
            .main-content {
                padding: 18px;
            }
            
            .sidebar-brand-logo {
                width: 75px;
            }
            
            .sidebar-menu a {
                padding: 10px 12px;
                font-size: 13px;
            }
            
            .logout-link {
                padding: 10px 12px;
                font-size: 13px;
            }
            
            .card {
                margin-bottom: 16px;
            }
            
            .card-header {
                padding: 16px;
            }
        }
        
        @media (max-width: 576px) {
            :root {
                --sidebar-width: 90vw;
            }
            
            .sidebar {
                max-width: 250px;
            }
            
            .sidebar-brand-logo {
                width: 70px;
            }
            
            .topbar {
                padding: 12px 14px;
            }
            
            .topbar-title {
                font-size: 13px;
            }
            
            .main-content {
                padding: 14px;
            }
            
            .toggle-sidebar {
                font-size: 18px;
            }
            
            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }
            
            .sidebar-menu a {
                padding: 9px 11px;
                font-size: 12px;
            }
            
            .sidebar-menu a i {
                font-size: 15px;
                width: 20px;
            }
            
            .logout-link {
                padding: 9px 11px;
                font-size: 12px;
            }
            
            .logout-link i {
                font-size: 15px;
                width: 20px;
            }
        }
        
        /* Dashboard Responsive Cards */
        @media (max-width: 1024px) {
            .row .col-md-3 {
                flex: 0 0 50%;
                max-width: 50%;
            }
            
            .row .col-md-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
        
        @media (max-width: 768px) {
            .row .col-md-3,
            .row .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .row .col-md-12 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            h2 {
                font-size: 20px !important;
                margin-bottom: 20px !important;
            }
        }
        
        @media (max-width: 576px) {
            h2 {
                font-size: 16px !important;
                margin-bottom: 16px !important;
            }
            
            .card-body {
                padding: 12px !important;
            }
            
            .card {
                margin-bottom: 12px !important;
            }
        }
        
        /* Scrollbar custom */
        .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-content::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar-content::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        
        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }
    </style>
    
    @yield('css')
</head>
<body>
    <div class="main-wrapper">
        <!-- Sidebar Overlay Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        @include('components.sidebar')
        
        <!-- Content Area -->
        <div class="content" id="mainContent">
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="toggle-sidebar" id="toggleSidebar" onclick="toggleSidebarMobile()">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
                </div>
                
                <div class="topbar-right">
                    <div class="user-profile">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="user-info"> 
                            <span class="user-name">{{ auth()->user()->name ?? 'User' }}</span>
                            <span class="user-role">{{ auth()->user()->role ?? 'Admin' }}</span>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                @if (!is_array($errors) && $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi Kesalahan!</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @elseif (is_array($errors) && count($errors) > 0)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi Kesalahan!</strong>
                        <ul class="mb-0">
                            @foreach ($errors as $error)
                                <li>{{ is_array($error) ? implode(', ', $error) : $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        // Detect if mobile/desktop
        const isMobile = () => window.innerWidth <= 992;
        
        // Toggle Sidebar Click Handler
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            if (isMobile()) {
                // Mobile: toggle open/close
                sidebar.classList.toggle('mobile-hidden');
                overlay.classList.toggle('show');
            } else {
                // Desktop: toggle collapse/expand
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            }
        });
        
        // Close sidebar on overlay click (mobile)
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.add('mobile-hidden');
                overlay.classList.remove('show');
            });
        }
        
        // Close sidebar when clicking menu items (mobile)
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function() {
                if (isMobile() && !this.classList.contains('menu-toggle')) {
                    sidebar.classList.add('mobile-hidden');
                    overlay.classList.remove('show');
                }
            });
        });
        
        // Close sidebar when clicking logout (mobile)
        document.querySelectorAll('.logout-link').forEach(link => {
            link.addEventListener('click', function() {
                if (isMobile()) {
                    sidebar.classList.add('mobile-hidden');
                    overlay.classList.remove('show');
                }
            });
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (!isMobile()) {
                // Switch to desktop: remove mobile classes
                sidebar.classList.remove('mobile-hidden');
                overlay.classList.remove('show');
            } else {
                // Switch to mobile: remove desktop classes
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
        });
        
        // Load sidebar preference on page load (desktop only)
        window.addEventListener('load', function() {
            if (!isMobile()) {
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }
            } else {
                // Mobile: always start with sidebar closed
                sidebar.classList.add('mobile-hidden');
            }
        });
    </script>
    
    @yield('js')
</body>
</html>
