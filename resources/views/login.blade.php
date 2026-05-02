<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sign in to your account - E-Commerce Shopping Platform">

    <title>User Login - {{ config('app.name', 'E-Commerce') }}</title>

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
            margin-bottom: 36px;
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
    </style>
</head>

<body>

    <div class="login-container">
        {{-- Left Panel - Login Form --}}
        <div class="login-left">
            <!-- Mobile Shop Now -->
            <div class="mobile-shop-now">
                <a href="{{ url('/') }}" class="btn-shop-now">
                    <span>SHOP NOW</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            <div class="login-form-wrapper">
                {{-- Logo --}}
                <div class="logo-animation" id="login-logo" style="margin-bottom: 1px; margin-left: -55px;">
                    <lottie-player src="{{ asset('images/shop-cart-kdp.json') }}" background="transparent" speed="1"
                        style="width: 200px; height: auto;" loop autoplay></lottie-player>
                </div>

                {{-- Welcome Heading --}}
                <p class="welcome-text">Welcome back !!!</p>
                <h1 class="signin-title">Sign in</h1>

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

                {{-- Login Form --}}
                <form class="login-form" method="POST" action="{{ url('/sign-in') }}" id="user-login-form">
                    @csrf

                    {{-- Email Field --}}
                    <div class="form-group">
                        <label for="user-email" class="form-label">Email</label>
                        <input type="email" id="user-email" name="email" class="form-input"
                            placeholder="Enter email address" value="{{ old('email') }}" required autocomplete="email"
                            autofocus>
                    </div>

                    {{-- Password Field --}}
                    <div class="form-group">
                        <div class="form-group-header">
                            <label for="user-password" class="form-label">Password</label>
                            <a href="{{ url('/forgot-password') }}" class="forgot-link" id="forgot-password-link">Forgot
                                Password ?</a>
                        </div>
                        <div class="password-wrapper">
                            <input type="password" id="user-password" name="password" class="form-input"
                                placeholder="Enter Password" required autocomplete="current-password">
                            <button type="button" class="password-toggle" id="toggle-password"
                                aria-label="Toggle password visibility">
                                {{-- Eye Icon (show) --}}
                                <svg id="eye-open" style="display:none;" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{-- Eye Slash Icon (hide) --}}
                                <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="remember-me-group">
                        <label class="remember-me-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Remember me</span>
                        </label>
                    </div>

                    {{-- Sign In Button --}}
                    <button type="submit" class="btn-signin" id="btn-user-signin">
                        <span class="btn-text">SIGN IN</span>
                        <span class="arrow-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
                            </svg>
                        </span>
                    </button>
                </form>

                {{-- Sign Up Link --}}
                <p class="signup-text">
                    I don't have an account ? <a href="{{ url('/sign-up') }}" class="signup-link"
                        id="signup-redirect-link">Sign up</a>
                </p>
            </div>
        </div>

        {{-- Right Panel - Illustration --}}
        <div class="login-right">
            <div class="illustration-wrapper">
                <div class="illustration-content">
                    <lottie-player src="{{ asset('images/shopping.json') }}" background="transparent" speed="1"
                        style="width: 100%; height: auto;" loop autoplay></lottie-player>

                    <a href="{{ url('/') }}" class="btn-shop-now">
                        <span>SHOP NOW</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('user-password');
        const eyeOpen = document.getElementById('eye-open');
        const eyeClosed = document.getElementById('eye-closed');

        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeOpen.style.display = isPassword ? 'block' : 'none';
            eyeClosed.style.display = isPassword ? 'none' : 'block';
        });

        // Form submit loading state
        const loginForm = document.getElementById('user-login-form');
        const signinBtn = document.getElementById('btn-user-signin');

        loginForm.addEventListener('submit', function () {
            signinBtn.classList.add('loading');
        });
    </script>

</body>

</html>