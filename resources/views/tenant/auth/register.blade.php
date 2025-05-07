<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Register for ') }} {{ $tenant->name }}</title>
    
    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.v3.site_key') }}"></script>
    <script>
        function executeRecaptcha() {
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('recaptcha.v3.site_key') }}', {action: 'register_user'})
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

        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            padding: 2.5rem;
            position: relative;
        }

        .tenant-badge {
            position: absolute;
            top: 0;
            right: 2rem;
            background: var(--copper);
            color: white;
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
            border: 3px solid var(--copper);
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
            border-color: var(--copper);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(160, 111, 77, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 2rem;
            top: 2.5rem;
            color: var(--copper);
        }

        .btn-register {
            width: calc(100% - 2rem);
            padding: 0.9rem;
            background: var(--copper);
            color: white;
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

        .btn-register:hover {
            background: var(--gold);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(160, 111, 77, 0.2);
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

        .success-message {
            margin: 0 1rem 1.5rem 1rem;
            padding: 1rem;
            background-color: #D1FAE5;
            border: 1px solid #A7F3D0;
            border-radius: 8px;
            color: #065F46;
        }

        @media (max-width: 480px) {
            .register-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .form-group,
            .btn-register {
                padding: 0 0.5rem;
            }

            .btn-register {
                width: calc(100% - 1rem);
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="tenant-badge">
            {{ $tenant->slug }}
        </div>
        
        <div class="logo-section">
            <img src="{{ asset('images/logo.jpg') }}" alt="{{ $tenant->name }} Logo">
        </div>

        <h1 class="portal-title">
          
            <span>Join the Pageant</span>
        </h1>

        @if(session('success'))
            <div class="success-message">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error-container">
                <div class="error-message">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('tenant.register', ['slug' => $tenant->slug]) }}" method="POST" id="register-form">
            @csrf
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <i class="fas fa-user input-icon"></i>
                <input type="text" class="form-input" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" class="form-input" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <i class="fas fa-phone input-icon"></i>
                <input type="tel" class="form-input" id="phone" name="phone" value="{{ old('phone') }}">
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>
    </div>
</body>
</html> 