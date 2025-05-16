<div class="container-fluid p-0">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0">
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <tr>
                            <th style="width: 150px">Event:</th>
                            <td>{{ $score->event_name }}</td>
                        </tr>
                        <tr>
                            <th>Contestant:</th>
                            <td>
                                {{ $score->contestant_name }}
                                <small class="text-muted d-block">Representing: {{ $score->representing }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td>
                                {{ $score->category_name }}
                                <small class="text-muted d-block">Weight: {{ $score->percentage }}%</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Judge:</th>
                            <td>{{ $score->judge_name }}</td>
                        </tr>
                        <tr>
                            <th>Raw Score:</th>
                            <td>{{ number_format($score->raw_score, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Weighted Score:</th>
                            <td>{{ number_format($score->weighted_score, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Comments:</th>
                            <td>{{ $score->comments ?? 'No comments provided' }}</td>
                        </tr>
                        <tr>
                            <th>Scored On:</th>
                            <td>{{ \Carbon\Carbon::parse($score->created_at)->format('M d, Y h:ia') }}</td>
                        </tr>
                    </table>

                    @if($score->criteria_scores)
                        <div class="mt-3">
                            <h6 class="font-weight-bold">Criteria Breakdown</h6>
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Criterion</th>
                                        <th class="text-center" style="width: 100px">Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(json_decode($score->criteria_scores, true) as $criterion => $score)
                                        <tr>
                                            <td>{{ $criterion }}</td>
                                            <td class="text-center">{{ number_format($score, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div> 