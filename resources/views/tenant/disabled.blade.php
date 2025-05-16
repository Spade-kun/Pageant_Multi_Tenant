<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        .error-container {
            width: 100%;
            max-width: 500px;
            padding: 3rem;
            text-align: center;
            background: white;
            border-radius: 10px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.08);
        }
        .lock-icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lock-icon {
            color: #dc3545;
            font-size: 3.5rem;
        }
        .error-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #343a40;
        }
        .error-message {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .contact-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
            text-align: left;
        }
        .contact-title {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #212529;
        }
        .contact-title i {
            margin-right: 10px;
            color: #495057;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.8rem;
            font-weight: 500;
            border-radius: 30px;
            transition: all 0.3s ease;
            text-decoration: none;
            border: 1px solid #dee2e6;
            color: #495057;
            background-color: white;
        }
        .back-btn:hover {
            background-color: #f8f9fa;
            color: #212529;
            border-color: #ced4da;
        }
        .back-btn i {
            margin-right: 8px;
        }
        .tenant-name {
            font-weight: 600;
            color: #495057;
        }
        .support-email {
            color: #007bff;
            text-decoration: none;
        }
        .support-email:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="lock-icon-wrapper">
                <i class="fas fa-lock lock-icon"></i>
            </div>
            
            <h1 class="error-title">Access Restricted</h1>
            
            <p class="error-message">
                @if($tenant)
                    The access to <span class="tenant-name">{{ $tenant->pageant_name }}</span> has been temporarily disabled by the administrator.
                @else
                    This tenant does not exist or has been disabled.
                @endif
            </p>
            
            <div class="contact-section">
                <h5 class="contact-title">
                    <i class="fas fa-envelope"></i> Contact Support
                </h5>
                <p>
                    If you believe this is an error, please contact our support team:
                    <br>
                    <a href="mailto:support@pageant-management.com" class="support-email">support@pageant-management.com</a>
                </p>
            </div>
            
        </div>
    </div>
</body>
</html> 