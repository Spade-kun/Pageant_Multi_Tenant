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

@section('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
        
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