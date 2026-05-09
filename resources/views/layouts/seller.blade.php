<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Seller Panel</title>

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

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(246, 48, 3, 0.5);
            border-radius: 20px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--admin-primary-hover);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--admin-primary);
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
            max-height: 100px;
            opacity: 1;
            padding: 8px 0;
            margin-top: 5px;
            margin-bottom: 10px;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .dropdown-menu::-webkit-scrollbar {
            width: 4px;
        }

        .dropdown-menu::-webkit-scrollbar-track {
            background: transparent;
        }

        .dropdown-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
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

        /* Admin Topbar */
        .admin-topbar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 30px;
        }

        .notification-dropdown {
            position: relative;
        }

        .bell-btn {
            background: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            position: relative;
            color: var(--admin-text-gray);
            transition: var(--transition);
        }

        .bell-btn:hover {
            color: var(--admin-primary);
        }

        .bell-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #EF4444;
            color: white;
            font-size: 10px;
            font-weight: bold;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--admin-bg);
        }

        .bell-menu {
            position: absolute;
            top: 120%;
            right: 0;
            width: 320px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition);
            z-index: 1000;
            overflow: hidden;
        }

        .bell-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .bell-menu-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bell-menu-header h4 {
            font-size: 14px;
            color: var(--admin-dark);
            margin: 0;
        }

        .bell-menu-header span {
            background: rgba(242, 92, 59, 0.1);
            color: var(--admin-primary);
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 20px;
        }

        .bell-menu-body {
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 15px 20px;
            text-decoration: none;
            border-bottom: 1px solid #f9f9f9;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            max-height: 200px;
        }

        .notification-item:hover {
            background: #fdfdfd;
        }

        .notif-icon-wrapper {
            position: relative;
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
            -webkit-user-drag: none;
        }

        .notif-icon {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            background: #E8F5E9;
            color: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }

        .notif-icon-wrapper:active .notif-icon {
            transform: scale(0.9);
        }

        .progress-ring {
            position: absolute;
            top: -2px;
            left: -2px;
            width: 40px;
            height: 40px;
            transform: rotate(-90deg);
            pointer-events: none;
        }

        .progress-ring__circle {
            stroke: var(--admin-primary);
            stroke-width: 2.5;
            fill: transparent;
            stroke-dasharray: 113.1;
            /* 2 * PI * 18 */
            stroke-dashoffset: 113.1;
            transition: none;
        }

        .notif-icon-wrapper.pressing .progress-ring__circle {
            transition: stroke-dashoffset 3s linear;
            /* Hold for 3 seconds */
            stroke-dashoffset: 0;
        }

        .notif-icon .icon-close {
            display: none !important;
        }

        .notif-icon-wrapper.pressing .notif-icon .icon-arrow {
            display: none !important;
        }

        .notif-icon-wrapper.pressing .notif-icon .icon-close {
            display: block !important;
        }

        .notif-content p {
            margin: 0 0 5px 0;
            font-size: 13px;
            color: var(--admin-dark);
            line-height: 1.4;
        }

        .notif-content span {
            font-size: 11px;
            color: var(--admin-text-gray);
        }

        .no-notif {
            padding: 30px;
            text-align: center;
            color: var(--admin-text-gray);
            font-size: 13px;
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
            <a href="{{ route('seller.dashboard') }}" class="logo-area">
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
                <p>Seller</p>
                <h4>{{ Auth::user()->name }}</h4>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('seller.dashboard') }}" title="Dashboard"
                    class="nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('seller.products') }}" title="Products"
                    class="nav-link {{ request()->routeIs('seller.products') ? 'active' : '' }}">
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
                <a href="{{ route('seller.orders') }}" title="Orders"
                    class="nav-link {{ request()->routeIs('seller.orders') ? 'active' : '' }}">
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
                <a href="{{ route('seller.customers') }}" title="Customers"
                    class="nav-link {{ request()->routeIs('seller.customers') ? 'active' : '' }}">
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
                <a href="{{ route('seller.delivery') }}" title="Delivery Partners"
                    class="nav-link {{ request()->routeIs('seller.delivery') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                    <span>Delivery Partners</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)" title="Settings"
                    class="nav-link {{ request()->routeIs('seller.settings') || request()->routeIs('seller.settings.auto-delete') ? 'active' : '' }}"
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
                <ul class="dropdown-menu" id="settings-dropdown">
                    <li class="dropdown-item">
                        <a href="{{ route('seller.settings.auto-delete') }}"
                            class="dropdown-link {{ request()->routeIs('seller.settings.auto-delete') ? 'active' : '' }}">Auto
                            Delete Data</a>
                    </li>
                    <li class="dropdown-item">
                        <a href="{{ route('seller.settings') }}"
                            class="dropdown-link {{ request()->routeIs('seller.settings') ? 'active' : '' }}">General
                            Settings</a>
                    </li>
                </ul>
            </li>

        </ul>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST" id="seller-logout-form">
                @csrf
                <button type="button" class="logout-link" onclick="openLogoutModal()" title="Sign Out">
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
        <!-- Top Navbar -->
        <div class="admin-topbar">
            <div class="topbar-right">
                @php
                    $cancelledCount = 0;
                    $cancelledOrders = collect();
                    $newOrdersCount = 0;
                    $newOrders = collect();
                    $deliveryNotifCount = 0;
                    $deliveryNotifications = collect();

                    if (Auth::check()) {
                        $sellerId = Auth::id();
                        $user = Auth::user();

                        $sellerOrdersQuery = \App\Models\Order::with('user')
                            ->whereJsonContains('items_json', ['seller_id' => (int) $sellerId]);

                        if ($user->admin_id) {
                            $sellerOrdersQuery->where('admin_id', $user->admin_id);
                        }

                        // Fetch cancelled orders
                        $cancelledOrders = (clone $sellerOrdersQuery)->where('status', 'cancelled')->orderBy('updated_at', 'desc')->take(5)->get();
                        $cancelledCount = (clone $sellerOrdersQuery)->where('status', 'cancelled')->count();

                        // Fetch recent paid orders
                        $newOrders = (clone $sellerOrdersQuery)->where('status', 'completed')->orderBy('created_at', 'desc')->take(5)->get();
                        $newOrdersCount = (clone $sellerOrdersQuery)->where('status', 'completed')->count();

                        // Fetch unread delivery applications
                        $deliveryNotifications = $user->unreadNotifications()->where('type', 'like', '%DeliveryApplicationNotification')->take(5)->get();
                        $deliveryNotifCount = $user->unreadNotifications()->where('type', 'like', '%DeliveryApplicationNotification')->count();

                        // Fetch unread stock notifications
                        $stockNotifications = $user->unreadNotifications()->where('type', 'like', '%ProductOutOfStockNotification')->take(5)->get();
                        $stockNotifCount = $user->unreadNotifications()->where('type', 'like', '%ProductOutOfStockNotification')->count();
                    }
                @endphp
                <div style="display: flex; gap: 15px;">
                    {{-- Cancellation Notification --}}
                    <div class="notification-dropdown">
                        <button class="bell-btn" id="cancelNotifToggle" title="Cancelled Orders">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#EF4444"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            <span class="bell-badge" id="cancelBadge"
                                style="background: #EF4444; {{ $cancelledCount > 0 ? '' : 'display: none;' }}">{{ $cancelledCount }}</span>
                        </button>
                        <div class="bell-menu" id="cancelNotifMenu">
                            <div class="bell-menu-header">
                                <h4 style="color: #EF4444;">Cancelled Orders</h4>
                                <span id="cancelHeaderCount"
                                    style="background: rgba(239, 68, 68, 0.1); color: #EF4444;">{{ $cancelledCount }}
                                    New</span>
                            </div>
                            <div class="bell-menu-body" id="cancelNotifList">
                                @forelse($cancelledOrders as $order)
                                    <a href="{{ route('seller.orders') }}" class="notification-item">
                                        <div class="notif-icon" style="background: #FEE2E2; color: #EF4444;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </div>
                                        <div class="notif-content">
                                            <p>Order <strong>#{{ $order->id }}</strong> cancelled by
                                                {{ $order->user->name ?? 'Guest' }}.
                                            </p>
                                            <span>{{ $order->updated_at->diffForHumans() }}</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="no-notif">No new cancellations.</div>
                                @endforelse
                                @if($cancelledCount > 0)
                                    <a href="{{ route('seller.orders') }}" class="view-all-link"
                                        style="display: block; text-align: center; padding: 10px; font-size: 12px; color: #EF4444; text-decoration: none; font-weight: 600; border-top: 1px solid #f1f1f1;">View
                                        All Cancellations</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Partner Notification --}}
                    <div class="notification-dropdown">
                        <button class="bell-btn" id="deliveryNotifToggle" title="Delivery Applications">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                            @if($deliveryNotifCount > 0)
                                <span class="bell-badge" id="deliveryBadge"
                                    style="background: var(--admin-primary);">{{ $deliveryNotifCount }}</span>
                            @else
                                <span class="bell-badge" id="deliveryBadge"
                                    style="background: var(--admin-primary); display: none;">0</span>
                            @endif
                        </button>
                        <div class="bell-menu" id="deliveryNotifMenu">
                            <div class="bell-menu-header">
                                <h4>Partner Applications</h4>
                                <span id="deliveryHeaderCount"
                                    style="background: rgba(242, 92, 59, 0.1); color: var(--admin-primary);">{{ $deliveryNotifCount }}
                                    New</span>
                            </div>
                            <div class="bell-menu-body" id="deliveryNotifList">
                                @forelse($deliveryNotifications as $notif)
                                    <div class="notification-item" id="notif-{{ $notif->id }}">
                                        <div class="notif-icon-wrapper"
                                            onmousedown="startNotifDismiss(event, '{{ $notif->id }}')"
                                            onmouseup="stopNotifDismiss()" onmouseleave="stopNotifDismiss()"
                                            ontouchstart="startNotifDismiss(event, '{{ $notif->id }}')"
                                            ontouchend="stopNotifDismiss()">
                                            <svg class="progress-ring">
                                                <circle class="progress-ring__circle" r="18" cx="20" cy="20" />
                                            </svg>
                                            <div class="notif-icon" style="background: #FDEEE4; color: #F25C3B;">
                                                <svg class="icon-arrow" width="18" height="18" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M5 12h14"></path>
                                                    <path d="m12 5 7 7-7 7"></path>
                                                </svg>
                                                <svg class="icon-close" width="18" height="18" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                </svg>
                                            </div>
                                        </div>
                                        <a href="{{ route('seller.delivery') }}" class="notif-content"
                                            style="text-decoration: none; flex: 1;">
                                            <p><strong>{{ $notif->data['delivery_boy_name'] }}</strong> applied as a
                                                partner.</p>
                                            <span>{{ $notif->created_at->diffForHumans() }}</span>
                                        </a>
                                    </div>
                                @empty
                                    <div class="no-notif">No new applications.</div>
                                @endforelse
                                @if($deliveryNotifCount > 0)
                                    <a href="{{ route('seller.delivery') }}" class="view-all-link"
                                        style="display: block; text-align: center; padding: 10px; font-size: 12px; color: var(--admin-primary); text-decoration: none; font-weight: 600; border-top: 1px solid #f1f1f1;">View
                                        All Applications</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Out of Stock Notification --}}
                    <div class="notification-dropdown">
                        <button class="bell-btn" id="stockNotifToggle" title="Out of Stock Products">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F59E0B"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                                </path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <span class="bell-badge" id="stockBadge"
                                style="background: #F59E0B; {{ $stockNotifCount > 0 ? '' : 'display: none;' }}">{{ $stockNotifCount }}</span>
                        </button>
                        <div class="bell-menu" id="stockNotifMenu">
                            <div class="bell-menu-header">
                                <h4 style="color: #F59E0B;">Out of Stock</h4>
                                <span id="stockHeaderCount"
                                    style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">{{ $stockNotifCount }}
                                    New</span>
                            </div>
                            <div class="bell-menu-body" id="stockNotifList">
                                @forelse($stockNotifications as $notif)
                                    <div class="notification-item" id="notif-{{ $notif->id }}">
                                        <div class="notif-icon-wrapper"
                                            onmousedown="startNotifDismiss(event, '{{ $notif->id }}')"
                                            onmouseup="stopNotifDismiss()" onmouseleave="stopNotifDismiss()"
                                            ontouchstart="startNotifDismiss(event, '{{ $notif->id }}')"
                                            ontouchend="stopNotifDismiss()">
                                            <svg class="progress-ring">
                                                <circle class="progress-ring__circle" r="18" cx="20" cy="20" />
                                            </svg>
                                            <div class="notif-icon" style="background: #FFFBEB; color: #F59E0B;">
                                                <svg class="icon-arrow" width="18" height="18" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M5 12h14"></path>
                                                    <path d="m12 5 7 7-7 7"></path>
                                                </svg>
                                                <svg class="icon-close" width="18" height="18" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2.5"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                </svg>
                                            </div>
                                        </div>
                                        <a href="{{ route('seller.products') }}" class="notif-content"
                                            style="text-decoration: none; flex: 1;">
                                            <p><strong>{{ $notif->data['product_name'] }}</strong> is out of stock.</p>
                                            <span>{{ $notif->created_at->diffForHumans() }}</span>
                                        </a>
                                    </div>
                                @empty
                                    <div class="no-notif">No out of stock alerts.</div>
                                @endforelse
                                @if($stockNotifCount > 0)
                                    <a href="{{ route('seller.products') }}" class="view-all-link"
                                        style="display: block; text-align: center; padding: 10px; font-size: 12px; color: #F59E0B; text-decoration: none; font-weight: 600; border-top: 1px solid #f1f1f1;">View
                                        All Products</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Order Notification (Bell) --}}
                    <div class="notification-dropdown">
                        <button class="bell-btn" id="bellToggle" title="Payment Notifications">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            @if($newOrdersCount > 0)
                                <span class="bell-badge" id="orderBadge">{{ $newOrdersCount }}</span>
                            @else
                                <span class="bell-badge" id="orderBadge" style="display: none;">0</span>
                            @endif
                        </button>
                        <div class="bell-menu" id="bellMenu">
                            <div class="bell-menu-header">
                                <h4>Payments</h4>
                                <span id="orderHeaderCount">{{ $newOrdersCount }} Successful</span>
                            </div>
                            <div class="bell-menu-body" id="orderNotifList">
                                @forelse($newOrders as $order)
                                    <a href="{{ route('seller.orders') }}" class="notification-item">
                                        <div class="notif-icon">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                        <div class="notif-content">
                                            <p>Order <strong>#{{ $order->id }}</strong> paid by
                                                {{ $order->user->name ?? 'Guest' }}.
                                            </p>
                                            <span>{{ $order->created_at->diffForHumans() }}</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="no-notif">No new payments.</div>
                                @endforelse
                                @if($newOrdersCount > 0)
                                    <a href="{{ route('seller.orders') }}" class="view-all-link"
                                        style="display: block; text-align: center; padding: 10px; font-size: 12px; color: #4CAF50; text-decoration: none; font-weight: 600; border-top: 1px solid #f1f1f1;">View
                                        All Orders</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
            <p>Are you sure you want to sign out? You will need to sign-in again to access the Seller Panel.</p>
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
        const logoutForm = document.getElementById('seller-logout-form');

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
            document.getElementById('seller-logout-form').submit();
        };

        // Close modal when clicking outside
        logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) closeLogoutModal();
        });

        // Dropdown Toggle Logic
        const settingsToggle = document.getElementById('settings-toggle');
        const settingsDropdown = document.getElementById('settings-dropdown');

        if (settingsToggle && settingsDropdown) {
            settingsToggle.addEventListener('click', (e) => {
                e.preventDefault();

                // If sidebar is collapsed, expand it first
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
                    updateToggleIcon(false);
                }

                settingsDropdown.classList.toggle('active');
                settingsToggle.classList.toggle('dropdown-open');
            });
        }

        // Bell Notification Logic
        const bellToggle = document.getElementById('bellToggle');
        const bellMenu = document.getElementById('bellMenu');
        const deliveryToggle = document.getElementById('deliveryNotifToggle');
        const deliveryMenu = document.getElementById('deliveryNotifMenu');
        const cancelToggle = document.getElementById('cancelNotifToggle');
        const cancelMenu = document.getElementById('cancelNotifMenu');
        const stockToggle = document.getElementById('stockNotifToggle');
        const stockMenu = document.getElementById('stockNotifMenu');

        const closeAllMenus = () => {
            if (bellMenu) bellMenu.classList.remove('active');
            if (deliveryMenu) deliveryMenu.classList.remove('active');
            if (cancelMenu) cancelMenu.classList.remove('active');
            if (stockMenu) stockMenu.classList.remove('active');
        };

        const setupToggle = (toggle, menu) => {
            if (toggle && menu) {
                toggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isActive = menu.classList.contains('active');
                    closeAllMenus();
                    if (!isActive) menu.classList.add('active');
                });
            }
        };

        setupToggle(bellToggle, bellMenu);
        setupToggle(deliveryToggle, deliveryMenu);
        setupToggle(cancelToggle, cancelMenu);
        setupToggle(stockToggle, stockMenu);

        document.addEventListener('click', (e) => {
            if (bellToggle && !bellToggle.contains(e.target) && bellMenu && !bellMenu.contains(e.target) &&
                deliveryToggle && !deliveryToggle.contains(e.target) && deliveryMenu && !deliveryMenu.contains(e.target) &&
                cancelToggle && !cancelToggle.contains(e.target) && cancelMenu && !cancelMenu.contains(e.target) &&
                stockToggle && !stockToggle.contains(e.target) && stockMenu && !stockMenu.contains(e.target)) {
                closeAllMenus();
            }
        });

        // --- Real-time Push Notifications (SSE) ---
        let dismissTimer = null;
        let currentDismissId = null;

        function startNotifDismiss(e, id) {
            e.preventDefault();
            currentDismissId = id;
            const wrapper = e.currentTarget;
            wrapper.classList.add('pressing');

            dismissTimer = setTimeout(() => {
                executeDismiss(id, wrapper);
            }, 2800); // Trigger slightly before 3s to ensure it fires before release
        }

        function stopNotifDismiss() {
            clearTimeout(dismissTimer);
            const wrappers = document.querySelectorAll('.notif-icon-wrapper.pressing');
            wrappers.forEach(w => w.classList.remove('pressing'));
        }

        async function executeDismiss(id, wrapper) {
            if (wrapper) wrapper.classList.remove('pressing');
            clearTimeout(dismissTimer);

            try {
                const url = "{{ route(auth()->user()->role . '.notifications.dismiss', ['id' => ':id']) }}".replace(':id', id);
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();
                if (result.success) {
                    const item = document.getElementById(`notif-${id}`);
                    if (item) {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(100px)';
                        item.style.maxHeight = '0';
                        item.style.paddingTop = '0';
                        item.style.paddingBottom = '0';
                        item.style.borderBottom = '0';
                        item.style.margin = '0';

                        setTimeout(() => {
                            item.remove();
                            // Update counts
                            const delBadge = document.getElementById('deliveryBadge');
                            const stockBadge = document.getElementById('stockBadge');

                            // Check which list the item belongs to
                            const isDelivery = wrapper.closest('#deliveryNotifList');
                            const isStock = wrapper.closest('#stockNotifList');

                            if (isDelivery) {
                                const count = parseInt(delBadge.innerText) - 1;
                                if (count > 0) {
                                    delBadge.innerText = count;
                                    document.getElementById('deliveryHeaderCount').innerText = count + ' New';
                                } else {
                                    delBadge.style.display = 'none';
                                    document.getElementById('deliveryHeaderCount').innerText = '0 New';
                                    document.getElementById('deliveryNotifList').innerHTML = '<div class="no-notif">No new applications.</div>';
                                }
                            } else if (isStock) {
                                const count = parseInt(stockBadge.innerText) - 1;
                                if (count > 0) {
                                    stockBadge.innerText = count;
                                    document.getElementById('stockHeaderCount').innerText = count + ' New';
                                } else {
                                    stockBadge.style.display = 'none';
                                    document.getElementById('stockHeaderCount').innerText = '0 New';
                                    document.getElementById('stockNotifList').innerHTML = '<div class="no-notif">No out of stock alerts.</div>';
                                }
                            }
                        }, 300);
                    }
                }
            } catch (error) {
                console.error("Failed to dismiss notification:", error);
            }
        }

        function startNotificationStreaming() {
            const eventSource = new EventSource("{{ route('sse.stream') }}");

            eventSource.addEventListener('update', (event) => {
                const data = JSON.parse(event.data);

                // 1. Update Order Notifications
                const orderBadge = document.getElementById('orderBadge');
                const orderHeaderCount = document.getElementById('orderHeaderCount');
                const orderNotifList = document.getElementById('orderNotifList');

                if (data.orders && data.orders.count !== undefined) {
                    if (data.orders.count > 0) {
                        orderBadge.innerText = data.orders.count;
                        orderBadge.style.display = 'flex';
                        orderHeaderCount.innerText = data.orders.count + ' Successful';
                    } else {
                        orderBadge.style.display = 'none';
                        orderHeaderCount.innerText = '0 Successful';
                    }

                    if (data.orders.items && data.orders.items.length > 0) {
                        let html = '';
                        data.orders.items.forEach(order => {
                            html += `
                                <a href="{{ route('seller.orders') }}" class="notification-item">
                                    <div class="notif-icon">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </div>
                                    <div class="notif-content">
                                        <p>Order <strong>#${order.id}</strong> paid by ${order.customer}.</p>
                                        <span>${order.time}</span>
                                    </div>
                                </a>
                            `;
                        });
                        html += `<a href="{{ route('seller.orders') }}" class="view-all-link" style="display: block; text-align: center; padding: 10px; font-size: 12px; color: #4CAF50; text-decoration: none; font-weight: 600; border-top: 1px solid #f1f1f1;">View All Orders</a>`;
                        orderNotifList.innerHTML = html;
                    } else {
                        orderNotifList.innerHTML = '<div class="no-notif">No new payments.</div>';
                    }

                    // Update Cancelled Order Notifications
                    const cancelBadge = document.getElementById('cancelBadge');
                    const cancelHeaderCount = document.getElementById('cancelHeaderCount');
                    const cancelNotifList = document.getElementById('cancelNotifList');

                    if (data.orders.cancelled_count !== undefined) {
                        if (data.orders.cancelled_count > 0) {
                            cancelBadge.innerText = data.orders.cancelled_count;
                            cancelBadge.style.display = 'flex';
                            cancelHeaderCount.innerText = data.orders.cancelled_count + ' New';
                        } else {
                            cancelBadge.style.display = 'none';
                            cancelHeaderCount.innerText = '0 New';
                        }
                    }

                    if (data.orders.cancelled_items && data.orders.cancelled_items.length > 0) {
                        let html = '';
                        data.orders.cancelled_items.forEach(order => {
                            html += `
                                <a href="{{ route('seller.orders') }}" class="notification-item">
                                    <div class="notif-icon" style="background: #FEE2E2; color: #EF4444;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </div>
                                    <div class="notif-content">
                                        <p>Order <strong>#${order.id}</strong> cancelled by ${order.customer}.</p>
                                        <span>${order.time}</span>
                                    </div>
                                </a>
                            `;
                        });
                        html += `<a href="{{ route('seller.orders') }}" class="view-all-link" style="display: block; text-align: center; padding: 10px; font-size: 12px; color: #EF4444; text-decoration: none; font-weight: 600; border-top: 1px solid #f1f1f1;">View All Cancellations</a>`;
                        cancelNotifList.innerHTML = html;
                    } else {
                        cancelNotifList.innerHTML = '<div class="no-notif">No new cancellations.</div>';
                    }
                }

                // 2. Update Delivery Partner Notifications
                const deliveryBadge = document.getElementById('deliveryBadge');
                const deliveryHeaderCount = document.getElementById('deliveryHeaderCount');
                const deliveryNotifList = document.getElementById('deliveryNotifList');

                if (data.delivery && data.delivery.count !== undefined) {
                    if (data.delivery.count > 0) {
                        deliveryBadge.innerText = data.delivery.count;
                        deliveryBadge.style.display = 'flex';
                        deliveryHeaderCount.innerText = data.delivery.count + ' New';
                    } else {
                        deliveryBadge.style.display = 'none';
                        deliveryHeaderCount.innerText = '0 New';
                    }

                    if (data.delivery.items && data.delivery.items.length > 0) {
                        let html = '';
                        data.delivery.items.forEach(notif => {
                            html += `
                                <div class="notification-item" id="notif-${notif.id}">
                                    <div class="notif-icon-wrapper" 
                                         onmousedown="startNotifDismiss(event, '${notif.id}')" 
                                         onmouseup="stopNotifDismiss()" 
                                         onmouseleave="stopNotifDismiss()"
                                         ontouchstart="startNotifDismiss(event, '${notif.id}')"
                                         ontouchend="stopNotifDismiss()">
                                        <svg class="progress-ring">
                                            <circle class="progress-ring__circle" r="18" cx="20" cy="20"/>
                                        </svg>
                                        <div class="notif-icon" style="background: #FDEEE4; color: #F25C3B;">
                                            <svg class="icon-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                                            <svg class="icon-close" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </div>
                                    </div>
                                    <a href="{{ route('seller.delivery') }}" class="notif-content" style="text-decoration: none; flex: 1;">
                                        <p><strong>${notif.name}</strong> applied as a partner.</p>
                                        <span>${notif.time}</span>
                                    </a>
                                </div>
                            `;
                        });
                        html += `<a href="{{ route('seller.delivery') }}" class="view-all-link" style="display: block; text-align: center; padding: 10px; font-size: 12px; color: var(--admin-primary); text-decoration: none; font-weight: 600; border-top: 1px solid #f1f1f1;">View All Applications</a>`;
                        deliveryNotifList.innerHTML = html;
                    } else {
                        deliveryNotifList.innerHTML = '<div class="no-notif">No new applications.</div>';
                    }
                }

                // 3. Update Stock Notifications
                const stockBadge = document.getElementById('stockBadge');
                const stockHeaderCount = document.getElementById('stockHeaderCount');
                const stockNotifList = document.getElementById('stockNotifList');

                if (data.stock && data.stock.count !== undefined) {
                    if (data.stock.count > 0) {
                        stockBadge.innerText = data.stock.count;
                        stockBadge.style.display = 'flex';
                        stockHeaderCount.innerText = data.stock.count + ' New';
                    } else {
                        stockBadge.style.display = 'none';
                        stockHeaderCount.innerText = '0 New';
                    }

                    if (data.stock.items && data.stock.items.length > 0) {
                        let html = '';
                        data.stock.items.forEach(notif => {
                            html += `
                                <div class="notification-item" id="notif-${notif.id}">
                                    <div class="notif-icon-wrapper" 
                                         onmousedown="startNotifDismiss(event, '${notif.id}')" 
                                         onmouseup="stopNotifDismiss()" 
                                         onmouseleave="stopNotifDismiss()"
                                         ontouchstart="startNotifDismiss(event, '${notif.id}')"
                                         ontouchend="stopNotifDismiss()">
                                        <svg class="progress-ring">
                                            <circle class="progress-ring__circle" r="18" cx="20" cy="20"/>
                                        </svg>
                                        <div class="notif-icon" style="background: #FFFBEB; color: #F59E0B;">
                                            <svg class="icon-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                                            <svg class="icon-close" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </div>
                                    </div>
                                    <a href="{{ route('seller.products') }}?search=${notif.product_name}" class="notif-content" style="text-decoration: none; flex: 1;">
                                        <p><strong>${notif.product_name}</strong> is out of stock.</p>
                                        <span>${notif.time}</span>
                                    </a>
                                </div>
                            `;
                        });
                        html += `<a href="{{ route('seller.products') }}" class="view-all-link" style="display: block; text-align: center; padding: 10px; font-size: 12px; color: #F59E0B; text-decoration: none; font-weight: 600; border-top: 1px solid #f1f1f1;">View All Products</a>`;
                        stockNotifList.innerHTML = html;
                    } else {
                        stockNotifList.innerHTML = '<div class="no-notif">No out of stock alerts.</div>';
                    }
                }
            });

            eventSource.onerror = (err) => {
                console.error("EventSource failed:", err);
                eventSource.close();
                // Retry after 10 seconds if connection drops
                setTimeout(startNotificationStreaming, 10000);
            };
        }

        startNotificationStreaming();
    </script>

    @yield('scripts')
</body>

</html>