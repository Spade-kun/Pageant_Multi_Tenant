@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Edit Judge</h4>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Edit Judge: {{ $judge->name }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('tenant.judges.update', ['slug' => $slug, 'judge' => $judge->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $judge->name }}" disabled>
                            <small class="form-text text-muted">Name cannot be changed. It is pulled from the user account.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ $judge->email }}" disabled>
                            <small class="form-text text-muted">Email cannot be changed. It is pulled from the user account.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="specialty">Specialty</label>
                            <input type="text" name="specialty" id="specialty" class="form-control @error('specialty') is-invalid @enderror" 
                                   value="{{ old('specialty', $judge->specialty) }}" placeholder="Enter judge's specialty" required>
                            @error('specialty')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Judge</button>
                            <a href="{{ route('tenant.judges.index', ['slug' => $slug]) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 