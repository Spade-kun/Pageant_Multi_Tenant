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

                        <!-- Leaderboard -->
                        <div class="card mb-4">
                            <div class="card-header bg-gradient-primary text-black">
                                <h5 class="mb-0"><i class="fas fa-trophy mr-2 "></i> Event Leaderboard</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    // Get all contestants for this event
                                    $contestants = DB::connection('tenant')
                                        ->table('scores')
                                        ->join('contestants', 'scores.contestant_id', '=', 'contestants.id')
                                        ->where('scores.event_id', $event->id)
                                        ->select('contestants.id', 'contestants.name')
                                        ->distinct()
                                        ->get();
                                    
                                    // Calculate total score for each contestant
                                    $leaderboard = [];
                                    foreach ($contestants as $contestant) {
                                        $totalScore = DB::connection('tenant')
                                            ->table('scores')
                                            ->where('event_id', $event->id)
                                            ->where('contestant_id', $contestant->id)
                                            ->sum('weighted_score');
                                        
                                        // Count how many categories were scored
                                        $scoredCategories = DB::connection('tenant')
                                            ->table('scores')
                                            ->where('event_id', $event->id)
                                            ->where('contestant_id', $contestant->id)
                                            ->count();
                                            
                                        // Get total number of categories for this event
                                        $totalCategories = DB::connection('tenant')
                                            ->table('categories')
                                            ->count();
                                            
                                        $completionPercentage = $totalCategories > 0 ? 
                                            ($scoredCategories / $totalCategories) * 100 : 0;
                                        
                                        $leaderboard[] = [
                                            'id' => $contestant->id,
                                            'name' => $contestant->name,
                                            'total_score' => $totalScore,
                                            'scored_categories' => $scoredCategories,
                                            'completion' => $completionPercentage
                                        ];
                                    }
                                    
                                    // Sort by total score (highest to lowest)
                                    usort($leaderboard, function($a, $b) {
                                        return $b['total_score'] <=> $a['total_score'];
                                    });
                                @endphp
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="5%" class="text-center">Rank</th>
                                                <th width="20%">Contestant</th>
                                                <th width="60%">Score Progress</th>
                                                <th width="15%" class="text-right">Total Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($leaderboard as $index => $contestant)
                                                <tr class="{{ $index < 3 ? 'table-'.($index == 0 ? 'warning' : ($index == 1 ? 'light' : 'secondary')) : '' }}">
                                                    <td class="text-center">
                                                        @if($index == 0)
                                                            <div class="position-relative">
                                                                <span class="badge badge-warning position-relative" style="font-size: 1.1rem;">
                                                                    <i class="fas fa-crown" style="color: #FFD700;"></i> 1
                                                                </span>
                                                            </div>
                                                        @elseif($index == 1)
                                                            <div class="position-relative">
                                                                <span class="badge badge-light position-relative" style="font-size: 1rem;">
                                                                    <i class="fas fa-medal" style="color: #C0C0C0;"></i> 2
                                                                </span>
                                                            </div>
                                                        @elseif($index == 2)
                                                            <div class="position-relative">
                                                                <span class="badge badge-secondary position-relative" style="font-size: 0.9rem;">
                                                                    <i class="fas fa-medal" style="color: #CD7F32;"></i> 3
                                                                </span>
                                                            </div>
                                                        @else
                                                            <span class="badge badge-light">{{ $index + 1 }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="{{ $index < 3 ? 'font-weight-bold' : '' }}">
                                                        {{ $contestant['name'] }}
                                                        @if($contestant['completion'] < 100)
                                                            <span class="badge badge-info ml-1">
                                                                <i class="fas fa-hourglass-half"></i> In Progress
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            // Get max possible total score
                                                            $categories = DB::connection('tenant')
                                                                ->table('categories')
                                                                ->sum('percentage');
                                                            
                                                            // Calculate percentage of max score
                                                            $maxPossibleScore = $categories; // 100% per category
                                                            $percentage = $maxPossibleScore > 0 ? ($contestant['total_score'] / $maxPossibleScore) * 100 : 0;
                                                            $percentage = min(100, $percentage); // Cap at 100%
                                                            
                                                            // Determine bar color
                                                            $barColor = 'primary';
                                                            if ($index == 0) $barColor = 'warning';
                                                            elseif ($index == 1) $barColor = 'info';
                                                            elseif ($index == 2) $barColor = 'success';
                                                        @endphp
                                                        <div class="progress" style="height: 25px; box-shadow: inset 0 1px 2px rgba(0,0,0,.1);">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-{{ $barColor }}" 
                                                                role="progressbar" 
                                                                style="width: {{ $percentage }}%;" 
                                                                aria-valuenow="{{ $percentage }}" 
                                                                aria-valuemin="0" 
                                                                aria-valuemax="100">
                                                                {{ number_format($percentage, 1) }}%
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ $contestant['scored_categories'] }} of {{ $totalCategories }} categories scored
                                                        </small>
                                                    </td>
                                                    <td class="text-right font-weight-bold {{ $index == 0 ? 'text-warning' : ($index == 1 ? 'text-info' : ($index == 2 ? 'text-success' : '')) }}" style="font-size: 1.1rem;">
                                                        {{ number_format($contestant['total_score'], 2) }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">
                                                        <div class="alert alert-light">
                                                            <i class="fas fa-info-circle mr-2"></i> No scores available yet.
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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