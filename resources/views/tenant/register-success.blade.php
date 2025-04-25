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
        .tenant-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px dashed #ddd;
            margin: 20px 0;
        }
        .info-value {
            font-family: monospace;
            font-size: 18px;
            color: #28a745;
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
                    <h4>{{ __('Thank you for registering your pageant!') }}</h4>
                    <p class="text-muted">{{ __('Your registration is pending approval by our administrators.') }}</p>
                </div>
        
        @if(session('tenant'))
                <div class="tenant-info">
                    <p class="mb-1"><strong>{{ __('Pageant Name') }}:</strong></p>
                    <p class="info-value">{{ session('tenant')['pageant_name'] }}</p>
            
                    <p class="mb-1 mt-3"><strong>{{ __('Pageant URL Slug') }}:</strong></p>
                    <p class="info-value">{{ session('tenant')['slug'] }}</p>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> {{ __('Once approved, you will be able to log in and access your pageant dashboard.') }}
        </div>
        @endif
        
                <div class="text-center mt-4">
                    <a href="{{ url('/tenants/login') }}" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>{{ __('Return to Login') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 