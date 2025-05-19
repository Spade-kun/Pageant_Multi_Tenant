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
                                    <th>Email</th>
                                    <th>Requested Plan</th>
                                    <th>Request Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td>{{ $request->tenant->name }}</td>
                                        <td>{{ $request->tenant->email }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $request->plan_type)) }}</td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#approveModal{{ $request->id }}">
                                                Approve
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal{{ $request->id }}">
                                                Reject
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Approve Modal -->
                                    <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Approve Plan Request</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('admin.plans.approve', $request) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to approve this plan request?</p>
                                                        <p>Tenant: {{ $request->tenant->name }}</p>
                                                        <p>Plan: {{ ucfirst(str_replace('_', ' ', $request->plan_type)) }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Approve</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Plan Request</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('admin.plans.reject', $request) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Reason for Rejection</label>
                                                            <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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