@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Tenants Management</h4>
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
                        <table id="tenants-table" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Pageant Name</th>
                                    <th>Slug</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Access Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tenants as $tenant)
                                    <tr>
                                        <td>{{ $tenant->pageant_name }}</td>
                                        <td>{{ $tenant->slug }}</td>
                                        <td>{{ $tenant->users->where('role', 'owner')->first()->email ?? 'N/A' }}</td>
                                        <td>
                                            @if($tenant->status === 'approved')
                                                <span class="badge bg-success text-white">Approved</span>
                                            @elseif($tenant->status === 'pending')
                                                <span class="badge bg-warning text-white">Pending</span>
                                            @elseif($tenant->status === 'rejected')
                                                <span class="badge bg-danger text-white">Rejected</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tenant->is_active)
                                                <span class="badge bg-success text-white">Enabled</span>
                                            @else
                                                <span class="badge bg-danger text-white">Disabled</span>
                                            @endif
                                        </td>
                                        <td>{{ $tenant->created_at->format('M d, Y H:i A') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="actionDropdown{{ $tenant->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $tenant->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewModal{{ $tenant->id }}">
                                                            <i class="fa fa-eye text-info"></i> View Details
                                                        </a>
                                                    </li>

                                                @if($tenant->status === 'pending')
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#approveModal{{ $tenant->id }}">
                                                            <i class="fa fa-check text-success"></i> Approve
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.tenants.reject.form', $tenant) }}">
                                                            <i class="fa fa-times text-danger"></i> Reject
                                                        </a>
                                                    </li>
                                                    @endif

                                                    <li>
                                                        @if($tenant->is_active)
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#disableModal{{ $tenant->id }}">
                                                            <i class="fa fa-ban text-danger"></i> Disable Access
                                                        </a>
                                                        @else
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#enableModal{{ $tenant->id }}">
                                                            <i class="fa fa-check-circle text-success"></i> Enable Access
                                                        </a>
                                                        @endif
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Details Modal -->
                                    <div class="modal fade" id="viewModal{{ $tenant->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $tenant->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewModalLabel{{ $tenant->id }}">Tenant Details: {{ $tenant->pageant_name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6 class="fw-bold">Tenant Information</h6>
                                                            <p><strong>Pageant Name:</strong> {{ $tenant->pageant_name }}</p>
                                                            <p><strong>Slug:</strong> {{ $tenant->slug }}</p>
                                                            <p><strong>Created At:</strong> {{ $tenant->created_at->format('M d, Y H:i A') }}</p>
                                                            <p><strong>Status:</strong> 
                                                                @if($tenant->status === 'approved')
                                                                    <span class="badge bg-success text-white">Approved</span>
                                                                @elseif($tenant->status === 'pending')
                                                                    <span class="badge bg-warning text-white">Pending</span>
                                                                @elseif($tenant->status === 'rejected')
                                                                    <span class="badge bg-danger text-white">Rejected</span>
                                                                @endif
                                                            </p>
                                                            <p><strong>Access Status:</strong> 
                                                                @if($tenant->is_active)
                                                                    <span class="badge bg-success text-white">Enabled</span>
                                                                @else
                                                                    <span class="badge bg-danger text-white">Disabled</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="fw-bold">Owner Information</h6>
                                                            @if($owner = $tenant->users->where('role', 'owner')->first())
                                                                <p><strong>Name:</strong> {{ $owner->name }}</p>
                                                                <p><strong>Email:</strong> {{ $owner->email }}</p>
                                                            @else
                                                                <p>No owner information available</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    @if($tenant->rejection_reason)
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6 class="fw-bold text-danger">Rejection Reason</h6>
                                                            <p>{{ $tenant->rejection_reason }}</p>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn btn-primary">Full Details</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Approve Modal -->
                                    @if($tenant->status === 'pending')
                                    <div class="modal fade" id="approveModal{{ $tenant->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $tenant->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title" id="approveModalLabel{{ $tenant->id }}">Approve Tenant</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to approve <strong>{{ $tenant->pageant_name }}</strong>?</p>
                                                    <p>This action will:</p>
                                                    <ul>
                                                        <li>Create a database for this tenant</li>
                                                        <li>Send an email with access details to the owner</li>
                                                    </ul>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" action="{{ route('admin.tenants.direct-approve', $tenant) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success">Approve Tenant</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <!-- Enable Modal -->
                                    @if(!$tenant->is_active)
                                    <div class="modal fade" id="enableModal{{ $tenant->id }}" tabindex="-1" aria-labelledby="enableModalLabel{{ $tenant->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title" id="enableModalLabel{{ $tenant->id }}">Enable Tenant Access</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to enable access for <strong>{{ $tenant->pageant_name }}</strong>?</p>
                                                    <p>This will allow users to access this tenant's application.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" action="{{ route('admin.tenants.enable', $tenant) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-success">Enable Access</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <!-- Disable Modal -->
                                    @if($tenant->is_active)
                                    <div class="modal fade" id="disableModal{{ $tenant->id }}" tabindex="-1" aria-labelledby="disableModalLabel{{ $tenant->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="disableModalLabel{{ $tenant->id }}">Disable Tenant Access</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to disable access for <strong>{{ $tenant->pageant_name }}</strong>?</p>
                                                    <p>This will prevent users from accessing this tenant's application. A disabled page will be shown instead.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" action="{{ route('admin.tenants.disable', $tenant) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-danger">Disable Access</button>
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
@endsection

@push('scripts')
    <script>
    $(document).ready(function() {
        // Initialize DataTable with advanced features
        $('#tenants-table').DataTable({
            "pageLength": 10,
            "order": [[5, "desc"]], // Sort by Created At column by default
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
