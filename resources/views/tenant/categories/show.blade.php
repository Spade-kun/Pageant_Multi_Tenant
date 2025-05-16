@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Category Details</h1>
        <div>
            <a href="{{ route('tenant.categories.edit', ['slug' => $slug, 'id' => $category->id]) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Category
            </a>
            <a href="{{ route('tenant.categories.index', ['slug' => $slug]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Category Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Name</label>
                                <p>{{ $category->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Percentage</label>
                                <p>{{ $category->percentage }}%</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Status</label>
                                <p>
                                    <span class="badge badge-{{ $category->is_active ? 'success' : 'danger' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Display Order</label>
                                <p>{{ $category->display_order }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Description</label>
                                <p>{{ $category->description ?? 'No description available' }}</p>
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
                        <label class="font-weight-bold">Category ID</label>
                        <p>{{ $category->id }}</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Created At</label>
                        <p>{{ date('F d, Y h:i A', strtotime($category->created_at)) }}</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Last Updated</label>
                        <p>{{ date('F d, Y h:i A', strtotime($category->updated_at)) }}</p>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-danger btn-block" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                        <i class="fas fa-trash"></i> Delete Category
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCategoryModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h5 class="font-weight-bold">{{ $category->name }}</h5>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-{{ $category->is_active ? 'success' : 'danger' }} me-2">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-muted">{{ $category->percentage }}%</span>
                        </div>
                        <div class="card bg-light p-3 mt-2">
                            <p class="mb-0">{{ $category->description ?? 'No description available' }}</p>
                        </div>
                    </div>
                    <p>Are you sure you want to delete this category?</p>
                    <p class="text-danger"><small>This action cannot be undone. All related data and scoring criteria will also be deleted.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('tenant.categories.destroy', ['slug' => $slug, 'id' => $category->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Category</button>
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
        var deleteButton = document.querySelector('[data-bs-target="#deleteCategoryModal"]');
        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                var modalElement = document.querySelector('#deleteCategoryModal');
                var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement, {
                    backdrop: false
                });
                modal.show();
            });
        }
    });
</script>
@endpush 