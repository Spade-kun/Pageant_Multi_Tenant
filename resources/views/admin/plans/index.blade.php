@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Subscription Plans</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.plans.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create New Plan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Interval</th>
                                    <th>Max Events</th>
                                    <th>Max Contestants</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plans as $plan)
                                    <tr>
                                        <td>{{ $plan->name }}</td>
                                        <td>₱{{ number_format($plan->price, 2) }}</td>
                                        <td>
                                            @switch($plan->interval)
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
                                        <td>{{ $plan->max_events }}</td>
                                        <td>{{ $plan->max_contestants }}</td>
                                        <td>
                                            <span class="badge badge-{{ $plan->is_active ? 'success' : 'danger' }}">
                                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.plans.show', $plan) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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