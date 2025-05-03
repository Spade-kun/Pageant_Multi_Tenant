@extends('layouts.DashboardTemplate')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Approve Tenant: {{ $tenant->name }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tenants.approve', $tenant) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group row mb-3">
                            <label for="message" class="col-md-4 col-form-label text-md-right">Approval Message</label>

                            <div class="col-md-6">
                                <textarea id="message" class="form-control @error('message') is-invalid @enderror" name="message" required autofocus>{{ old('message') }}</textarea>

                                @error('message')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Approve Tenant
                                </button>
                                <a href="{{ route('admin.tenants.index') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 