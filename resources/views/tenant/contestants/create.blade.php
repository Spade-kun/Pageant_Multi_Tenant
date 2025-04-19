@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Create Contestant</h4>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Add New Contestant</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.contestants.store', ['slug' => $slug]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="age">Age <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('age') is-invalid @enderror" 
                                           id="age" name="age" value="{{ old('age') }}" required>
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
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
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
                                           id="representing" name="representing" value="{{ old('representing') }}" required>
                                    @error('representing')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" 
                                      id="bio" name="bio" rows="3">{{ old('bio') }}</textarea>
                            @error('bio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="photo">Photo</label>
                                    <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                           id="photo" name="photo" accept="image/*">
                                    <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG. Max size: 2MB</small>
                                    @error('photo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="registration_date">Registration Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('registration_date') is-invalid @enderror" 
                                           id="registration_date" name="registration_date" value="{{ old('registration_date', date('Y-m-d')) }}" required>
                                    @error('registration_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                       {{ old('is_active') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active Status</label>
                            </div>
                        </div>

                        <div class="card-action">
                            <button type="submit" class="btn btn-primary">Create Contestant</button>
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
                let preview = document.createElement('img');
                preview.src = e.target.result;
                preview.style.maxWidth = '200px';
                preview.style.marginTop = '10px';
                let container = document.getElementById('photo').parentNode;
                let oldPreview = container.querySelector('img');
                if (oldPreview) {
                    container.removeChild(oldPreview);
                }
                container.appendChild(preview);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
@endpush 