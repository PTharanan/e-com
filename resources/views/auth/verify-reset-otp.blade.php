<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Verify OTP - E-Commerce Shopping Platform">

    <title>Verify OTP - {{ config('app.name', 'E-Commerce') }}</title>
    
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
        }

        .container { display: flex; min-height: 100vh; min-height: 100dvh; width: 100%; overflow: hidden; overflow-y: auto; }
        .left-panel { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px; background: var(--color-white); }
        .form-wrapper { width: 100%; max-width: 420px; text-align: center; }
        
        .signin-title { font-size: 32px; font-weight: 700; color: var(--color-primary-hover); margin-bottom: 12px; }
        .welcome-text { font-size: 14px; color: var(--color-text-light); margin-bottom: 36px; }

        .otp-container { display: flex; justify-content: center; gap: 12px; margin-bottom: 32px; }
        .otp-input {
            width: 50px; height: 60px; font-size: 24px; font-weight: 600; text-align: center;
            border: 1.5px solid var(--color-border); border-radius: 8px; outline: none; transition: 0.2s;
        }
        .otp-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(242, 92, 59, 0.1); }

        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 14px 36px; font-size: 14px; font-weight: 600; letter-spacing: 1.5px;
            text-transform: uppercase; color: var(--color-white);
            background: linear-gradient(135deg, var(--color-primary), #E8553A);
            border: none; border-radius: var(--radius-pill); cursor: pointer;
            width: 100%; box-shadow: 0 4px 16px rgba(242, 92, 59, 0.35);
        }

        .resend-text { margin-top: 24px; font-size: 13px; color: var(--color-text-light); }
        .resend-link { color: var(--color-primary); text-decoration: none; font-weight: 600; cursor: pointer; }

        .right-panel { width: 50%; background: var(--color-bg-right); display: flex; align-items: center; justify-content: center; }
        .alert { padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 13px; display: none; text-align: left; }
        .alert-error { background: #FFF0EE; border: 1px solid #FFD4CC; color: #D93025; }

        @media (max-width: 768px) { 
            .right-panel { display: none; } 
            .left-panel { padding: 30px 20px; }
            .signin-title { font-size: 26px; }
            .welcome-text { margin-bottom: 24px; }
            .otp-input { width: 42px; height: 55px; font-size: 20px; }
            .otp-container { gap: 8px; }
        }

        @media (max-width: 360px) {
            .otp-input { width: 38px; height: 50px; font-size: 18px; }
            .otp-container { gap: 6px; }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="left-panel">
            <div class="form-wrapper">
                <div style="width: 100px; height: 100px; background: var(--color-bg-right); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <path d="M9 12l2 2 4-4"></path>
                    </svg>
                </div>
                
                <h1 class="signin-title">Verify OTP</h1>
                <p class="welcome-text">We've sent a 6-digit code to your email. Please enter it below.</p>

                <div id="alert-box" class="alert alert-error"></div>
                <div id="success-box" class="alert alert-success" style="display:none; background: #E6F4EA; border: 1px solid #CEEAD6; color: #1E8E3E; margin-bottom: 20px; padding: 12px 16px; border-radius: 6px; font-size: 13px; text-align: left;"></div>

                <form id="verify-otp-form">
                    <div class="otp-container">
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required autofocus>
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                    </div>

                    <button type="submit" class="btn-primary" id="submit-btn">VERIFY CODE</button>
                </form>

                <p class="resend-text" id="resend-wrapper">
                    Didn't receive code? <span class="resend-link" id="resend-btn">Resend OTP</span>
                </p>
                <p id="timer-text" style="margin-top: 24px; font-size: 13px; color: var(--color-text-light); display: none;">
                    Resend available in <span id="timer-seconds">60</span>s
                </p>
                <a href="{{ url('/forgot-password') }}" style="display: block; margin-top: 16px; font-size: 12px; color: var(--color-text-light); text-decoration: none;">Change Email</a>
            </div>
        </div>

        <div class="right-panel">
            <lottie-player src="{{ asset('lottie/shopping.json') }}" background="transparent" speed="1" style="width: 85%; height: auto;" loop autoplay></lottie-player>
        </div>
    </div>

    <script>
        const otpInputs = document.querySelectorAll('.otp-input');
        const form = document.getElementById('verify-otp-form');
        const alertBox = document.getElementById('alert-box');
        const successBox = document.getElementById('success-box');
        const resendBtn = document.getElementById('resend-btn');
        const resendWrapper = document.getElementById('resend-wrapper');
        const timerText = document.getElementById('timer-text');
        const timerSeconds = document.getElementById('timer-seconds');

        let countdown = 60;
        let timerInterval;

        function startTimer() {
            countdown = 60;
            resendWrapper.style.display = 'none';
            timerText.style.display = 'block';
            timerSeconds.innerText = countdown;

            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                countdown--;
                timerSeconds.innerText = countdown;
                if (countdown <= 0) {
                    clearInterval(timerInterval);
                    resendWrapper.style.display = 'block';
                    timerText.style.display = 'none';
                    
                    // Notify user that the OTP has expired
                    alertBox.innerText = 'OTP has expired. Please request a new one.';
                    alertBox.style.display = 'block';
                }
            }, 1000);
        }

        // Start timer on load
        startTimer();

        // OTP Input navigation
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const otp = Array.from(otpInputs).map(i => i.value).join('');
            
            if (otp.length !== 6) return;

            alertBox.style.display = 'none';
            successBox.style.display = 'none';

            try {
                const response = await fetch("{{ url('/forgot-password/verify') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ otp })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = "{{ url('/forgot-password/reset') }}";
                } else {
                    alertBox.innerText = data.message;
                    alertBox.style.display = 'block';
                }
            } catch (error) {
                alertBox.innerText = 'Verification failed. Try again.';
                alertBox.style.display = 'block';
            }
        });

        resendBtn.addEventListener('click', async () => {
            alertBox.style.display = 'none';
            successBox.style.display = 'none';

            try {
                const response = await fetch("{{ url('/forgot-password/resend-otp') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    successBox.innerText = data.message;
                    successBox.style.display = 'block';
                    startTimer();
                } else {
                    alertBox.innerText = data.message;
                    alertBox.style.display = 'block';
                }
            } catch (error) {
                alertBox.innerText = 'Failed to resend OTP.';
                alertBox.style.display = 'block';
            }
        });
    </script>
</body>
</html>
