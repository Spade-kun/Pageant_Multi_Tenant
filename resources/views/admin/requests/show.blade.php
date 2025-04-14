@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plan Request Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Requests
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Tenant Information</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">Pageant Name</th>
                                            <td>{{ $request->tenant->pageant_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Owner Name</th>
                                            <td>{{ $request->tenant->users->where('role', 'owner')->first()->name ?? 'No owner name' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Owner Email</th>
                                            <td>{{ $request->tenant->users->where('role', 'owner')->first()->email ?? 'No owner email' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Current Plan</th>
                                            <td>{{ $request->tenant->plan->name ?? 'No plan' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Requested Plan</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">Plan Name</th>
                                            <td>{{ $request->plan->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Price</th>
                                            <td>₱{{ number_format($request->plan->price, 2) }}/{{ $request->plan->interval }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <span class="badge badge-{{ 
                                                    $request->status === 'pending' ? 'warning' : 
                                                    ($request->status === 'approved' ? 'success' : 'danger') 
                                                }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($request->notes)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Notes</h4>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">{{ $request->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($request->status === 'pending')
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Actions</h4>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('admin.requests.approve', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this request?')">
                                                <i class="fas fa-check"></i> Approve Request
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.requests.reject', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this request?')">
                                                <i class="fas fa-times"></i> Reject Request
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 