@component('mail::message')
# Tenant Application Status Update

@if($status === 'approved')
Your tenant application for **{{ $tenant->pageant_name }}** has been approved!

You can now access your tenant dashboard using the following credentials:

**Email:** {{ $tenant->owner->email }}  
**Temporary Password:** {{ $temporaryPassword }}

Please make sure to change your password after your first login.

@component('mail::button', ['url' => 'http://127.0.0.1:8000/tenant/login'])
Login to Your Tenant Dashboard
@endcomponent

@else
Your tenant application for **{{ $tenant->pageant_name }}** has been rejected.

**Reason:** {{ $reason }}

If you have any questions, please contact our support team.
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent 