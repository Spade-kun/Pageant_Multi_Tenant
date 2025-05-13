@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Contestants</h1>
            <a href="{{ route('tenant.contestants.create', ['slug' => $slug]) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Contestant
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
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Representing</th>
                                    <th>Status</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contestants as $contestant)
                                <tr>
                                    <td style="width: 100px;">
                                        @if($contestant->photo)
                                            <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                                 alt="{{ $contestant->name }}" 
                                                 class="img-fluid rounded"
                                                 style="max-width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="avatar avatar-sm">
                                                <span class="avatar-title rounded-circle border border-secondary bg-light">
                                                <i class="fas fa-user text-secondary"></i>
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $contestant->name }}</td>
                                    <td>{{ $contestant->age }}</td>
                                    <td>{{ ucfirst($contestant->gender) }}</td>
                                    <td>{{ $contestant->representing }}</td>
                                    <td>
                                        @if($contestant->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($contestant->registration_date)->format('M d, Y') }}</td>
                                    <td>
                                    <div class="btn-group" role="group">
                                            <a href="{{ route('tenant.contestants.show', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('tenant.contestants.edit', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $contestant->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal{{ $contestant->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $contestant->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $contestant->id }}">Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    @if($contestant->photo)
                                                        <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                                            alt="{{ $contestant->name }}" 
                                                            class="img-fluid rounded me-3"
                                                            style="max-width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar avatar-md me-3">
                                                            <span class="avatar-title rounded-circle border border-secondary bg-light">
                                                                <i class="fas fa-user text-secondary"></i>
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $contestant->name }}</h6>
                                                        <small class="text-muted">{{ $contestant->representing }}</small>
                                                    </div>
                                                </div>
                                                <p>Are you sure you want to delete this contestant?</p>
                                                <p class="text-danger"><small>This action cannot be undone.</small></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('tenant.contestants.destroy', ['slug' => $slug, 'id' => $contestant->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete Contestant</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                <td colspan="8" class="text-center">No contestants found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "pageLength": 10,
            "responsive": true,
            "order": [[1, "asc"]], // Sort by name by default
            "columnDefs": [
                { "orderable": false, "targets": [0, 7] } // Disable sorting for photo and actions columns
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
@endsection 