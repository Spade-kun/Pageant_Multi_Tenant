@extends('layouts.DashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Tenant Access Management</h4>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Tenant Access Control</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tenant-access-table" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Pageant Name</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th>Access Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tenants as $tenant)
                                    <tr>
                                        <td>{{ $tenant->pageant_name }}</td>
                                        <td>{{ $tenant->slug }}</td>
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
                                        <td>{{ $tenant->updated_at->format('M d, Y H:i A') }}</td>
                                        <td>
                                            <div class="form-button-action">
                                                @if($tenant->is_active)
                                                    <form class="d-inline" method="POST" action="{{ route('admin.tenants.disable', $tenant) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-danger btn-round btn-sm" data-toggle="tooltip" title="Disable Access">
                                                            <span class="btn-label">
                                                                <i class="fa fa-ban"></i>
                                                            </span>
                                                            Disable
                                                        </button>
                                                    </form>
                                                @else
                                                    <form class="d-inline" method="POST" action="{{ route('admin.tenants.enable', $tenant) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-success btn-round btn-sm" data-toggle="tooltip" title="Enable Access">
                                                            <span class="btn-label">
                                                                <i class="fa fa-check-circle"></i>
                                                            </span>
                                                            Enable
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
        $('#tenant-access-table').DataTable({
            "pageLength": 10,
            "order": [[4, "desc"]], // Sort by Last Updated column by default
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

        // Add confirmation dialogs
        $('form').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            const action = $(this).find('button[type="submit"]').text().trim().toLowerCase();
            
            if(confirm(`Are you sure you want to ${action} this tenant's access?`)) {
                form.submit();
            }
        });
    });
</script>
@endpush 