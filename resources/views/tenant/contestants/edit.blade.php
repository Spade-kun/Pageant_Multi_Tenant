@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Contestant</h1>
        <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-secondary">
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
            
                    <form action="{{ route('tenant.contestants.update', ['slug' => $slug, 'id' => $contestant->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name', $contestant->name) }}" required>
                                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="age">Age <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('age') is-invalid @enderror" 
                                                   id="age" name="age" value="{{ old('age', $contestant->age) }}" required>
                                            @error('age')
                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gender">Gender <span class="text-danger">*</span></label>
                                            <select class="form-control @error('gender') is-invalid @enderror" 
                                                    id="gender" name="gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="female" {{ old('gender', $contestant->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="male" {{ old('gender', $contestant->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                            </select>
                                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="representing">Representing <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('representing') is-invalid @enderror" 
                                                   id="representing" name="representing" value="{{ old('representing', $contestant->representing) }}" required>
                                            @error('representing')
                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="bio">Bio</label>
                                    <textarea class="form-control @error('bio') is-invalid @enderror" 
                                              id="bio" name="bio" rows="3">{{ old('bio', $contestant->bio) }}</textarea>
                                    @error('bio')
                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                            <label for="photo">Photo</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                   id="photo" name="photo" accept="image/*">
                            <small class="form-text text-muted">Upload a new photo only if you want to change the current one.</small>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                            <div class="text-center mt-2">
                                @if($contestant->photo)
                                    <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                         alt="{{ $contestant->name }}" 
                                         class="img-fluid rounded shadow-sm mb-2" 
                                         style="max-height: 200px; width: auto;">
                                @endif
                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                            <label for="registration_date">Registration Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('registration_date') is-invalid @enderror" 
                                   id="registration_date" name="registration_date" 
                                   value="{{ old('registration_date', $contestant->registration_date ? date('Y-m-d', strtotime($contestant->registration_date)) : '') }}" required>
                            @error('registration_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mt-3">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', $contestant->is_active) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">Active Status</label>
                            </div>
                            @error('is_active')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Contestant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Preview uploaded photo
    document.getElementById('photo').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let container = document.getElementById('photo').closest('.form-group');
                let previewDiv = container.querySelector('.text-center');
                
                // Create new image preview
                let preview = document.createElement('img');
                preview.src = e.target.result;
                preview.className = 'img-fluid rounded shadow-sm mb-2';
                preview.style.maxHeight = '200px';
                preview.style.width = 'auto';
                
                // Clear existing preview
                previewDiv.innerHTML = '';
                previewDiv.appendChild(preview);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
@endsection 