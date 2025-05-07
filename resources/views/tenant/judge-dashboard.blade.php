@extends('layouts.TenantDashboardTemplate')

@section('title', 'Judge Dashboard')

@section('styles')
<style>
    .stats-card {
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .event-card {
        border-left: 4px solid #1572E8;
        transition: all 0.3s;
    }
    
    .event-card:hover {
        background-color: #f8f9fa;
    }
    
    .event-card.upcoming {
        border-left-color: #6861CE;
    }
    
    .event-card.active {
        border-left-color: #31CE36;
    }
    
    .event-card.past {
        border-left-color: #FFAD46;
    }
    
    .status-badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    
    .category-badge {
        font-size: 0.75rem;
        background-color: #f1f1f1;
        color: #444;
        padding: 0.15rem 0.5rem;
        border-radius: 15px;
        margin-right: 0.25rem;
        margin-bottom: 0.25rem;
        display: inline-block;
    }
    
    .action-btn {
        min-width: 100px;
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        background-color: #f8f9fa;
        border-radius: 10px;
        margin: 1.5rem 0;
    }
    
    .empty-state i {
        font-size: 2.5rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
@php
    // Get the current judge's email from the session
    $judgeEmail = session('tenant_user.email');
    $slug = request()->route('slug');
    
    // Set up the tenant database connection
    $tenant = \App\Models\Tenant::where('slug', $slug)->firstOrFail();
    $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
    
    \Illuminate\Support\Facades\Config::set('database.connections.tenant', [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => $databaseName,
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
    ]);
    
    \Illuminate\Support\Facades\DB::purge('tenant');
    \Illuminate\Support\Facades\DB::reconnect('tenant');
    
    // Find the judge record
    $judge = \Illuminate\Support\Facades\DB::connection('tenant')
        ->table('judges')
        ->where('email', $judgeEmail)
        ->first();
    
    $judgeId = $judge ? $judge->id : null;
    
    // Fetch assigned events (events where this judge has scores or assignments)
    $assignedEvents = 0;
    if ($judgeId) {
        $assignedEvents = \Illuminate\Support\Facades\DB::connection('tenant')
            ->table('events')
            ->distinct()
            ->join('scores', 'events.id', '=', 'scores.event_id')
            ->where('scores.judge_id', $judgeId)
            ->count('events.id');
    }
    
    // Fetch events that have been judged (completed scoring)
    $eventsJudged = 0;
    if ($judgeId) {
        // Get events with status 'completed' or 'past' where this judge has submitted scores
        $eventsJudged = \Illuminate\Support\Facades\DB::connection('tenant')
            ->table('events')
            ->distinct()
            ->join('scores', 'events.id', '=', 'scores.event_id')
            ->where('scores.judge_id', $judgeId)
            ->where('events.status', 'completed')
            ->count('events.id');
    }
    
    // Fetch categories this judge has been assigned to score
    $categoriesCount = 0;
    if ($judgeId) {
        $categoriesCount = \Illuminate\Support\Facades\DB::connection('tenant')
            ->table('categories')
            ->distinct()
            ->join('scores', 'categories.id', '=', 'scores.category_id')
            ->where('scores.judge_id', $judgeId)
            ->count('categories.id');
    }
    
    // Fetch contestants this judge has scored or will score
    $contestantsCount = 0;
    if ($judgeId) {
        $contestantsCount = \Illuminate\Support\Facades\DB::connection('tenant')
            ->table('contestants')
            ->distinct()
            ->join('scores', 'contestants.id', '=', 'scores.contestant_id')
            ->where('scores.judge_id', $judgeId)
            ->count('contestants.id');
    }
    
    // Fetch upcoming and active events for this judge
    $judgeEvents = [];
    if ($judgeId) {
        // Get all distinct events where this judge has scores
        $eventIds = \Illuminate\Support\Facades\DB::connection('tenant')
            ->table('scores')
            ->where('judge_id', $judgeId)
            ->distinct()
            ->pluck('event_id');
        
        if (count($eventIds) > 0) {
            $judgeEvents = \Illuminate\Support\Facades\DB::connection('tenant')
                ->table('events')
                ->whereIn('id', $eventIds)
                ->get();
            
            // For each event, get categories and contestant count
            foreach ($judgeEvents as &$event) {
                // Get categories for this event
                $event->categories = \Illuminate\Support\Facades\DB::connection('tenant')
                    ->table('categories')
                    ->distinct()
                    ->join('scores', 'categories.id', '=', 'scores.category_id')
                    ->where('scores.event_id', $event->id)
                    ->where('scores.judge_id', $judgeId)
                    ->pluck('categories.name')
                    ->toArray();
                
                // Get contestant count for this event
                $event->contestant_count = \Illuminate\Support\Facades\DB::connection('tenant')
                    ->table('contestants')
                    ->distinct()
                    ->join('scores', 'contestants.id', '=', 'scores.contestant_id')
                    ->where('scores.event_id', $event->id)
                    ->where('scores.judge_id', $judgeId)
                    ->count('contestants.id');
            }
        }
    }
@endphp

<!-- HEADER SECTION -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="page-title fw-bold">Judge Dashboard</h4>
        <p class="text-muted">Welcome, {{ session('tenant_user.name') }}!</p>
    </div>
    <div class="page-tools">
        <span class="text-muted"><i class="fas fa-clock"></i> {{ now()->format('l, F d, Y') }}</span>
    </div>
</div>

<div class="row">
    <!-- STATS SECTION -->
    <div class="col-md-6 col-xl-3">
        <div class="card stats-card bg-primary bg-gradient text-white mb-4">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0">Assigned Events</h5>
                    <h2 class="mt-2 mb-0">{{ $assignedEvents }}</h2>
                </div>
                <div>
                    <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="col-md-6 col-xl-3">
        <div class="card stats-card bg-info bg-gradient text-white mb-4">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0">Categories</h5>
                    <h2 class="mt-2 mb-0">{{ $categoriesCount }}</h2>
                </div>
                <div>
                    <i class="fas fa-layer-group fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card stats-card bg-warning bg-gradient text-white mb-4">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0">Contestants</h5>
                    <h2 class="mt-2 mb-0">{{ $contestantsCount }}</h2>
                </div>
                <div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- UPCOMING EVENTS SECTION -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Your Judging Assignments</h4>
                <div>
                    <button class="btn btn-sm btn-primary" onclick="refreshAssignments()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(count($judgeEvents) > 0)
                    @foreach($judgeEvents as $event)
                        <div class="event-card card mb-3 {{ $event->status }}">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="card-title mb-1">{{ $event->name }}</h5>
                                        <p class="mb-2 text-muted">
                                            <i class="fas fa-calendar-day"></i> 
                                            {{ $event->start_date ? date('F d, Y - h:i A', strtotime($event->start_date)) : 'Date TBD' }}
                                        </p>
                                        <p class="mb-2 text-muted">
                                            <i class="fas fa-map-marker-alt"></i> {{ $event->location ?? 'Location TBD' }}
                                        </p>
                                        <p class="mb-0">
                                            <span class="badge bg-info text-white rounded-pill">
                                                <i class="fas fa-users"></i> {{ $event->contestant_count }} Contestants
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Categories:</strong></p>
                                        <div>
                                            @foreach($event->categories as $category)
                                                <span class="category-badge">{{ $category }}</span>
                                            @endforeach
                                        </div>
                                        <p class="mt-2 mb-0">
                                            @if($event->status == 'active')
                                                <span class="badge bg-success status-badge">In Progress</span>
                                            @elseif($event->status == 'upcoming')
                                                <span class="badge bg-primary status-badge">Upcoming</span>
                                            @elseif($event->status == 'completed')
                                                <span class="badge bg-secondary status-badge">Completed</span>
                                            @else
                                                <span class="badge bg-info status-badge">{{ ucfirst($event->status) }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        @if($event->status == 'active')
                                            <a href="{{ route('tenant.judges.scoring.index', ['slug' => $slug]) }}" class="btn btn-success btn-sm action-btn mb-2">
                                                <i class="fas fa-gavel"></i> Score Now
                                            </a>
                                        @elseif($event->status == 'upcoming')
                                            <a href="{{ route('tenant.judges.scoring.index', ['slug' => $slug]) }}" class="btn btn-info btn-sm action-btn mb-2">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        @else
                                            <a href="{{ route('tenant.judges.scoring.index', ['slug' => $slug]) }}" class="btn btn-secondary btn-sm action-btn mb-2">
                                                <i class="fas fa-chart-bar"></i> View Results
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="fas fa-calendar-alt"></i>
                        <h5>No Assignments Yet</h5>
                        <p class="text-muted">You don't have any judging assignments at the moment.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- QUICK GUIDE SECTION -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Judge's Quick Guide</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light mb-3">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                                <h5>Scoring Guidelines</h5>
                                <p class="mb-0">Review the criteria and scoring procedures for each category.</p>
                                <a href="#" class="btn btn-sm btn-outline-primary mt-3">View Guidelines</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light mb-3">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-question-circle fa-3x text-info mb-3"></i>
                                <h5>FAQ for Judges</h5>
                                <p class="mb-0">Find answers to common questions about judging procedures.</p>
                                <a href="#" class="btn btn-sm btn-outline-info mt-3">Read FAQ</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light mb-3">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-video fa-3x text-success mb-3"></i>
                                <h5>Tutorial Videos</h5>
                                <p class="mb-0">Watch instructional videos on how to use the scoring system.</p>
                                <a href="#" class="btn btn-sm btn-outline-success mt-3">Watch Tutorials</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 

@push('js')
<script>
    function refreshAssignments() {
        // In a real implementation, this would fetch updated assignments
        // For demo, just show a loading indicator and refresh the page
        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        btn.disabled = true;
        
        setTimeout(() => {
            location.reload();
        }, 1500);
    }
</script>
@endpush 