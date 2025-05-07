<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glam Agency - Admin Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.v3.site_key') }}"></script>
    <script>
        function onSubmit(token) {
            document.getElementById("login-form").submit();
        }
        
        function executeRecaptcha() {
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('recaptcha.v3.site_key') }}', {action: 'login'})
                    .then(function(token) {
                        document.getElementById('g-recaptcha-response').value = token;
                    });
            });
        }
        
        // Execute recaptcha on page load
        window.onload = executeRecaptcha;
    </script>
    <style>
        :root {
            --burgundy: #3F081C;
            --copper: #A06F4D;
            --gold: #EDC07F;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Public Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--burgundy) 0%, #2a0513 100%);
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            position: relative;
        }

        .admin-badge {
            position: absolute;
            top: 0;
            right: 2rem;
            background: var(--gold);
            color: var(--burgundy);
            padding: 0.5rem 1.5rem;
            border-radius: 0 0 10px 10px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-section img {
            width: 150px;
            height: auto;
            border-radius: 50%;
            border: 3px solid var(--gold);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .portal-title {
            text-align: center;
            margin: 1.5rem 0;
            color: var(--burgundy);
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .portal-title span {
            display: block;
            font-size: 0.9rem;
            color: var(--copper);
            font-weight: 400;
            margin-top: 0.25rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
            padding: 0 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--burgundy);
            font-weight: 500;
        }

        .form-input {
            width: calc(100% - 3.5rem);
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--gold);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(237, 192, 127, 0.2);
        }

        .input-icon {
            position: absolute;
            left: 2rem;
            top: 2.5rem;
            color: var(--copper);
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: var(--burgundy);
            padding: 0 1rem;
        }

        .remember-me input {
            margin-right: 0.5rem;
            accent-color: var(--gold);
        }

        .btn-login {
            width: calc(100% - 2rem);
            padding: 0.9rem;
            background: var(--gold);
            color: var(--burgundy);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 1rem;
        }

        .btn-login:hover {
            background: var(--copper);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(160, 111, 77, 0.2);
        }

        .login-divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .login-divider span {
            background: white;
            padding: 0 1rem;
            font-size: 0.85rem;
            color: var(--burgundy);
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .login-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e1e1e1;
        }

        .btn-google {
            width: calc(100% - 2rem);
            padding: 0.75rem;
            background: white;
            color: #444;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .btn-google:hover {
            background: #f5f5f5;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .btn-google i {
            margin-right: 10px;
            font-size: 1.2rem;
            color: #4285F4;
        }

        .forgot-link {
            text-align: center;
            margin-top: 1.5rem;
            padding: 0 1rem;
        }

        .forgot-link a {
            color: var(--copper);
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .forgot-link a:hover {
            color: var(--gold);
            text-decoration: underline;
        }

        .error-container {
            margin: 0 1rem 1.5rem 1rem;
            padding: 1rem;
            background-color: #FFF5F5;
            border: 1px solid #FED7D7;
            border-radius: 8px;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
        }

        .error-message::before {
            content: '⚠';
            margin-right: 0.5rem;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .form-group,
            .btn-login,
            .remember-me,
            .forgot-link {
                padding: 0 0.5rem;
            }

            .btn-login {
                width: calc(100% - 1rem);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="admin-badge">
            Admin Portal
        </div>

        <div class="logo-section">
            <img src="{{ asset('images/logo.jpg') }}" alt="Glam Agency Logo">
        </div>

        <h1 class="portal-title">
          
            <span>Administrator Access</span>
        </h1>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" id="login-form">
            @csrf
            
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            
            @if($errors->any())
                <div class="error-container">
                    <div class="error-message">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <i class="fas fa-envelope input-icon"></i>
                <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                <x-input-error :messages="$errors->get('email')" class="error-message" />
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <i class="fas fa-lock input-icon"></i>
                <input id="password" class="form-input" type="password" name="password" required autocomplete="current-password">
                <x-input-error :messages="$errors->get('password')" class="error-message" />
            </div>

            <div class="remember-me">
                <input id="remember_me" type="checkbox" name="remember">
                <label for="remember_me">Keep me signed in</label>
            </div>

            <button type="submit" class="btn-login">
                Admin Login
            </button>

            <div class="login-divider">
                <span>OR</span>
            </div>

            <a href="{{ route('admin.google.redirect') }}" class="btn-google">
                <i class="fab fa-google"></i> Continue with Google
            </a>

            @if (Route::has('password.request'))
                <div class="forgot-link">
                    <a href="{{ route('password.request') }}">
                        <i class="fas fa-key"></i> Forgot your password?
                    </a>
                </div>
            @endif
        </form>
    </div>
</body>
</html>
