@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-header">
    <h4 class="page-title">{{ __('Welcome to your Pageant Dashboard') }}</h4>
</div>

@php
    // Get tenant's current plan
    $tenant = App\Models\Tenant::where('slug', $slug)->first();
    $tenantPlan = $tenant->plan;
@endphp

@if($tenant->hasNoPlan())
<div class="alert alert-warning">
    <h5><i class="fas fa-exclamation-triangle"></i> {{ __('You are currently on the basic "No Plan" subscription') }}</h5>
    <p>{{ __('Upgrade to a premium plan to access advanced features like Pageant Management and Reports.') }}</p>
    <a href="{{ route('tenant.subscription.plans', ['slug' => $slug]) }}" class="btn btn-warning">
        <i class="fas fa-arrow-up"></i> {{ __('Upgrade Now') }}
    </a>
</div>
@endif

<div class="row">
    <!-- Always show these cards for basic features -->
    <!-- User Management Card -->
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('User Management') }}</p>
                            <h4 class="card-title">{{ __('Invite and manage users for your pageant.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="{{ route('tenant.users.index', ['slug' => $slug]) }}" class="btn btn-primary btn-round">
                        <span class="btn-label">
                            <i class="fas fa-users"></i>
                        </span>
                        {{ __('Manage Users') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Management Card -->
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('Subscription') }}</p>
                            <h4 class="card-title">{{ __('Manage your subscription plan.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="{{ route('tenant.subscription.plans', ['slug' => $slug]) }}" class="btn btn-success btn-round">
                        <span class="btn-label">
                            <i class="fas fa-crown"></i>
                        </span>
                        {{ __('View Plans') }}
                        @if($tenant->hasNoPlan())
                            <span class="badge badge-danger ml-2">No Plan</span>
                        @elseif(session('trial_days_left'))
                            <span class="badge badge-warning ml-2">Trial: {{ session('trial_days_left') }} days left</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Show these cards only if Pageant Management is enabled -->
    @if(!$tenant->hasNoPlan() && $tenantPlan->pageant_management)
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('Contestants') }}</p>
                            <h4 class="card-title">{{ __('Manage contestants participating in your pageant.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-primary btn-round">
                        <span class="btn-label">
                            <i class="fas fa-eye"></i>
                        </span>
                        {{ __('View Contestants') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-secondary bubble-shadow-small">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('Categories') }}</p>
                            <h4 class="card-title">{{ __('Define competition categories and their weights.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="{{ route('tenant.categories.index', ['slug' => $slug]) }}" class="btn btn-secondary btn-round">
                        <span class="btn-label">
                            <i class="fas fa-cog"></i>
                        </span>
                        {{ __('Manage Categories') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('Events') }}</p>
                            <h4 class="card-title">{{ __('Schedule and manage pageant events.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="{{ route('tenant.events.index', ['slug' => $slug]) }}" class="btn btn-success btn-round">
                        <span class="btn-label">
                            <i class="fas fa-eye"></i>
                        </span>
                        {{ __('View Events') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="fas fa-gavel"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('Judges') }}</p>
                            <h4 class="card-title">{{ __('Manage judges and scoring for your pageant.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="#" class="btn btn-info btn-round">
                        <span class="btn-label">
                            <i class="fas fa-eye"></i>
                        </span>
                        {{ __('View Judges') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-warning bubble-shadow-small">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('Event Assignments') }}</p>
                            <h4 class="card-title">{{ __('Manage event assignments and schedules.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="{{ route('tenant.event-assignments.index', ['slug' => $slug]) }}" class="btn btn-warning btn-round">
                        <span class="btn-label">
                            <i class="fas fa-eye"></i>
                        </span>
                        {{ __('View Assignments') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-danger bubble-shadow-small">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('Scores') }}</p>
                            <h4 class="card-title">{{ __('View and manage scores from judges.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="{{ route('tenant.scores.index', ['slug' => $slug]) }}" class="btn btn-danger btn-round">
                        <span class="btn-label">
                            <i class="fas fa-eye"></i>
                        </span>
                        {{ __('View Scores') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    @else
    <!-- Show locked feature cards if on "No Plan" -->
    @if($tenant->hasNoPlan())
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round bg-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-secondary bubble-shadow-small">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('Pageant Management') }}</p>
                            <h4 class="card-title">{{ __('Unlock to manage contestants, events, and more.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="{{ route('tenant.subscription.plans', ['slug' => $slug]) }}" class="btn btn-secondary btn-round">
                        <span class="btn-label">
                            <i class="fas fa-arrow-up"></i>
                        </span>
                        {{ __('Upgrade Plan to Unlock') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- Show Reports card only if Reports Module is enabled -->
    @if(!$tenant->hasNoPlan() && $tenantPlan->reports_module)
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-secondary bubble-shadow-small">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('Reports') }}</p>
                            <h4 class="card-title">{{ __('Generate reports and insights for your pageant.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="{{ route('tenant.reports.generate', ['slug' => $slug]) }}" class="btn btn-secondary btn-round">
                        <span class="btn-label">
                            <i class="fas fa-file-pdf"></i>
                        </span>
                        {{ __('Generate PDF Report') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    @elseif($tenant->hasNoPlan())
    <!-- Show locked Reports feature card if on "No Plan" -->
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round bg-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-secondary bubble-shadow-small">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">{{ __('Reports Module') }}</p>
                            <h4 class="card-title">{{ __('Unlock to access analytics and insights.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-action mt-3">
                    <a href="{{ route('tenant.subscription.plans', ['slug' => $slug]) }}" class="btn btn-secondary btn-round">
                        <span class="btn-label">
                            <i class="fas fa-arrow-up"></i>
                        </span>
                        {{ __('Upgrade Plan to Unlock') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
                        @endif
</div>
@endsection