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
            padding: 20px;
            background-color: #f9f9f9;
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
        <p>Hello {{ $name }},</p>
        
        <p>Welcome to CLAM Agency! We're excited to have you on board.</p>
        
        <div class="credentials">
            <p><strong>Your temporary password is:</strong> {{ $tempPassword }}</p>
            <p>Please use this password to log in to your account. We strongly recommend changing your password after your first login.</p>
        </div>
        
        <p>To get started, please log in to your account using your email address and the temporary password provided above.</p>
        
        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
        
        <p>Best regards,<br>
        The Clam Agency Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message, please do not reply to this email.</p>
    </div>
</body>
</html> 