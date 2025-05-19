@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Contestant Details</h1>
        <div>
            <a href="{{ route('tenant.contestants.edit', ['slug' => $slug, 'id' => $contestant->id]) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Contestant
            </a>
            <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contestant Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Name</label>
                                <p>{{ $contestant->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Status</label>
                                <p>
                                    @if($contestant->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Age</label>
                                <p>{{ $contestant->age }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Gender</label>
                                <p>{{ ucfirst($contestant->gender) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Representing</label>
                                <p>{{ $contestant->representing }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Registration Date</label>
                                <p>{{ date('F d, Y', strtotime($contestant->registration_date)) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Bio</label>
                                <p>{{ $contestant->bio ?? 'No bio available' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Photo</h6>
                </div>
                <div class="card-body">
                            <div class="text-center">
                                @if($contestant->photo)
                                    <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                         alt="{{ $contestant->name }}" 
                                 class="img-fluid rounded shadow mb-3"
                                         style="max-height: 300px; width: auto;">
                                @else
                                    <div class="text-center p-5 bg-light rounded shadow-sm">
                                <i class="fas fa-user fa-5x text-secondary"></i>
                                        <p class="mt-3 text-muted">No photo available</p>
                                    </div>
                                @endif
                            </div>
                        </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Additional Information</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Score</label>
                        <p>{{ $contestant->score ?? 'Not scored yet' }}</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Created At</label>
                        <p>{{ date('F d, Y h:i A', strtotime($contestant->created_at)) }}</p>
                        </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Last Updated</label>
                        <p>{{ date('F d, Y h:i A', strtotime($contestant->updated_at)) }}</p>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-danger btn-block" data-bs-toggle="modal" data-bs-target="#deleteContestantModal">
                        <i class="fas fa-trash"></i> Delete Contestant
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteContestantModal" tabindex="-1" aria-labelledby="deleteContestantModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteContestantModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($contestant->photo)
                            <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                 alt="{{ $contestant->name }}" 
                                 class="img-fluid rounded me-3"
                                 style="max-width: 50px; height: 50px; object-fit: cover;">
                        @else
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-title rounded-circle border border-secondary bg-light" style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fa-2x text-secondary"></i>
                                </span>
                            </div>
                        @endif
                        <div>
                            <h5 class="mb-1">{{ $contestant->name }}</h5>
                            <p class="mb-0 text-muted">{{ $contestant->representing }}</p>
                        </div>
                    </div>
                    <p>Are you sure you want to delete this contestant?</p>
                    <p class="text-danger"><small>This action cannot be undone. All related data including scores and assignments will also be deleted.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('tenant.contestants.destroy', ['slug' => $slug, 'id' => $contestant->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Contestant</button>
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
        var deleteButton = document.querySelector('[data-bs-target="#deleteContestantModal"]');
        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                var modalElement = document.querySelector('#deleteContestantModal');
                var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement, {
                    backdrop: false
                });
                modal.show();
            });
        }
    });
</script>
@endpush