<!DOCTYPE html>
<html>
<head>
    <title>Plan Request Status Update</title>
</head>
<body>
    <h2>Plan Request Status Update</h2>
    
    <p>Hello {{ $tenant->name }},</p>

    @if($status === 'approved')
        <p>Your request for the {{ $tenant->subscription_plan }} plan has been approved.</p>
        <p>Your subscription will be active until {{ $tenant->subscription_ends_at->format('F d, Y') }}.</p>
    @elseif($status === 'rejected')
        <p>Your plan request has been rejected.</p>
        <p>Reason: {{ $reason }}</p>
    @elseif($status === 'updated')
        <p>Your subscription plan has been updated to {{ $tenant->subscription_plan }}.</p>
        <p>Your subscription will be active until {{ $tenant->subscription_ends_at->format('F d, Y') }}.</p>
    @endif

    <p>If you have any questions, please contact our support team.</p>

    <p>Best regards,<br>Pageant Management System</p>
</body>
</html> 