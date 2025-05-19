@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Subscription Plans</h3>
                    <div class="card-tools">
                        <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                            <i class="fas fa-plus"></i> Create New Plan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="plans-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Interval</th>
                                    <th>Max Events</th>
                                    <th>Max Contestants</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plans as $plan)
                                    <tr>
                                        <td>{{ $plan->name }}</td>
                                        <td>₱{{ number_format($plan->price, 2) }}</td>
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
                                        <td>{{ $plan->max_events }}</td>
                                        <td>{{ $plan->max_contestants }}</td>
                                        <td>
                                            <span class="badge badge-{{ $plan->is_active ? 'success' : 'danger' }}">
                                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{ $plan->id }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $plan->id }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $plan->id }}">
                                                    <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Details Modal -->
                                    <div class="modal fade" id="viewModal{{ $plan->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $plan->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewModalLabel{{ $plan->id }}">Plan Details: {{ $plan->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6 class="fw-bold">Basic Information</h6>
                                                            <p><strong>Name:</strong> {{ $plan->name }}</p>
                                                            <p><strong>Price:</strong> ₱{{ number_format($plan->price, 2) }}</p>
                                                            <p><strong>Interval:</strong> 
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
                                                            </p>
                                                            <p><strong>Status:</strong> 
                                                                <span class="badge badge-{{ $plan->is_active ? 'success' : 'danger' }}">
                                                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="fw-bold">Features</h6>
                                                            <p><strong>Max Events:</strong> {{ $plan->max_events }}</p>
                                                            <p><strong>Max Contestants:</strong> {{ $plan->max_contestants }}</p>
                                                            <p><strong>Max Categories:</strong> {{ $plan->max_categories }}</p>
                                                            <p><strong>Max Judges:</strong> {{ $plan->max_judges }}</p>
                                                            <p><strong>Description:</strong> {{ $plan->description ?: 'No description available' }}</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6 class="fw-bold">Additional Features</h6>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p><i class="fas {{ $plan->analytics ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> <strong>Analytics</strong></p>
                                                                    <p><i class="fas {{ $plan->support_priority ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> <strong>Priority Support</strong></p>
                                                                    <p><i class="fas {{ $plan->dashboard_access ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> <strong>Dashboard Access</strong></p>
                                                                    <p><i class="fas {{ $plan->user_management ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> <strong>User Management</strong></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><i class="fas {{ $plan->subscription_management ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> <strong>Subscription Management</strong></p>
                                                                    <p><i class="fas {{ $plan->pageant_management ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> <strong>Pageant Management</strong></p>
                                                                    <p><i class="fas {{ $plan->reports_module ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> <strong>Reports Module</strong></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <a href="{{ route('admin.plans.show', $plan) }}" class="btn btn-primary">Full Details</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Edit Plan Modal -->
                                    <div class="modal fade" id="editModal{{ $plan->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $plan->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title" id="editModalLabel{{ $plan->id }}">Edit Plan: {{ $plan->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="name{{ $plan->id }}" class="form-label">Plan Name</label>
                                                                <input type="text" class="form-control" id="name{{ $plan->id }}" name="name" value="{{ $plan->name }}" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="price{{ $plan->id }}" class="form-label">Price (₱)</label>
                                                                <input type="number" step="0.01" class="form-control" id="price{{ $plan->id }}" name="price" value="{{ $plan->price }}" required>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="interval{{ $plan->id }}" class="form-label">Billing Interval</label>
                                                                <select class="form-select" id="interval{{ $plan->id }}" name="interval" required>
                                                                    <option value="3_days" {{ $plan->interval === '3_days' ? 'selected' : '' }}>3 Days</option>
                                                                    <option value="15_days" {{ $plan->interval === '15_days' ? 'selected' : '' }}>15 Days</option>
                                                                    <option value="monthly" {{ $plan->interval === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                                    <option value="yearly" {{ $plan->interval === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="is_active{{ $plan->id }}" class="form-label">Status</label>
                                                                <select class="form-select" id="is_active{{ $plan->id }}" name="is_active">
                                                                    <option value="1" {{ $plan->is_active ? 'selected' : '' }}>Active</option>
                                                                    <option value="0" {{ !$plan->is_active ? 'selected' : '' }}>Inactive</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="max_events{{ $plan->id }}" class="form-label">Max Events</label>
                                                                <input type="number" class="form-control" id="max_events{{ $plan->id }}" name="max_events" value="{{ $plan->max_events }}" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="max_contestants{{ $plan->id }}" class="form-label">Max Contestants</label>
                                                                <input type="number" class="form-control" id="max_contestants{{ $plan->id }}" name="max_contestants" value="{{ $plan->max_contestants }}" required>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="max_categories{{ $plan->id }}" class="form-label">Max Categories</label>
                                                                <input type="number" class="form-control" id="max_categories{{ $plan->id }}" name="max_categories" value="{{ $plan->max_categories }}" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="max_judges{{ $plan->id }}" class="form-label">Max Judges</label>
                                                                <input type="number" class="form-control" id="max_judges{{ $plan->id }}" name="max_judges" value="{{ $plan->max_judges }}" required>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="description{{ $plan->id }}" class="form-label">Description</label>
                                                            <textarea class="form-control" id="description{{ $plan->id }}" name="description" rows="3">{{ $plan->description }}</textarea>
                                                        </div>
                                                        
                                                        <div class="row mb-3">
                                                            <div class="col-12">
                                                                <label class="form-label">Features</label>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="checkbox" name="analytics" id="analytics{{ $plan->id }}" value="1" {{ $plan->analytics ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="analytics{{ $plan->id }}">Analytics</label>
                                                                </div>
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="checkbox" name="support_priority" id="support_priority{{ $plan->id }}" value="1" {{ $plan->support_priority ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="support_priority{{ $plan->id }}">Priority Support</label>
                                                                </div>
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="checkbox" name="dashboard_access" id="dashboard_access{{ $plan->id }}" value="1" {{ $plan->dashboard_access ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="dashboard_access{{ $plan->id }}">Dashboard Access</label>
                                                                </div>
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="checkbox" name="user_management" id="user_management{{ $plan->id }}" value="1" {{ $plan->user_management ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="user_management{{ $plan->id }}">User Management</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="checkbox" name="subscription_management" id="subscription_management{{ $plan->id }}" value="1" {{ $plan->subscription_management ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="subscription_management{{ $plan->id }}">Subscription Management</label>
                                                                </div>
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="checkbox" name="pageant_management" id="pageant_management{{ $plan->id }}" value="1" {{ $plan->pageant_management ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="pageant_management{{ $plan->id }}">Pageant Management</label>
                                                                </div>
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="checkbox" name="reports_module" id="reports_module{{ $plan->id }}" value="1" {{ $plan->reports_module ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="reports_module{{ $plan->id }}">Reports Module</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update Plan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteModal{{ $plan->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $plan->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $plan->id }}">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete the plan <strong>{{ $plan->name }}</strong>?</p>
                                                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete Plan</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Plan Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createPlanModalLabel">Create New Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.plans.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Plan Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (₱)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="interval" class="form-label">Billing Interval</label>
                            <select class="form-select" id="interval" name="interval" required>
                                <option value="3_days">3 Days</option>
                                <option value="15_days">15 Days</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_events" class="form-label">Max Events</label>
                            <input type="number" class="form-control" id="max_events" name="max_events" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="max_contestants" class="form-label">Max Contestants</label>
                            <input type="number" class="form-control" id="max_contestants" name="max_contestants" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_categories" class="form-label">Max Categories</label>
                            <input type="number" class="form-control" id="max_categories" name="max_categories" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="max_judges" class="form-label">Max Judges</label>
                            <input type="number" class="form-control" id="max_judges" name="max_judges" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Features</label>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="analytics" id="analytics" value="1">
                                <label class="form-check-label" for="analytics">Analytics</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="support_priority" id="support_priority" value="1">
                                <label class="form-check-label" for="support_priority">Priority Support</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="dashboard_access" id="dashboard_access" value="1" checked>
                                <label class="form-check-label" for="dashboard_access">Dashboard Access</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="user_management" id="user_management" value="1" checked>
                                <label class="form-check-label" for="user_management">User Management</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="subscription_management" id="subscription_management" value="1" checked>
                                <label class="form-check-label" for="subscription_management">Subscription Management</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="pageant_management" id="pageant_management" value="1">
                                <label class="form-check-label" for="pageant_management">Pageant Management</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="reports_module" id="reports_module" value="1">
                                <label class="form-check-label" for="reports_module">Reports Module</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection 

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable with advanced features
        $('#plans-table').DataTable({
            "pageLength": 10,
            "responsive": true,
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'copy',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'csv',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                }
            ],
            "language": {
                "paginate": {
                    "previous": "<",
                    "next": ">"
                },
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries"
            }
        });
    });
</script>

<style>
    /* Modal animation */
    .modal-content {
        animation: modalFadeIn 0.3s ease-out;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* DataTable Styling */
    .dataTables_wrapper .dataTables_length select {
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        padding: 5px 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
    
    .dataTables_wrapper .dt-buttons {
        margin-bottom: 15px;
    }
    
    .dataTables_wrapper .dt-buttons .dt-button {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px 10px;
        margin-right: 5px;
        font-size: 12px;
    }
    
    .dataTables_wrapper .dt-buttons .dt-button:hover {
        background-color: #e9ecef;
    }
</style>
@endpush 