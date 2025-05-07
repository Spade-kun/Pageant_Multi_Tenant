@extends('layouts.TenantDashboardTemplate')

@section('title', 'User Dashboard')

@section('styles')
<style>
    .stats-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s;
        margin-bottom: 1.5rem;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .stats-card .card-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        background: rgba(255,255,255,0.2);
    }
    
    .stats-card .card-icon i {
        font-size: 1.8rem;
        color: white;
    }
    
    .event-card {
        border-radius: 8px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s;
        overflow: hidden;
        border: none;
    }
    
    .event-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.12);
    }
    
    .event-card .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        background: #fff;
    }
    
    .event-card .card-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .event-info {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .event-info i {
        width: 20px;
        margin-right: 8px;
        color: #6c757d;
    }
    
    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .status-badge.scheduled {
        background-color: #d1e7ff;
        color: #0047b3;
    }
    
    .status-badge.ongoing {
        background-color: #d1f3e0;
        color: #0f5132;
    }
    
    .status-badge.completed {
        background-color: #e0e0e0;
        color: #495057;
    }
    
    .stats-box {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .stats-box-icon {
        width: 45px;
        height: 45px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }
    
    .stats-box-icon i {
        font-size: 1.2rem;
        color: white;
    }
    
    .stats-box-content .stats-box-number {
        font-size: 1.5rem;
        font-weight: 600;
        line-height: 1.2;
    }
    
    .stats-box-content .stats-box-text {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .category-card {
        height: 100%;
        border-radius: 8px;
        box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        transition: all 0.3s;
        border: none;
    }
    
    .category-card:hover {
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .category-card .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 0.75rem 1rem;
    }
    
    .category-card .card-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin: 1rem 0;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #adb5bd;
        margin-bottom: 1rem;
    }
    
    table.score-table {
        font-size: 0.9rem;
    }
    
    table.score-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .page-header {
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="page-title fw-bold">User Dashboard</h4>
        <p class="text-muted">Overview of pageant events and statistics</p>
    </div>
    <div class="page-tools">
        <span class="text-muted"><i class="fas fa-clock"></i> {{ now()->format('l, F d, Y') }}</span>
    </div>
</div>

<div class="container-fluid px-0">
    <!-- Summary Cards -->
    <div class="row">
        
        <div class="col-md-6 col-xl-3">
            <div class="card stats-card bg-success bg-gradient text-white">
                <div class="card-body d-flex align-items-center">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="mb-1">Contestants</p>
                        <h2 class="mb-0 fw-bold">{{ DB::connection('tenant')->table('contestants')->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stats-card bg-info bg-gradient text-white">
                <div class="card-body d-flex align-items-center">
                    <div class="card-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <div>
                        <p class="mb-1">Judges</p>
                        <h2 class="mb-0 fw-bold">{{ DB::connection('tenant')->table('judges')->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stats-card bg-warning bg-gradient text-white">
                <div class="card-body d-flex align-items-center">
                    <div class="card-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <p class="mb-1">Events</p>
                        <h2 class="mb-0 fw-bold">{{ DB::connection('tenant')->table('events')->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Events List -->
    <div class="row mt-3">
        <div class="col-md-12">
            <h4 class="section-title mb-3"><i class="fas fa-calendar-alt me-2"></i> Events</h4>
            
            @php
                $events = DB::connection('tenant')
                    ->table('events')
                    ->orderBy('start_date', 'desc')
                    ->get();
            @endphp

            @if(count($events) > 0)
                @foreach($events as $event)
                    <div class="event-card card">
                        <div class="card-header">
                            <h4 class="card-title">{{ $event->name }}</h4>
                            <div class="event-info">
                                <i class="fas fa-calendar-day"></i>
                                <span>{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y h:i A') }} - 
                                {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="event-info">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $event->location ?: 'Location TBD' }}</span>
                            </div>
                            <div class="event-info">
                                <i class="fas fa-info-circle"></i>
                                <span class="status-badge {{ $event->status }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Event Summary -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="stats-box">
                                        <div class="stats-box-icon bg-info">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="stats-box-content">
                                            <div class="stats-box-number">
                                                {{ DB::connection('tenant')
                                                    ->table('scores')
                                                    ->where('event_id', $event->id)
                                                    ->distinct('contestant_id')
                                                    ->count() }}
                                            </div>
                                            <div class="stats-box-text">Contestants</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-box">
                                        <div class="stats-box-icon bg-success">
                                            <i class="fas fa-gavel"></i>
                                        </div>
                                        <div class="stats-box-content">
                                            <div class="stats-box-number">
                                                {{ DB::connection('tenant')
                                                    ->table('scores')
                                                    ->where('event_id', $event->id)
                                                    ->distinct('judge_id')
                                                    ->count() }}
                                            </div>
                                            <div class="stats-box-text">Judges</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-box">
                                        <div class="stats-box-icon bg-warning">
                                            <i class="fas fa-list"></i>
                                        </div>
                                        <div class="stats-box-content">
                                            <div class="stats-box-number">
                                                {{ DB::connection('tenant')
                                                    ->table('scores')
                                                    ->where('event_id', $event->id)
                                                    ->distinct('category_id')
                                                    ->count() }}
                                            </div>
                                            <div class="stats-box-text">Categories</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-box">
                                        <div class="stats-box-icon bg-primary">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="stats-box-content">
                                            <div class="stats-box-number">
                                                {{ DB::connection('tenant')
                                                    ->table('scores')
                                                    ->where('event_id', $event->id)
                                                    ->count() }}
                                            </div>
                                            <div class="stats-box-text">Total Scores</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categories Breakdown -->
                            <h5 class="mb-3"><i class="fas fa-list-ul me-2"></i> Categories</h5>
                            <div class="row row-cols-1 row-cols-md-3 g-4">
                                @php
                                    $categories = DB::connection('tenant')
                                        ->table('scores')
                                        ->join('categories', 'scores.category_id', '=', 'categories.id')
                                        ->where('scores.event_id', $event->id)
                                        ->select('categories.id', 'categories.name')
                                        ->distinct()
                                        ->get();
                                @endphp

                                @if(count($categories) > 0)
                                    @foreach($categories as $category)
                                        <div class="col">
                                            <div class="category-card card h-100">
                                                <div class="card-header">
                                                    <h6 class="card-title">{{ $category->name }}</h6>
                                                </div>
                                                <div class="card-body p-0">
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

                                                    @if(count($scores) > 0)
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-hover score-table mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Contestant</th>
                                                                        <th>Judge</th>
                                                                        <th class="text-end">Score</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($scores as $score)
                                                                        <tr>
                                                                            <td>{{ $score->contestant_name }}</td>
                                                                            <td>{{ $score->judge_name }}</td>
                                                                            <td class="text-end fw-semibold">{{ number_format($score->weighted_score, 2) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="p-3 text-center text-muted">
                                                            <small>No scores available yet</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            No category scores available for this event yet.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h5>No Events Found</h5>
                    <p class="text-muted">There are no events to display at this time.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 