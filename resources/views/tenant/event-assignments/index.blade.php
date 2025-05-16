@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Event Assignments</h1>
        <a href="{{ route('tenant.event-assignments.create', ['slug' => $slug]) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Assignment
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
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Contestants</th>
                            <th>Categories</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment['event_name'] }}</td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($assignment['contestants'] as $contestant)
                                            <li>{{ $contestant }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($assignment['categories'] as $category)
                                            <li>{{ $category }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $assignment['status'] === 'confirmed' ? 'success' : ($assignment['status'] === 'withdrawn' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($assignment['status']) }}
                                    </span>
                                </td>
                                <td>{{ $assignment['notes'] ?? 'No notes' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tenant.event-assignments.show', ['slug' => $slug, 'id' => $assignment['id']]) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('tenant.event-assignments.edit', ['slug' => $slug, 'id' => $assignment['id']]) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $assignment['id'] }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteModal{{ $assignment['id'] }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $assignment['id'] }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title" id="deleteModalLabel{{ $assignment['id'] }}">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <h6 class="font-weight-bold">{{ $assignment['event_name'] }}</h6>
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge badge-{{ $assignment['status'] === 'confirmed' ? 'success' : ($assignment['status'] === 'withdrawn' ? 'danger' : 'warning') }} me-2">
                                                        {{ ucfirst($assignment['status']) }}
                                                    </span>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <p class="mb-1 font-weight-bold">Contestants:</p>
                                                        <ul class="list-unstyled small">
                                                            @foreach($assignment['contestants'] as $contestant)
                                                                <li>{{ $contestant }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-1 font-weight-bold">Categories:</p>
                                                        <ul class="list-unstyled small">
                                                            @foreach($assignment['categories'] as $category)
                                                                <li>{{ $category }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <p>Are you sure you want to delete all assignments for this event?</p>
                                            <p class="text-danger"><small>This action cannot be undone. All related scoring data will also be deleted.</small></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="{{ route('tenant.event-assignments.destroy', ['slug' => $slug, 'id' => $assignment['id']]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Delete Assignment</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
        $('#dataTable').DataTable({
            "responsive": true,
            "pageLength": 10,
            "order": [[0, 'asc']], // Sort by event name by default
            "columnDefs": [
                { "orderable": false, "targets": 5 } // Disable sorting for actions column
            ],
            language: {
                search: "Search assignments:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries to show",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
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