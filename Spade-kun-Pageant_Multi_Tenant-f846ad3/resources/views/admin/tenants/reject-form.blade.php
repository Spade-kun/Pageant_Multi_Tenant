@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Reject Tenant</h4>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Provide Rejection Reason for {{ $tenant->pageant_name }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tenants.reject', $tenant) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('rejection_reason') is-invalid @enderror" 
                                id="rejection_reason" 
                                name="rejection_reason" 
                                rows="5" 
                                required>{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">
                                Please provide a clear reason for rejecting this tenant. This information will be sent to the tenant owner via email.
                            </small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-times"></i> Reject Tenant
                            </button>
                            <a href="{{ route('admin.tenants.index') }}" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 