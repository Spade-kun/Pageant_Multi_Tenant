@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Event Details</h4>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">{{ $event->name }}</h4>
                        <div class="ml-auto">
                            <a href="{{ route('tenant.events.edit', ['slug' => $slug, 'event' => $event->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('tenant.events.destroy', ['slug' => $slug, 'event' => $event->id]) }}" 
                                  method="POST" 
                                  style="display: inline-block;"
                                  onsubmit="return confirm('Are you sure you want to delete this event?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th style="width: 150px;">ID</th>
                                        <td>{{ $event->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $event->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Description</th>
                                        <td>{{ $event->description ?? 'No description available' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Start Date</th>
                                        <td>{{ date('F d, Y h:i A', strtotime($event->start_date)) }}</td>
                                    </tr>
                                    <tr>
                                        <th>End Date</th>
                                        <td>{{ date('F d, Y h:i A', strtotime($event->end_date)) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Location</th>
                                        <td>{{ $event->location }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($event->status == 'ongoing')
                                                <span class="badge badge-success">Ongoing</span>
                                            @elseif($event->status == 'upcoming')
                                                <span class="badge badge-info">Upcoming</span>
                                            @elseif($event->status == 'completed')
                                                <span class="badge badge-secondary">Completed</span>
                                            @else
                                                <span class="badge badge-warning">{{ ucfirst($event->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ date('F d, Y h:i A', strtotime($event->created_at)) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{ date('F d, Y h:i A', strtotime($event->updated_at)) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('tenant.events.index', ['slug' => $slug]) }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 