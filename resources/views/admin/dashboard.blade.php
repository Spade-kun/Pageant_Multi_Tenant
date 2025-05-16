@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __("Admin Dashboard") }}</h3>
                </div>
                <div class="card-body">
                    <!-- Info boxes -->
                    <div class="row">
                        <!-- Manage Tenants -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-info elevation-1">
                                    <i class="fas fa-building"></i>
                                </span>
                                <div class="info-box-content">
                                    <h5 class="info-box-text">{{ __('Manage Tenants') }}</h5>
                                    <p class="info-box-desc text-sm mb-2">{{ __('View, approve, or reject tenant registrations.') }}</p>
                                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-arrow-right"></i> {{ __('View Tenants') }}
                            </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Manage Users -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-success elevation-1">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <h5 class="info-box-text">{{ __('Manage Users') }}</h5>
                                    <p class="info-box-desc text-sm mb-2">{{ __('Manage admin users and their permissions.') }}</p>
                                    <a href="#" class="btn btn-success btn-sm">
                                        <i class="fas fa-arrow-right"></i> {{ __('View Users') }}
                            </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Plan Requests -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-danger elevation-1">
                                    <i class="fas fa-tasks"></i>
                                </span>
                                <div class="info-box-content">
                                    <h5 class="info-box-text">{{ __('Plan Requests') }}</h5>
                                    <p class="info-box-desc text-sm mb-2">{{ __('Review and manage tenant plan requests.') }}</p>
                                    <a href="{{ route('admin.requests.index') }}" class="btn btn-danger btn-sm">
                                        <i class="fas fa-arrow-right"></i> {{ __('View Requests') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Quick Stats') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-md-3">
                                            <div class="small-box bg-info">
                                                <div class="inner">
                                                    <h3>{{ __('Active') }}</h3>
                                                    <p>{{ __('Tenants') }}</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-3">
                                            <div class="small-box bg-warning">
                                                <div class="inner">
                                                    <h3>{{ __('Pending') }}</h3>
                                                    <p>{{ __('Registrations') }}</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-3">
                                            <div class="small-box bg-success">
                                                <div class="inner">
                                                    <h3>{{ __('Active') }}</h3>
                                                    <p>{{ __('Users') }}</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-3">
                                            <div class="small-box bg-danger">
                                                <div class="inner">
                                                    <h3>{{ __('Pending') }}</h3>
                                                    <p>{{ __('Requests') }}</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-tasks"></i>
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
    </div>
</div>

<style>
.info-box {
    min-height: 160px;
    padding: 15px;
    background: #ffffff;
    border-radius: 4px;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
.info-box-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    color: #fff;
}
.info-box-content {
    padding-left: 0;
}
.info-box-desc {
    color: #6c757d;
}
.small-box {
    border-radius: 4px;
    position: relative;
    display: block;
    margin-bottom: 20px;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
.small-box > .inner {
    padding: 10px;
    color: #fff;
}
.small-box h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
}
.small-box .icon {
    position: absolute;
    top: 5px;
    right: 10px;
    font-size: 45px;
    color: rgba(255,255,255,0.3);
}
</style>
@endsection