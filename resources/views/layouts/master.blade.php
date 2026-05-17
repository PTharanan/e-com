<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'E-Commerce') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Lottie Player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        /* ========== Common Styles & Tokens ========== */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FAFAFA;
            color: #1A1A1A;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        body.modal-open {
            overflow: hidden;
            padding-right: 15px;
            /* Prevent layout shift if scrollbar disappears */
        }

        :root {
            --color-primary: #F25C3B;
            --color-primary-hover: #E04A2A;
            --color-bg-light: #FDEEE4;
            --color-text-dark: #1A1A1A;
            --color-text-medium: #555555;
            --color-text-light: #888888;
            --color-white: #FFFFFF;
            --color-border: #E0E0E0;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 8px 24px rgba(242, 92, 59, 0.12);
            --radius-sm: 8px;
            --radius-md: 16px;
            --radius-lg: 24px;
            --radius-pill: 50px;
            --transition-fast: 0.2s ease;
            --transition-normal: 0.35s ease;
        }

        main {
            min-height: calc(100vh - 75px);
            position: relative;
        }

        /* ========== NAVBAR ========== */
        .navbar {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 0 5%;
            height: 75px;
            background-color: var(--color-white);
            box-shadow: var(--shadow-sm);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        @media (min-width: 769px) {
            .navbar {
                position: fixed;
                background-color: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
            }
        }

        @media (max-width: 768px) {
            .navbar {
                grid-template-columns: 1fr auto;
                padding: 0 15px;
            }

            .nav-links {
                display: none;
            }
        }

        .navbar+main {
            padding-top: 75px;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            gap: 10px;
            justify-self: start;
        }

        .brand-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--color-text-dark);
            letter-spacing: -0.5px;
        }

        .brand-text span {
            color: var(--color-primary);
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
            justify-self: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--color-text-medium);
            font-weight: 500;
            font-size: 0.95rem;
            transition: var(--transition-fast);
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--color-primary);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--color-primary);
            transition: var(--transition-normal);
            border-radius: 2px;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            justify-self: end;
        }

        .user-info-wrapper,
        .cart-wrapper {
            display: flex;
            align-items: center;
            position: relative;
            cursor: pointer;
        }

        .user-name-display,
        .cart-label-display {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--color-text-dark);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            max-width: 0;
            opacity: 0;
            margin-left: 0;
            line-height: 1.2;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .user-info-wrapper:hover .user-name-display,
        .cart-wrapper:hover .cart-label-display {
            max-width: 150px;
            opacity: 1;
            margin-left: 10px;
            color: var(--color-primary);
        }

        .profile-btn {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-bg-light);
            color: var(--color-primary);
            border-radius: 50%;
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1.5px solid transparent;
        }

        .profile-btn:hover {
            background: var(--color-primary);
            color: var(--color-white);
            transform: scale(1.05);
        }

        .profile-btn svg {
            width: 24px;
            height: 24px;
        }

        .online-dot {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 10px;
            height: 10px;
            background: #28a745;
            border: 2px solid var(--color-white);
            border-radius: 50%;
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--color-primary);
            color: var(--color-white);
            font-size: 10px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 2px solid var(--color-white);
            animation: bounceIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: none;
            z-index: 10;
        }

        .bottom-nav .cart-badge {
            top: 5px;
            right: 15%;
        }

        @media (max-width: 768px) {
            .nav-actions .cart-wrapper {
                display: none !important;
            }
        }

        .flying-item {
            position: fixed;
            z-index: 9999;
            width: 50px;
            height: 50px;
            background: var(--color-primary);
            border-radius: 50%;
            pointer-events: none;
            transition: all 0.8s cubic-bezier(0.42, 0, 0.58, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 15px rgba(242, 92, 59, 0.4);
        }

        @keyframes bounceIn {
            from {
                transform: scale(0);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: var(--color-white);
            border-radius: var(--radius-sm);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            min-width: 150px;
            padding: 8px 0;
            display: none;
            z-index: 1100;
            border: 1px solid var(--color-border);
        }

        .profile-dropdown.active {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: var(--color-text-medium);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: var(--color-bg-light);
            color: var(--color-primary);
        }

        .dropdown-item svg {
            width: 18px;
            height: 18px;
        }


        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .user-name-display,
            .cart-label-display {
                display: none;
            }

            .bottom-nav-container {
                display: block !important;
            }

            body {
                padding-bottom: 70px;
            }

            /* ===== Mobile Cart Drawer ===== */
            .cart-drawer {
                max-width: 100%;
                right: -100%;
                width: 100%;
            }

            .cart-header {
                padding: 18px 20px;
            }

            .cart-title {
                font-size: 1.1rem;
            }

            .cart-body {
                padding: 20px;
            }

            .cart-footer {
                padding: 18px 20px;
                padding-bottom: calc(18px + env(safe-area-inset-bottom, 0px));
            }

            .cart-summary-item {
                margin-bottom: 10px;
                font-size: 0.9rem;
            }

            .cart-total {
                font-size: 1.05rem;
                margin-bottom: 18px;
            }

            .btn-checkout {
                padding: 14px;
                font-size: 0.95rem;
            }

            .empty-cart-msg h4 {
                font-size: 1.05rem !important;
            }

            .empty-cart-msg p {
                font-size: 0.9rem;
            }
        }

        /* ========== Bottom Nav ========== */
        .bottom-nav-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 70px;
            z-index: 5000;
            display: none;
        }

        .bottom-nav {
            width: 100%;
            height: 100%;
            background: #fcf6f1ff;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 0 5px;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.08);
            /* Simplified "Magic" Transparent Notch Mask */
            --mask-x: 50%;
            -webkit-mask-image: radial-gradient(circle at var(--mask-x) 0px, transparent 40px, black 41px);
            mask-image: radial-gradient(circle at var(--mask-x) 0px, transparent 40px, black 41px);
        }

        /* The Moving Indicator (Now Outside the Mask) */
        .nav-indicator {
            position: absolute;
            top: -32px;
            left: 0;
            width: 70px;
            height: 70px;
            background: #fcf6f1ff;
            border-radius: 50%;
            border: 6px solid #fcf6f1ff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 2500;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .nav-indicator svg {
            width: 28px;
            height: 28px;
            color: var(--color-primary);
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            color: var(--color-text-medium);
            font-size: 0.65rem;
            font-weight: 700;
            transition: all 0.5s ease;
            flex: 1;
            z-index: 2200;
            position: relative;
            height: 100%;
            justify-content: center;
            padding-top: 10px;
        }

        .bottom-nav-item svg {
            width: 22px;
            height: 22px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .bottom-nav-item.active {
            color: var(--color-primary);
        }

        .bottom-nav-item.active svg {
            opacity: 0;
        }

        .bottom-nav-item span {
            font-size: 0.65rem;
            font-weight: 700;
            transition: all 0.5s ease;
        }

        /* Active item text in primary color */
        .bottom-nav-item.active span {
            color: var(--color-primary);
        }

        .bottom-nav-item:not(.active) span {
            opacity: 0.7;
        }

        .bottom-nav .cart-badge {
            position: absolute;
            top: 4px;
            right: calc(50% - 22px);
            background: #ff3b30;
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            border-radius: 20px;
            display: none;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--color-bg-light);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 2400;
            padding: 0 3px;
            line-height: 1;
        }

        .bottom-nav-item.active .cart-badge {
            top: -44px;
            right: calc(50% - 22px);
        }

        /* Magic U-Shape Curve FAB */
        .cart-item-center {
            position: relative;
            z-index: 2100;
            margin-top: -50px;
            /* Pull it up higher into the U */
        }

        .cart-icon-circle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(242, 92, 59, 0.4);
            color: var(--color-white);
            border: 6px solid var(--color-bg-light);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* ========== GLOBAL PRODUCT CARD SYSTEM ========== */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: var(--color-white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-border);
            transition: var(--transition-normal);
            position: relative;
            display: flex;
            flex-direction: column;
            cursor: pointer;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            border-color: rgba(242, 92, 59, 0.2);
        }

        .product-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--color-primary);
            color: var(--color-white);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            z-index: 2;
            text-transform: uppercase;
        }

        .product-image {
            width: 100% !important;
            height: 220px !important;
            overflow: hidden !important;
            position: relative !important;
            margin: 0 !important;
            padding: 0 !important;
            background: none !important;
        }

        .product-image img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block !important;
            transition: var(--transition-normal);
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-info {
            padding: 20px;
        }

        .product-category {
            font-size: 0.8rem;
            color: var(--color-text-light);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .product-info {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--color-text-dark);
            line-height: 1.4;
            margin-bottom: 15px;
            transition: var(--transition-fast);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 3.1rem;
        }

        .product-card:hover .product-title {
            color: var(--color-primary);
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.04);
            position: relative;
            height: 56px;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--color-text-dark);
            transition: opacity 0.3s ease;
        }

        .product-card.active-qty .product-price {
            opacity: 0;
            pointer-events: none;
        }

        .btn-add {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--color-bg-light);
            color: var(--color-primary);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-fast);
        }

        .btn-add:hover:not(.max-reached):not(.is-success) {
            background: var(--color-primary);
            color: var(--color-white);
            transform: scale(1.1) rotate(90deg);
        }

        .btn-add.is-success:hover {
            transform: scale(1.1);
        }

        .btn-add.max-reached {
            background: #fdeaea !important;
            color: #d93025 !important;
            cursor: not-allowed;
            font-size: 0.7rem;
            font-weight: 800;
            border: 1.5px solid #f8b4b0 !important;
            position: relative;
            overflow: hidden;
        }

        .btn-add.max-reached span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            transition: transform 0.4s ease, opacity 0.3s ease;
        }

        .btn-add.max-reached .prohibited-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            width: 22px;
            height: 22px;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-add.max-reached:hover {
            background: #d93025 !important;
            color: white !important;
            transform: rotate(180deg);
        }

        .btn-add.max-reached:hover span {
            opacity: 0;
            transform: scale(0.5);
        }

        .btn-add.max-reached:hover .prohibited-icon {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1) rotate(-180deg);
        }

        .product-actions {
            position: absolute;
            right: 0;
            bottom: 0;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 44px;
            z-index: 5;
        }

        .product-card.active-qty .product-actions {
            width: 100%;
            justify-content: center;
        }

        .qty-selector {
            display: none;
            align-items: center;
            gap: 8px;
            background: var(--color-bg-light);
            padding: 4px;
            border-radius: var(--radius-pill);
            width: 100%;
            height: 44px;
            animation: fadeIn 0.3s ease;
        }

        .product-card.active-qty .qty-selector {
            display: flex;
        }

        .qty-btn {
            background: var(--color-white);
            border: none;
            color: var(--color-primary);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .qty-btn:hover:not(:disabled) {
            background: var(--color-primary);
            color: var(--color-white);
        }

        .qty-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .btn-confirm-add {
            flex: 1;
            background: var(--color-primary);
            color: var(--color-white);
            border: none;
            height: 32px;
            border-radius: var(--radius-pill);
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
            padding: 0 12px;
        }

        .qty-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--color-text-dark);
            min-width: 20px;
            text-align: center;
        }

        .cart-item-center:active .cart-icon-circle {
            transform: scale(0.9) translateY(5px);
        }

        .bottom-nav-item .cart-badge-mobile {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .cart-label-mobile {
            display: none;
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .product-card {
                /* Padding removed to allow full image */
            }

            .product-image {
                height: 200px;
                /* margin removed to allow full image */
            }

            .product-title {
                font-size: 1.05rem;
                height: auto;
                min-height: 2.8rem;
                margin-bottom: 12px;
            }

            .product-price {
                font-size: 1.25rem;
            }

            .btn-add {
                width: 40px;
                height: 40px;
            }
        }

        /* ========== Logout Modal ========== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 6000;
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease;
        }

        .logout-modal {
            background: var(--color-white);
            padding: 30px;
            border-radius: var(--radius-md);
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease;
        }

        .modal-icon {
            width: 60px;
            height: 60px;
            background: var(--color-bg-light);
            color: var(--color-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--color-text-dark);
        }

        .modal-text {
            color: var(--color-text-medium);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
        }

        .btn-modal {
            flex: 1;
            padding: 12px;
            border-radius: var(--radius-pill);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-cancel {
            background: #F5F5F5;
            color: var(--color-text-dark);
        }

        .btn-cancel:hover {
            background: #EEEEEE;
        }

        .btn-confirm-logout {
            background: var(--color-primary);
            color: var(--color-white);
        }

        .btn-confirm-logout:hover {
            background: var(--color-primary-hover);
            box-shadow: 0 4px 12px rgba(242, 92, 59, 0.3);
        }

        /* ========== Cart Drawer ========== */
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            z-index: 4000;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .cart-overlay.active {
            display: block;
            opacity: 1;
        }

        .cart-drawer {
            position: fixed;
            top: 0;
            right: -450px;
            width: 100%;
            max-width: 420px;
            height: 100%;
            background: var(--color-white);
            z-index: 4001;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
            transition: right 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            display: flex;
            flex-direction: column;
        }

        .cart-drawer.active {
            right: 0;
        }

        .cart-header {
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--color-border);
        }

        .cart-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-text-dark);
        }

        .cart-close-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-text-medium);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-close-btn:hover {
            transform: rotate(90deg);
            color: var(--color-primary);
        }

        .cart-body {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
        }

        .empty-cart-msg {
            text-align: center;
            margin-top: 50px;
        }

        .empty-cart-msg p {
            color: var(--color-text-light);
            margin-top: 15px;
        }

        .cart-footer {
            padding: 25px;
            border-top: 1px solid var(--color-border);
            background: #FAFAFA;
        }

        .cart-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .cart-total {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--color-text-dark);
            margin-bottom: 25px;
        }

        .btn-checkout {
            width: 100%;
            padding: 16px;
            background: var(--color-primary);
            color: var(--color-white);
            border: none;
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(242, 92, 59, 0.25);
        }

        .btn-checkout:hover {
            background: var(--color-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(242, 92, 59, 0.35);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
    @yield('styles')
</head>

<body>
    @if(!request()->is('admin/dashboard'))
        <nav class="navbar">
            <a href="{{ route('home') }}" class="navbar-brand">
                <div
                    style="width: 40px; height: 40px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--color-bg-light); border-radius: 50%;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </div>
                <span class="brand-text">E-<span>Shop</span></span>
            </a>

            <div class="nav-links">
                <a href="{{ route('home') }}"
                    class="{{ request()->routeIs('home') ? 'nav-link active' : 'nav-link' }}">Home</a>
                <a href="{{ route('products') }}"
                    class="{{ request()->routeIs('products', 'product.show') ? 'nav-link active' : 'nav-link' }}">Products</a>
                <a href="{{ route('categories') }}"
                    class="{{ request()->routeIs('categories') ? 'nav-link active' : 'nav-link' }}">Categories</a>
                <a href="{{ route('offers') }}"
                    class="{{ request()->routeIs('offers') ? 'nav-link active' : 'nav-link' }}">Offers</a>
            </div>

            <div class="nav-actions">
                <a href="{{ route('cart') }}" class="cart-wrapper" style="text-decoration: none;">
                    <div class="profile-btn" title="Shopping Cart">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        <span class="cart-badge" id="cart-badge">0</span>
                    </div>
                    <span class="cart-label-display">Cart</span>
                </a>

                @auth
                    <div class="user-info-wrapper">
                        <a href="#" class="profile-btn" title="My Account">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="online-dot"></span>
                        </a>
                        <span class="user-name-display">{{ Auth::user()->name }}</span>

                        <!-- Dropdown -->
                        <div class="profile-dropdown">
                            <a href="{{ route('dashboard') }}" class="dropdown-item" style="text-decoration: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>My Account</span>
                            </a>
                            <button type="button" class="dropdown-item" id="logout-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                <span>Sign Out</span>
                            </button>
                        </div>
                    </div>
                @else
                    <a href="{{ route('sign-in') }}" class="profile-btn" title="Sign In">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </a>
                @endauth
            </div>
        </nav>
    @endif

    <main>
        @yield('content')
    </main>

    @if(!request()->is('admin/dashboard'))
        <!-- Mobile Bottom Navigation Container -->
        <div class="bottom-nav-container" id="magic-nav-container">
            <!-- Moving Indicator Circle (Moved OUTSIDE the mask) -->
            <div class="nav-indicator" id="nav-indicator"></div>

            <nav class="bottom-nav" id="magic-nav">
                <a href="{{ route('home') }}"
                    class="bottom-nav-item {{ request()->routeIs('home') ? 'active' : 'nav-item-js' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span>Home</span>
                </a>
                <a href="{{ route('products') }}"
                    class="bottom-nav-item {{ request()->routeIs('products', 'product.show') ? 'active' : 'nav-item-js' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <span>Products</span>
                </a>

                <a href="{{ route('cart') }}"
                    class="bottom-nav-item {{ request()->is('cart*') ? 'active' : 'nav-item-js' }}"
                    id="cart-trigger-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                    <span class="cart-badge cart-badge-mobile">0</span>
                    <span>Cart</span>
                </a>

                <a href="{{ route('categories') }}"
                    class="bottom-nav-item {{ request()->routeIs('categories') ? 'active' : 'nav-item-js' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25a2.25 2.25 0 01-2.25 2.25h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z" />
                    </svg>
                    <span>Categories</span>
                </a>
                <a href="{{ Auth::check() ? route('dashboard') : route('login') }}"
                    class="bottom-nav-item {{ request()->routeIs('dashboard') || request()->routeIs('login') ? 'active' : 'nav-item-js' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.963-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Account</span>
                </a>
            </nav>
        </div>
    @endif

    <!-- Logout Confirmation Modal -->
    <div class="modal-overlay" id="logout-modal">
        <div class="logout-modal">
            <div class="modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" style="width: 30px; height: 30px;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
            </div>
            <h3 class="modal-title">Wait, Sign Out?</h3>
            <p class="modal-text">Are you sure you want to sign out of your account?</p>
            <div class="modal-actions">
                <button class="btn-modal btn-cancel" id="logout-cancel">Cancel</button>
                <form action="{{ route('logout') }}" method="POST" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn-modal btn-confirm-logout" style="width: 100%;">Yes, Sign
                        Out</button>
                </form>
            </div>
        </div>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userInfoWrapper = document.querySelector('.user-info-wrapper');
            const profileDropdown = document.querySelector('.profile-dropdown');
            const logoutTrigger = document.getElementById('logout-trigger');
            const logoutModal = document.getElementById('logout-modal');
            const logoutCancel = document.getElementById('logout-cancel');

            // Cart Elements
            const cartTrigger = document.getElementById('cart-trigger');
            const cartDrawer = document.getElementById('cart-drawer');
            const cartClose = document.getElementById('cart-close');
            const cartOverlay = document.getElementById('cart-overlay');

            if (userInfoWrapper && profileDropdown) {
                userInfoWrapper.addEventListener('click', (e) => {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('active');
                });
            }

            if (logoutTrigger) {
                logoutTrigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    profileDropdown.classList.remove('active');
                    logoutModal.style.display = 'flex';
                    document.body.classList.add('modal-open');
                });
            }

            if (logoutCancel) {
                logoutCancel.addEventListener('click', () => {
                    logoutModal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                });
            }

            // Cart Logic (Desktop & Mobile)
            const openCart = () => {
                const isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
                if (!isAuthenticated) {
                    window.location.href = "{{ route('sign-in') }}";
                    return;
                }
                cartDrawer.classList.add('active');
                cartOverlay.classList.add('active');
                document.body.classList.add('modal-open');
                // Hide bottom nav so it doesn't block checkout button
                const navContainer = document.getElementById('magic-nav-container');
                if (navContainer) navContainer.style.setProperty('display', 'none', 'important');
            };

            if (cartTrigger) cartTrigger.addEventListener('click', openCart);

            const cartTriggerMobile = document.getElementById('cart-trigger-mobile');
            if (cartTriggerMobile) cartTriggerMobile.addEventListener('click', openCart);

            const closeCart = () => {
                cartDrawer.classList.remove('active');
                cartOverlay.classList.remove('active');
                if (!logoutModal.style.display || logoutModal.style.display === 'none') {
                    document.body.classList.remove('modal-open');
                }
                // Restore bottom nav and reset active state to current page
                const navContainer = document.getElementById('magic-nav-container');
                if (navContainer && window.innerWidth <= 768) {
                    navContainer.style.setProperty('display', 'block', 'important');
                    // Restore the original server-side active item
                    const nav = document.getElementById('magic-nav');
                    const items = nav.querySelectorAll('.bottom-nav-item');
                    items.forEach(i => i.classList.remove('active'));
                    const idx = window.__originalActiveIndex;
                    if (idx !== undefined && items[idx]) {
                        items[idx].classList.add('active');
                    }
                    // Re-run magicNav to reposition indicator
                    magicNav();
                }
            };

            if (cartClose) cartClose.addEventListener('click', closeCart);
            if (cartOverlay) cartOverlay.addEventListener('click', closeCart);

            // Global Cart State & Persistence
            const userId = "{{ Auth::id() }}";
            const cartKey = userId ? `cart_user_${userId}` : null;
            const isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};

            // Initialize from localStorage
            window.cartItems = {};
            if (cartKey) {
                const savedCart = localStorage.getItem(cartKey);
                if (savedCart) {
                    try {
                        const rawCart = JSON.parse(savedCart);
                        const now = Date.now();
                        const expiry = 24 * 60 * 60 * 1000; // 24 Hours

                        Object.keys(rawCart).forEach(id => {
                            const entry = rawCart[id];
                            // Handle both old format (number) and new format (object)
                            const qty = typeof entry === 'object' ? entry.qty : entry;
                            const timestamp = typeof entry === 'object' ? entry.t : now;

                            if (now - timestamp < expiry) {
                                window.cartItems[id] = { qty: qty, t: timestamp };
                            }
                        });

                        // Save cleaned cart
                        localStorage.setItem(cartKey, JSON.stringify(window.cartItems));

                        const totalItems = Object.values(window.cartItems).reduce((a, b) => a + (b.qty || 0), 0);
                        if (totalItems > 0) {
                            const badges = document.querySelectorAll('.cart-badge');
                            badges.forEach(badge => {
                                badge.innerText = totalItems;
                                badge.style.display = 'flex';
                            });
                        }
                    } catch (e) { console.error("Error parsing cart", e); }
                }
            }

            // Global Fly Animation
            window.flyToCart = (startElement, countToAdd, productId, options = {}) => {
                let cartBtn = document.querySelector('.nav-actions .profile-btn[title="Shopping Cart"]');
                if (window.innerWidth <= 768) cartBtn = document.querySelector('#cart-trigger-mobile svg');
                const badges = document.querySelectorAll('.cart-badge');
                if (!cartBtn || !startElement) return;

                if (productId) {
                    // Create a unique key if variants exist, otherwise just use productId
                    const variantKey = (options.color || options.size) ? `${productId}_${options.color || ''}_${options.size || ''}` : productId;

                    const current = window.cartItems[variantKey] || {
                        productId: productId,
                        qty: 0,
                        t: Date.now(),
                        color: options.color || null,
                        size: options.size || null
                    };

                    // If it's the old format (just an object with qty and t), convert to new format
                    if (!current.productId) current.productId = productId;

                    window.cartItems[variantKey] = {
                        ...current,
                        qty: current.qty + countToAdd,
                        t: Date.now()
                    };
                    if (cartKey) localStorage.setItem(cartKey, JSON.stringify(window.cartItems));
                }

                const startRect = startElement.getBoundingClientRect();
                const endRect = cartBtn.getBoundingClientRect();
                const flyer = document.createElement('div');
                flyer.className = 'flying-item';
                flyer.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path></svg>`;
                flyer.style.left = `${startRect.left + startRect.width / 2 - 25}px`;
                flyer.style.top = `${startRect.top + startRect.height / 2 - 25}px`;
                document.body.appendChild(flyer);

                setTimeout(() => {
                    flyer.style.left = `${endRect.left + endRect.width / 2 - 25}px`;
                    flyer.style.top = `${endRect.top + endRect.height / 2 - 25}px`;
                    flyer.style.transform = 'scale(0.2)';
                    flyer.style.opacity = '0';
                }, 50);

                setTimeout(() => {
                    flyer.remove();
                    const totalItems = Object.values(window.cartItems).reduce((a, b) => a + b.qty, 0);
                    badges.forEach(badge => {
                        badge.innerText = totalItems;
                        badge.style.display = 'flex';
                    });
                    const visibleCart = cartBtn.closest('.profile-btn') || cartBtn.closest('.bottom-nav-item');
                    if (visibleCart) {
                        visibleCart.style.transform = 'scale(1.2)';
                        setTimeout(() => visibleCart.style.transform = '', 200);
                    }
                }, 850);
            };

            // ========== GLOBAL PRODUCT CARD INTERACTION (Event Delegation) ==========
            document.addEventListener('click', async (e) => {
                // 1. Add to Cart Button
                const btnAdd = e.target.closest('.btn-add');
                if (btnAdd) {
                    e.stopPropagation();
                    const card = btnAdd.closest('.product-card');
                    if (!card) return;

                    const productId = card.dataset.productId;
                    const stockLimit = parseInt(card.dataset.stock || 0);

                    if (!isAuthenticated) { window.location.href = "{{ route('sign-in') }}"; return; }

                    const inCart = window.cartItems[productId] ? window.cartItems[productId].qty : 0;
                    if (inCart >= stockLimit) {
                        alert('Sorry, out of stock!');
                        return;
                    }

                    const originalContent = btnAdd.innerHTML;
                    btnAdd.disabled = true;
                    btnAdd.innerHTML = '<span style="font-size:0.6rem">...</span>';

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch('{{ route("products.add-to-cart") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                _token: csrfToken,
                                product_id: productId,
                                quantity: 1
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            if (window.flyToCart) window.flyToCart(btnAdd, 1, productId);
                            card.dataset.stock = result.new_stock;
                            btnAdd.classList.add('is-success');
                            btnAdd.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                            btnAdd.style.background = '#28a745';
                            btnAdd.style.color = '#fff';

                            setTimeout(() => {
                                btnAdd.disabled = false;
                                btnAdd.classList.remove('is-success');
                                btnAdd.innerHTML = originalContent;
                                btnAdd.style.background = '';
                                btnAdd.style.color = '';
                                if (result.new_stock <= 0) location.reload();
                            }, 1500);
                        } else {
                            alert(result.message || 'Failed');
                            btnAdd.disabled = false;
                            btnAdd.innerHTML = originalContent;
                        }
                    } catch (err) {
                        btnAdd.disabled = false;
                        btnAdd.innerHTML = originalContent;
                    }
                    return;
                }

                // 2. Product Card Click (Navigate to detail)
                const card = e.target.closest('.product-card');
                if (card) {
                    // Don't navigate if clicking buttons or qty controls
                    if (e.target.closest('.btn-add, .qty-selector, .qty-btn, .btn-confirm-add, .product-actions button')) return;

                    const pid = card.dataset.productId;
                    if (pid) {
                        window.location.href = `{{ url('/product') }}/${pid}`;
                    }
                }
            });

            // ========== MAGIC NAV ANIMATION ==========
            const magicNav = () => {
                const container = document.getElementById('magic-nav-container');
                const nav = document.getElementById('magic-nav');
                const indicator = document.getElementById('nav-indicator');
                const items = nav.querySelectorAll('.bottom-nav-item');

                const updatePosition = (activeItem) => {
                    const rect = activeItem.getBoundingClientRect();
                    const navRect = nav.getBoundingClientRect();
                    const centerX = (rect.left + rect.width / 2) - navRect.left;

                    indicator.style.left = `${centerX - 35}px`;
                    // Update the mask position variable on the nav element
                    nav.style.setProperty('--mask-x', `${centerX}px`);

                    // Clone the icon into the indicator bubble
                    const originalSvg = activeItem.querySelector('svg');
                    if (originalSvg) {
                        indicator.innerHTML = originalSvg.outerHTML;
                    }
                };

                const activeItem = nav.querySelector('.bottom-nav-item.active');
                if (activeItem) {
                    // Small delay to ensure layout is ready
                    setTimeout(() => updatePosition(activeItem), 50);
                }

                // Handle clicks for instant feedback (though Laravel refreshes page)
                items.forEach(item => {
                    item.addEventListener('click', function () {
                        items.forEach(i => i.classList.remove('active'));
                        this.classList.add('active');
                        updatePosition(this);
                    });
                });

                window.addEventListener('resize', () => {
                    const currentActive = nav.querySelector('.bottom-nav-item.active');
                    if (currentActive) updatePosition(currentActive);
                });
            };

            magicNav();

            // Save the original server-rendered active item index
            (function () {
                const nav = document.getElementById('magic-nav');
                const items = nav.querySelectorAll('.bottom-nav-item');
                items.forEach((item, index) => {
                    if (item.classList.contains('active')) {
                        window.__originalActiveIndex = index;
                    }
                });
            })();

            // Close everything on click outside
            window.addEventListener('click', (e) => {
                if (profileDropdown) {
                    profileDropdown.classList.remove('active');
                }
                if (e.target === logoutModal) {
                    logoutModal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                }
            });
        });
    </script>

    @yield('scripts')
</body>

</html>