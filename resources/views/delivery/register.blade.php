<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delivery Partner Sign Up - E-Shop</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lottie Player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

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
            -webkit-font-smoothing: antialiased;
        }

        :root {
            --color-primary: #F25C3B;
            --color-primary-hover: #E04A2A;
            --color-bg-right: #FDEEE4;
            --color-text-dark: #1A1A1A;
            --color-text-medium: #555555;
            --color-text-light: #888888;
            --color-border: #E0E0E0;
            --color-border-focus: #F25C3B;
            --color-input-bg: #FFFFFF;
            --color-white: #FFFFFF;
            --shadow-input: 0 2px 8px rgba(0, 0, 0, 0.04);
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-pill: 50px;
            --transition-fast: 0.2s ease;
            --transition-normal: 0.35s ease;
        }

        .login-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
        }

        /* Left Panel - Animation (White BG) */
        .login-left {
            width: 50%;
            min-width: 420px;
            background: var(--color-white);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            position: relative;
            overflow: hidden;
            padding: 80px 40px 40px;
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

        .illustration-wrapper {
            position: absolute;
            z-index: 1;
            width: 100%;
            max-width: 600px;
            left: 0;
            bottom: 2%;
            animation: floatIn 1s ease-out;
        }

        /* Right Panel - Form (Colored BG) */
        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: var(--color-bg-right);
            position: relative;
        }

        .login-right::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(253, 238, 228, 0.3) 100%);
            pointer-events: none;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            animation: floatIn 0.8s ease-out;
        }

        .logo-text {
            font-size: 26px;
            font-weight: 800;
            color: var(--color-primary);
            margin-bottom: 15px;
        }

        .signin-title {
            font-size: 30px;
            font-weight: 700;
            color: var(--color-primary-hover);
            margin-bottom: 5px;
            line-height: 1.2;
        }

        .welcome-text {
            font-size: 14px;
            color: var(--color-text-medium);
            margin-bottom: 25px;
        }

        /* Form Steps */
        .form-step {
            display: none;
            flex-direction: column;
            gap: 15px;
            animation: slideInForm 0.4s ease forwards;
        }

        .form-step.active {
            display: flex;
        }
        
        @keyframes slideInForm {
            0% { opacity: 0; transform: translateX(10px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        .login-form {
            position: relative;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-dark);
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: var(--color-text-dark);
            background: var(--color-input-bg);
            border: 1.5px solid var(--color-border);
            border-radius: var(--radius-sm);
            outline: none;
            transition: var(--transition-fast);
            box-shadow: var(--shadow-input);
        }

        .form-input:focus {
            border-color: var(--color-border-focus);
            box-shadow: 0 0 0 3px rgba(242, 92, 59, 0.1), var(--shadow-input);
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

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px;
        }

        .remember-me-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--color-text-medium);
            cursor: pointer;
        }

        .remember-me-label input[type="checkbox"] {
            accent-color: var(--color-primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .btn-signin {
            margin-top: 10px;
            padding: 14px 36px;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: var(--color-white);
            background: linear-gradient(135deg, var(--color-primary), #E8553A);
            border: none;
            border-radius: var(--radius-pill);
            cursor: pointer;
            transition: var(--transition-normal);
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 16px rgba(242, 92, 59, 0.35);
        }

        .btn-signin:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-signin:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--color-primary-hover), #D14328);
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(242, 92, 59, 0.45);
        }

        .signup-text {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: var(--color-text-medium);
        }
        .signup-text a {
            color: var(--color-primary);
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
        }
        .signup-text a:hover {
            text-decoration: underline;
        }

        @keyframes floatIn {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .desktop-logo {
            display: block;
        }
        
        .mobile-logo {
            display: none;
        }

        /* OTP Inputs */
        .otp-inputs-container {
            display: flex;
            gap: 12px;
            justify-content: center;
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

        /* Toast Notification */
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

        .toast.success {
            border-left-color: #28a745;
        }

        .toast.error {
            border-left-color: #dc3545;
        }

        @keyframes slideInToast {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        @media (max-width: 900px) {
            .login-left {
                display: none;
            }
            .login-right {
                width: 100%;
                padding: 30px 20px;
            }
            .login-form-wrapper {
                padding: 20px;
            }
            
            .desktop-logo {
                display: none;
            }
            .mobile-logo {
                display: block;
                margin-left: -45px !important;
                margin-bottom: -15px !important;
            }
            
            .mobile-logo lottie-player {
                width: 150px !important;
            }
            .otp-input {
                width: 40px;
                height: 50px;
                font-size: 20px;
                gap: 8px;
            }
        }
    </style>
</head>

<body>
    <div id="toast-container"></div>
    <div class="login-container">
        {{-- Left Panel - Animation (White Background) --}}
        <div class="login-left">
            <h2 style="margin-top: 0; margin-bottom: 5px; color: var(--color-text-dark); font-weight: 800; font-size: 32px; text-align: center;">Join Our Fleet</h2>
            <p style="color: var(--color-text-medium); font-size: 15px; margin-top: 0; margin-bottom: 20px; text-align: center;">Start earning by delivering smiles today.</p>
            <div class="illustration-wrapper">
                <lottie-player src="{{ asset('lottie/delivery.json') }}" background="transparent" speed="1" loop autoplay></lottie-player>
            </div>
        </div>

        {{-- Right Panel - Form (Colored Background) --}}
        <div class="login-right">
            <div class="login-form-wrapper">
                {{-- Desktop Logo --}}
                <div class="desktop-logo logo-text" style="text-align: center; margin-bottom: 20px; font-size: 26px; font-weight: 800;">
                    <span style="color: var(--color-text-dark);">E-</span><span style="color: var(--color-primary);">Shop</span>
                </div>

                {{-- Mobile Logo Animation --}}
                <div class="mobile-logo logo-animation" id="login-logo" style="margin-left: -55px;">
                    <lottie-player src="{{ asset('lottie/shop-cart-kdp.json') }}" background="transparent" speed="1" style="width: 200px; height: auto;" loop autoplay></lottie-player>
                </div>
                
                <h1 class="signin-title" style="text-align: center;">Partner Sign Up</h1>
                <p class="welcome-text" style="text-align: center; margin-bottom: 25px;">Create your account to join our team.</p>

                <form class="login-form" id="delivery-register-form">
                    @csrf
                    
                    {{-- STEP 1: Registration Form --}}
                    <div class="form-step active" id="step-1">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-input" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-input" placeholder="Enter your email address" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="password" class="form-input" placeholder="Enter your password" required>
                                <button type="button" class="password-toggle" id="togglePassword">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" placeholder="Confirm your password" required>
                                <button type="button" class="password-toggle" id="togglePasswordConfirm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="remember-me-label">
                                <input type="checkbox" name="terms" required>
                                <a href="https://roth-compensators.com/en/delivery-conditions.pdf">
                                    <span>I agree to the Terms & Conditions</span>
                                </a>
                            </label>
                        </div>

                        <button type="submit" class="btn-signin" id="btn-submit">
                            JOIN NOW
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </div>

                    {{-- STEP 2: OTP Verification --}}
                    <div class="form-step" id="step-2">
                        <h3 style="font-size: 18px; margin-bottom: 8px; text-align: center; color: var(--color-primary-hover)">Verify Your Email</h3>
                        <p style="font-size: 13px; color: var(--color-text-medium); margin-bottom: 16px; text-align: center;">
                            We've sent a 6-digit OTP to your email. It expires in <span id="otp-timer" style="font-weight:bold;color:var(--color-primary)">60</span>s.
                        </p>
                        
                        <div class="form-group">
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

                        <button type="button" class="btn-signin" id="btn-verify-otp">
                            VERIFY OTP
                        </button>
                        <button type="button" class="btn-signin" id="btn-resend-otp" style="display: none; background: linear-gradient(135deg, #666, #444); margin-top: 10px; box-shadow: none;">
                            RESEND OTP
                        </button>
                    </div>
                    
                    <div class="signup-text">
                        Already have an account? <a href="{{ route('delivery.login') }}">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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

        const form = document.getElementById('delivery-register-form');
        const btnSubmit = document.getElementById('btn-submit');
        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');
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

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            
            if (document.getElementById('password').value !== document.getElementById('password_confirmation').value) {
                showNotify("Passwords do not match");
                return;
            }
            
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = "SENDING...";
            btnSubmit.disabled = true;

            const formData = new FormData(form);
            
            fetch("{{ route('delivery.register.post') }}", {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
                if (data.success) {
                    step1.classList.remove('active');
                    step2.classList.add('active');
                    startTimer(60);
                } else {
                    if(data.errors) {
                        showNotify(Object.values(data.errors)[0][0]);
                    } else {
                        showNotify(data.message || 'Error occurred');
                    }
                }
            }).catch(e => {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
                showNotify('Network error or server unreachable');
            });
        });

        // OTP Boxes Input Handling
        const otpInputs = document.querySelectorAll('.otp-input');
        const hiddenOtp = document.getElementById('otp');
        
        function updateHiddenOtp() {
            let val = '';
            otpInputs.forEach(inp => val += inp.value);
            hiddenOtp.value = val;
        }
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value !== '' && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                updateHiddenOtp();
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
            
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                if (pastedData) {
                    for (let i = 0; i < pastedData.length; i++) {
                        if (otpInputs[i]) otpInputs[i].value = pastedData[i];
                    }
                    otpInputs[Math.min(pastedData.length, 5)].focus();
                    updateHiddenOtp();
                }
            });
        });

        // Verify OTP
        document.getElementById('btn-verify-otp').addEventListener('click', function() {
            const otpValue = hiddenOtp.value;
            if(otpValue.length !== 6) {
                showNotify("Please enter a valid 6-digit OTP");
                return;
            }
            
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = "VERIFYING...";
            btn.disabled = true;

            const formData = new FormData();
            formData.append('otp', otpValue);
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            fetch("{{ route('delivery.verify-otp.post') }}", {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                if (data.success) {
                    showNotify("Registration successful!", 'success');
                    setTimeout(() => window.location.href = data.redirect, 1500);
                } else {
                    showNotify(data.message);
                }
            }).catch(e => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showNotify('Error verifying OTP');
            });
        });

        // Resend OTP
        document.getElementById('btn-resend-otp').addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = "SENDING...";
            btn.disabled = true;
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            fetch("{{ route('delivery.resend-otp.post') }}", {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                if (data.success) {
                    showNotify("New OTP sent!", 'success');
                    startTimer(60);
                } else {
                    showNotify(data.message);
                }
            }).catch(e => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showNotify('Error resending OTP');
            });
        });
        // Password Visibility Toggle
        function togglePasswordVisibility(toggleBtnId, passwordInputId) {
            const toggleBtn = document.getElementById(toggleBtnId);
            const passwordInput = document.getElementById(passwordInputId);

            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    if (type === 'password') {
                        this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>`;
                    } else {
                        this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>`;
                    }
                });
            }
        }

        togglePasswordVisibility('togglePassword', 'password');
        togglePasswordVisibility('togglePasswordConfirm', 'password_confirmation');
    </script>
</body>
</html>
