@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Plan</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.plans.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Plan Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                                    @error('price')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Interval</label>
                                    <select name="interval" class="form-control @error('interval') is-invalid @enderror" required>
                                        <option value="3_days" {{ old('interval') == '3_days' ? 'selected' : '' }}>3 Days</option>
                                        <option value="15_days" {{ old('interval') == '15_days' ? 'selected' : '' }}>15 Days</option>
                                        <option value="monthly" {{ old('interval') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="yearly" {{ old('interval') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                    </select>
                                    @error('interval')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Max Events</label>
                                    <input type="number" name="max_events" class="form-control @error('max_events') is-invalid @enderror" value="{{ old('max_events', 0) }}" required>
                                    @error('max_events')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Max Contestants</label>
                                    <input type="number" name="max_contestants" class="form-control @error('max_contestants') is-invalid @enderror" value="{{ old('max_contestants', 0) }}" required>
                                    @error('max_contestants')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Max Categories</label>
                                    <input type="number" name="max_categories" class="form-control @error('max_categories') is-invalid @enderror" value="{{ old('max_categories', 0) }}" required>
                                    @error('max_categories')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Max Judges</label>
                                    <input type="number" name="max_judges" class="form-control @error('max_judges') is-invalid @enderror" value="{{ old('max_judges', 0) }}" required>
                                    @error('max_judges')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Feature Access</h4>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Select which features will be accessible with this plan:</p>
                                        
                                        <div class="row">
                                            <!-- Basic Features (Always enabled) -->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="dashboard_access" name="dashboard_access" value="1" checked disabled>
                                                        <input type="hidden" name="dashboard_access" value="1">
                                                        <label class="custom-control-label" for="dashboard_access">Dashboard Access</label>
                                                        <small class="form-text text-muted">Basic dashboard access (always included)</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="user_management" name="user_management" value="1" checked disabled>
                                                        <input type="hidden" name="user_management" value="1">
                                                        <label class="custom-control-label" for="user_management">User Management</label>
                                                        <small class="form-text text-muted">User management (always included)</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="subscription_management" name="subscription_management" value="1" checked disabled>
                                                        <input type="hidden" name="subscription_management" value="1">
                                                        <label class="custom-control-label" for="subscription_management">Subscription Management</label>
                                                        <small class="form-text text-muted">Subscription management (always included)</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- Premium Features -->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="pageant_management" name="pageant_management" value="1" {{ old('pageant_management') ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="pageant_management">Pageant Management</label>
                                                        <small class="form-text text-muted">Access to pageant, event, contestant, and category management</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="reports_module" name="reports_module" value="1" {{ old('reports_module') ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="reports_module">Reports Module</label>
                                                        <small class="form-text text-muted">Access to reporting and analytics</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="analytics" name="analytics" value="1" {{ old('analytics') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="analytics">Analytics</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="support_priority" name="support_priority" value="1" {{ old('support_priority') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="support_priority">Priority Support</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Create Plan</button>
                            <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 