<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Cache control headers to prevent back button caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <title>Eurotaxisystem - Login &amp; Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        // Prevent back button caching
        (function() {
            window.onpageshow = function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            };
            
            // Clear history when coming from authenticated pages
            if (performance.navigation.type === 2) {
                window.location.replace(window.location.href);
            }
        })();
    </script>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .flip-container {
            perspective: 1500px;
            width: 100%;
            height: 100vh;
        }

        .flipper {
            position: relative;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 0.8s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .flipper.state-login {
            transform: rotateY(0deg);
        }

        .flipper.state-register {
            transform: rotateY(180deg);
        }

        .flipper.state-forgot {
            transform: rotateY(360deg);
        }

        .flip-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .front-face {
            z-index: 2;
        }

        .back-face {
            transform: rotateY(180deg);
        }

        .forgot-face {
            transform: rotateY(180deg);
        }

        .form-wrapper {
            width: 100%;
            max-width: 280px;
            padding: 0.875rem;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .form-panel {
            width: 100%;
            display: none;
        }

        .login-panel {
            display: block;
        }

        .flipper.state-login .login-panel,
        .flipper.state-register .back-face .register-panel,
        .flipper.state-forgot .forgot-panel {
            display: block;
        }

        .flipper.state-login .forgot-panel,
        .flipper.state-forgot .login-panel {
            display: none;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            width: 100%;
        }

        .mb-4 button {
            display: flex !important;
            align-items: center;
            padding: 0.5rem 1rem;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            color: #6b7280;
            transition: color 0.3s ease;
            margin-bottom: 1rem;
            z-index: 10;
            position: relative;
        }

        .mb-4 button:hover {
            color: #2563eb !important;
        }

        .form-panel .mb-4 {
            display: block !important;
            visibility: visible !important;
        }

        .form-panel button {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .input-group {
            position: relative;
            margin-bottom: 0.75rem;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 0.625rem 0.625rem 0.625rem 2.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background: white;
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .input-group i {
            position: absolute;
            left: 0.625rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 0.875rem;
        }

        /* ---- Password field with built-in eye toggle ---- */
        .pw-group {
            display: flex;
            align-items: center;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            background: white;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 0.75rem;
        }

        .pw-group:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .pw-group .pw-icon {
            flex-shrink: 0;
            padding: 0 0.4rem 0 0.625rem;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .pw-group input {
            flex: 1;
            min-width: 0;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0.625rem 0.25rem !important;
            font-size: 0.875rem;
        }

        .toggle-password {
            flex-shrink: 0;
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 0.875rem;
            padding: 0 0.625rem;
            display: flex;
            align-items: center;
            align-self: stretch;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: #3b82f6;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            width: 100%;
            padding: 0.625rem;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 0.75rem;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
        }

        .forgot-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .forgot-option {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .forgot-option:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .forgot-option.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .otp-inputs {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .resend-btn {
            background: #6b7280;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .resend-btn:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .resend-btn:not(:disabled):hover {
            background: #4b5563;
        }

        .message-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .message-toast.show {
            transform: translateX(0);
        }

        .message-toast.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .message-toast.error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .frosted-overlay {
            background: rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        .text-shadow-enhanced {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .text-shadow-light {
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        @keyframes logoBounce {
            0% {
                transform: translateY(-50px);
                opacity: 0;
            }

            50% {
                transform: translateY(10px);
                opacity: 1;
            }

            65% {
                transform: translateY(-5px);
            }

            80% {
                transform: translateY(3px);
            }

            95% {
                transform: translateY(-1px);
            }

            100% {
                transform: translateY(0);
            }
        }

        .logo-bounce {
            animation: logoBounce 1.5s ease-out;
        }

        @keyframes iconSlideIn1 {
            0% {
                transform: translateX(-100px);
                opacity: 0;
            }

            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes iconSlideIn2 {
            0% {
                transform: translateY(50px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes iconSlideIn3 {
            0% {
                transform: translateX(100px);
                opacity: 0;
            }

            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .icon-animate-1 {
            animation: iconSlideIn1 0.8s ease-out 0.5s both;
        }

        .icon-animate-2 {
            animation: iconSlideIn2 0.8s ease-out 0.7s both;
        }

        .icon-animate-3 {
            animation: iconSlideIn3 0.8s ease-out 0.9s both;
        }

        .logo-container {
            max-width: 400px;
            width: 100%;
        }

        .logo-image {
            width: 100%;
            height: auto;
            max-height: 320px;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3));
        }

        @media (max-width: 768px) {
            .split-layout {
                flex-direction: column;
            }

            .left-side {
                height: 40vh;
            }

            .right-side {
                height: 60vh;
            }

            .logo-container {
                max-width: 260px;
            }

            .logo-image {
                max-height: 260px;
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    </style>
</head>

<body>
    <!-- Message Toast -->
    <div id="messageToast" class="message-toast"></div>

    <!-- Main Split Layout -->
    <div class="split-layout flex h-screen">

        <!-- Left Side - Static Image -->
        <div class="left-side w-full md:w-1/2 h-full relative overflow-hidden">
            <img src="{{ asset('uploads/1000053201.jpg') }}" alt="Eurotaxisystem" class="w-full h-full object-cover">

            <!-- Overlay Content -->
            <div class="absolute inset-0 frosted-overlay flex flex-col">
                <!-- Logo at top -->
                <div class="text-center px-8 pt-16">
                    <div class="logo-container mx-auto logo-bounce">
                        <img src="{{ asset('uploads/logo.png') }}" alt="Eurotaxisystem Logo" class="logo-image">
                    </div>
                </div>

                <!-- Icons -->
                <div class="flex-1 flex items-start justify-center px-8 pt-16">
                    <div class="flex justify-center gap-8">
                        <div class="text-center icon-animate-1">
                            <i class="fas fa-users text-3xl mb-2 text-white text-shadow-light"></i>
                            <p class="text-sm text-white text-shadow-light">200+ Drivers</p>
                        </div>
                        <div class="text-center icon-animate-2">
                            <i class="fas fa-route text-3xl mb-2 text-white text-shadow-light"></i>
                            <p class="text-sm text-white text-shadow-light">10K+ Trips</p>
                        </div>
                        <div class="text-center icon-animate-3">
                            <i class="fas fa-car text-3xl mb-2 text-white text-shadow-light"></i>
                            <p class="text-sm text-white text-shadow-light">94 Units</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - 3D Flip Forms -->
        <div class="right-side w-full md:w-1/2 h-full bg-gray-50">
            <div class="flip-container">
                <div class="flipper state-login" id="flipper">

                    <!-- Front Face - Login & Forgot -->
                    <div class="flip-face front-face">
                        <div class="form-wrapper">

                            <!-- Login Panel -->
                            <div class="form-panel login-panel">
                                <div class="text-center mb-8">
                                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back</h2>
                                    <p class="text-gray-600">Sign in to your account</p>
                                </div>

                                @if($errors->any())
                                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                @if(session('info'))
                                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 text-sm animate-pulse">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        {{ session('info') }}
                                    </div>
                                @endif

                                @if(session('success'))
                                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        {{ session('success') }}
                                    </div>
                                @endif

                                <form id="loginForm" method="POST" action="{{ route('login.submit') }}">
                                    @csrf
                                    <div class="input-group">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" name="email" id="loginEmail" placeholder="Email address"
                                            value="{{ old('email') }}" required>
                                    </div>

                                    <div class="pw-group">
                                        <i class="fas fa-lock pw-icon"></i>
                                        <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                                        <button type="button" class="toggle-password" onclick="togglePassword('loginPassword', this)" tabindex="-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>

                                    <div class="flex items-center justify-between mb-6">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="remember" id="remember" class="mr-2">
                                            <span class="text-gray-600">Remember me</span>
                                        </label>
                                        <button type="button" onclick="setState('forgot')"
                                            class="text-blue-600 hover:underline text-sm">Forgot password?</button>
                                    </div>

                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-sign-in-alt mr-2"></i>
                                        Sign In
                                    </button>
                                </form>

                                <div class="text-center mt-8">
                                    <p class="text-gray-600">
                                        Don't have an account?
                                        <button type="button" onclick="setState('register')"
                                            class="text-blue-600 font-semibold hover:underline">
                                            Create Account
                                        </button>
                                    </p>
                                </div>
                            </div>

                            <!-- Forgot Password Panel -->
                            <div class="form-panel forgot-panel">
                                <div class="mb-4" id="forgotBackButton" style="display:none;">
                                    <button type="button" onclick="backToRecoveryOptions()"
                                        class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                                        <i class="fas fa-arrow-left mr-2"></i> Back
                                    </button>
                                </div>

                                <div class="text-center mb-8">
                                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Reset Password</h2>
                                    <p class="text-gray-600">Choose your recovery method</p>
                                </div>

                                <div id="recoveryOptions" class="forgot-options">
                                    <div class="forgot-option" onclick="selectRecoveryMethod('email')">
                                        <i class="fas fa-envelope text-2xl text-blue-600 mb-2"></i>
                                        <p class="text-sm font-semibold">Email Verification</p>
                                    </div>
                                    <div class="forgot-option" onclick="selectRecoveryMethod('phone')">
                                        <i class="fas fa-mobile-alt text-2xl text-green-600 mb-2"></i>
                                        <p class="text-sm font-semibold">Phone OTP</p>
                                    </div>
                                </div>

                                <form id="emailResetForm" style="display:none;">
                                    @csrf
                                    <div class="input-group">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" id="resetEmail" placeholder="Enter your email address"
                                            required>
                                    </div>
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-paper-plane mr-2"></i> Send Verification Link
                                    </button>
                                </form>

                                <form id="phoneResetForm" style="display:none;">
                                    @csrf
                                    <div class="input-group">
                                        <i class="fas fa-phone"></i>
                                        <input type="tel" id="resetPhone" placeholder="Enter your phone number"
                                            required>
                                    </div>
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-paper-plane mr-2"></i> Send OTP
                                    </button>
                                </form>

                                <div id="otpSection" style="display:none;">
                                    <div class="mb-4">
                                        <button type="button" onclick="backToRecoveryOptions()"
                                            class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                                            <i class="fas fa-arrow-left mr-2"></i> Back
                                        </button>
                                    </div>
                                    <p class="text-center text-gray-600 mb-4">Enter 6-digit code sent to your phone</p>
                                    <div class="otp-inputs">
                                        @for($i = 0; $i < 6; $i++)
                                            <input type="text" class="otp-input" maxlength="1" data-index="{{ $i }}">
                                        @endfor
                                    </div>
                                    <button type="button" onclick="verifyOTP()" class="btn-primary mb-4">
                                        <i class="fas fa-check mr-2"></i> Verify OTP
                                    </button>
                                    <div class="text-center">
                                        <button type="button" id="resendBtn" class="resend-btn" onclick="resendOTP()"
                                            disabled>
                                            Resend OTP (<span id="countdown">120</span>s)
                                        </button>
                                    </div>
                                </div>

                                <form id="newPasswordForm" style="display:none;">
                                    @csrf
                                    <div class="mb-4">
                                        <button type="button" onclick="backToRecoveryOptions()"
                                            class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                                            <i class="fas fa-arrow-left mr-2"></i> Back
                                        </button>
                                    </div>
                                    <p class="text-center text-green-600 mb-4">✓ Verification successful! Set your new
                                        password.</p>
                                    <div class="input-group">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" id="newPassword" placeholder="New password" required>
                                    </div>
                                    <div class="input-group">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" id="confirmNewPassword"
                                            placeholder="Confirm new password" required>
                                    </div>
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-save mr-2"></i> Update Password
                                    </button>
                                </form>

                                <div class="text-center mt-8">
                                    <p class="text-gray-600">
                                        Remember your password?
                                        <button type="button" onclick="setState('login')"
                                            class="text-blue-600 font-semibold hover:underline">
                                            Back to Login
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Face - Register -->
                    <div class="flip-face back-face">
                        <div class="form-wrapper">
                            <div class="form-panel register-panel">
                                <div class="text-center mb-6">
                                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Create Account</h2>
                                    <p class="text-gray-600">Join Eurotaxisystem today</p>
                                </div>

                                <form id="registerForm" method="POST" action="{{ route('register') }}">
                                    @csrf
                                    <div class="grid mb-4">
                                        <div class="input-group">
                                            <i class="fas fa-user"></i>
                                            <input type="text" name="first_name" id="firstName" placeholder="First name"
                                                required>
                                        </div>
                                        <div class="input-group">
                                            <i class="fas fa-user"></i>
                                            <input type="text" name="last_name" id="lastName" placeholder="Last name"
                                                required>
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <i class="fas fa-user-tag"></i>
                                        <select name="role" id="regRole" required>
                                            <option value="">Select Role</option>
                                            <option value="staff">Staff</option>
                                            <option value="secretary">Secretary</option>
                                            <option value="manager">Manager</option>
                                            <option value="dispatcher">Dispatcher</option>
                                        </select>
                                    </div>


                                    <div class="input-group">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" name="email" id="regEmail" placeholder="Email address"
                                            required>
                                    </div>

                                    <div class="pw-group">
                                        <i class="fas fa-lock pw-icon"></i>
                                        <input type="password" name="password" id="regPassword" placeholder="Password" required>
                                        <button type="button" class="toggle-password" onclick="togglePassword('regPassword', this)" tabindex="-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>

                                    <div class="pw-group">
                                        <i class="fas fa-lock pw-icon"></i>
                                        <input type="password" name="password_confirmation" id="regPasswordConfirm" placeholder="Confirm password" required>
                                        <button type="button" class="toggle-password" onclick="togglePassword('regPasswordConfirm', this)" tabindex="-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>

                                    <button type="submit" class="btn-secondary">
                                        <i class="fas fa-user-plus mr-2"></i> Create Account
                                    </button>
                                </form>
                                
                                <div class="text-center mt-6">
                                    <p class="text-gray-600">
                                        Already have an account?
                                        <button type="button" onclick="setState('login')"
                                            class="text-blue-600 font-semibold hover:underline">
                                            Sign In
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        let currentState = 'login';

        // Remember Me functionality
        function saveRememberMe() {
            const rememberCheckbox = document.getElementById('remember');
            const emailInput = document.getElementById('loginEmail');
            
            if (rememberCheckbox.checked && emailInput.value) {
                localStorage.setItem('rememberedEmail', emailInput.value);
                localStorage.setItem('rememberMeChecked', 'true');
            } else {
                localStorage.removeItem('rememberedEmail');
                localStorage.removeItem('rememberMeChecked');
            }
        }

        function loadRememberMe() {
            const rememberedEmail = localStorage.getItem('rememberedEmail');
            const rememberMeChecked = localStorage.getItem('rememberMeChecked');
            const emailInput = document.getElementById('loginEmail');
            const rememberCheckbox = document.getElementById('remember');
            
            if (rememberedEmail && rememberMeChecked === 'true') {
                emailInput.value = rememberedEmail;
                rememberCheckbox.checked = true;
            }
        }

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function setState(state) {
            currentState = state;
            const flipper = document.getElementById('flipper');
            flipper.className = 'flipper state-' + state;
            lucide && lucide.createIcons && lucide.createIcons();
        }

        function goBack() {
            setState('login');
        }

        function selectRecoveryMethod(method) {
            document.getElementById('recoveryOptions').style.display = 'none';
            document.getElementById('forgotBackButton').style.display = 'block';
            if (method === 'email') {
                document.getElementById('emailResetForm').style.display = 'block';
                document.getElementById('phoneResetForm').style.display = 'none';
            } else {
                document.getElementById('phoneResetForm').style.display = 'block';
                document.getElementById('emailResetForm').style.display = 'none';
            }
        }

        function backToRecoveryOptions() {
            document.getElementById('recoveryOptions').style.display = 'flex';
            document.getElementById('forgotBackButton').style.display = 'none';
            document.getElementById('emailResetForm').style.display = 'none';
            document.getElementById('phoneResetForm').style.display = 'none';
            document.getElementById('otpSection').style.display = 'none';
            document.getElementById('newPasswordForm').style.display = 'none';
        }

        function verifyOTP() {
            const inputs = document.querySelectorAll('.otp-input');
            let otp = '';
            inputs.forEach(i => otp += i.value);
            if (otp.length === 6) {
                document.getElementById('otpSection').style.display = 'none';
                document.getElementById('newPasswordForm').style.display = 'block';
            } else {
                showToast('Please enter the 6-digit OTP', 'error');
            }
        }

        let countdownInterval;
        function startCountdown() {
            let seconds = 120;
            const btn = document.getElementById('resendBtn');
            const span = document.getElementById('countdown');
            btn.disabled = true;
            countdownInterval = setInterval(() => {
                seconds--;
                span.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    btn.disabled = false;
                    span.textContent = '0';
                }
            }, 1000);
        }

        function resendOTP() {
            showToast('OTP resent!', 'success');
            startCountdown();
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('messageToast');
            toast.textContent = message;
            toast.className = 'message-toast ' + type + ' show';
            setTimeout(() => { toast.classList.remove('show'); }, 3000);
        }

        // Update username preview based on role and first name
        function updateUsernamePreview() {
            const role = document.getElementById('regRole').value;
            const firstName = document.getElementById('firstName').value;
            const preview = document.getElementById('usernamePreview');
            
            if (role && firstName) {
                const cleanFirstName = firstName.toLowerCase().replace(/\s+/g, '');
                preview.value = role + '-' + cleanFirstName;
            } else {
                preview.value = '';
            }
        }

        // OTP input auto-advance
        document.querySelectorAll('.otp-input').forEach((input, index) => {
            input.addEventListener('input', function () {
                if (this.value.length === 1) {
                    const next = document.querySelector(`.otp-input[data-index="${index + 1}"]`);
                    if (next) next.focus();
                }
            });
        });

        // Remember Me event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Load remembered email on page load
            loadRememberMe();
            
            // Add event listeners for remember me functionality
            const rememberCheckbox = document.getElementById('remember');
            const emailInput = document.getElementById('loginEmail');
            const loginForm = document.getElementById('loginForm');
            
            // Add event listener for first name to update username preview
            const firstNameInput = document.getElementById('firstName');
            if (firstNameInput) {
                firstNameInput.addEventListener('input', updateUsernamePreview);
            }
            
            if (rememberCheckbox) {
                rememberCheckbox.addEventListener('change', saveRememberMe);
            }
            
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    if (rememberCheckbox && rememberCheckbox.checked) {
                        saveRememberMe();
                    }
                });
            }
            
            if (loginForm) {
                loginForm.addEventListener('submit', saveRememberMe);
            }
        });

        // Show error if any from Laravel session
        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
    </script>
</body>

</html>