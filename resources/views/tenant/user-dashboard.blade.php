@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">{{ __('Welcome to Pageant User Dashboard') }}</h4>
    </div>
    
    <div class="row">
        <!-- Contestants Card -->
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
                                <h4 class="card-title">{{ __('View all contestants') }}</h4>
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

        <!-- Events Card -->
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
                                <h4 class="card-title">{{ __('View upcoming events') }}</h4>
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

        <!-- Results Card -->
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-trophy"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">{{ __('Results') }}</p>
                                <h4 class="card-title">{{ __('View competition results') }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-action mt-3">
                        <a href="#" class="btn btn-info btn-round">
                            <span class="btn-label">
                                <i class="fas fa-eye"></i>
                            </span>
                            {{ __('View Results') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 