<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Reset Password - E-Commerce Shopping Platform">

    <title>Reset Password - {{ config('app.name', 'E-Commerce') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lottie Player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        /* Reusing styles */
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
            --color-white: #FFFFFF;
            --radius-sm: 6px;
            --radius-pill: 50px;
            --transition-normal: 0.35s ease;
        }

        .container { display: flex; min-height: 100vh; min-height: 100dvh; width: 100%; overflow: hidden; overflow-y: auto; }
        .left-panel { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; background: var(--color-white); }
        .form-wrapper { width: 100%; max-width: 420px; }
        
        .signin-title { font-size: 32px; font-weight: 700; color: var(--color-primary-hover); margin-bottom: 12px; }
        .welcome-text { font-size: 14px; color: var(--color-text-light); margin-bottom: 36px; }

        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
        .form-label { font-size: 13px; font-weight: 500; color: var(--color-text-medium); }
        .form-input { width: 100%; padding: 14px 16px; font-size: 14px; border: 1.5px solid var(--color-border); border-radius: var(--radius-sm); outline: none; transition: 0.2s; }
        .form-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(242, 92, 59, 0.1); }

        .button-group { display: flex; gap: 12px; margin-top: 10px; }
        
        .btn-primary {
            flex: 2; display: inline-flex; align-items: center; justify-content: center;
            padding: 14px 20px; font-size: 14px; font-weight: 600; letter-spacing: 1px;
            text-transform: uppercase; color: var(--color-white);
            background: linear-gradient(135deg, var(--color-primary), #E8553A);
            border: none; border-radius: var(--radius-pill); cursor: pointer;
            box-shadow: 0 4px 16px rgba(242, 92, 59, 0.35); transition: 0.3s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(242, 92, 59, 0.45); }

        .btn-clear {
            flex: 1; padding: 14px 20px; font-size: 14px; font-weight: 600;
            text-transform: uppercase; color: var(--color-text-medium);
            background: #F5F5F5; border: 1.5px solid var(--color-border);
            border-radius: var(--radius-pill); cursor: pointer; transition: 0.3s;
        }
        .btn-clear:hover { background: #EEEEEE; border-color: #CCCCCC; }

        .right-panel { width: 50%; background: var(--color-bg-right); display: flex; align-items: center; justify-content: center; }
        .alert { padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 13px; display: none; }
        .alert-error { background: #FFF0EE; border: 1px solid #FFD4CC; color: #D93025; }
        .alert-success { background: #E6F4EA; border: 1px solid #CEEAD6; color: #1E8E3E; }

        @media (max-width: 768px) { 
            .right-panel { display: none; } 
            .left-panel { padding: 30px 20px; }
            .signin-title { font-size: 26px; }
            .welcome-text { margin-bottom: 24px; }
            .form-input { padding: 12px 14px; font-size: 13px; }
            .button-group { flex-direction: column-reverse; }
            .btn-clear { width: 100%; }
            .btn-primary { width: 100%; }
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
                
                <h1 class="signin-title">Set New Password</h1>
                <p class="welcome-text">Please choose a strong password to protect your account.</p>

                <div id="alert-box" class="alert"></div>

                <form id="reset-password-form">
                    @csrf
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Min. 6 characters" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Repeat password" required>
                    </div>

                    <div class="button-group">
                        <button type="button" class="btn-clear" id="clear-btn">CLEAR</button>
                        <button type="submit" class="btn-primary" id="submit-btn">CHANGE PASSWORD</button>
                    </div>
                </form>

                <a href="{{ url('/sign-in') }}" style="display: block; text-align: center; margin-top: 24px; font-size: 13px; color: var(--color-text-light); text-decoration: none; font-weight: 500;">← Back to Sign In</a>
            </div>
        </div>

        <div class="right-panel">
            <lottie-player src="{{ asset('lottie/shopping.json') }}" background="transparent" speed="1" style="width: 85%; height: auto;" loop autoplay></lottie-player>
        </div>
    </div>

    <script>
        const form = document.getElementById('reset-password-form');
        const submitBtn = document.getElementById('submit-btn');
        const clearBtn = document.getElementById('clear-btn');
        const alertBox = document.getElementById('alert-box');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');

        clearBtn.addEventListener('click', () => {
            passwordInput.value = '';
            confirmInput.value = '';
            passwordInput.focus();
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (passwordInput.value !== confirmInput.value) {
                alertBox.className = 'alert alert-error';
                alertBox.innerText = 'Passwords do not match.';
                alertBox.style.display = 'block';
                return;
            }

            submitBtn.innerText = 'UPDATING...';
            submitBtn.disabled = true;

            try {
                const response = await fetch("{{ url('/forgot-password/reset') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        password: passwordInput.value,
                        password_confirmation: confirmInput.value
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alertBox.className = 'alert alert-success';
                    alertBox.innerText = data.message;
                    alertBox.style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = "{{ url('/sign-in') }}";
                    }, 2000);
                } else {
                    alertBox.className = 'alert alert-error';
                    alertBox.innerText = data.message;
                    alertBox.style.display = 'block';
                    submitBtn.innerText = 'CHANGE PASSWORD';
                    submitBtn.disabled = false;
                }
            } catch (error) {
                alertBox.className = 'alert alert-error';
                alertBox.innerText = 'An error occurred. Please try again.';
                alertBox.style.display = 'block';
                submitBtn.innerText = 'CHANGE PASSWORD';
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
