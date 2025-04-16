<!DOCTYPE html>
<html>
<head>
    <title>Welcome to CLAM Agency</title>
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
        <h1>Welcome to CLAM Agency</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        
        <p>Thank you for registering with {{ $tenant->name }}. Your account has been successfully created.</p>
        
        <div class="credentials">
            <h3>Your Login Credentials:</h3>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Temporary Password:</strong> {{ $tempPassword }}</p>
        </div>
        
        <p>Please use these credentials to log in to your account. For security reasons, we strongly recommend changing your password after your first login.</p>
        
        <a href="{{ route('tenant.login') }}" class="button">Login to Your Account</a>
        
        <div class="footer">
            <p>This is an automated message, please do not reply to this email.</p>
            <p>If you did not request this registration, please ignore this email.</p>
        </div>
    </div>
</body>
</html> 