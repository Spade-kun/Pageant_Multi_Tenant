@extends('layouts.TenantDashboardTemplate')

@section('title', 'Update Successful')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-check-circle mr-2"></i> System Update Successful
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="d-inline-block p-3 rounded-circle bg-success-light mb-3">
                                <i class="fas fa-check-circle fa-4x text-success"></i>
                            </div>
                            <h2 class="font-weight-bold">Your system has been successfully updated!</h2>
                            <p class="lead">You are now running version {{ $version }}</p>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card card-outline card-primary h-100">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-info-circle mr-2"></i> Update Details
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li class="mb-3">
                                                <strong>Version:</strong> 
                                                <span class="badge badge-primary">{{ $version }}</span>
                                            </li>
                                            <li class="mb-3">
                                                <strong>Date:</strong> 
                                                <span>{{ now()->format('F d, Y h:i A') }}</span>
                                            </li>
                                            <li class="mb-3">
                                                <strong>Files Updated:</strong>
                                                <span class="badge badge-info">{{ session('updated_files_count') ?? 'Multiple' }}</span>
                                            </li>
                                            <li>
                                                <strong>Migration Status:</strong> 
                                                <div class="alert alert-info mt-2">
                                                    <i class="fas fa-database mr-2"></i> {{ $migrationStatus }}
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-outline card-info h-100">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-tasks mr-2"></i> Next Steps
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="fa-ul">
                                            <li class="mb-2">
                                                <span class="fa-li"><i class="fas fa-check text-success"></i></span>
                                                Refresh your browser to load updated assets
                                            </li>
                                            <li class="mb-2">
                                                <span class="fa-li"><i class="fas fa-check text-success"></i></span>
                                                Check that all features are working correctly
                                            </li>
                                            <li class="mb-4">
                                                <span class="fa-li"><i class="fas fa-check text-success"></i></span>
                                                Review any new features or changes
                                            </li>
                                        </ul>
                                        <div class="mt-3 text-center">
                                            <a href="{{ route('tenant.dashboard', ['slug' => $slug]) }}" class="btn btn-primary btn-lg mr-2">
                                                <i class="fas fa-home"></i> Go to Dashboard
                                            </a>
                                            <a href="{{ route('tenant.updates.index', ['slug' => $slug]) }}" class="btn btn-info btn-lg">
                                                <i class="fas fa-history"></i> View Update History
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(session('update_log_file'))
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-outline card-secondary">
                                    <div class="card-header d-flex align-items-center">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-file-alt mr-2"></i> Update Log
                                        </h5>
                                        <div class="ml-auto">
                                            <button class="btn btn-sm btn-outline-secondary" id="toggle-logs">
                                                <i class="fas fa-eye"></i> Show Logs
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-0" id="logs-container" style="display: none;">
                                        <div class="p-3">
                                            <small class="text-muted">This log contains details about the update process and can be helpful for troubleshooting.</small>
                                        </div>
                                        <div class="bg-dark p-3 text-light" style="max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                                            <pre id="update-logs">Loading logs...</pre>
                                        </div>
                                        <div class="p-3 border-top">
                                            <button class="btn btn-sm btn-outline-info" id="show-client-logs">
                                                <i class="fas fa-laptop"></i> Show Browser Logs
                                            </button>
                                            <div id="client-logs-container" style="display: none;">
                                                <div class="mt-3 mb-2">
                                                    <small class="text-muted">These logs were captured in your browser during the update process:</small>
                                                </div>
                                                <div class="bg-dark p-3 text-light" style="max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                                                    <pre id="client-logs">No client logs available</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.15);
    }
    .card-outline {
        border-top: 3px solid;
        }
    .card-outline.card-primary {
        border-top-color: #007bff;
        }
    .card-outline.card-info {
        border-top-color: #17a2b8;
    }
    .card-outline.card-secondary {
        border-top-color: #6c757d;
    }
    pre#update-logs {
        white-space: pre-wrap;
        word-wrap: break-word;
    }
</style>
@endpush

@push('scripts')
<script>
    // Clear any update-related session storage
    if (window.sessionStorage) {
        sessionStorage.removeItem('system_update_in_progress');
        sessionStorage.removeItem('update_version');
    }
    
    // Clear any update cookies
    function clearUpdateCookie() {
        document.cookie = 'update_in_progress=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    
    // Clear cookies when page loads
    clearUpdateCookie();
    
    // Handle toggle logs button
    $(document).ready(function() {
        $('#toggle-logs').click(function() {
            const $container = $('#logs-container');
            const $button = $(this);
            
            if ($container.is(':visible')) {
                $container.hide();
                $button.html('<i class="fas fa-eye"></i> Show Logs');
            } else {
                $container.show();
                $button.html('<i class="fas fa-eye-slash"></i> Hide Logs');
                
                // Load log content if it's the first time showing
                const $logsElement = $('#update-logs');
                if ($logsElement.text() === 'Loading logs...') {
                    @if(session('update_log_file'))
                    $.get('{{ route("tenant.updates.get-logs", ["slug" => $slug]) }}', function(data) {
                        if (data && data.logs) {
                            $logsElement.text(data.logs);
                        } else {
                            $logsElement.text('No log data available');
                        }
                    }).fail(function() {
                        $logsElement.text('Error loading log data. Check with your administrator.');
                    });
                    @else
                    $logsElement.text('No log file information available.');
                    @endif
                }
            }
        });
        
        // Handle the client logs button
        $('#show-client-logs').click(function() {
            const $container = $('#client-logs-container');
            const $button = $(this);
            
            if ($container.is(':visible')) {
                $container.hide();
                $button.html('<i class="fas fa-laptop"></i> Show Browser Logs');
            } else {
                $container.show();
                $button.html('<i class="fas fa-laptop"></i> Hide Browser Logs');
                
                // Get client-side logs from localStorage
                try {
                    if (window.localStorage) {
                        const version = '{{ $version }}';
                        const logKey = `update_log_${version}`;
                        const logs = JSON.parse(localStorage.getItem(logKey) || '[]');
                        
                        if (logs.length > 0) {
                            $('#client-logs').text(logs.join('\n'));
                        } else {
                            // Try with other keys
                            let foundLogs = false;
                            Object.keys(localStorage).forEach(key => {
                                if (key.startsWith('update_log_')) {
                                    const otherLogs = JSON.parse(localStorage.getItem(key) || '[]');
                                    if (otherLogs.length > 0) {
                                        $('#client-logs').text(`Logs for ${key.replace('update_log_', '')}:\n${otherLogs.join('\n')}`);
                                        foundLogs = true;
                                    }
                                }
                            });
                            
                            if (!foundLogs) {
                                $('#client-logs').text('No browser logs were captured during the update process.');
                            }
                        }
                    } else {
                        $('#client-logs').text('LocalStorage is not available in your browser.');
                    }
                } catch (e) {
                    $('#client-logs').text(`Error retrieving browser logs: ${e.message}`);
                }
            }
        });
    });
</script>
@endpush 