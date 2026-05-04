<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Forgot Password - E-Commerce Shopping Platform">

    <title>Forgot Password - {{ config('app.name', 'E-Commerce') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lottie Player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        /* Reusing styles from login.blade.php */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; -webkit-font-smoothing: antialiased; }
        :root {
            --color-primary: #F25C3B;
            --color-primary-hover: #E04A2A;
            --color-bg-right: #FDEEE4;
            --color-text-dark: #1A1A1A;
            --color-text-medium: #555555;
            --color-text-light: #888888;
            --color-border: #E0E0E0;
            --color-border-focus: #F25C3B;
            --color-white: #FFFFFF;
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-pill: 50px;
            --transition-normal: 0.35s ease;
        }

        .container { display: flex; min-height: 100vh; min-height: 100dvh; width: 100%; overflow: hidden; overflow-y: auto; }
        .left-panel { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; background: var(--color-white); position: relative; }
        .form-wrapper { width: 100%; max-width: 420px; z-index: 1; }
        
        .signin-title { font-size: 32px; font-weight: 700; color: var(--color-primary-hover); margin-bottom: 12px; line-height: 1.2; }
        .welcome-text { font-size: 14px; color: var(--color-text-light); margin-bottom: 36px; }

        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px; }
        .form-label { font-size: 13px; font-weight: 500; color: var(--color-text-medium); }
        .form-input { width: 100%; padding: 14px 16px; font-size: 14px; border: 1.5px solid var(--color-border); border-radius: var(--radius-sm); outline: none; transition: 0.2s; }
        .form-input:focus { border-color: var(--color-border-focus); box-shadow: 0 0 0 3px rgba(242, 92, 59, 0.1); }

        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            padding: 14px 36px; font-size: 14px; font-weight: 600; letter-spacing: 1.5px;
            text-transform: uppercase; color: var(--color-white);
            background: linear-gradient(135deg, var(--color-primary), #E8553A);
            border: none; border-radius: var(--radius-pill); cursor: pointer;
            transition: var(--transition-normal); box-shadow: 0 4px 16px rgba(242, 92, 59, 0.35);
            width: 100%; margin-top: 10px;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(242, 92, 59, 0.45); }
        .btn-primary:active { transform: translateY(0); }

        .right-panel { width: 50%; background: var(--color-bg-right); display: flex; align-items: center; justify-content: center; }
        .illustration-wrapper { width: 85%; max-width: 520px; animation: float 5s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }

        .back-to-login { display: block; text-align: center; margin-top: 24px; font-size: 13px; color: var(--color-text-light); text-decoration: none; font-weight: 500; }
        .back-to-login:hover { color: var(--color-primary); }

        .alert { padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 13px; display: none; }
        .alert-error { background: #FFF0EE; border: 1px solid #FFD4CC; color: #D93025; }
        .alert-success { background: #E6F4EA; border: 1px solid #CEEAD6; color: #1E8E3E; }

        .loading { pointer-events: none; opacity: 0.7; }

        @media (max-width: 768px) { .right-panel { display: none; } }
        
        @media (max-width: 480px) {
            .left-panel { padding: 15px; padding-top: 50px; justify-content: flex-start; }
            .signin-title { font-size: 22px; }
            .welcome-text { font-size: 13px; margin-bottom: 24px; }
            .form-input { padding: 12px 14px; }
            .btn-primary { padding: 12px 20px; font-size: 13px; }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="left-panel">
            <div class="form-wrapper">
                <div style="margin-bottom: 20px; margin-left: -50px;">
                    <lottie-player src="{{ asset('lottie/shop-cart-kdp.json') }}" background="transparent" speed="1" style="width: 180px; height: auto;" loop autoplay></lottie-player>
                </div>
                
                <h1 class="signin-title">Forgot Password?</h1>
                <p class="welcome-text">No worries, it happens! Enter your email to reset your password.</p>

                <div id="alert-box" class="alert"></div>

                <form id="forgot-password-form">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="e.g. user@example.com" required autofocus>
                    </div>

                    <button type="submit" class="btn-primary" id="submit-btn">
                        <span>SEND OTP</span>
                    </button>
                </form>

                <a href="{{ url('/sign-in') }}" class="back-to-login">← Back to Sign-in</a>
            </div>
        </div>

        <div class="right-panel">
            <div class="illustration-wrapper">
                <lottie-player src="{{ asset('lottie/shopping.json') }}" background="transparent" speed="1" style="width: 100%; height: auto;" loop autoplay></lottie-player>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('forgot-password-form');
        const submitBtn = document.getElementById('submit-btn');
        const alertBox = document.getElementById('alert-box');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            submitBtn.classList.add('loading');
            submitBtn.innerText = 'PROCESSING...';
            alertBox.style.display = 'none';

            try {
                const response = await fetch("{{ url('/forgot-password/check') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email: document.getElementById('email').value })
                });

                const data = await response.json();

                if (data.success) {
                    alertBox.className = 'alert alert-success';
                    alertBox.innerText = data.message;
                    alertBox.style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = "{{ url('/forgot-password/verify') }}";
                    }, 1500);
                } else {
                    alertBox.className = 'alert alert-error';
                    alertBox.innerText = data.message;
                    alertBox.style.display = 'block';
                    submitBtn.classList.remove('loading');
                    submitBtn.innerText = 'SEND OTP';

                    // If email doesn't exist, provide option to sign up
                    if (data.message.includes('not registered')) {
                         alertBox.innerHTML += ` <a href="{{ url('/sign-up') }}" style="color: inherit; text-decoration: underline; font-weight: 600;">Sign up here</a>`;
                    }
                }
            } catch (error) {
                alertBox.className = 'alert alert-error';
                alertBox.innerText = 'An error occurred. Please try again.';
                alertBox.style.display = 'block';
                submitBtn.classList.remove('loading');
                submitBtn.innerText = 'SEND OTP';
            }
        });
    </script>
</body>
</html>
