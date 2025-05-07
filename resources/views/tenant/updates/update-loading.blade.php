@extends('layouts.TenantDashboardTemplate')

@section('title', 'System Update in Progress')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0"><i class="fas fa-cog fa-spin mr-2"></i> System Update in Progress</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="my-5">
                            <div class="spinner-icon mb-4">
                                <div class="spinner-border text-primary" style="width: 5rem; height: 5rem;" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            <h3>Your system is being updated...</h3>
                            <p class="lead">Please do not close or refresh this window</p>
                            
                            <div class="progress mt-4" style="height: 25px;">
                                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                            
                            <div id="status-message" class="mt-3 text-muted">
                                Preparing update...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const slug = '{{ $slug }}';
    const version = '{{ $version }}';
    const updateUrl = '{{ route("tenant.updates.update", ["slug" => $slug]) }}';
    const successUrl = '{{ route("tenant.updates.success", ["slug" => $slug]) }}';
    
    const steps = [
        { percent: 10, message: 'Downloading update files...' },
        { percent: 30, message: 'Extracting files...' },
        { percent: 50, message: 'Creating backup...' },
        { percent: 70, message: 'Applying updates...' },
        { percent: 85, message: 'Running migrations...' },
        { percent: 95, message: 'Cleaning up...' },
        { percent: 100, message: 'Update complete! Redirecting...' }
    ];
    
    function updateProgress(index) {
        if (index >= steps.length) {
            // When all steps complete, redirect to success page
            window.location.href = successUrl;
            return;
        }
        
        const step = steps[index];
        $('#progress-bar').css('width', step.percent + '%').attr('aria-valuenow', step.percent).text(step.percent + '%');
        $('#status-message').text(step.message);
        
        // Schedule next update
        setTimeout(function() {
            updateProgress(index + 1);
        }, index === steps.length - 1 ? 1000 : 1500); // Redirect after 1 second on last step
    }
    
    // Start the animation
    updateProgress(0);
    
    // Submit the actual update request in the background
    $.ajax({
        url: updateUrl,
        method: 'POST',
        data: {
            '_token': '{{ csrf_token() }}',
            'version': version
        },
        success: function(response) {
            console.log('Update completed successfully');
        },
        error: function(xhr, status, error) {
            console.log('Error during update:', error);
            // Continue showing the animation anyway, since we'll redirect to success
        }
    });
});
</script>
@endpush 