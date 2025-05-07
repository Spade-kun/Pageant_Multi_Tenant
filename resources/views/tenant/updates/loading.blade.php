@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Update in Progress</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <img src="{{ asset('img/updating.gif') }}" alt="Updating" style="max-width: 100px;" onerror="this.src='https://i.gifer.com/ZKZx.gif'; this.onerror=null;">
                    </div>

                    <h4 class="mb-3">Updating to version {{ $version }}</h4>
                    
                    <div class="progress mb-4" style="height: 25px;">
                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    
                    <div id="status-message" class="alert alert-info">
                        Starting update process...
                    </div>
                    
                    <p class="mb-0 text-muted">Please do not close this page. You will be redirected when the update is complete.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Define update steps for the progress bar
    const totalSteps = 7;
    const steps = [
        { percent: 10, message: "Downloading update package..." },
        { percent: 25, message: "Extracting files..." },
        { percent: 40, message: "Creating backup..." },
        { percent: 55, message: "Copying new files..." },
        { percent: 70, message: "Running composer update..." },
        { percent: 85, message: "Running database migrations..." },
        { percent: 100, message: "Finalizing update..." }
    ];
    
    let currentStep = 0;
    
    // Update progress function
    function updateProgress() {
        if (currentStep < steps.length) {
            const step = steps[currentStep];
            $('#progress-bar').css('width', step.percent + '%');
            $('#progress-bar').attr('aria-valuenow', step.percent);
            $('#progress-bar').text(step.percent + '%');
            $('#status-message').text(step.message);
            currentStep++;
            
            // Schedule next update after a delay
            setTimeout(updateProgress, 5000);
        } else {
            // Final step - redirect to the updates page
            $('#status-message').removeClass('alert-info').addClass('alert-success');
            $('#status-message').text("Update complete! Redirecting to updates page...");
            
            // Redirect after a short delay
            setTimeout(function() {
                window.location.href = "{{ route('tenant.updates.index', ['slug' => $slug]) }}";
            }, 3000);
        }
    }
    
    // Check update status periodically
    function checkUpdateStatus() {
        $.ajax({
            url: "{{ route('tenant.updates.status', ['slug' => $slug, 'job_id' => $jobId]) }}",
            type: "GET",
            dataType: "json",
            success: function(data) {
                if (data.status === 'completed') {
                    // Update is complete, update UI and redirect
                    $('#progress-bar').css('width', '100%');
                    $('#progress-bar').attr('aria-valuenow', 100);
                    $('#progress-bar').text('100%');
                    
                    if (data.success) {
                        $('#status-message').removeClass('alert-info').addClass('alert-success');
                        $('#status-message').text("Update completed successfully! Redirecting...");
                    } else {
                        $('#status-message').removeClass('alert-info').addClass('alert-danger');
                        $('#status-message').text("Update failed: " + data.message);
                    }
                    
                    // Redirect after a short delay
                    setTimeout(function() {
                        window.location.href = "{{ route('tenant.updates.index', ['slug' => $slug]) }}";
                    }, 3000);
                } else if (data.status === 'failed') {
                    // Update failed
                    $('#status-message').removeClass('alert-info').addClass('alert-danger');
                    $('#status-message').text("Update failed: " + data.message);
                    
                    // Redirect after a longer delay
                    setTimeout(function() {
                        window.location.href = "{{ route('tenant.updates.index', ['slug' => $slug]) }}";
                    }, 5000);
                } else {
                    // Update is still in progress
                    if (data.current_step && data.total_steps) {
                        const percent = Math.round((data.current_step / data.total_steps) * 100);
                        $('#progress-bar').css('width', percent + '%');
                        $('#progress-bar').attr('aria-valuenow', percent);
                        $('#progress-bar').text(percent + '%');
                    }
                    
                    if (data.message) {
                        $('#status-message').text(data.message);
                    }
                    
                    // Check again after a delay
                    setTimeout(checkUpdateStatus, 3000);
                }
            },
            error: function() {
                // If we can't get status, use the fallback progress bar
                if (!window.usingFallback) {
                    window.usingFallback = true;
                    updateProgress();
                }
            }
        });
    }
    
    $(document).ready(function() {
        // First check if we can get update status
        $.ajax({
            url: "{{ route('tenant.updates.status', ['slug' => $slug, 'job_id' => $jobId]) }}",
            type: "GET",
            dataType: "json",
            success: function(data) {
                // If we can get status, use the real-time status updates
                checkUpdateStatus();
            },
            error: function() {
                // If we can't get status, fall back to simulated progress
                updateProgress();
            }
        });
    });
</script>
@endsection 