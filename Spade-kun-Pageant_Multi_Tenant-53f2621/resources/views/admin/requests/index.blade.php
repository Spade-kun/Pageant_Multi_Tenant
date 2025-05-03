@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plan Requests</h3>
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
                                    <th>Tenant Name</th>
                                    <th>Owner Email</th>
                                    <th>Requested Plan</th>
                                    <th>Price</th>
                                    <th>Interval</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td>{{ $request->tenant->pageant_name }}</td>
                                        <td>{{ $request->tenant->users->where('role', 'owner')->first()->email ?? 'No owner email' }}</td>
                                        <td>{{ $request->plan->name }}</td>
                                        <td>₱{{ number_format($request->plan->price, 2) }}</td>
                                        <td>
                                            @switch($request->plan->interval)
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
                                        <td>
                                            <span class="badge badge-{{ 
                                                $request->status === 'pending' ? 'warning' : 
                                                ($request->status === 'approved' ? 'success' : 'danger') 
                                            }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.requests.show', $request) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.requests.change-plan', $request->tenant_id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-exchange-alt"></i> Change Plan
                                            </a>
                                            @if($request->status === 'pending')
                                                <form action="{{ route('admin.requests.approve', $request) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this request?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.requests.reject', $request) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this request?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
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