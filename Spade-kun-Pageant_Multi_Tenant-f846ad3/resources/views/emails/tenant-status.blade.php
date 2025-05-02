<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Application Status Update</title>
</head>
<body style="font-family: 'Public Sans', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: linear-gradient(135deg, #3F081C 0%, #2a0513 100%);">
    <div style="max-width: 600px; margin: 20px auto; padding: 20px;">
        <div style="background-color: #ffffff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); padding: 40px; position: relative;">
            <!-- Status Badge -->
            <div style="position: absolute; top: 0; right: 2rem; background: #A06F4D; color: white; padding: 0.5rem 1.5rem; border-radius: 0 0 10px 10px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                {{ $status === 'approved' ? 'Approved' : 'Rejected' }}
            </div>

            <!-- Logo Section -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <img src="{{ $message->embed(public_path('images/logo.jpg')) }}" alt="Glam Agency Logo" style="width: 150px; height: auto; border-radius: 50%; border: 3px solid #A06F4D; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            </div>

            <!-- Title -->
            <h1 style="text-align: center; color: #3F081C; font-size: 1.75rem; font-weight: 600; margin-bottom: 1rem;">
                Tenant Application Status Update
            </h1>

            @if($status === 'approved')
                <p style="text-align: center; color: #A06F4D; font-size: 1.1rem; margin-bottom: 2rem;">
                    Your tenant application for <strong>{{ $tenant->pageant_name }}</strong> has been approved!
                </p>
                
                <!-- Credentials Box -->
                <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 8px; border: 1px dashed #A06F4D; margin: 1.5rem 0;">
                    <div style="margin-bottom: 1rem;">
                        <div style="color: #3F081C; font-weight: 500; margin-bottom: 0.5rem;">Email</div>
                        <div style="font-family: monospace; font-size: 1.1rem; color: #A06F4D; font-weight: 600; letter-spacing: 1px;">{{ $tenant->owner->email }}</div>
                    </div>
                    
                    <div>
                        <div style="color: #3F081C; font-weight: 500; margin-bottom: 0.5rem;">Temporary Password</div>
                        <div style="font-family: monospace; font-size: 1.1rem; color: #A06F4D; font-weight: 600; letter-spacing: 1px;">{{ $temporaryPassword }}</div>
                    </div>
                </div>

                <p style="color: #666; font-size: 0.9rem; margin: 1.5rem 0;">Please make sure to change your password after your first login.</p>

                <!-- Login Button -->
                <div style="text-align: center; margin: 1.5rem 0;">
                    <a href="http://127.0.0.1:8000/tenant/login" style="display: inline-block; background: #A06F4D; color: white; padding: 1rem 2rem; border-radius: 8px; text-decoration: none; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; transition: all 0.3s ease;">
                        Login to Your Tenant Dashboard
                    </a>
                </div>
            @else
                <p style="text-align: center; color: #A06F4D; font-size: 1.1rem; margin-bottom: 2rem;">
                    Your tenant application for <strong>{{ $tenant->pageant_name }}</strong> has been rejected.
                </p>
                
                <!-- Rejection Reason Box -->
                <div style="background-color: #FFF5F5; border: 1px solid #FED7D7; border-radius: 8px; padding: 1rem; margin: 1.5rem 0;">
                    <strong style="color: #991B1B;">Reason:</strong><br>
                    {{ $reason }}
                </div>

                <p style="color: #666; text-align: center;">If you have any questions, please contact our support team.</p>
            @endif

            <!-- Footer -->
            <div style="text-align: center; margin-top: 2rem; color: #666; font-size: 0.9rem;">
                <p>Thanks,<br>{{ config('app.name') }}</p>
            </div>
        </div>
    </div>
</body>
</html> 