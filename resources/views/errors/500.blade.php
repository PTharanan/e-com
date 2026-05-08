<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Error - {{ config('app.name', 'E-Commerce') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #F25C3B;
            --primary-light: rgba(242, 92, 59, 0.1);
            --dark: #1E293B;
            --gray: #64748B;
            --bg: #FFFFFF;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg);
            color: var(--dark);
            height: 100vh;
            width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* Absolute fix for scrollbar */
        }

        .error-wrapper {
            text-align: center;
            width: 100%;
            max-width: 500px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeIn 0.8s ease-out;
        }

        .error-visual {
            position: relative;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-number {
            font-size: 150px;
            font-weight: 900;
            color: rgba(242, 92, 59, 0.15);
            line-height: 1;
            user-select: none;
            letter-spacing: -5px;
        }

        .error-img {
            position: absolute;
            max-height: 300px;
            width: auto;
            z-index: 2;
            mix-blend-mode: multiply; /* Removes white background from image */
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.05));
            animation: float 4s ease-in-out infinite;
        }

        .error-content h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .error-content p {
            font-size: 14px;
            color: var(--gray);
            line-height: 1.6;
            margin-bottom: 30px;
            max-width: 380px;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 30px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(242, 92, 59, 0.3);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(242, 92, 59, 0.4);
            filter: brightness(1.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @media (max-width: 480px) {
            .bg-number { font-size: 120px; }
            .error-img { max-height: 240px; }
            .error-content h1 { font-size: 24px; }
        }
    </style>
</head>
<body>

    <div class="error-wrapper">
        <div class="error-visual">
            <div class="bg-number">500</div>
            <img src="{{ asset('images/500-error.png') }}" class="error-img" alt="Error">
        </div>

        <div class="error-content">
            <h1>Oops! Server Error</h1>
            <p>We're experiencing some technical difficulties. Our team has been notified and is working to fix it. Please try again later.</p>
            
            <a href="{{ url('/') }}" class="btn-home">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5m0 0l7 7m-7-7l7-7"/></svg>
                BACK TO HOME
            </a>
        </div>
    </div>

</body>
</html>
