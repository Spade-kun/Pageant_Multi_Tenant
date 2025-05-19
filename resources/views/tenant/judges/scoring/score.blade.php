@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Score Contestant</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Scoring Details</h6>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Event: {{ $event->name }}</h5>
                    <h5>Contestant: {{ $contestant->name }}</h5>
                    <h5>Category: {{ $category->name }}</h5>
                    <h5>Category Weight: {{ $category->percentage }}%</h5>
                </div>
            </div>

            <form action="{{ route('tenant.judges.scoring.store', ['slug' => $slug]) }}" method="POST">
                @csrf
                <input type="hidden" name="event_id" value="{{ $event->id }}">
                <input type="hidden" name="contestant_id" value="{{ $contestant->id }}">
                <input type="hidden" name="category_id" value="{{ $category->id }}">

                <div class="form-group">
                    <label for="raw_score">Raw Score (0-100)</label>
                    <input type="number" class="form-control" id="raw_score" name="raw_score" 
                           min="0" max="100" step="0.1" required
                           value="{{ old('raw_score', $existingScore ? $existingScore->raw_score : '') }}">
                </div>

                <div class="form-group">
                    <label>Weighted Score</label>
                    <div class="alert alert-info">
                        <h4 id="weighted_score">0.00</h4>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comments">Comments (Optional)</label>
                    <textarea class="form-control" id="comments" name="comments" rows="3">{{ old('comments', $existingScore ? $existingScore->comments : '') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Save Score</button>
                <a href="{{ route('tenant.judges.scoring.index', ['slug' => $slug]) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const categoryPercentage = {{ $category->percentage }};
        
        function calculateWeightedScore() {
            const rawScore = parseFloat($('#raw_score').val()) || 0;
            const weightedScore = (rawScore * categoryPercentage) / 100;
            $('#weighted_score').text(weightedScore.toFixed(2));
        }

        $('#raw_score').on('input', calculateWeightedScore);
        calculateWeightedScore(); // Initial calculation
    });
</script>
@endsection 