@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Judges</h1>
        <a href="{{ route('tenant.judges.create', ['slug' => $slug]) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Judge
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
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Specialty</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($judges as $judge)
                                    <tr>
                                        <td>{{ $judge->id }}</td>
                                        <td>{{ $judge->name }}</td>
                                        <td>{{ $judge->email }}</td>
                                        <td>{{ $judge->specialty }}</td>
                                        <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tenant.judges.show', ['slug' => $slug, 'judge' => $judge->id]) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                            </a>
                                        <a href="{{ route('tenant.judges.edit', ['slug' => $slug, 'judge' => $judge->id]) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $judge->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                    </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteModal{{ $judge->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $judge->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $judge->id }}">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <h6 class="font-weight-bold">{{ $judge->name }}</h6>
                                                        <div class="text-muted">
                                                            <p class="mb-1">{{ $judge->email }}</p>
                                                            @if($judge->specialty)
                                                                <p class="mb-0"><small>Specialty: {{ $judge->specialty }}</small></p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <p>Are you sure you want to remove this judge?</p>
                                                    <p class="text-danger"><small>This action cannot be undone. All related data including scoring history will also be deleted.</small></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('tenant.judges.destroy', ['slug' => $slug, 'judge' => $judge->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete Judge</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                <td colspan="5" class="text-center">No judges found.</td>
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
        $('#dataTable').DataTable({
            "responsive": true,
            "pageLength": 10,
            "order": [[1, 'asc']], // Sort by name by default
            "columnDefs": [
                { "orderable": false, "targets": 4 } // Disable sorting for actions column
            ],
            language: {
                search: "Search judges:",
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