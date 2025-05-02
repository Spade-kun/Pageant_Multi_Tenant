@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Plan: {{ $plan->name }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Plan Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $plan->name) }}" required>
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
                                    <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $plan->price) }}" required>
                                    @error('price')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Interval</label>
                                    <select name="interval" class="form-control @error('interval') is-invalid @enderror" required>
                                        <option value="3_days" {{ old('interval', $plan->interval) == '3_days' ? 'selected' : '' }}>3 Days</option>
                                        <option value="15_days" {{ old('interval', $plan->interval) == '15_days' ? 'selected' : '' }}>15 Days</option>
                                        <option value="monthly" {{ old('interval', $plan->interval) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="yearly" {{ old('interval', $plan->interval) == 'yearly' ? 'selected' : '' }}>Yearly</option>
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
                                    <input type="number" name="max_events" class="form-control @error('max_events') is-invalid @enderror" value="{{ old('max_events', $plan->max_events) }}" required>
                                    @error('max_events')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Max Contestants</label>
                                    <input type="number" name="max_contestants" class="form-control @error('max_contestants') is-invalid @enderror" value="{{ old('max_contestants', $plan->max_contestants) }}" required>
                                    @error('max_contestants')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Max Categories</label>
                                    <input type="number" name="max_categories" class="form-control @error('max_categories') is-invalid @enderror" value="{{ old('max_categories', $plan->max_categories) }}" required>
                                    @error('max_categories')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Max Judges</label>
                                    <input type="number" name="max_judges" class="form-control @error('max_judges') is-invalid @enderror" value="{{ old('max_judges', $plan->max_judges) }}" required>
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
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $plan->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="analytics" name="analytics" value="1" {{ old('analytics', $plan->analytics) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="analytics">Analytics</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="support_priority" name="support_priority" value="1" {{ old('support_priority', $plan->support_priority) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="support_priority">Priority Support</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Plan</button>
                            <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 