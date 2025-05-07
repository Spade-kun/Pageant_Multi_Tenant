@extends('layouts.TenantDashboardTemplate')

@section('title', 'Update Successful')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="card-title mb-0"><i class="fas fa-check-circle mr-2"></i> System Update Successful</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <div class="success-icon mb-3">
                                <i class="fas fa-check-circle fa-5x text-success"></i>
                            </div>
                            <h3>Your system has been successfully updated!</h3>
                            <p class="lead">Current version: <span class="badge badge-success">{{ $currentVersion }}</span></p>
                            
                            @if(session('update_success'))
                            <div class="alert alert-success mt-3">
                                {{ session('update_success') }}
                            </div>
                            @endif
                        </div>

                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle mr-2"></i> Update Information</h5>
                            <p>The update has been applied successfully. The following actions have been completed:</p>
                            <ul class="text-left">
                                <li>System files have been updated</li>
                                <li>Database migrations have been applied</li>
                                <li>Cache has been cleared</li>
                                <li>Composer dependencies have been updated</li>
                            </ul>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('tenant.updates.index', ['slug' => $slug]) }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left mr-2"></i> Return to Updates Page
                            </a>
                            
                            <a href="{{ route('tenant.dashboard', ['slug' => $slug]) }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-home mr-2"></i> Go to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .success-icon {
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
        }
    }
</style>
@endsection 