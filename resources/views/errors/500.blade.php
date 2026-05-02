<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Error - {{ config('app.name', 'E-Commerce') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ========== CSS Reset & Base ========== */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background-color: #FFFFFF;
            color: #1A1A1A;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ========== Design Tokens ========== */
        :root {
            --color-primary: #F25C3B;
            --color-primary-hover: #E04A2A;
            --color-text-dark: #1A1A1A;
            --color-text-medium: #555555;
            --color-white: #FFFFFF;
            --radius-pill: 50px;
            --transition-normal: 0.35s ease;
        }

        /* ========== Error Container ========== */
        .error-container {
            text-align: center;
            padding: 40px 20px;
            max-width: 600px;
            width: 100%;
            animation: fadeInUp 0.6s ease-out;
        }

        .error-image {
            width: 100%;
            max-width: 400px;
            height: auto;
            margin: 0 auto 32px auto;
            display: block;
            /* Added a subtle float animation */
            animation: float 4s ease-in-out infinite;
        }

        .error-title {
            font-size: 36px;
            font-weight: 700;
            color: var(--color-text-dark);
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .error-description {
            font-size: 16px;
            font-weight: 400;
            color: var(--color-text-medium);
            margin-bottom: 32px;
            line-height: 1.5;
        }

        /* ========== Button ========== */
        .btn-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 36px;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--color-white);
            background: linear-gradient(135deg, var(--color-primary), #E8553A);
            border: none;
            border-radius: var(--radius-pill);
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(242, 92, 59, 0.35);
            transition: var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .btn-home::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn-home:hover::before {
            left: 100%;
        }

        .btn-home:hover {
            background: linear-gradient(135deg, var(--color-primary-hover), #D14328);
            box-shadow: 0 6px 24px rgba(242, 92, 59, 0.45);
            transform: translateY(-2px);
        }

        .btn-home:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(242, 92, 59, 0.3);
        }

        .btn-home svg {
            width: 20px;
            height: 20px;
            transition: var(--transition-normal);
        }

        .btn-home:hover svg {
            transform: translateX(-4px);
        }

        /* ========== Animations ========== */
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        /* ========== Responsive ========== */
        @media (max-width: 480px) {
            .error-title { font-size: 28px; }
            .error-description { font-size: 14px; margin-bottom: 24px; }
            .error-image { max-width: 300px; margin-bottom: 24px; }
            .btn-home { padding: 12px 28px; font-size: 14px; }
        }
    </style>
</head>
<body>

    <div class="error-container">
        {{-- Generated 500 error image --}}
        <img src="{{ asset('images/500-error.png') }}" alt="500 Internal Server Error" class="error-image">
        
        <h1 class="error-title">Oops! Server Error</h1>
        <p class="error-description">
            We're experiencing some technical difficulties on our end. Our team has been notified and is working to fix the issue. Please try again shortly.
        </p>
        
        <a href="{{ url('/') }}" class="btn-home">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to Home
        </a>
    </div>

</body>
</html>
