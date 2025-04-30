@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-header card-header-warning card-header-icon">
                    <div class="card-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="card-category">Total Scores</p>
                    <h3 class="card-title">
                        {{ DB::connection('tenant')->table('scores')->count() }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-header card-header-success card-header-icon">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <p class="card-category">Contestants</p>
                    <h3 class="card-title">
                        {{ DB::connection('tenant')->table('contestants')->count() }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-header card-header-info card-header-icon">
                    <div class="card-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <p class="card-category">Judges</p>
                    <h3 class="card-title">
                        {{ DB::connection('tenant')->table('judges')->count() }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-header card-header-primary card-header-icon">
                    <div class="card-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <p class="card-category">Events</p>
                    <h3 class="card-title">
                        {{ DB::connection('tenant')->table('events')->count() }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Events List -->
    <div class="row mt-4">
        <div class="col-md-12">
            @php
                $events = DB::connection('tenant')
                    ->table('events')
                    ->orderBy('start_date', 'desc')
                    ->get();
            @endphp

            @foreach($events as $event)
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title">{{ $event->name }}</h4>
                        <p class="text-muted">
                            Event Date: {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y h:i A') }} - 
                            {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y h:i A') }}
                        </p>
                        <p class="text-muted">Location: {{ $event->location }}</p>
                        <p class="text-muted">
                            Status: 
                            <span class="badge badge-{{ 
                                $event->status == 'scheduled' ? 'primary' : 
                                ($event->status == 'ongoing' ? 'success' : 
                                ($event->status == 'completed' ? 'info' : 'danger')) 
                            }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </p>
                    </div>
                    <div class="card-body">
                        <!-- Event Summary -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Contestants</span>
                                        <span class="info-box-number">
                                            {{ DB::connection('tenant')
                                                ->table('scores')
                                                ->where('event_id', $event->id)
                                                ->distinct('contestant_id')
                                                ->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-gavel"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Judges</span>
                                        <span class="info-box-number">
                                            {{ DB::connection('tenant')
                                                ->table('scores')
                                                ->where('event_id', $event->id)
                                                ->distinct('judge_id')
                                                ->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-list"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Categories</span>
                                        <span class="info-box-number">
                                            {{ DB::connection('tenant')
                                                ->table('scores')
                                                ->where('event_id', $event->id)
                                                ->distinct('category_id')
                                                ->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-star"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Scores</span>
                                        <span class="info-box-number">
                                            {{ DB::connection('tenant')
                                                ->table('scores')
                                                ->where('event_id', $event->id)
                                                ->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Categories Breakdown -->
                        <h5 class="mb-3">Categories</h5>
                        <div class="row">
                            @php
                                $categories = DB::connection('tenant')
                                    ->table('scores')
                                    ->join('categories', 'scores.category_id', '=', 'categories.id')
                                    ->where('scores.event_id', $event->id)
                                    ->select('categories.id', 'categories.name')
                                    ->distinct()
                                    ->get();
                            @endphp

                            @foreach($categories as $category)
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="card-title">{{ $category->name }}</h6>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $scores = DB::connection('tenant')
                                                    ->table('scores')
                                                    ->join('contestants', 'scores.contestant_id', '=', 'contestants.id')
                                                    ->join('judges', 'scores.judge_id', '=', 'judges.id')
                                                    ->where('scores.event_id', $event->id)
                                                    ->where('scores.category_id', $category->id)
                                                    ->select(
                                                        'contestants.name as contestant_name',
                                                        'judges.name as judge_name',
                                                        'scores.raw_score',
                                                        'scores.weighted_score',
                                                        'scores.comments'
                                                    )
                                                    ->orderBy('contestants.name')
                                                    ->get();
                                            @endphp

                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Contestant</th>
                                                            <th>Judge</th>
                                                            <th>Score</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($scores as $score)
                                                            <tr>
                                                                <td>{{ $score->contestant_name }}</td>
                                                                <td>{{ $score->judge_name }}</td>
                                                                <td>{{ number_format($score->weighted_score, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection 