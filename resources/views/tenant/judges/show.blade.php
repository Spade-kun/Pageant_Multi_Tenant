@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Judge Details</h1>
        <div>
            <a href="{{ route('tenant.judges.edit', ['slug' => $slug, 'judge' => $judge->id]) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Judge
            </a>
            <a href="{{ route('tenant.judges.index', ['slug' => $slug]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Judge Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Name</label>
                                <p>{{ $judge->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Email</label>
                                <p>{{ $judge->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Specialty</label>
                                <p>{{ $judge->specialty ?? 'Not specified' }}</p>
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
                        <label class="font-weight-bold">Judge ID</label>
                        <p>{{ $judge->id }}</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Created At</label>
                        <p>{{ date('F d, Y h:i A', strtotime($judge->created_at)) }}</p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Last Updated</label>
                        <p>{{ date('F d, Y h:i A', strtotime($judge->updated_at)) }}</p>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.judges.destroy', ['slug' => $slug, 'judge' => $judge->id]) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this judge? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Judge
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 