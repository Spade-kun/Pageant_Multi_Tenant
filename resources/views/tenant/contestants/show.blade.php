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
                                <label class="font-weight-bold">Age</label>
                                <p>{{ $contestant->age }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Gender</label>
                                <p>{{ ucfirst($contestant->gender) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Representing</label>
                                <p>{{ $contestant->representing }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Registration Date</label>
                                <p>{{ date('F d, Y', strtotime($contestant->registration_date)) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Status</label>
                                <p>
                                    <span class="badge badge-{{ $contestant->is_active ? 'success' : 'danger' }}">
                                        {{ $contestant->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
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
                <div class="card-body text-center">
                                @if($contestant->photo)
                                    <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                         alt="{{ $contestant->name }}" 
                             class="img-fluid rounded shadow-sm"
                                         style="max-height: 300px; width: auto;">
                                @else
                                    <div class="text-center p-5 bg-light rounded shadow-sm">
                            <i class="fas fa-user fa-5x text-secondary"></i>
                                        <p class="mt-3 text-muted">No photo available</p>
                                    </div>
                                @endif
                            </div>
                        </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.contestants.destroy', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this contestant? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Contestant
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection