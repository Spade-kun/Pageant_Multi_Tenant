@extends('layouts.TenantDashboardTemplate')

@section('content')
<!-- IMPROVED HEADER SECTION -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h3 class="page-title fw-bold">{{ __('Welcome to your Pageant Dashboard') }}</h3>
        <!-- <h1>v2</h1> -->
        <p class="text-muted">{{ __('Manage your pageant events and activities from one place') }}</p>
    </div>
    <div class="page-tools">
        <span class="text-muted"><i class="fas fa-clock"></i> {{ now()->format('l, F d, Y') }}</span>
    </div>
</div>

@php
    // Get tenant's current plan
    $tenant = App\Models\Tenant::where('slug', $slug)->first();
    $tenantPlan = $tenant->plan;

    // Check for updates
    $updater = app(\Codedge\Updater\UpdaterManager::class);
    $isNewVersionAvailable = $updater->source()->isNewVersionAvailable();
    $currentVersion = $updater->source()->getVersionInstalled();
    $newVersion = $isNewVersionAvailable ? $updater->source()->getVersionAvailable() : null;
@endphp

@if($tenant->hasNoPlan())
<div class="alert alert-warning shadow-sm border-0 rounded-lg">
    <div class="d-flex align-items-center">
        <div class="me-3">
            <i class="fas fa-exclamation-triangle fa-2x"></i>
        </div>
        <div>
            <h5 class="mb-1">{{ __('You are currently on the basic "No Plan" subscription') }}</h5>
            <p class="mb-2">{{ __('Upgrade to a premium plan to access advanced features like Pageant Management and Reports.') }}</p>
            <a href="{{ route('tenant.subscription.plans', ['slug' => $slug]) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-arrow-up"></i> {{ __('Upgrade Now') }}
            </a>
        </div>
    </div>
</div>
@endif

