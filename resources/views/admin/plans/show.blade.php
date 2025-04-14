@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plan Details: {{ $plan->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Plan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Basic Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $plan->name }}</td>
                                </tr>
                                <tr>
                                    <th>Price</th>
                                    <td>₱{{ number_format($plan->price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Interval</th>
                                    <td>
                                        @switch($plan->interval)
                                            @case('3_days')
                                                3 Days
                                                @break
                                            @case('15_days')
                                                15 Days
                                                @break
                                            @case('monthly')
                                                Monthly
                                                @break
                                            @case('yearly')
                                                Yearly
                                                @break
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $plan->is_active ? 'success' : 'danger' }}">
                                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>Limits</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Max Events</th>
                                    <td>{{ $plan->max_events }}</td>
                                </tr>
                                <tr>
                                    <th>Max Contestants</th>
                                    <td>{{ $plan->max_contestants }}</td>
                                </tr>
                                <tr>
                                    <th>Max Categories</th>
                                    <td>{{ $plan->max_categories }}</td>
                                </tr>
                                <tr>
                                    <th>Max Judges</th>
                                    <td>{{ $plan->max_judges }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4>Features</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Analytics</th>
                                    <td>
                                        <span class="badge badge-{{ $plan->analytics ? 'success' : 'secondary' }}">
                                            {{ $plan->analytics ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Priority Support</th>
                                    <td>
                                        <span class="badge badge-{{ $plan->support_priority ? 'success' : 'secondary' }}">
                                            {{ $plan->support_priority ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($plan->description)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4>Description</h4>
                            <div class="card">
                                <div class="card-body">
                                    {{ $plan->description }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>Subscribed Tenants</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tenant Name</th>
                                            <th>Email</th>
                                            <th>Subscription Start</th>
                                            <th>Subscription End</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($plan->tenants as $tenant)
                                            <tr>
                                                <td>{{ $tenant->name }}</td>
                                                <td>{{ $tenant->email }}</td>
                                                <td>{{ $tenant->subscription_starts_at ? $tenant->subscription_starts_at->format('M d, Y') : 'N/A' }}</td>
                                                <td>{{ $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('M d, Y') : 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No tenants subscribed to this plan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 