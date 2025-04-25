<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Registration Successful') }}</title>
    
    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        .success-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            padding: 2.5rem;
            position: relative;
        }

        .success-badge {
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

        .success-icon {
            text-align: center;
            margin-bottom: 2rem;
        }

        .success-icon i {
            font-size: 4rem;
            color: var(--copper);
            background: rgba(160, 111, 77, 0.1);
            padding: 1.5rem;
            border-radius: 50%;
            margin-bottom: 1rem;
        }

        .success-title {
            text-align: center;
            color: var(--burgundy);
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .success-subtitle {
            text-align: center;
            color: var(--copper);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .temp-password {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px dashed var(--copper);
            margin: 1.5rem 0;
        }

        .password-label {
            color: var(--burgundy);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .password-value {
            font-family: monospace;
            font-size: 1.25rem;
            color: var(--copper);
            font-weight: 600;
            letter-spacing: 1px;
            margin: 1rem 0;
            padding: 0.75rem;
            background: white;
            border: 2px solid var(--copper);
            border-radius: 8px;
            text-align: center;
        }

        .password-note {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .alert-info {
            background-color: rgba(160, 111, 77, 0.1);
            border: 1px solid var(--copper);
            color: var(--burgundy);
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            display: flex;
            align-items: center;
        }

        .alert-info i {
            margin-right: 0.75rem;
            color: var(--copper);
        }

        .btn-login {
            display: inline-block;
            background: var(--copper);
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
            margin-top: 1.5rem;
        }

        .btn-login:hover {
            background: var(--gold);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(160, 111, 77, 0.2);
            color: white;
        }

        @media (max-width: 480px) {
            .success-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .success-title {
                font-size: 1.5rem;
            }

            .success-subtitle {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-badge">
            Registration Complete
        </div>
        
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>

        <h1 class="success-title">
            {{ __('Registration Successful!') }}
        </h1>

        <p class="success-subtitle">
            Welcome to {{ $tenant->slug }}!
        </p>

        @if($tempPassword)
            <div class="temp-password">
                <span class="password-label">Temporary Password</span>
                <p class="password-value">{{ $tempPassword }}</p>
                <p class="password-note">Please use this password to log in. You should change it after your first login.</p>
            </div>
            
            <div class="alert-info">
                <i class="fas fa-info-circle"></i>
                <span>Normally, this password would be sent to your email. It's displayed here for testing purposes.</span>
            </div>
        @else
            <div class="alert-info">
                <i class="fas fa-envelope"></i>
                <span>A temporary password has been sent to your email. Please check your inbox.</span>
            </div>
        @endif

        <a href="{{ url('/tenant/login') }}" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Log In Now
        </a>
    </div>
</body>
</html> 