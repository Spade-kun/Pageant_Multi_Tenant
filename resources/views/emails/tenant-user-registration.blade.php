<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $tenantName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4a90e2;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .credentials {
            background-color: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('assets/img/logoproduct.svg') }}" alt="{{ $tenantName }} Logo" class="logo">
        <h1>Welcome to {{ $tenantName }}</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $name }},</p>
        
        <p>Thank you for registering with {{ $tenantName }}. Your account has been successfully created.</p>
        
        <div class="credentials">
            <h3>Your Login Credentials:</h3>
            <p><strong>Email:</strong> {{ $email }}</p>
            <p><strong>Temporary Password:</strong> {{ $tempPassword }}</p>
            <p><em>Please change your password after your first login for security purposes.</em></p>
        </div>
        
        <p>You can now log in to your account using these credentials.</p>
        
        <a href="{{ route('tenant.login') }}" class="button">Log In Now</a>
        
        <p>If you need any assistance or have questions, please don't hesitate to contact our support team.</p>
        
        <p>Best regards,<br>{{ $tenantName }} Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message, please do not reply to this email.</p>
    </div>
</body>
</html> 