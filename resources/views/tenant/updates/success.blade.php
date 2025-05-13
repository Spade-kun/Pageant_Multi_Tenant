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
</style>
@endpush 