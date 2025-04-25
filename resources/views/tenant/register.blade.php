<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glam Agency - Pageant Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            padding: 0 1rem;
        }

        .login-link a {
            color: var(--copper);
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-block;
            padding: 0.5rem 1rem;
            border: 1px solid var(--copper);
            border-radius: 5px;
        }

        .login-link a:hover {
            background: var(--copper);
            color: white;
            transform: translateY(-1px);
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
            .register-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .form-group,
            .btn-register,
            .login-link {
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
            Pageant Registration
        </div>
        
        <div class="logo-section">
            <img src="{{ asset('images/logo.jpg') }}" alt="Glam Agency Logo">
        </div>

        <h1 class="portal-title">
           
            <span>Create Your Pageant Portal</span>
        </h1>

        <form method="POST" action="{{ route('register') }}">
        @csrf

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
                <label for="pageant_name" class="form-label">Organizer Name</label>
                <i class="fas fa-user input-icon"></i>
                <input id="pageant_name" class="form-input" type="text" name="pageant_name" value="{{ old('pageant_name') }}" required autofocus autocomplete="pageant_name">
        </div>

            <div class="form-group">
                <label for="slug" class="form-label">Custom URL Slug</label>
                <i class="fas fa-link input-icon"></i>
                <input id="slug" class="form-input" type="text" name="slug" value="{{ old('slug') }}" required autocomplete="slug">
            <p class="text-sm text-gray-500 mt-1">This will be used in your pageant's URL. Use only letters, numbers, and hyphens.</p>
        </div>

            <div class="form-group">
                <label for="name" class="form-label">Name</label>
                <i class="fas fa-user input-icon"></i>
                <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
        </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <i class="fas fa-envelope input-icon"></i>
                <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <i class="fas fa-phone input-icon"></i>
                <input id="phone" class="form-input" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel">
            </div>

            <div class="form-group">
                <label for="age" class="form-label">Age</label>
                <i class="fas fa-calendar input-icon"></i>
                <input id="age" class="form-input" type="text" name="age" value="{{ old('age') }}" required>
        </div>

            <div class="form-group">
                <label for="gender" class="form-label">Gender</label>
                <i class="fas fa-venus-mars input-icon"></i>
                <select id="gender" name="gender" class="form-input">
                <option value="">Select Gender</option>
                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <i class="fas fa-map-marker-alt input-icon"></i>
                <textarea id="address" name="address" class="form-input" rows="3">{{ old('address') }}</textarea>
        </div>

            <button type="submit" class="btn-register">
                Register Pageant
            </button>

            <div class="login-link">
                <a href="{{ route('tenant.login') }}">
                    <i class="fas fa-sign-in-alt"></i> Already registered? Sign in
                </a>
        </div>
    </form>
    </div>
</body>
</html> 