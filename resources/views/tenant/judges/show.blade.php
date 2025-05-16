@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Judge Details</h1>
        <div>
            <a href="{{ route('tenant.judges.edit', ['slug' => $slug, 'judge' => $judge->id]) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Judge
            </a>
            <a href="{{ route('tenant.judges.index', ['slug' => $slug]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Judge Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Name</label>
                                <p>{{ $judge->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Email</label>
                                <p>{{ $judge->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Specialty</label>
                                <p>{{ $judge->specialty ?? 'Not specified' }}</p>
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
                        <label class="font-weight-bold">Judge ID</label>
                        <p>{{ $judge->id }}</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Created At</label>
                        <p>{{ date('F d, Y h:i A', strtotime($judge->created_at)) }}</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Last Updated</label>
                        <p>{{ date('F d, Y h:i A', strtotime($judge->updated_at)) }}</p>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-danger btn-block" data-bs-toggle="modal" data-bs-target="#deleteJudgeModal">
                        <i class="fas fa-trash"></i> Delete Judge
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteJudgeModal" tabindex="-1" aria-labelledby="deleteJudgeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteJudgeModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">{{ $judge->name }}</h5>
                        <div class="text-muted mb-3">
                            <p class="mb-1">{{ $judge->email }}</p>
                            @if($judge->specialty)
                                <p class="mb-0">Specialty: {{ $judge->specialty }}</p>
                            @endif
                        </div>
                        <div class="card bg-light p-3 mt-2">
                            <p class="mb-0 small">Judge ID: {{ $judge->id }}</p>
                            <p class="mb-0 small">Created: {{ date('F d, Y h:i A', strtotime($judge->created_at)) }}</p>
                        </div>
                    </div>
                    <p>Are you sure you want to delete this judge?</p>
                    <p class="text-danger"><small>This action cannot be undone. All related data including scoring history will also be deleted.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('tenant.judges.destroy', ['slug' => $slug, 'judge' => $judge->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Judge</button>
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
        var deleteButton = document.querySelector('[data-bs-target="#deleteJudgeModal"]');
        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                var modalElement = document.querySelector('#deleteJudgeModal');
                var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement, {
                    backdrop: false
                });
                modal.show();
            });
        }
    });
</script>
@endpush 