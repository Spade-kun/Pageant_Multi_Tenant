@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Event Assignment Details</h1>
        <div>
            <a href="{{ route('tenant.event-assignments.edit', ['slug' => $slug, 'id' => $assignment->id]) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Assignment
            </a>
            <a href="{{ route('tenant.event-assignments.index', ['slug' => $slug]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Assignment Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Event</label>
                                <p>{{ $assignment->event_name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Status</label>
                                <p>
                                    <span class="badge badge-{{ $assignment->status === 'confirmed' ? 'success' : ($assignment->status === 'withdrawn' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Contestant</label>
                                <p>{{ $assignment->contestant_name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Category</label>
                                <p>{{ $assignment->category_name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Notes</label>
                                <p>{{ $assignment->notes ?? 'No notes available' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Additional Information</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Created At</label>
                        <p>{{ \Carbon\Carbon::parse($assignment->created_at)->format('F j, Y g:i A') }}</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Last Updated</label>
                        <p>{{ \Carbon\Carbon::parse($assignment->updated_at)->format('F j, Y g:i A') }}</p>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.event-assignments.destroy', ['slug' => $slug, 'id' => $assignment->id]) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this assignment? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Assignment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 