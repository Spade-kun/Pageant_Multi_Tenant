@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Contestant Details</h4>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-profile">
                <div class="card-header" style="background-image: url('{{ asset('assets/img/blogpost.jpg') }}')">
                    <div class="profile-picture">
                        <div class="avatar avatar-xl">
                            <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                 alt="Contestant Photo" 
                                 class="avatar-img rounded-circle">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="user-profile text-center">
                        <div class="name">{{ $contestant->name }}</div>
                        <div class="job">Contestant #{{ $contestant->id }}</div>
                        <div class="desc">{{ $contestant->gender }} | {{ $contestant->age }} years old</div>
                        
                        @if($contestant->score)
                        <div class="mt-3">
                            <h4>Current Score</h4>
                            <div class="view-profile">
                                <div class="h1">{{ number_format($contestant->score, 2) }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Contestant Information</h4>
                        <div class="ml-auto">
                            <a href="{{ route('tenant.contestants.edit', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                               class="btn btn-primary btn-round">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('tenant.contestants.destroy', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                                  method="POST" 
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-danger btn-round"
                                        onclick="return confirm('Are you sure you want to delete this contestant?')">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Bio</label>
                                <div class="border rounded p-3 bg-light">
                                    {{ $contestant->bio }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h4>Competition Details</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="200">Registration Date</th>
                                            <td>{{ \Carbon\Carbon::parse($contestant->created_at)->format('F d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated</th>
                                            <td>{{ \Carbon\Carbon::parse($contestant->updated_at)->format('F d, Y h:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <span class="badge badge-success">Active</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scoring History Card (if implemented) -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Scoring History</h4>
                </div>
                <div class="card-body">
                    @if(isset($scoringHistory) && count($scoringHistory) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Score</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($scoringHistory as $score)
                                    <tr>
                                        <td>{{ $score->event_name }}</td>
                                        <td>{{ $score->score }}</td>
                                        <td>{{ \Carbon\Carbon::parse($score->created_at)->format('F d, Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <p class="text-muted">No scoring history available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection