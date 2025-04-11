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
                        <h4 class="card-title">Tenants List</h4>
                        <a href="{{ route('admin.tenants.access') }}" class="btn btn-primary btn-round ml-auto">
                            <span class="btn-label">
                                <i class="fa fa-toggle-on"></i>
                            </span>
                            Access Management
                        </a>
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
                                        <td>{{ $tenant->created_at->format('M d, Y H:i A') }}</td>
                                        <td>
                                            <div class="form-button-action">
                                                <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn btn-info btn-round btn-sm" data-toggle="tooltip" title="View">
                                                    <span class="btn-label">
                                                        <i class="fa fa-eye"></i>
                                                    </span>
                                                    View
                                                </a>

                                                @if($tenant->status === 'pending')
                                                    <form class="d-inline" method="POST" action="{{ route('admin.tenants.approve', $tenant) }}">
                                                @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-success btn-round btn-sm" data-toggle="tooltip" title="Approve">
                                                            <span class="btn-label">
                                                                <i class="fa fa-check"></i>
                                                            </span>
                                                            Approve
                                                        </button>
                                            </form>
                                            
                                                    <form class="d-inline" method="POST" action="{{ route('admin.tenants.reject', $tenant) }}">
                                                @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-danger btn-round btn-sm" data-toggle="tooltip" title="Reject">
                                                            <span class="btn-label">
                                                                <i class="fa fa-times"></i>
                                                            </span>
                                                            Reject
                                                        </button>
                                            </form>
                                                @endif
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
        $('#tenants-table').DataTable({
            "pageLength": 10,
            "order": [[4, "desc"]], // Sort by Created At column by default
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
        });
    </script>
@endpush
