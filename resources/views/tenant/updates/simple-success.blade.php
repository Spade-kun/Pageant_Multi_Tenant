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
                            <h5><i class="fas fa-check-circle"></i> {{ $message ?? 'Your system has been successfully updated.' }}</h5>
                        </div>

                        <div class="mt-4">
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
@endsection 