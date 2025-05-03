@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pageant Scores Overview</h1>
        <div>
            <a href="{{ route('tenant.reports.generate', ['slug' => $slug]) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-file-pdf"></i> Generate PDF Report
            </a>
        </div>
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

    @foreach($events as $event)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $event->name }}</h6>
                    <span class="badge badge-{{ $event->status === 'ongoing' ? 'success' : 'secondary' }}">
                        {{ ucfirst($event->status) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                @foreach($categories as $category)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="font-weight-bold">{{ $category->name }}</h5>
                            <span class="badge badge-info">{{ $category->percentage }}%</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 25%">Contestant</th>
                                        <th style="width: 20%">Judge</th>
                                        <th style="width: 15%">Raw Score</th>
                                        <th style="width: 15%">Weighted Score</th>
                                        <th style="width: 15%">Date Scored</th>
                                        <th style="width: 10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $eventScores = $scores->where('event_id', $event->id)
                                                            ->where('category_id', $category->id);
                                    @endphp

                                    @foreach($contestants as $contestant)
                                        @php
                                            $contestantScores = $eventScores->where('contestant_id', $contestant->id);
                                            $averageRawScore = $contestantScores->avg('raw_score');
                                            $averageWeightedScore = $contestantScores->avg('weighted_score');
                                        @endphp

                                        @foreach($contestantScores as $score)
                                            <tr>
                                                @if($loop->first)
                                                    <td rowspan="{{ $contestantScores->count() }}">
                                                        <strong>{{ $contestant->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $contestant->representing }}</small>
                                                    </td>
                                                @endif
                                                <td>
                                                    {{ $judges->where('id', $score->judge_id)->first()->name }}
                                                </td>
                                                <td class="text-center">{{ number_format($score->raw_score, 2) }}</td>
                                                <td class="text-center">{{ number_format($score->weighted_score, 2) }}</td>
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($score->created_at)->format('M d, Y h:ia') }}
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-info" 
                                                            data-toggle="tooltip" data-placement="top" 
                                                            title="View Details"
                                                            onclick="viewScoreDetails({{ $score->id }})">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- Average Score Row -->
                                        <tr class="table-light">
                                            <td colspan="2" class="text-right">
                                                <strong>Average Score for {{ $contestant->name }}:</strong>
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ number_format($averageRawScore, 2) }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ number_format($averageWeightedScore, 2) }}</strong>
                                            </td>
                                            <td colspan="2"></td>
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

<!-- Score Details Modal -->
<div class="modal fade" id="scoreDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Score Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="scoreDetailsContent">
                    <!-- Score details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function viewScoreDetails(scoreId) {
        // Show loading state in modal
        $('#scoreDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $('#scoreDetailsModal').modal('show');

        // Fetch score details
        $.ajax({
            url: `{{ url('/${tenant->slug}/scores') }}/${scoreId}`,
            method: 'GET',
            success: function(response) {
                $('#scoreDetailsContent').html(response);
            },
            error: function() {
                $('#scoreDetailsContent').html('<div class="alert alert-danger">Error loading score details.</div>');
            }
        });
    }

    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection 