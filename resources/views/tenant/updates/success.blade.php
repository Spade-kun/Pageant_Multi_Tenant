@extends('layouts.TenantDashboardTemplate')

@section('title', 'Update Successful')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">System Update Successful</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> Your system has been successfully updated to version {{ $version }}</h5>
                            <p>The update process has completed and your system is now running the latest version.</p>
                            </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h5 class="card-title">Update Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Version:</strong> {{ $version }}</p>
                                        <p><strong>Date:</strong> {{ now()->format('F d, Y h:i A') }}</p>
                                        <p><strong>Migration Status:</strong> {{ $migrationStatus }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h5 class="card-title">Next Steps</h5>
                        </div>
                                    <div class="card-body">
                                        <ul class="fa-ul">
                                            <li><span class="fa-li"><i class="fas fa-check"></i></span>Refresh your browser to load any updated assets</li>
                                            <li><span class="fa-li"><i class="fas fa-check"></i></span>Check that all features are working correctly</li>
                                            <li><span class="fa-li"><i class="fas fa-check"></i></span>Review any new features or changes</li>
                            </ul>
                                        <div class="mt-3">
                                            <a href="{{ route('tenant.dashboard', ['slug' => $slug]) }}" class="btn btn-primary">
                                                <i class="fas fa-home"></i> Go to Dashboard
                            </a>
                                            <a href="{{ route('tenant.updates.index', ['slug' => $slug]) }}" class="btn btn-info">
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