<!-- OVERVIEW SECTION WITH STATS & GRAPH -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">{{ __('Pageant Performance') }}</h5>
                <div class="chart-container" style="position: relative; height:260px; width:100%">
                    <canvas id="pageantActivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title fw-bold mb-3">{{ __('Quick Stats') }}</h5>
                
                <div class="stats-item d-flex align-items-center mb-3 pb-2 border-bottom">
                    <div class="stats-icon bg-primary text-white rounded-circle p-2 me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ __('Contestants') }}</h6>
                        <h3 class="mb-0 fw-bold">{{ $tenant->hasNoPlan() ? '--' : rand(10, 30) }}</h3>
                    </div>
                </div>
                
                <div class="stats-item d-flex align-items-center mb-3 pb-2 border-bottom">
                    <div class="stats-icon bg-success text-white rounded-circle p-2 me-3">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ __('Events') }}</h6>
                        <h3 class="mb-0 fw-bold">{{ $tenant->hasNoPlan() ? '--' : rand(3, 8) }}</h3>
                    </div>
                </div>
                
                <div class="stats-item d-flex align-items-center">
                    <div class="stats-icon bg-info text-white rounded-circle p-2 me-3">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ __('Judges') }}</h6>
                        <h3 class="mb-0 fw-bold">{{ $tenant->hasNoPlan() ? '--' : rand(5, 12) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SYSTEM UPDATES ALERT (Only for owners) -->
@if(auth()->guard('tenant')->user()->role === 'owner' && $isNewVersionAvailable)
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-warning shadow-sm border-0">
            <div class="card-body d-flex align-items-center">
                <div class="me-3">
                    <div class="avatar avatar-lg bg-warning-light rounded-circle">
                        <i class="fas fa-sync text-warning fa-lg"></i>
                    </div>
                </div>
                <div>
                    <h5 class="card-title mb-1">{{ __('System Update Available!') }}</h5>
                    <p class="mb-2">{{ __('A new version') }} (v{{ $newVersion }}) {{ __('is available. Your current version is') }} v{{ $currentVersion }}</p>
                    <a href="{{ route('tenant.updates.index', ['slug' => $slug]) }}" class="btn btn-sm btn-light">
                        <i class="fas fa-download me-1"></i> {{ __('Update Now') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- MAIN FEATURES SECTION with improved layout -->
<div class="row mb-4">
    <div class="col-lg-12">
        <h4 class="section-title fw-bold mb-3">{{ __('Core Features') }}</h4>
    </div>
</div>

<div class="row mb-4">
    <!-- User Management Card -->
    <div class="col-sm-6 col-lg-4 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body position-relative">
                <div class="position-absolute top-0 end-0 mt-3 me-3">
                    <div class="icon-circle bg-primary-light">
                        <i class="fas fa-user-plus text-primary"></i>
                    </div>
                </div>
                <h5 class="card-title fw-bold mt-4 mb-2">{{ __('User Management') }}</h5>
                <p class="card-text text-muted mb-4">{{ __('Invite and manage users for your pageant. Assign roles and permissions.') }}</p>
                <a href="{{ route('tenant.users.index', ['slug' => $slug]) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-users me-1"></i> {{ __('Manage Users') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Subscription Management Card -->
    <div class="col-sm-6 col-lg-4 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body position-relative">
                <div class="position-absolute top-0 end-0 mt-3 me-3">
                    <div class="icon-circle bg-success-light">
                        <i class="fas fa-crown text-success"></i>
                    </div>
                </div>
                <h5 class="card-title fw-bold mt-4 mb-2">{{ __('Subscription') }}</h5>
                <p class="card-text text-muted mb-4">{{ __('Manage your subscription plan and access premium features.') }}</p>
                <a href="{{ route('tenant.subscription.plans', ['slug' => $slug]) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-crown me-1"></i> 
                    {{ __('View Plans') }}
                    @if($tenant->hasNoPlan())
                        <span class="badge bg-danger ms-1">No Plan</span>
                    @elseif(session('trial_days_left'))
                        <span class="badge bg-warning ms-1">Trial: {{ session('trial_days_left') }} days</span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    @if(auth()->guard('tenant')->user()->role === 'owner')
    <!-- System Updates Card (Only visible to owners) -->
    <div class="col-sm-6 col-lg-4 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body position-relative">
                <div class="position-absolute top-0 end-0 mt-3 me-3">
                    <div class="icon-circle {{ $isNewVersionAvailable ? 'bg-warning-light' : 'bg-success-light' }}">
                        <i class="fas fa-sync {{ $isNewVersionAvailable ? 'text-warning' : 'text-success' }}"></i>
                    </div>
                </div>
                <h5 class="card-title fw-bold mt-4 mb-2">{{ __('System Updates') }}</h5>
                <p class="card-text text-muted mb-4">
                    @if($isNewVersionAvailable)
                        {{ __('New version available! Update to get the latest features.') }}
                    @else
                        {{ __('Your system is up to date with the latest features.') }}
                    @endif
                </p>
                <a href="{{ route('tenant.updates.index', ['slug' => $slug]) }}" class="btn {{ $isNewVersionAvailable ? 'btn-warning' : 'btn-success' }} btn-sm">
                    <i class="fas fa-{{ $isNewVersionAvailable ? 'download' : 'check' }} me-1"></i>
                    @if($isNewVersionAvailable)
                        {{ __('Update Now') }} <span class="badge bg-light text-dark ms-1">v{{ $newVersion }}</span>
                    @else
                        {{ __('Check Updates') }} <span class="badge bg-light text-dark ms-1">v{{ $currentVersion }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- PAGEANT MANAGEMENT SECTION - Only show if plan allows -->
@if(!$tenant->hasNoPlan() && $tenantPlan->pageant_management)
<div class="row mb-4">
    <div class="col-lg-12">
        <h4 class="section-title fw-bold mb-3">{{ __('Pageant Management') }}</h4>
    </div>
</div>

<div class="row mb-4">
    <!-- Contestants Card -->
    <div class="col-sm-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body text-center">
                <div class="icon-circle-lg bg-primary-light mx-auto mb-4">
                    <i class="fas fa-users text-primary"></i>
                </div>
                <h5 class="card-title">{{ __('Contestants') }}</h5>
                <p class="card-text text-muted small">{{ __('Manage contestants in your pageant.') }}</p>
                <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-sm btn-outline-primary mt-2">
                    {{ __('View Contestants') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Categories Card -->
    <div class="col-sm-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body text-center">
                <div class="icon-circle-lg bg-secondary-light mx-auto mb-4">
                    <i class="fas fa-tags text-secondary"></i>
                </div>
                <h5 class="card-title">{{ __('Categories') }}</h5>
                <p class="card-text text-muted small">{{ __('Define competition categories.') }}</p>
                <a href="{{ route('tenant.categories.index', ['slug' => $slug]) }}" class="btn btn-sm btn-outline-secondary mt-2">
                    {{ __('Manage Categories') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Events Card -->
    <div class="col-sm-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body text-center">
                <div class="icon-circle-lg bg-success-light mx-auto mb-4">
                    <i class="fas fa-calendar-alt text-success"></i>
                </div>
                <h5 class="card-title">{{ __('Events') }}</h5>
                <p class="card-text text-muted small">{{ __('Schedule and manage pageant events.') }}</p>
                <a href="{{ route('tenant.events.index', ['slug' => $slug]) }}" class="btn btn-sm btn-outline-success mt-2">
                    {{ __('View Events') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Judges Card -->
    <div class="col-sm-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body text-center">
                <div class="icon-circle-lg bg-info-light mx-auto mb-4">
                    <i class="fas fa-gavel text-info"></i>
                </div>
                <h5 class="card-title">{{ __('Judges') }}</h5>
                <p class="card-text text-muted small">{{ __('Manage judges for your pageant.') }}</p>
                <a href="#" class="btn btn-sm btn-outline-info mt-2">
                    {{ __('View Judges') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Event Assignments Card -->
    <div class="col-sm-6 col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body position-relative">
                <div class="position-absolute top-0 end-0 mt-3 me-3">
                    <div class="icon-circle bg-warning-light">
                        <i class="fas fa-tasks text-warning"></i>
                    </div>
                </div>
                <h5 class="card-title fw-bold mt-4 mb-2">{{ __('Event Assignments') }}</h5>
                <p class="card-text text-muted mb-4">{{ __('Manage event assignments and schedules for your pageant.') }}</p>
                <a href="{{ route('tenant.event-assignments.index', ['slug' => $slug]) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-eye me-1"></i> {{ __('View Assignments') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Scores Card -->
    <div class="col-sm-6 col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body position-relative">
                <div class="position-absolute top-0 end-0 mt-3 me-3">
                    <div class="icon-circle bg-danger-light">
                        <i class="fas fa-star text-danger"></i>
                    </div>
                </div>
                <h5 class="card-title fw-bold mt-4 mb-2">{{ __('Scores') }}</h5>
                <p class="card-text text-muted mb-4">{{ __('View and manage scores from judges for your pageant events.') }}</p>
                <a href="{{ route('tenant.scores.index', ['slug' => $slug]) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-eye me-1"></i> {{ __('View Scores') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Reports Card (Only if module is enabled) -->
    @if($tenantPlan->reports_module)
    <div class="col-sm-6 col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
            <div class="card-body position-relative">
                <div class="position-absolute top-0 end-0 mt-3 me-3">
                    <div class="icon-circle bg-secondary-light">
                        <i class="fas fa-chart-bar text-secondary"></i>
                    </div>
                </div>
                <h5 class="card-title fw-bold mt-4 mb-2">{{ __('Reports') }}</h5>
                <p class="card-text text-muted mb-4">{{ __('Generate reports and insights for your pageant events.') }}</p>
                <a href="{{ route('tenant.reports.generate', ['slug' => $slug]) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-file-pdf me-1"></i> {{ __('Generate PDF Report') }}
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

@else
<!-- LOCKED FEATURES (If on No Plan) -->
@if($tenant->hasNoPlan())
<div class="row mb-4">
    <div class="col-lg-12">
        <h4 class="section-title fw-bold mb-3">{{ __('Premium Features') }}</h4>
    </div>
</div>

<div class="row mb-4">
    <!-- Locked Pageant Management Card -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body position-relative">
                <div class="locked-overlay text-center p-5">
                    <i class="fas fa-lock fa-3x mb-3 text-secondary"></i>
                    <h5>{{ __('Pageant Management') }}</h5>
                    <p class="mb-3">{{ __('Unlock to manage contestants, events, judges, and more.') }}</p>
                    <a href="{{ route('tenant.subscription.plans', ['slug' => $slug]) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-up me-1"></i> {{ __('Upgrade Plan to Unlock') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Locked Reports Module Card -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body position-relative">
                <div class="locked-overlay text-center p-5">
                    <i class="fas fa-lock fa-3x mb-3 text-secondary"></i>
                    <h5>{{ __('Reports Module') }}</h5>
                    <p class="mb-3">{{ __('Unlock to access analytics and insights for your pageant.') }}</p>
                    <a href="{{ route('tenant.subscription.plans', ['slug' => $slug]) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-up me-1"></i> {{ __('Upgrade Plan to Unlock') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endif

<!-- SCORE DISTRIBUTION CHART - Only show if plan allows -->
@if(!$tenant->hasNoPlan() && $tenantPlan->pageant_management)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">{{ __('Score Distribution by Category') }}</h5>
                <div class="chart-container" style="position: relative; height:300px; width:100%">
                    <canvas id="scoreDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Activity chart data
        const activityData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Events',
                backgroundColor: 'rgba(66, 133, 244, 0.2)',
                borderColor: 'rgba(66, 133, 244, 1)',
                borderWidth: 2,
                data: [3, 5, 2, 8, 6, 4],
                tension: 0.4
            }, {
                label: 'Contestants',
                backgroundColor: 'rgba(219, 68, 55, 0.2)',
                borderColor: 'rgba(219, 68, 55, 1)',
                borderWidth: 2,
                data: [12, 15, 18, 14, 20, 25],
                tension: 0.4
            }]
        };

        // Score distribution data
        const scoreData = {
            labels: ['Beauty', 'Talent', 'Intelligence', 'Fitness', 'Personality'],
            datasets: [{
                label: 'Average Score',
                data: [8.5, 7.9, 8.2, 8.7, 9.1],
                backgroundColor: [
                    'rgba(66, 133, 244, 0.7)',
                    'rgba(219, 68, 55, 0.7)',
                    'rgba(244, 180, 0, 0.7)',
                    'rgba(15, 157, 88, 0.7)',
                    'rgba(171, 71, 188, 0.7)'
                ],
                borderWidth: 1
            }]
        };

        // Create activity chart
        const activityCtx = document.getElementById('pageantActivityChart')?.getContext('2d');
        if (activityCtx) {
            new Chart(activityCtx, {
                type: 'line',
                data: activityData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Create score distribution chart
        const scoreCtx = document.getElementById('scoreDistributionChart')?.getContext('2d');
        if (scoreCtx) {
            new Chart(scoreCtx, {
                type: 'radar',
                data: scoreData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    elements: {
                        line: {
                            borderWidth: 3
                        }
                    },
                    scales: {
                        r: {
                            angleLines: {
                                display: true
                            },
                            suggestedMin: 0,
                            suggestedMax: 10
                        }
                    }
                }
            });
        }
    });
</script>

<style>
    /* Additional custom styles */
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .transition-all {
        transition: all 0.3s ease;
    }
    
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .icon-circle-lg {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-primary-light { background-color: rgba(66, 133, 244, 0.15); }
    .bg-success-light { background-color: rgba(15, 157, 88, 0.15); }
    .bg-warning-light { background-color: rgba(244, 180, 0, 0.15); }
    .bg-danger-light { background-color: rgba(219, 68, 55, 0.15); }
    .bg-info-light { background-color: rgba(66, 186, 255, 0.15); }
    .bg-secondary-light { background-color: rgba(108, 117, 125, 0.15); }
    
    .section-title {
        position: relative;
        padding-bottom: 8px;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        width: 50px;
        height: 3px;
        background: #4285f4;
        bottom: 0;
        left: 0;
    }
    
    .locked-overlay {
        border: 1px dashed #ccc;
        border-radius: 8px;
    }
</style>
@endpush