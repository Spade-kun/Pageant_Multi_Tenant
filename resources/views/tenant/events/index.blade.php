@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Events</h1>
        <a href="{{ route('tenant.events.create', ['slug' => $slug]) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Event
        </a>
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
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="eventsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr>
                            <td>{{ $event->name }}</td>
                            <td data-sort="{{ $event->start_date }}">{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y h:i A') }}</td>
                            <td data-sort="{{ $event->end_date }}">{{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y h:i A') }}</td>
                            <td>{{ $event->location }}</td>
                            <td>
                                @php
                                    $statusClass = [
                                        'scheduled' => 'primary',
                                        'ongoing' => 'success',
                                        'completed' => 'info',
                                        'cancelled' => 'danger'
                                    ][$event->status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $statusClass }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tenant.events.show', ['slug' => $slug, 'event' => $event->id]) }}" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tenant.events.edit', ['slug' => $slug, 'event' => $event->id]) }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $event->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Delete Confirmation Modal -->
                        <div class="modal fade" id="deleteModal{{ $event->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $event->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title" id="deleteModalLabel{{ $event->id }}">Confirm Delete</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete the event <strong>{{ $event->name }}</strong>?</p>
                                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form action="{{ route('tenant.events.destroy', ['slug' => $slug, 'event' => $event->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete Event</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No events found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}">
<style>
    /* DataTables Styling */
    .dataTables_wrapper .dataTables_length select {
        width: 75px;
        display: inline-block;
    }

    .dataTables_wrapper .dataTables_filter {
        float: right;
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_filter input {
        width: 300px;
        margin-left: 0.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding-top: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 1rem;
        margin-left: 0.25rem;
        border-radius: 0.25rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0d6efd !important;
        color: white !important;
        border: 1px solid #0d6efd !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #0b5ed7 !important;
        color: white !important;
        border: 1px solid #0b5ed7 !important;
    }
</style>
@endpush

@push('scripts')
<!-- jQuery -->
<script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
<!-- Bootstrap JS -->
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<!-- DataTables JS -->
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#eventsTable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search events...",
                lengthMenu: "Show _MENU_ events per page",
                info: "Showing _START_ to _END_ of _TOTAL_ events",
                infoEmpty: "Showing 0 to 0 of 0 events",
                infoFiltered: "(filtered from _MAX_ total events)"
            },
            pagingType: "full_numbers",
            order: [[1, 'asc']], // Sort by start date by default
            columnDefs: [
                { orderable: false, targets: 5 } // Disable sorting on actions column
            ]
        });
        
        // Ensure modals work properly
        var deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var targetModal = this.getAttribute('data-bs-target');
                var modalElement = document.querySelector(targetModal);
                var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement, {
                    backdrop: false
                });
                modal.show();
            });
        });
    });
</script>
@endpush 