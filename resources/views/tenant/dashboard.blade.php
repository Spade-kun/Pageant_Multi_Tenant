@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">{{ __('Welcome to your Pageant Dashboard') }}</h4>
    </div>
    
    <div class="row">
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
                        <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-success btn-round">
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
                        <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-info btn-round">
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
                                <i class="fas fa-list-alt"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="card-category">{{ __('Scoring Criteria') }}</p>
                                <h4 class="card-title">{{ __('Define the criteria and weights for judging.') }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-action mt-3">
                        <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-warning btn-round">
                            <span class="btn-label">
                                <i class="fas fa-cog"></i>
                            </span>
                            {{ __('Manage Criteria') }}
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
                        <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-danger btn-round">
                            <span class="btn-label">
                                <i class="fas fa-eye"></i>
                            </span>
                            {{ __('View Scores') }}
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
                        <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-secondary btn-round">
                            <span class="btn-label">
                                <i class="fas fa-file-alt"></i>
                            </span>
                            {{ __('View Reports') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
