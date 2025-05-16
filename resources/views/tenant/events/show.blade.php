@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Event Details</h1>
        <div>
            <a href="{{ route('tenant.events.edit', ['slug' => $slug, 'event' => $event->id]) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Event
            </a>
            <a href="{{ route('tenant.events.index', ['slug' => $slug]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Event Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Name</label>
                                <p>{{ $event->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Status</label>
                                <p>
                                            @if($event->status == 'ongoing')
                                                <span class="badge badge-success">Ongoing</span>
                                            @elseif($event->status == 'upcoming')
                                                <span class="badge badge-info">Upcoming</span>
                                            @elseif($event->status == 'completed')
                                                <span class="badge badge-secondary">Completed</span>
                                            @else
                                                <span class="badge badge-warning">{{ ucfirst($event->status) }}</span>
                                            @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Start Date</label>
                                <p>{{ date('F d, Y h:i A', strtotime($event->start_date)) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">End Date</label>
                                <p>{{ date('F d, Y h:i A', strtotime($event->end_date)) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Location</label>
                                <p>{{ $event->location }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Description</label>
                                <p>{{ $event->description ?? 'No description available' }}</p>
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
                        <label class="font-weight-bold">Event ID</label>
                        <p>{{ $event->id }}</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Created At</label>
                        <p>{{ date('F d, Y h:i A', strtotime($event->created_at)) }}</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Last Updated</label>
                        <p>{{ date('F d, Y h:i A', strtotime($event->updated_at)) }}</p>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-danger btn-block" data-bs-toggle="modal" data-bs-target="#deleteEventModal">
                        <i class="fas fa-trash"></i> Delete Event
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteEventModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the event <strong>{{ $event->name }}</strong>?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('tenant.events.destroy', ['slug' => $slug, 'event' => $event->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Event</button>
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
        var deleteButton = document.querySelector('[data-bs-target="#deleteEventModal"]');
        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                var modalElement = document.querySelector('#deleteEventModal');
                var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement, {
                    backdrop: false
                });
                modal.show();
            });
        }
    });
</script>
@endpush 