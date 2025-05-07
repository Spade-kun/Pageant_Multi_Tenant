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
                    <div class="spinner-border text-primary mb-4" style="width: 3rem; height: 3rem;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    
                    <h2 class="h4 mb-3">Updating to version {{ $version }}</h2>
                    <p class="lead">Please do not close this window or navigate away.</p>
                    <p class="text-muted">This process may take several minutes. You will be redirected automatically when the update is complete.</p>
                    
                    <div class="progress mb-4" style="height: 25px;">
                        <div id="update-progress" class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%;" 
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    
                    <div id="update-status" class="alert alert-info mt-4">
                        Starting update process...
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
        
        // Start the progress animation
        const interval = setInterval(function() {
            if (currentStep < steps.length) {
                const step = steps[currentStep];
                $('#update-progress').css('width', step.percent + '%');
                $('#update-progress').attr('aria-valuenow', step.percent);
                $('#update-progress').text(step.percent + '%');
                $('#update-status').text(step.message);
                
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
                $('#update-status').text('Update completed successfully!');
                
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
                
                $('#update-status').text(errorMessage);
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