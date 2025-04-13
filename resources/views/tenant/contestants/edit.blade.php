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
                    <div class="card-title">Contestant Information</div>
                </div>
                <form method="POST" action="{{ route('tenant.contestants.update', ['slug' => $slug, 'id' => $contestant->id]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $contestant->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="age">Age <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('age') is-invalid @enderror" id="age" name="age" value="{{ old('age', $contestant->age) }}" required min="1" max="150">
                                    @error('age')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="gender">Gender <span class="text-danger">*</span></label>
                                    <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Female" {{ old('gender', $contestant->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Male" {{ old('gender', $contestant->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="representing">Representing <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('representing') is-invalid @enderror" 
                                           id="representing" name="representing" value="{{ old('representing', $contestant->representing) }}" required>
                                    @error('representing')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="photo">Photo</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                                        <label class="custom-file-label" for="photo">Choose file to change photo</label>
                                        @error('photo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div id="preview" class="mt-2">
                                        @if($contestant->photo)
                                            <img src="{{ asset('storage/' . $contestant->photo) }}" alt="Current Photo" class="img-fluid rounded" style="max-height: 200px">
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="bio">Bio <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="5" required>{{ old('bio', $contestant->bio) }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">Status</label>
                                    <select class="form-control @error('is_active') is-invalid @enderror" 
                                            id="is_active" name="is_active">
                                        <option value="1" {{ old('is_active', $contestant->is_active) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !old('is_active', $contestant->is_active) ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="registration_date">Registration Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('registration_date') is-invalid @enderror" 
                                           id="registration_date" name="registration_date" 
                                           value="{{ old('registration_date', $contestant->registration_date->format('Y-m-d')) }}" required>
                                    @error('registration_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('tenant.contestants.show', ['slug' => $slug, 'id' => $contestant->id]) }}" class="btn btn-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Preview uploaded image
    document.getElementById('photo').addEventListener('change', function(e) {
        const preview = document.getElementById('preview');
        preview.innerHTML = '';
        
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-fluid rounded';
                img.style.maxHeight = '200px';
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
            
            // Update file input label
            const label = document.querySelector('.custom-file-label');
            label.textContent = file.name;
        }
    });
</script>
@endpush
@endsection 