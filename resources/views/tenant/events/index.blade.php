@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <div class="d-flex align-items-center">
            <h4 class="page-title">Events</h4>
            <a href="{{ route('tenant.events.create', ['slug' => $slug]) }}" class="btn btn-primary btn-round ml-auto">
                <i class="fa fa-plus"></i>
                Add Event
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
                    <h4 class="card-title">Events List</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th style="width: 10%">Actions</th>
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
                                        <div class="form-button-action">
                                            <a href="{{ route('tenant.events.show', ['slug' => $slug, 'event' => $event->id]) }}" class="btn btn-link btn-primary btn-lg">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('tenant.events.edit', ['slug' => $slug, 'event' => $event->id]) }}" class="btn btn-link btn-primary btn-lg">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('tenant.events.destroy', ['slug' => $slug, 'event' => $event->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                                <button type="submit" class="btn btn-link btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">
                                                    <i class="fa fa-times"></i>
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
    </div>
</div>
@endsection 