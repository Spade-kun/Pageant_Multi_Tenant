@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="text-center my-5">
        <div class="d-flex justify-content-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">System Update in Progress</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="py-5">
                    <div class="mb-4">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h2 class="h4 mb-3">Updating to version {{ $version }}</h2>
                        <div class="d-flex justify-content-center my-3">
                            <div class="version-change d-flex align-items-center">
                                <span class="badge badge-secondary p-2 mr-2" style="font-size: 14px;">Current</span>
                                <i class="fas fa-arrow-right mx-3"></i>
                                <span class="badge badge-success p-2 ml-2" style="font-size: 14px;">v{{ $version }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mb-4">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Important:</strong> Please do not close this window or navigate away.
                    </div>
                    
                    <p class="lead">The update is in progress. Please be patient.</p>
                    <p class="text-muted">This process may take several minutes. You will be redirected automatically when the update is complete.</p>
                    
                    <div class="progress mb-4" style="height: 25px;">
                        <div id="update-progress" class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%;" 
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    
                    <div id="update-status" class="alert alert-info mt-4">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span id="update-message">Starting update process...</span>
                    </div>
                    
                    <div id="update-steps" class="mt-4 text-left">
                        <div class="step" id="step-1">
                            <span class="step-number badge badge-secondary mr-2">1</span>
                            <span class="step-text text-muted">Downloading update package...</span>
                        </div>
                        <div class="step" id="step-2">
                            <span class="step-number badge badge-secondary mr-2">2</span>
                            <span class="step-text text-muted">Extracting files...</span>
                        </div>
                        <div class="step" id="step-3">
                            <span class="step-number badge badge-secondary mr-2">3</span>
                            <span class="step-text text-muted">Creating backup...</span>
                        </div>
                        <div class="step" id="step-4">
                            <span class="step-number badge badge-secondary mr-2">4</span>
                            <span class="step-text text-muted">Copying new files...</span>
                        </div>
                        <div class="step" id="step-5">
                            <span class="step-number badge badge-secondary mr-2">5</span>
                            <span class="step-text text-muted">Running composer update...</span>
                        </div>
                        <div class="step" id="step-6">
                            <span class="step-number badge badge-secondary mr-2">6</span>
                            <span class="step-text text-muted">Running database migrations...</span>
                        </div>
                        <div class="step" id="step-7">
                            <span class="step-number badge badge-secondary mr-2">7</span>
                            <span class="step-text text-muted">Completing update...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Update progress values
        const steps = [
            { percent: 10, message: 'Downloading update package...' },
            { percent: 30, message: 'Extracting files...' },
            { percent: 50, message: 'Creating backup...' },
            { percent: 70, message: 'Copying new files...' },
            { percent: 80, message: 'Running composer update...' },
            { percent: 90, message: 'Running database migrations...' },
            { percent: 100, message: 'Update complete! Redirecting...' }
        ];
        
        let currentStep = 0;
        
        // Add CSS for steps
        $('.step').css({
            'padding': '8px 0',
            'border-left': '2px solid #e9ecef',
            'padding-left': '15px',
            'margin-left': '10px',
            'position': 'relative',
            'opacity': '0.5'
        });
        
        // Start the progress animation
        const interval = setInterval(function() {
            if (currentStep < steps.length) {
                const step = steps[currentStep];
                $('#update-progress').css('width', step.percent + '%');
                $('#update-progress').attr('aria-valuenow', step.percent);
                $('#update-progress').text(step.percent + '%');
                $('#update-message').text(step.message);
                
                // Update the steps display
                for (let i = 0; i <= currentStep; i++) {
                    $(`#step-${i+1}`).css({
                        'opacity': '1',
                        'border-left': '2px solid #4e73df'
                    });
                    $(`#step-${i+1} .step-number`).removeClass('badge-secondary').addClass('badge-primary');
                    $(`#step-${i+1} .step-text`).removeClass('text-muted').addClass('text-primary');
                }
                
                currentStep++;
            } else {
                clearInterval(interval);
                // Redirect to updates page
                setTimeout(function() {
                    window.location.href = "{{ route('tenant.updates.index', ['slug' => $slug]) }}?updated=true";
                }, 1500);
            }
        }, 3000); // Update progress every 3 seconds
        
        // Initiate the actual update in the background
        $.ajax({
            url: "{{ route('tenant.updates.process', ['slug' => $slug]) }}",
            type: "POST",
            data: {
                version: "{{ $version }}",
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('#update-status').removeClass('alert-info').addClass('alert-success');
                $('#update-message').text('Update completed successfully!');
                
                // Force redirect after success
                setTimeout(function() {
                    window.location.href = "{{ route('tenant.updates.index', ['slug' => $slug]) }}?updated=true&status=success";
                }, 1500);
            },
            error: function(xhr) {
                // Show error message
                $('#update-status').removeClass('alert-info').addClass('alert-danger');
                
                let errorMessage = 'Update failed. Please check the logs for details.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                $('#update-message').text(errorMessage);
                $('#update-progress').removeClass('progress-bar-animated progress-bar-striped bg-primary')
                    .addClass('bg-danger');
                
                // Redirect after error with error status
                setTimeout(function() {
                    window.location.href = "{{ route('tenant.updates.index', ['slug' => $slug]) }}?updated=true&status=error&message=" + encodeURIComponent(errorMessage);
                }, 5000);
            }
        });
    });
</script>
@endsection 