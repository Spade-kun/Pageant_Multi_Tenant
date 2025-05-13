@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Tenant Management</h4>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Tenants Management</h4>
                    </div>
                </div>
                <div class="card-body">
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
                                                        <a class="dropdown-item" href="{{ route('admin.tenants.show', $tenant) }}">
                                                            <i class="fa fa-eye text-info"></i> View Details
                                                </a>
                                                    </li>

                                                @if($tenant->status === 'pending')
                                                    <li>
                                                        <form method="POST" action="{{ route('admin.tenants.direct-approve', $tenant) }}" class="dropdown-item-form">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fa fa-check text-success"></i> Approve
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.tenants.reject.form', $tenant) }}">
                                                            <i class="fa fa-times text-danger"></i> Reject
                                                        </a>
                                                    </li>
                                                    @endif

                                                    <li>
                                                        @if($tenant->is_active)
                                                        <form method="POST" action="{{ route('admin.tenants.disable', $tenant) }}" class="dropdown-item-form">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fa fa-ban text-danger"></i> Disable Access
                                                            </button>
                                                        </form>
                                                        @else
                                                        <form method="POST" action="{{ route('admin.tenants.enable', $tenant) }}" class="dropdown-item-form">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fa fa-check-circle text-success"></i> Enable Access
                                                            </button>
                                                        </form>
                                                @endif
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
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
        // Initialize combined table
        $('#tenants-table').DataTable({
            "pageLength": 10,
            "order": [[5, "desc"]], // Sort by Created At column by default
            "responsive": true,
            "language": {
                "paginate": {
                    "previous": "<",
                    "next": ">"
                }
            }
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Add confirmation dialogs for forms
        $('.dropdown-item-form').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            const buttonText = $(this).find('button').text().trim();
            const buttonIcon = $(this).find('button i').attr('class');
            
            let confirmMessage = `Are you sure you want to ${buttonText}?`;
            
            // Customize message based on the action
            if (buttonIcon.includes('check') && !buttonIcon.includes('check-circle')) {
                confirmMessage = 'Are you sure you want to approve this tenant? This will create their database and send them an email with access details.';
            } else if (buttonIcon.includes('ban')) {
                confirmMessage = 'Are you sure you want to disable access for this tenant?';
            } else if (buttonIcon.includes('check-circle')) {
                confirmMessage = 'Are you sure you want to enable access for this tenant?';
            }
            
            if(confirm(confirmMessage)) {
                form.submit();
            }
        });
    });
    </script>
    
    <style>
    .dropdown-item-form {
        margin: 0;
        padding: 0;
    }
    
    .dropdown-item-form .dropdown-item {
        display: block;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 0.25rem 1.5rem;
        clear: both;
        font-weight: 400;
        color: #212529;
        white-space: nowrap;
    }
    
    .dropdown-item-form .dropdown-item:hover, 
    .dropdown-item-form .dropdown-item:focus {
        color: #16181b;
        text-decoration: none;
        background-color: #f8f9fa;
    }
    </style>
@endpush
