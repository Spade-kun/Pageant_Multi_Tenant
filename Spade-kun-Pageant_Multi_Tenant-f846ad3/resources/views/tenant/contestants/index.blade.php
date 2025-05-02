@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Contestants</h4>
        <div class="ml-auto">
            <a href="{{ route('tenant.contestants.create', ['slug' => $slug]) }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add New Contestant
            </a>
        </div>
    </div>

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

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Contestants List</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="contestants-table" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Representing</th>
                                    <th>Status</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contestants as $contestant)
                                <tr>
                                    <td style="width: 100px;">
                                        @if($contestant->photo)
                                            <img src="{{ asset('storage/' . $contestant->photo) }}" 
                                                 alt="{{ $contestant->name }}" 
                                                 class="img-fluid rounded"
                                                 style="max-width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="avatar avatar-sm">
                                                <span class="avatar-title rounded-circle border border-secondary bg-light">
                                                    <i class="fa fa-user text-secondary"></i>
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $contestant->name }}</td>
                                    <td>{{ $contestant->age }}</td>
                                    <td>{{ ucfirst($contestant->gender) }}</td>
                                    <td>{{ $contestant->representing }}</td>
                                    <td>
                                        @if($contestant->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($contestant->registration_date)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="form-button-action">
                                            <a href="{{ route('tenant.contestants.show', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                                               class="btn btn-link btn-info">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('tenant.contestants.edit', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                                               class="btn btn-link btn-primary">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('tenant.contestants.destroy', ['slug' => $slug, 'id' => $contestant->id]) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-link btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this contestant?')">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No contestants found.</td>
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

@push('scripts')
<script>
    $(document).ready(function() {
        $('#contestants-table').DataTable({
            "pageLength": 10,
            "responsive": true,
            "order": [[1, "asc"]], // Sort by name by default
            "columnDefs": [
                { "orderable": false, "targets": [0, 5] } // Disable sorting for photo and actions columns
            ]
        });
    });
</script>
@endpush
@endsection 