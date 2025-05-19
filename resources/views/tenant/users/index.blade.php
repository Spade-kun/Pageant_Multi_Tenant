@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">{{ __('User Management') }}</h4>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ __('Registration Link') }}</div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>{{ __('Share this link with users to register for your pageant') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="registrationLink" 
                                value="{{ route('tenant.register.form', ['slug' => $slug]) }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-primary" id="copyLinkBtn" onclick="copyRegistrationLink(event)">
                                    <i class="fas fa-copy"></i> {{ __('Copy Link') }}
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            {{ __('Users who click this link will be taken to a registration form.') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyRegistrationLink(event) {
    const linkInput = document.getElementById('registrationLink');
    const button = event.currentTarget;
    const originalText = button.innerHTML;
    
    // Use modern clipboard API if available
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(linkInput.value)
            .then(() => {
                // Show feedback
                button.innerHTML = '<i class="fas fa-check"></i> {{ __("Copied!") }}';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-primary');
                }, 2000);
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
                fallbackCopyMethod(linkInput, button, originalText);
            });
    } else {
        // Fallback for older browsers
        fallbackCopyMethod(linkInput, button, originalText);
    }
}

function fallbackCopyMethod(linkInput, button, originalText) {
    // Select the text
    linkInput.select();
    linkInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        // Execute copy command
        const successful = document.execCommand('copy');
        
        if (successful) {
            // Show success feedback
            button.innerHTML = '<i class="fas fa-check"></i> {{ __("Copied!") }}';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
        } else {
            // Show error feedback
            button.innerHTML = '<i class="fas fa-times"></i> {{ __("Failed!") }}';
            button.classList.remove('btn-primary');
            button.classList.add('btn-danger');
        }
    } catch (err) {
        console.error('Failed to copy: ', err);
        button.innerHTML = '<i class="fas fa-times"></i> {{ __("Failed!") }}';
        button.classList.remove('btn-primary');
        button.classList.add('btn-danger');
    }
    
    // Reset button after delay
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success', 'btn-danger');
        button.classList.add('btn-primary');
    }, 2000);
}
</script>
@endpush
@endsection 