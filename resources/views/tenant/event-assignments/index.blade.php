@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Event Assignments</h1>
        <a href="{{ route('tenant.event-assignments.create', ['slug' => $slug]) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Assignment
        </a>
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

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Contestants</th>
                            <th>Categories</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment['event_name'] }}</td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($assignment['contestants'] as $contestant)
                                            <li>{{ $contestant }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($assignment['categories'] as $category)
                                            <li>{{ $category }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $assignment['status'] === 'confirmed' ? 'success' : ($assignment['status'] === 'withdrawn' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($assignment['status']) }}
                                    </span>
                                </td>
                                <td>{{ $assignment['notes'] ?? 'No notes' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tenant.event-assignments.show', ['slug' => $slug, 'id' => $assignment['id']]) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('tenant.event-assignments.edit', ['slug' => $slug, 'id' => $assignment['id']]) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('tenant.event-assignments.destroy', ['slug' => $slug, 'id' => $assignment['id']]) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete all assignments for this event? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>
@endsection 