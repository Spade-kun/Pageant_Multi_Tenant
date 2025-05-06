@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title">Plan Details: {{ $plan->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-light btn-sm">
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
                            <h4>Features Access</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Default Features</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Dashboard Access</th>
                                            <td>
                                                <span class="badge badge-success">
                                                    Always Enabled
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>User Management</th>
                                            <td>
                                                <span class="badge badge-success">
                                                    Always Enabled
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Subscription Management</th>
                                            <td>
                                                <span class="badge badge-success">
                                                    Always Enabled
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Premium Features</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Pageant Management</th>
                                            <td>
                                                <span class="badge badge-{{ $plan->pageant_management ? 'success' : 'secondary' }}">
                                                    {{ $plan->pageant_management ? 'Enabled' : 'Disabled' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Reports Module</th>
                                            <td>
                                                <span class="badge badge-{{ $plan->reports_module ? 'success' : 'secondary' }}">
                                                    {{ $plan->reports_module ? 'Enabled' : 'Disabled' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <h5 class="mt-3">Additional Features</h5>
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
                            <div class="card">
                                <div class="card-header bg-gradient-primary">
                                    <h4 class="card-title m-0">Subscribed Tenants</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered datatables">
                                            <thead>
                                                <tr>
                                                   
                                                    <th>Pageant Name</th>
                                                    <th>Status</th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($plan->tenants as $tenant)
                                                    <tr>
                                                      
                                                        <td>{{ $tenant->pageant_name ?? 'N/A' }}</td>
                                                        <td>
                                                            <span class="badge badge-{{ $tenant->is_active ? 'success' : 'danger' }}">
                                                                {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </td>
                                                       
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center">No tenants subscribed to this plan.</td>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.datatables').DataTable({
            responsive: true,
            order: [[4, 'desc']]
        });
    });
</script>
@endpush 