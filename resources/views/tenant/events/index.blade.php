@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Events</h1>
        <a href="{{ route('tenant.events.create', ['slug' => $slug]) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Event
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
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>{{ $event->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y h:i A') }}</td>
                                <td>{{ $event->location }}</td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'scheduled' => 'primary',
                                            'ongoing' => 'success',
                                            'completed' => 'info',
                                            'cancelled' => 'danger'
                                        ][$event->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $statusClass }}">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tenant.events.show', ['slug' => $slug, 'event' => $event->id]) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('tenant.events.edit', ['slug' => $slug, 'event' => $event->id]) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('tenant.events.destroy', ['slug' => $slug, 'event' => $event->id]) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this event?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No events found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $events->links() }}
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