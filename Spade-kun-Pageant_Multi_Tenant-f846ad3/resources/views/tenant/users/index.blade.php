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
                                <button class="btn btn-primary" onclick="copyRegistrationLink()">
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
function copyRegistrationLink() {
    const linkInput = document.getElementById('registrationLink');
    linkInput.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = event.currentTarget;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> {{ __("Copied!") }}';
    setTimeout(() => {
        button.innerHTML = originalText;
    }, 2000);
}
</script>
@endpush
@endsection 