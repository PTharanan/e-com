<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Admin Panel</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --admin-primary: #F25C3B;
            --admin-primary-hover: #E04A2A;
            --admin-bg: #F8F9FA;
            --admin-dark: #1A1A1A;
            --admin-dark-accent: #2D2D2D;
            --admin-text-gray: #A0AEC0;
            --admin-white: #FFFFFF;
            --admin-border-dark: rgba(255, 255, 255, 0.08);
            --sidebar-width-expanded: 280px;
            --sidebar-width-collapsed: 85px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--admin-bg);
            color: var(--admin-dark);
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width-expanded);
            background: var(--admin-dark);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            z-index: 1000;
            color: var(--admin-white);
        }

        .sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }

        /* Header Section */
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 85px;
            border-bottom: 1px solid var(--admin-border-dark);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
            white-space: nowrap;
            transition: var(--transition);
            text-decoration: none;
            font-size: 24px;
            font-weight: 800;
            color: var(--admin-white);
        }

        .logo-area span {
            color: var(--admin-primary);
        }

        .sidebar.collapsed .logo-area {
            opacity: 0;
            width: 0;
            pointer-events: none;
        }

        .toggle-btn {
            background: var(--admin-dark-accent);
            border: none;
            cursor: pointer;
            color: var(--admin-white);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: 8px;
            transition: var(--transition);
        }

        .sidebar.collapsed .toggle-btn {
            margin: 0 auto;
        }

        .toggle-btn:hover {
            background: var(--admin-primary);
        }

        /* User Profile Section */
        .user-profile {
            padding: 25px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: var(--transition);
            border-bottom: 1px solid var(--admin-border-dark);
            margin-bottom: 10px;
        }

        .avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--admin-primary), #FF8E75);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(242, 92, 59, 0.3);
        }

        .user-info {
            overflow: hidden;
            transition: var(--transition);
            flex: 1;
        }

        .user-info p {
            font-size: 11px;
            color: var(--admin-text-gray);
            margin: 0;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .user-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--admin-white);
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sidebar.collapsed .user-profile {
            justify-content: center;
            padding: 25px 10px;
        }

        .sidebar.collapsed .user-info {
            width: 0;
            opacity: 0;
            display: none;
        }

        /* Navigation Menu */
        .nav-menu {
            list-style: none;
            padding: 10px 15px;
            flex: 1;
        }

        .sidebar.collapsed .nav-menu {
            padding: 10px 10px;
        }

        .nav-item {
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            color: var(--admin-text-gray);
            text-decoration: none;
            border-radius: 12px;
            transition: var(--transition);
            font-weight: 500;
            white-space: nowrap;
            gap: 15px;
        }

        .nav-link svg {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            transition: var(--transition);
        }

        .nav-link span {
            transition: var(--transition);
            opacity: 1;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 14px 0;
        }

        .nav-link:hover {
            color: var(--admin-white);
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-link.active {
            background: rgba(242, 92, 59, 0.15);
            color: var(--admin-primary);
            font-weight: 600;
        }

        /* Bottom Section */
        .sidebar-footer {
            padding: 20px 15px;
            border-top: 1px solid var(--admin-border-dark);
        }

        .sidebar.collapsed .sidebar-footer {
            padding: 20px 10px;
        }

        .logout-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 18px;
            color: #FF5C5C;
            text-decoration: none;
            border-radius: 12px;
            transition: var(--transition);
            font-weight: 600;
            background: none;
            border: none;
            width: 100%;
            cursor: pointer;
            font-family: inherit;
            font-size: 15px;
        }

        .logout-link svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .logout-link:hover {
            background: rgba(255, 92, 92, 0.1);
        }

        .sidebar.collapsed .logout-link {
            justify-content: center;
            padding: 14px 0;
        }

        .sidebar.collapsed .logout-link span {
            display: none;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width-expanded);
            padding: 40px;
            transition: var(--transition);
        }

        .sidebar.collapsed+.main-content {
            margin-left: var(--sidebar-width-collapsed);
        }

        /* Dropdown Menu Styling */
        .dropdown-menu {
            list-style: none;
            padding-left: 0;
            max-height: 0;
            overflow: hidden;
            transition: var(--transition);
            opacity: 0;
            background: rgba(0, 0, 0, 0.2);
            margin: 0 10px;
            border-radius: 12px;
        }

        .dropdown-menu.active {
            max-height: 200px;
            opacity: 1;
            padding: 8px 0;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .dropdown-item {
            margin: 0 5px;
        }

        .dropdown-link {
            display: flex;
            align-items: center;
            padding: 10px 15px 10px 45px;
            color: var(--admin-text-gray);
            text-decoration: none;
            border-radius: 10px;
            transition: var(--transition);
            font-size: 13px;
            font-weight: 500;
        }

        .dropdown-link:hover {
            color: var(--admin-white);
            background: rgba(255, 255, 255, 0.05);
        }

        .dropdown-link.active {
            color: var(--admin-primary);
            background: rgba(242, 92, 59, 0.05);
        }

        /* Dropdown Arrow */
        .dropdown-arrow {
            margin-left: auto;
            width: 16px;
            height: 16px;
            transition: var(--transition);
            opacity: 0.7;
        }

        .nav-link.dropdown-open .dropdown-arrow {
            transform: rotate(180deg);
        }

        .sidebar.collapsed .dropdown-menu {
            display: none;
        }

        .sidebar.collapsed .dropdown-arrow {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -100%;
            }

            .sidebar.mobile-active {
                left: 0;
                width: var(--sidebar-width-expanded);
            }

            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
    @yield('styles')
</head>

<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="logo-area">
                E-<span>Shop</span>
            </a>
            <button class="toggle-btn" id="sidebar-toggle">
                <svg id="toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </button>
        </div>

        <div class="user-profile">
            <div class="avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
            <div class="user-info">
                <p>Admin</p>
                <h4>{{ Auth::user()->name }}</h4>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.products') }}"
                    class="nav-link {{ request()->routeIs('admin.products') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span>Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.orders') }}"
                    class="nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                    <span>Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.customers') }}"
                    class="nav-link {{ request()->routeIs('admin.customers') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>Customers</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)"
                    class="nav-link {{ request()->routeIs('admin.settings') || request()->routeIs('admin.categories') || request()->routeIs('admin.currency') ? 'active' : '' }}"
                    id="settings-toggle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path
                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1V11a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-1.82-.33l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                        </path>
                    </svg>
                    <span>Settings</span>
                    <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <ul class="dropdown-menu"
                    id="settings-dropdown">
                    <li class="dropdown-item">
                        <a href="{{ route('admin.categories') }}"
                            class="dropdown-link {{ request()->routeIs('admin.categories') ? 'active' : '' }}">Categories</a>
                    </li>
                    <li class="dropdown-item">
                        <a href="{{ route('admin.currency') }}"
                            class="dropdown-link {{ request()->routeIs('admin.currency') ? 'active' : '' }}">Currency</a>
                    </li>
                    <li class="dropdown-item">
                        <a href="{{ route('admin.settings') }}"
                            class="dropdown-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">General
                            Settings</a>
                    </li>
                </ul>
            </li>
        </ul>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST" id="admin-logout-form">
                @csrf
                <button type="button" class="logout-link" onclick="openLogoutModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        @yield('content')
    </main>

    <!-- Logout Confirmation Modal -->
    <div class="logout-modal-overlay" id="logoutModal">
        <div class="logout-modal">
            <div class="modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </div>
            <h3>Sign Out</h3>
            <p>Are you sure you want to sign out? You will need to sign-in again to access the admin panel.</p>
            <div class="modal-actions">
                <button class="modal-btn btn-cancel" onclick="closeLogoutModal()">Cancel</button>
                <button class="modal-btn btn-confirm" onclick="confirmLogout()">Yes, Sign Out</button>
            </div>
        </div>
    </div>

    <style>
        .logout-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }

        .logout-modal-overlay.active {
            display: flex;
        }

        .logout-modal {
            background: white;
            padding: 30px;
            border-radius: 24px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            animation: modalSlideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes modalSlideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-icon {
            width: 60px;
            height: 60px;
            background: #FEF2F2;
            color: #EF4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .modal-icon svg {
            width: 30px;
            height: 30px;
        }

        .logout-modal h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .logout-modal p {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .modal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .modal-btn {
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }

        .btn-cancel {
            background: #F3F4F6;
            color: #374151;
        }

        .btn-cancel:hover {
            background: #E5E7EB;
        }

        .btn-confirm {
            background: #EF4444;
            color: white;
        }

        .btn-confirm:hover {
            background: #DC2626;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }
    </style>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');
        const toggleIcon = document.getElementById('toggle-icon');
        const logoutModal = document.getElementById('logoutModal');
        const logoutForm = document.getElementById('admin-logout-form');

        const updateToggleIcon = (isCollapsed) => {
            if (isCollapsed) {
                toggleIcon.innerHTML = '<path d="M9 18l6-6-6-6"/>'; // Right arrow
            } else {
                toggleIcon.innerHTML = '<path d="M15 18l-6-6 6-6"/>'; // Left arrow
            }
        };

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            updateToggleIcon(isCollapsed);
            localStorage.setItem('admin_sidebar_collapsed', isCollapsed);
        });

        // Restore state on load
        const savedState = localStorage.getItem('admin_sidebar_collapsed');
        if (savedState !== null) {
            const isCollapsed = savedState === 'true';
            if (isCollapsed) sidebar.classList.add('collapsed');
            else sidebar.classList.remove('collapsed');
            updateToggleIcon(isCollapsed);
        } else {
            sidebar.classList.remove('collapsed');
            updateToggleIcon(false);
        }

        // Logout Flow
        const openLogoutModal = () => {
            logoutModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        const closeLogoutModal = () => {
            logoutModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        };

        const confirmLogout = () => {
            document.getElementById('admin-logout-form').submit();
        };

        // Close modal when clicking outside
        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) closeLogoutModal();
        });

        // Dropdown Toggle Logic
        const settingsToggle = document.getElementById('settings-toggle');
        const settingsDropdown = document.getElementById('settings-dropdown');

        settingsToggle.addEventListener('click', (e) => {
            e.preventDefault();

            // If sidebar is collapsed, expand it first
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                // We don't save this state to localStorage so it returns to collapsed on the next page load
                updateToggleIcon(false);
            }

            settingsDropdown.classList.toggle('active');
            settingsToggle.classList.toggle('dropdown-open');
        });
    </script>

    @yield('scripts')
</body>

</html>