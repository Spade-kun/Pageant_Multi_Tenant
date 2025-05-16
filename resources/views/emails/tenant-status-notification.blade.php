<!DOCTYPE html>
<html>
<head>
    <title>Tenant Status Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
        }
        .footer {
            text-align: center;
            padding: 10px;
            font-size: 0.8em;
            color: #777;
        }
        .approved {
            color: #28a745;
            font-weight: bold;
        }
        .rejected {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>CLAM Agency</h2>
        </div>
        <div class="content">
            <p>Hello,</p>
            
            @if($status === 'approved')
                <p>We are pleased to inform you that your pageant <span class="approved">"{{ $tenant->pageant_name }}"</span> has been <span class="approved">APPROVED</span>.</p>
                <p>Your pageant is now active and you can start setting it up by logging in to your account.</p>
                <p>You can access your pageant dashboard to customize your settings, manage contestants, and more.</p>
            @else
                <p>Thank you for your interest in our Pageant Management System.</p>
                <p>We regret to inform you that your pageant <span class="rejected">"{{ $tenant->pageant_name }}"</span> has been <span class="rejected">REJECTED</span>.</p>
                
                @if($rejectionReason)
                    <p><strong>Reason for rejection:</strong> {{ $rejectionReason }}</p>
                @endif
                
                <p>If you would like to address the issues mentioned and reapply, or if you have any questions, please feel free to contact our support team.</p>
            @endif
            
            <p>Thank you for choosing our CLAM Agency.</p>
            
            <p>Best regards,<br>CLAM Agency Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} CLAM Agency. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 