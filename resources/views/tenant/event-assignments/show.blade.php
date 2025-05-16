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
                    <button type="button" class="btn btn-danger btn-block" data-bs-toggle="modal" data-bs-target="#deleteAssignmentModal">
                        <i class="fas fa-trash"></i> Delete Assignment
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteAssignmentModal" tabindex="-1" aria-labelledby="deleteAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAssignmentModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">{{ $assignment->event_name }}</h5>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-{{ $assignment->status === 'confirmed' ? 'success' : ($assignment->status === 'withdrawn' ? 'danger' : 'warning') }} me-2">
                                {{ ucfirst($assignment->status) }}
                            </span>
                        </div>
                        <div class="card bg-light p-3 mt-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1 font-weight-bold">Contestant:</p>
                                    <p class="mb-0">{{ $assignment->contestant_name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1 font-weight-bold">Category:</p>
                                    <p class="mb-0">{{ $assignment->category_name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p>Are you sure you want to delete this assignment?</p>
                    <p class="text-danger"><small>This action cannot be undone. All related scoring data will also be deleted.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('tenant.event-assignments.destroy', ['slug' => $slug, 'id' => $assignment->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Assignment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure modal works properly
        var deleteButton = document.querySelector('[data-bs-target="#deleteAssignmentModal"]');
        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                var modalElement = document.querySelector('#deleteAssignmentModal');
                var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement, {
                    backdrop: false
                });
                modal.show();
            });
        }
    });
</script>
@endpush 