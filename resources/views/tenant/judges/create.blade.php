@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Judge</h1>
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
            
            <form action="{{ route('tenant.judges.store', ['slug' => $slug]) }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="user_id">User <span class="text-danger">*</span></label>
                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Only users with the 'user' role can be selected as judges.</small>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="specialty">Specialty <span class="text-danger">*</span></label>
                    <input type="text" name="specialty" id="specialty" class="form-control @error('specialty') is-invalid @enderror" 
                           value="{{ old('specialty') }}" placeholder="Enter judge's specialty" required>
                    @error('specialty')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Judge
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize select2 for user dropdown
        $('#user_id').select2({
            theme: 'bootstrap4',
            placeholder: 'Select a user'
        });
    });
</script>
@endsection 