<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $tenantName }}</title>
</head>
<body style="font-family: 'Public Sans', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: linear-gradient(135deg, #3F081C 0%, #2a0513 100%);">
    <div style="max-width: 600px; margin: 20px auto; padding: 20px;">
        <div style="background-color: #ffffff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); padding: 40px; position: relative;">
            <!-- Logo Section -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <img src="{{ $message->embed(public_path('images/logo.jpg')) }}" alt="{{ $tenantName }} Logo" style="width: 150px; height: auto; border-radius: 50%; border: 3px solid #A06F4D; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            </div>

            <!-- Welcome Title -->
            <h1 style="text-align: center; color: #3F081C; font-size: 1.75rem; font-weight: 600; margin-bottom: 1rem;">
                Welcome to {{ $tenantName }}
            </h1>

            <!-- Greeting -->
            <p style="text-align: center; color: #A06F4D; font-size: 1.1rem; margin-bottom: 2rem;">
                Dear {{ $name }},
            </p>

            <!-- Main Content -->
            <p style="text-align: center; color: #666; margin-bottom: 2rem;">
                Thank you for registering with {{ $tenantName }}. Your account has been successfully created.
            </p>

            <!-- Credentials Box -->
            <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 8px; border: 1px dashed #A06F4D; margin: 1.5rem 0;">
                <h3 style="color: #3F081C; text-align: center; margin-bottom: 1.5rem;">Your Login Credentials</h3>
                
                <div style="margin-bottom: 1rem;">
                    <div style="color: #3F081C; font-weight: 500; margin-bottom: 0.5rem;">Email</div>
                    <div style="font-family: monospace; font-size: 1.1rem; color: #A06F4D; font-weight: 600; letter-spacing: 1px;">{{ $email }}</div>
                </div>
                
                <div>
                    <div style="color: #3F081C; font-weight: 500; margin-bottom: 0.5rem;">Temporary Password</div>
                    <div style="font-family: monospace; font-size: 1.1rem; color: #A06F4D; font-weight: 600; letter-spacing: 1px;">{{ $tempPassword }}</div>
                </div>

                <p style="color: #666; font-size: 0.9rem; margin-top: 1rem; text-align: center;">
                    <em>Please change your password after your first login for security purposes.</em>
                </p>
            </div>

            <!-- Login Button -->
            <div style="text-align: center; margin: 1.5rem 0;">
                <a href="{{ route('tenant.login') }}" style="display: inline-block; background: #A06F4D; color: white; padding: 1rem 2rem; border-radius: 8px; text-decoration: none; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                    Log In Now
                </a>
            </div>

            <!-- Support Message -->
            <p style="color: #666; text-align: center; margin: 1.5rem 0;">
                If you need any assistance or have questions, please don't hesitate to contact our support team.
            </p>

            <!-- Footer -->
            <div style="text-align: center; margin-top: 2rem; color: #666; font-size: 0.9rem;">
                <p>Best regards,<br>{{ $tenantName }} Team</p>
                <p style="margin-top: 1rem; font-size: 0.8rem;">This is an automated message, please do not reply to this email.</p>
            </div>
        </div>
    </div>
</body>
</html> 