@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Edit Contestant</h4>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Edit Contestant Details</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.contestants.update', ['slug' => $slug, 'id' => $contestant->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name', $contestant->name) }}" required>
                                            @error('name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="age">Age <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('age') is-invalid @enderror" 
                                                   id="age" name="age" value="{{ old('age', $contestant->age) }}" required>
                                            @error('age')
                                                <span class="invalid-feedback">{{ $message }}</span>
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
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="representing">Representing <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('representing') is-invalid @enderror" 
                                                   id="representing" name="representing" value="{{ old('representing', $contestant->representing) }}" required>
                                            @error('representing')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="bio">Bio</label>
                                    <textarea class="form-control @error('bio') is-invalid @enderror" 
                                              id="bio" name="bio" rows="3">{{ old('bio', $contestant->bio) }}</textarea>
                                    @error('bio')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="registration_date">Registration Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('registration_date') is-invalid @enderror" 
                                                   id="registration_date" name="registration_date" 
                                                   value="{{ old('registration_date', date('Y-m-d', strtotime($contestant->registration_date))) }}" required>
                                            @error('registration_date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="d-block">Status</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                                       {{ old('is_active', $contestant->is_active) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">Active Status</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="photo">Photo</label>
                                    <div class="text-center mb-3">
                                        @if($contestant->photo)
                                            <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                                 alt="Current Photo" 
                                                 class="img-fluid rounded shadow-sm mb-2"
                                                 style="max-height: 200px; width: auto;"
                                                 id="currentPhoto">
                                        @else
                                            <div class="text-center p-4 bg-light rounded shadow-sm mb-2">
                                                <i class="fa fa-user fa-4x text-secondary"></i>
                                                <p class="mt-2 text-muted">No photo available</p>
                                            </div>
                                        @endif
                                    </div>
                                    <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                           id="photo" name="photo" accept="image/*">
                                    <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG. Max size: 2MB</small>
                                    @error('photo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-action">
                            <button type="submit" class="btn btn-primary">Update Contestant</button>
                            <a href="{{ route('tenant.contestants.index', ['slug' => $slug]) }}" class="btn btn-danger">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
@endpush 