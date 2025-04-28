@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Score Contestants</h1>
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

    @foreach($assignments as $eventId => $eventData)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ $eventData['event_name'] }}</h6>
            </div>
            <div class="card-body">
                @foreach($eventData['categories'] as $categoryId => $category)
                    <div class="mb-4">
                        <h5 class="font-weight-bold">{{ $category }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Contestant</th>
                                        <th>Current Score</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($eventData['contestants'] as $contestantId => $contestant)
                                        <tr>
                                            <td>{{ $contestant }}</td>
                                            <td>
                                                @if(isset($eventData['scores'][$categoryId][$contestantId]))
                                                    {{ number_format($eventData['scores'][$categoryId][$contestantId], 2) }}
                                                @else
                                                    Not scored yet
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('tenant.judges.scoring.score', [
                                                    'slug' => $slug,
                                                    'eventId' => $eventId,
                                                    'contestantId' => $contestantId,
                                                    'categoryId' => $categoryId
                                                ]) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Score
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize any necessary scripts here
    });
</script>
@endsection 