@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Create Category</h4>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Add New Category</h4>
                    </div>
                </div>
                <div class="card-body">
                    @if($currentTotal >= 100)
                        <div class="alert alert-warning">
                            <strong>Warning!</strong> The total percentage of all categories is already 100%. 
                            You cannot add more categories unless you adjust existing ones.
                        </div>
                    @else
                        <div class="alert alert-info">
                            <strong>Current Total:</strong> {{ number_format($currentTotal, 2) }}%
                            <br>
                            <strong>Remaining Available:</strong> {{ number_format($remainingPercentage, 2) }}%
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('tenant.categories.store', ['slug' => $slug]) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="percentage">Percentage (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('percentage') is-invalid @enderror" 
                                           id="percentage" name="percentage" value="{{ old('percentage') }}" 
                                           min="1" max="{{ $remainingPercentage }}" step="0.01" required>
                                    <small class="form-text text-muted">Maximum allowed: {{ number_format($remainingPercentage, 2) }}%</small>
                                    @error('percentage')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="display_order">Display Order <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                           id="display_order" name="display_order" value="{{ old('display_order', $nextDisplayOrder) }}" 
                                           min="0" required>
                                    <small class="form-text text-muted">Each category must have a unique display order number</small>
                                    @error('display_order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                    @error('is_active')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-action">
                            <button type="submit" class="btn btn-primary" {{ $currentTotal >= 100 ? 'disabled' : '' }}>
                                Create Category
                            </button>
                            <a href="{{ route('tenant.categories.index', ['slug' => $slug]) }}" class="btn btn-danger">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 