@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plan Requests</h3>
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
                        <table id="requests-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tenant Name</th>
                                    <th>Owner Email</th>
                                    <th>Current Plan</th>
                                    <th>Requested Plan</th>
                                    <th>Price</th>
                                    <th>Interval</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td>{{ $request->tenant->pageant_name }}</td>
                                        <td>{{ $request->tenant->users->where('role', 'owner')->first()->email ?? 'No owner email' }}</td>
                                        <td>
                                            @if($request->tenant->plan)
                                                {{ $request->tenant->plan->name }}
                                            @else
                                                <span class="badge badge-secondary">No Plan</span>
                                            @endif
                                        </td>
                                        <td>{{ $request->plan->name }}</td>
                                        <td>₱{{ number_format($request->plan->price, 2) }}</td>
                                        <td>
                                            @switch($request->plan->interval)
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
                                        <td>
                                            <span class="badge badge-{{ 
                                                $request->status === 'pending' ? 'warning' : 
                                                ($request->status === 'approved' ? 'success' : 'danger') 
                                            }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="actionDropdown{{ $request->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $request->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewModal{{ $request->id }}">
                                                            <i class="fas fa-eye text-info"></i> View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePlanModal{{ $request->tenant_id }}">
                                                            <i class="fas fa-exchange-alt text-warning"></i> Change Plan
                                                        </a>
                                                    </li>
                                            @if($request->status === 'pending')
                                                        <li>
                                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#approveModal{{ $request->id }}">
                                                                <i class="fas fa-check text-success"></i> Approve Request
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">
                                                                <i class="fas fa-times text-danger"></i> Reject Request
                                                            </a>
                                                        </li>
                                            @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Details Modal -->
                                    <div class="modal fade" id="viewModal{{ $request->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $request->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewModalLabel{{ $request->id }}">Request Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6 class="fw-bold">Tenant Information</h6>
                                                            <p><strong>Tenant Name:</strong> {{ $request->tenant->pageant_name }}</p>
                                                            <p><strong>Tenant Slug:</strong> {{ $request->tenant->slug }}</p>
                                                            <p><strong>Owner Email:</strong> {{ $request->tenant->users->where('role', 'owner')->first()->email ?? 'No owner email' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="fw-bold">Request Information</h6>
                                                            <p><strong>Current Plan:</strong> 
                                                                @if($request->tenant->plan)
                                                                    {{ $request->tenant->plan->name }}
                                                                @else
                                                                    <span class="badge badge-secondary">No Plan</span>
                                                                @endif
                                                            </p>
                                                            <p><strong>Requested Plan:</strong> {{ $request->plan->name }}</p>
                                                            <p><strong>Price:</strong> ₱{{ number_format($request->plan->price, 2) }}</p>
                                                            <p><strong>Status:</strong> 
                                                                <span class="badge badge-{{ 
                                                                    $request->status === 'pending' ? 'warning' : 
                                                                    ($request->status === 'approved' ? 'success' : 'danger') 
                                                                }}">
                                                                    {{ ucfirst($request->status) }}
                                                                </span>
                                                            </p>
                                                            <p><strong>Request Date:</strong> {{ $request->created_at->format('M d, Y H:i A') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <a href="{{ route('admin.requests.show', $request) }}" class="btn btn-primary">Full Details</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Change Plan Modal -->
                                    <div class="modal fade" id="changePlanModal{{ $request->tenant_id }}" tabindex="-1" aria-labelledby="changePlanModalLabel{{ $request->tenant_id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning text-white">
                                                    <h5 class="modal-title" id="changePlanModalLabel{{ $request->tenant_id }}">
                                                        Change Plan for {{ $request->tenant->pageant_name }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.requests.update-plan', $request->tenant_id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="row mb-4">
                                                            <div class="col-md-6">
                                                                <h6 class="fw-bold">Tenant Information</h6>
                                                                <p><strong>Tenant Name:</strong> {{ $request->tenant->pageant_name }}</p>
                                                                <p><strong>Tenant Slug:</strong> {{ $request->tenant->slug }}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="fw-bold">Current Plan Information</h6>
                                                                <p><strong>Current Plan:</strong> 
                                                                    @if($request->tenant->plan)
                                                                        {{ $request->tenant->plan->name }}
                                                                    @else
                                                                        <span class="badge badge-secondary">No Plan</span>
                                                                    @endif
                                                                </p>
                                                                @if($request->tenant->plan)
                                                                <p><strong>Price:</strong> ₱{{ number_format($request->tenant->plan->price, 2) }}</p>
                                                                <p><strong>Interval:</strong> 
                                                                    @switch($request->tenant->plan->interval)
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
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="plan_id{{ $request->tenant_id }}" class="form-label">Select New Plan</label>
                                                            <select class="form-select" id="plan_id{{ $request->tenant_id }}" name="plan_id" required>
                                                                <option value="">-- Select a Plan --</option>
                                                                @foreach($plans as $plan)
                                                                    <option value="{{ $plan->id }}" {{ $request->plan_id == $plan->id ? 'selected' : '' }}>
                                                                        {{ $plan->name }} - ₱{{ number_format($plan->price, 2) }} 
                                                                        (@switch($plan->interval)
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
                                                                        @endswitch)
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            Changing the plan will immediately update the tenant's subscription. Ensure you've communicated with the tenant about this change.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-warning">Change Plan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Approve Modal -->
                                    @if($request->status === 'pending')
                                    <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $request->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title" id="approveModalLabel{{ $request->id }}">Approve Plan Request</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to approve this plan request?</p>
                                                    <div class="mb-3">
                                                        <p><strong>Tenant:</strong> {{ $request->tenant->pageant_name }}</p>
                                                        <p><strong>Current Plan:</strong> 
                                                            @if($request->tenant->plan)
                                                                {{ $request->tenant->plan->name }}
                                                            @else
                                                                <span class="badge badge-secondary">No Plan</span>
                                                            @endif
                                                        </p>
                                                        <p><strong>Requested Plan:</strong> {{ $request->plan->name }}</p>
                                                        <p><strong>Price:</strong> ₱{{ number_format($request->plan->price, 2) }}</p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('admin.requests.approve', $request) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-success">Approve Request</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $request->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="rejectModalLabel{{ $request->id }}">Reject Plan Request</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to reject this plan request?</p>
                                                    <div class="mb-3">
                                                        <p><strong>Tenant:</strong> {{ $request->tenant->pageant_name }}</p>
                                                        <p><strong>Requested Plan:</strong> {{ $request->plan->name }}</p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('admin.requests.reject', $request) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-danger">Reject Request</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable with advanced features
    $('#requests-table').DataTable({
        "pageLength": 10,
        "order": [[7, "desc"]], // Sort by Date column by default
        "responsive": true,
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'copy',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            },
            {
                extend: 'csv',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            },
            {
                extend: 'excel',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            },
            {
                extend: 'pdf',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            },
            {
                extend: 'print',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
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
@endsection 