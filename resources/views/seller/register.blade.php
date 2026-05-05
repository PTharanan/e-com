<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sign in to your account - E-Commerce Shopping Platform">

    <title>Seller Sign Up - {{ config('app.name', 'E-Commerce') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Lottie Player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        /* ========== CSS Reset & Base ========== */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ========== Design Tokens ========== */
        :root {
            --color-primary: #F25C3B;
            --color-primary-hover: #E04A2A;
            --color-primary-light: #FFF5F2;
            --color-bg-right: #FDEEE4;
            --color-text-dark: #1A1A1A;
            --color-text-medium: #555555;
            --color-text-light: #888888;
            --color-text-link: #F25C3B;
            --color-border: #E0E0E0;
            --color-border-focus: #F25C3B;
            --color-input-bg: #FFFFFF;
            --color-white: #FFFFFF;
            --color-card-bg: rgba(255, 255, 255, 0.85);
            --shadow-card: 0 8px 40px rgba(0, 0, 0, 0.06);
            --shadow-input: 0 2px 8px rgba(0, 0, 0, 0.04);
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 24px;
            --radius-pill: 50px;
            --transition-fast: 0.2s ease;
            --transition-normal: 0.35s ease;
        }

        /* ========== Layout ========== */
        .login-container {
            display: flex;
            min-height: 100vh;
            min-height: 100dvh;
            width: 100%;
            overflow: hidden;
            overflow-y: auto;
        }

        /* ========== Left Panel - Form ========== */
        .login-left {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: var(--color-white);
            position: relative;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -100px;
            left: -100px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(242, 92, 59, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        /* ========== Logo ========== */
        .logo-text {
            font-size: 26px;
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: 48px;
            letter-spacing: -0.5px;
            cursor: default;
            transition: var(--transition-normal);
        }

        .logo-text:hover {
            letter-spacing: 1px;
        }

        /* ========== Welcome Text ========== */
        .welcome-text {
            font-size: 14px;
            font-weight: 400;
            color: #f79f8bff;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .signin-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-primary-hover);
            margin-bottom: 20px;
            line-height: 1.2;
        }

        /* ========== Form ========== */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: var(--color-text-medium);
            letter-spacing: 0.2px;
        }

        .forgot-link {
            font-size: 12px;
            font-weight: 400;
            color: var(--color-text-light);
            text-decoration: none;
            transition: var(--transition-fast);
            position: relative;
        }

        .forgot-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background: var(--color-primary);
            transition: var(--transition-normal);
        }

        .forgot-link:hover {
            color: var(--color-primary);
        }

        .forgot-link:hover::after {
            width: 100%;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 400;
            color: var(--color-text-dark);
            background: var(--color-input-bg);
            border: 1.5px solid var(--color-border);
            border-radius: var(--radius-sm);
            outline: none;
            transition: var(--transition-fast);
            box-shadow: var(--shadow-input);
        }

        .form-input::placeholder {
            color: var(--color-text-light);
            font-weight: 300;
        }

        .form-input:focus {
            border-color: var(--color-border-focus);
            box-shadow: 0 0 0 3px rgba(242, 92, 59, 0.1), var(--shadow-input);
        }

        .form-input:hover:not(:focus) {
            border-color: #CCC;
        }

        /* ========== Password Wrapper ========== */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-input {
            padding-right: 48px;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-text-light);
            padding: 4px;
            transition: var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: var(--color-primary);
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        /* ========== Submit Button ========== */
        .btn-signin {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 8px;
            padding: 14px 36px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--color-white);
            background: linear-gradient(135deg, var(--color-primary), #E8553A);
            border: none;
            border-radius: var(--radius-pill);
            cursor: pointer;
            transition: var(--transition-normal);
            box-shadow: 0 4px 16px rgba(242, 92, 59, 0.35);
            width: fit-content;
            position: relative;
            overflow: hidden;
        }

        .btn-signin::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: 0.5s;
        }

        .btn-signin:hover::before {
            left: 100%;
        }

        .btn-signin:hover {
            background: linear-gradient(135deg, var(--color-primary-hover), #D14328);
            box-shadow: 0 6px 24px rgba(242, 92, 59, 0.45);
            transform: translateY(-1px);
        }

        .btn-signin:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(242, 92, 59, 0.3);
        }

        .btn-signin .arrow-icon {
            display: flex;
            align-items: center;
            transition: var(--transition-fast);
        }

        .btn-signin:hover .arrow-icon {
            transform: translateX(4px);
        }

        .btn-signin .arrow-icon svg {
            width: 18px;
            height: 18px;
        }

        /* ========== Sign Up Link ========== */
        .signup-text {
            margin-top: 32px;
            font-size: 13px;
            color: var(--color-text-light);
            text-align: center;
        }

        .signup-link {
            color: var(--color-text-link);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition-fast);
            position: relative;
        }

        .signup-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--color-primary);
            transition: var(--transition-normal);
            border-radius: 2px;
        }

        .signup-link:hover::after {
            width: 100%;
        }

        /* ========== Error Messages ========== */
        .alert-error {
            background: #FFF0EE;
            border: 1px solid #FFD4CC;
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            margin-bottom: 8px;
        }

        .alert-error ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .alert-error li {
            font-size: 13px;
            color: #D93025;
            line-height: 1.5;
        }

        /* ========== Right Panel - Illustration ========== */
        .login-right {
            width: 50%;
            min-width: 420px;
            background: var(--color-bg-right);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-right::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                linear-gradient(180deg, transparent 0%, rgba(253, 238, 228, 0.3) 100%);
            pointer-events: none;
        }

        /* Decorative shelf lines */
        .login-right::after {
            content: '';
            position: absolute;
            top: 20%;
            right: 10%;
            width: 120px;
            height: 1px;
            background: rgba(0, 0, 0, 0.06);
            box-shadow:
                0 60px 0 rgba(0, 0, 0, 0.06),
                -40px 30px 0 rgba(0, 0, 0, 0.04),
                60px 90px 0 rgba(0, 0, 0, 0.04);
        }

        .illustration-wrapper {
            position: relative;
            z-index: 1;
            width: 85%;
            max-width: 520px;
            animation: floatIn 1s ease-out;
        }

        .illustration-wrapper img,
        .illustration-wrapper lottie-player {
            width: 100%;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 8px 24px rgba(0, 0, 0, 0.08));
        }

        /* ========== Animations ========== */
        @keyframes floatIn {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-form-wrapper>* {
            animation: fadeInUp 0.6s ease-out both;
        }

        .login-form-wrapper>*:nth-child(1) {
            animation-delay: 0.05s;
        }

        .login-form-wrapper>*:nth-child(2) {
            animation-delay: 0.1s;
        }

        .login-form-wrapper>*:nth-child(3) {
            animation-delay: 0.15s;
        }

        .login-form-wrapper>*:nth-child(4) {
            animation-delay: 0.2s;
        }

        .login-form-wrapper>*:nth-child(5) {
            animation-delay: 0.25s;
        }

        /* ========== Subtle Floating Animation ========== */
        @keyframes subtleFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .illustration-wrapper {
            animation: floatIn 1s ease-out, subtleFloat 5s ease-in-out 1s infinite;
        }

        /* ========== Remember Me ========== */
        .remember-me-group {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .remember-me-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--color-text-medium);
            cursor: pointer;
            user-select: none;
        }

        .remember-me-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--color-primary);
        }

        /* ========== Responsive ========== */
        @media (max-width: 1024px) {
            .login-right {
                width: 45%;
                min-width: 350px;
            }
        }

        @media (max-width: 768px) {
            .login-left {
                padding: 32px 24px 48px;
            }

            .login-right {
                display: none;
            }

            .logo-text {
                margin-bottom: 28px;
                font-size: 22px;
            }

            .signin-title {
                font-size: 26px;
                margin-bottom: 24px;
            }

            .signup-text {
                margin-top: 24px;
            }
        }

        @media (max-width: 480px) {
            .login-left {
                padding: 15px 15px 30px;
                justify-content: flex-start;
                padding-top: 60px;
            }

            .login-form-wrapper {
                max-width: 100%;
            }

            .signin-title {
                font-size: 22px;
                margin-bottom: 15px;
                color: var(--color-primary-hover);
            }

            .welcome-text {
                font-size: 13px;
                margin-bottom: 5px;
            }

            .logo-animation lottie-player {
                width: 140px !important;
            }

            .logo-animation {
                margin-left: -35px !important;
                margin-bottom: -10px !important;
            }

            .form-input {
                padding: 10px 12px;
                font-size: 13px;
            }

            .btn-signin {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
                font-size: 13px;
            }

            .signup-text {
                font-size: 12px;
            }

            .mobile-shop-now {
                top: 15px;
                right: 15px;
            }

            .mobile-shop-now .btn-shop-now {
                padding: 8px 15px;
                font-size: 11px;
            }
        }

        /* ========== Loading Spinner ========== */
        .btn-signin.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-signin.loading .btn-text {
            visibility: hidden;
        }

        .btn-signin.loading .arrow-icon {
            visibility: hidden;
        }

        .btn-signin.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ========== Shop Now Button (Illustration Side) ========== */
        .illustration-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            width: 100%;
        }

        .btn-shop-now {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 14px 40px;
            background: var(--color-white);
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 1px;
            border-radius: var(--radius-pill);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1.5px solid transparent;
        }

        .btn-shop-now:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 35px rgba(242, 92, 59, 0.2);
            background: var(--color-primary);
            color: var(--color-white);
        }

        .btn-shop-now svg {
            width: 20px;
            height: 20px;
            transition: transform 0.4s ease;
        }

        .btn-shop-now:hover svg {
            transform: translateX(5px);
        }

        /* Mobile specific shop now */
        .mobile-shop-now {
            display: none;
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 100;
        }

        .mobile-shop-now .btn-shop-now {
            padding: 10px 20px;
            font-size: 13px;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .mobile-shop-now {
                display: block;
            }
        }

        /* ========== Multi-step Form ========== */
        .step-indicator {
            font-size: 13px;
            color: var(--color-primary);
            font-weight: 500;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .step-dots {
            display: flex;
            gap: 6px;
        }

        .step-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--color-border);
            transition: var(--transition-normal);
        }

        .step-dot.active {
            background: var(--color-primary);
            transform: scale(1.2);
        }

        .form-step {
            display: none;
            flex-direction: column;
            gap: 12px;
            animation: slideIn 0.4s ease forwards;
        }

        .form-step.active {
            display: flex;
        }

        @keyframes slideIn {
            0% { opacity: 0; transform: translateX(10px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 24px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            border-radius: var(--radius-pill);
            cursor: pointer;
            transition: var(--transition-normal);
            color: var(--color-text-medium);
            background: transparent;
            border: 1.5px solid var(--color-border);
        }

        .btn-secondary:hover {
            background: #F9F9F9;
            color: var(--color-text-dark);
            border-color: #CCC;
        }

        /* ========== OTP Inputs ========== */
        .otp-inputs-container {
            display: flex;
            gap: 12px;
            justify-content: flex-start;
            margin-bottom: 20px;
        }
        
        .otp-input {
            width: 45px;
            height: 55px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            border: 2px solid var(--color-border);
            border-radius: 8px;
            background: var(--color-input-bg);
            color: var(--color-text-dark);
            transition: all 0.3s ease;
        }
        
        .otp-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 4px rgba(242, 92, 59, 0.1);
            outline: none;
        }

        /* ========== Toast Notification ========== */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: #333;
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInToast 0.3s ease-out forwards;
            min-width: 280px;
            font-size: 14px;
            border-left: 4px solid var(--color-primary);
        }

        .toast.success { border-left-color: #28a745; }
        .toast.error { border-left-color: #dc3545; }

        @keyframes slideInToast {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
</head>

<body>

    <div id="toast-container"></div>

    <div class="login-container">
        {{-- Left Panel - Login Form --}}
        <div class="login-left">
            <!-- Mobile Shop Now Removed for Admin -->

            <div class="login-form-wrapper">
                {{-- Logo --}}
                <div class="logo-animation" id="login-logo" style="margin-bottom: -15px; margin-left: -40px;">
                    <lottie-player src="{{ asset('lottie/shop-cart-kdp.json') }}" background="transparent" speed="1"
                        style="width: 150px; height: auto;" loop autoplay></lottie-player>
                </div>

                {{-- Welcome Heading --}}
                <p class="welcome-text">Create Seller Account</p>
                <h1 class="signin-title">Seller Sign Up</h1>

                <div class="step-indicator">
                    <span id="step-text">Step 1 of 2</span>
                    <div class="step-dots">
                        <div class="step-dot active" id="dot-1"></div>
                        <div class="step-dot" id="dot-2"></div>
                    </div>
                </div>

                {{-- Error Messages --}}
                @if ($errors->any())
                    <div class="alert-error" id="login-errors">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Register Form --}}
                <form class="login-form" method="POST" action="{{ route('seller.register.post') }}" id="seller-register-form">
                    @csrf

                    {{-- STEP 1 --}}
                    <div class="form-step active" id="step-1">
                        {{-- Name Field --}}
                        <div class="form-group">
                            <label for="user-name" class="form-label">Full Name</label>
                            <input type="text" id="user-name" name="name" class="form-input"
                                placeholder="Enter full name" value="{{ old('name') }}" required autocomplete="name"
                                autofocus>
                        </div>

                        {{-- Email Field --}}
                        <div class="form-group">
                            <label for="user-email" class="form-label">Email</label>
                            <input type="email" id="user-email" name="email" class="form-input"
                                placeholder="Enter email address" value="{{ old('email') }}" required autocomplete="email">
                        </div>

                        {{-- Phone Number Field --}}
                        <div class="form-group">
                            <label for="user-phno" class="form-label">Phone Number</label>
                            <input type="text" id="user-phno" name="phno" class="form-input"
                                placeholder="Enter phone number" value="{{ old('phno') }}">
                        </div>

                        {{-- Address Field --}}
                        <div class="form-group">
                            <label for="user-address" class="form-label">Address</label>
                            <textarea id="user-address" name="address" class="form-input" rows="2"
                                placeholder="Enter address">{{ old('address') }}</textarea>
                        </div>

                        {{-- Store Selection Field --}}
                        <div class="form-group">
                            <label for="admin-id" class="form-label">Select Store Owner (Admin)</label>
                            <select id="admin-id" name="admin_id" class="form-input" required>
                                <option value="" disabled selected>Choose a Store...</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ old('admin_id') == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}'s Store
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn-signin" id="btn-next" style="width: 100%;">
                                <span class="btn-text">NEXT STEP</span>
                                <span class="arrow-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- STEP 2 --}}
                    <div class="form-step" id="step-2">
                        {{-- Password Field --}}
                        <div class="form-group">
                            <div class="form-group-header">
                                <label for="user-password" class="form-label">Password</label>
                            </div>
                            <div class="password-wrapper">
                                <input type="password" id="user-password" name="password" class="form-input"
                                    placeholder="Enter Password" required autocomplete="new-password">
                                <button type="button" class="password-toggle toggle-pw" data-target="user-password"
                                aria-label="Toggle password visibility">
                                {{-- Eye Icon (show) --}}
                                <svg class="eye-open" style="display:none;" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{-- Eye Slash Icon (hide) --}}
                                <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                            </div>
                        </div>

                        {{-- Confirm Password Field --}}
                        <div class="form-group">
                            <div class="form-group-header">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                            </div>
                            <div class="password-wrapper">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input"
                                placeholder="Confirm Password" required autocomplete="new-password">
                            <button type="button" class="password-toggle toggle-pw" data-target="password_confirmation"
                                aria-label="Toggle password visibility">
                                {{-- Eye Icon (show) --}}
                                <svg class="eye-open" style="display:none;" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{-- Eye Slash Icon (hide) --}}
                                <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn-secondary" id="btn-prev">BACK</button>
                            <button type="submit" class="btn-signin" id="btn-user-signin" style="flex: 1;">
                                <span class="btn-text">SIGN UP</span>
                                <span class="arrow-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- STEP 3 (OTP) --}}
                    <div class="form-step" id="step-3">
                        <h3 style="font-size: 18px; margin-bottom: 8px;">Email Verification</h3>
                        <p style="font-size: 13px; color: var(--color-text-medium); margin-bottom: 16px;">
                            We've sent a 6-digit OTP to your email. It expires in <span id="otp-timer" style="font-weight:bold;color:var(--color-primary)">60</span>s.
                        </p>
                        
                        <div class="form-group">
                            <label class="form-label" style="display: block; margin-bottom: 15px;">Enter OTP</label>
                            <div class="otp-inputs-container">
                                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                            </div>
                            <input type="hidden" id="otp" name="otp">
                        </div>

                        <div class="btn-group" style="flex-direction: column;">
                            <button type="button" class="btn-signin" id="btn-verify-otp" style="width: 100%;">
                                <span class="btn-text">VERIFY & REGISTER</span>
                            </button>
                            <button type="button" class="btn-secondary" id="btn-resend-otp" style="display: none; width: 100%;">
                                <span class="btn-text">RESEND OTP</span>
                            </button>
                        </div>
                    </div>

                    <p class="signup-text" style="margin-top: 15px;">
                        Already have an account? <a href="{{ route('seller.login') }}" class="signup-link">Sign in</a>
                    </p>
                </form>
            </div>
        </div>

        {{-- Right Panel - Illustration --}}
        <div class="login-right">
            <div class="illustration-wrapper">
                <div class="illustration-content">
                    <lottie-player src="{{ asset('lottie/seller-signup-animation.json') }}" background="transparent" speed="1"
                        style="width: 100%; height: auto;" loop autoplay></lottie-player>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.querySelectorAll('.toggle-pw').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const isPassword = input.type === 'password';

                input.type = isPassword ? 'text' : 'password';
                this.querySelector('.eye-open').style.display = isPassword ? 'block' : 'none';
                this.querySelector('.eye-closed').style.display = isPassword ? 'none' : 'block';
            });
        });

        // Form submit loading state
        const registerForm = document.getElementById('seller-register-form');
        const submitBtn = document.getElementById('btn-user-signin');
        const toastContainer = document.getElementById('toast-container');

        function showNotify(message, type = 'error') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerText = message;
            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.5s ease-out forwards';
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }

        let otpTimer;

        function startTimer(duration) {
            let timer = duration, seconds;
            const timerDisplay = document.getElementById('otp-timer');
            const resendBtn = document.getElementById('btn-resend-otp');
            resendBtn.style.display = 'none';

            clearInterval(otpTimer);

            otpTimer = setInterval(function () {
                seconds = parseInt(timer % 60, 10);
                timerDisplay.textContent = seconds < 10 ? "0" + seconds : seconds;

                if (--timer < 0) {
                    clearInterval(otpTimer);
                    resendBtn.style.display = 'block';
                    timerDisplay.textContent = "00";
                }
            }, 1000);
        }

        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                const otpVal = Array.from(otpInputs).map(i => i.value).join('');
                document.getElementById('otp').value = otpVal;
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });

        registerForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            // Password validation
            if (document.getElementById('user-password').value !== document.getElementById('password_confirmation').value) {
                showNotify("Passwords do not match");
                return;
            }

            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            const formData = new FormData(registerForm);
            
            try {
                const response = await fetch("{{ route('seller.register.post') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();
                
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                if (data.success) {
                    step2.classList.remove('active');
                    document.getElementById('step-3').classList.add('active');
                    document.getElementById('step-text').innerText = "Step 3 of 3";

                    // Auto-focus first OTP box
                    setTimeout(() => {
                        if (otpInputs[0]) otpInputs[0].focus();
                    }, 100);
                    
                    // Add a dot for step 3 if needed
                    const dots = document.querySelector('.step-dots');
                    if(dots.children.length < 3) {
                        const newDot = document.createElement('div');
                        newDot.className = 'step-dot active';
                        dots.appendChild(newDot);
                        document.getElementById('dot-1').classList.remove('active');
                        document.getElementById('dot-2').classList.remove('active');
                    }

                    startTimer(60);
                } else {
                    if(data.errors) {
                        const firstError = Object.values(data.errors)[0][0];
                        showNotify(firstError);
                    } else {
                        showNotify(data.message || 'Error occurred');
                    }
                }
            } catch (error) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                showNotify('Network error or server unreachable.');
            }
        });

        // Verify OTP
        document.getElementById('btn-verify-otp').addEventListener('click', async function() {
            const otpValue = document.getElementById('otp').value;
            if(!otpValue || otpValue.length !== 6) {
                showNotify("Please enter a valid 6-digit OTP");
                return;
            }

            const btn = this;
            btn.classList.add('loading');

            try {
                const response = await fetch("{{ route('seller.verify-otp.post') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ otp: otpValue })
                });
                const data = await response.json();
                
                btn.classList.remove('loading');
                btn.disabled = false;
                if (data.success) {
                    showNotify(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || "{{ route('home') }}";
                    }, 1500);
                } else {
                    showNotify(data.message);
                }
            } catch (error) {
                btn.classList.remove('loading');
                btn.disabled = false;
                showNotify('Network error or server unreachable.');
            }
        });

        // Resend OTP
        document.getElementById('btn-resend-otp').addEventListener('click', async function() {
            const btn = this;
            btn.classList.add('loading');

            try {
                const response = await fetch("{{ route('seller.resend-otp.post') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                
                btn.classList.remove('loading');
                btn.disabled = false;
                if (data.success) {
                    showNotify(data.message, 'success');
                    startTimer(60);
                    
                    otpInputs.forEach(input => input.value = '');
                    document.getElementById('otp').value = '';
                    otpInputs[0].focus();
                } else {
                    showNotify(data.message);
                }
            } catch (error) {
                btn.classList.remove('loading');
                btn.disabled = false;
                showNotify('Network error or server unreachable.');
            }
        });

        // Steps logic
        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');
        const dot1 = document.getElementById('dot-1');
        const dot2 = document.getElementById('dot-2');
        const stepText = document.getElementById('step-text');

        document.getElementById('btn-next').addEventListener('click', function () {
            const name = document.getElementById('user-name').value;
            const email = document.getElementById('user-email').value;
            if (!name || !email) {
                alert('Please enter your Name and Email to proceed.');
                return;
            }
            step1.classList.remove('active');
            step2.classList.add('active');
            dot1.classList.remove('active');
            dot2.classList.add('active');
            stepText.innerText = "Step 2 of 2";
        });

        document.getElementById('btn-prev').addEventListener('click', function () {
            step2.classList.remove('active');
            step1.classList.add('active');
            dot2.classList.remove('active');
            dot1.classList.add('active');
            stepText.innerText = "Step 1 of 2";
        });
    </script>

</body>

</html>
