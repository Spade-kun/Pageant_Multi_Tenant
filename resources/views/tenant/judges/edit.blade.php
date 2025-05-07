@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Judge</h1>
        <a href="{{ route('tenant.judges.index', ['slug' => $slug]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('tenant.judges.update', ['slug' => $slug, 'judge' => $judge->id]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $judge->name }}" disabled>
                            <small class="form-text text-muted">Name cannot be changed. It is pulled from the user account.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ $judge->email }}" disabled>
                            <small class="form-text text-muted">Email cannot be changed. It is pulled from the user account.</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="specialty">Specialty <span class="text-danger">*</span></label>
                    <input type="text" name="specialty" id="specialty" class="form-control @error('specialty') is-invalid @enderror" 
                           value="{{ old('specialty', $judge->specialty) }}" placeholder="Enter judge's specialty" required>
                    @error('specialty')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Judge
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 