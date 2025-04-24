<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Registration Successful') }}</title>
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .success-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #28a745;
            border-bottom: none;
            padding: 20px;
            text-align: center;
            color: white;
        }
        .card-body {
            padding: 30px;
        }
        .temp-password {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px dashed #ddd;
            margin: 20px 0;
            text-align: center;
        }
        .password-value {
            font-family: monospace;
            font-size: 18px;
            color: #dc3545;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .btn-login {
            background-color: #007bff;
            color: white;
            padding: 10px 30px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-login:hover {
            background-color: #0069d9;
            color: white;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-check-circle me-2"></i>{{ __('Registration Successful!') }}</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-user-check fa-4x text-success mb-3"></i>
                    <h4>Welcome to {{ $tenant->name }}!</h4>
                    <p class="text-muted">Your account has been created successfully.</p>
                </div>
                
                @if($tempPassword)
                <div class="temp-password">
                    <p class="mb-1"><strong>Temporary Password:</strong></p>
                    <p class="password-value">{{ $tempPassword }}</p>
                    <p class="small text-muted mt-2">Please use this password to log in. You should change it after your first login.</p>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Normally, this password would be sent to your email. It's displayed here for testing purposes.
                </div>
                @else
                <div class="alert alert-success">
                    <i class="fas fa-envelope"></i> A temporary password has been sent to your email. Please check your inbox.
                </div>
                @endif
                
                <div class="text-center mt-4">
                    <a href="{{ url('/tenants/login') }}" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>{{ __('Log In Now') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 