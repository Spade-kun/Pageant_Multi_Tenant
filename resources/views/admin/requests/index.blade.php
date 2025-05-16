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
                                    <th>Current Plan</th>
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
                                        <td>
                                            @if($request->tenant->plan)
                                                {{ $request->tenant->plan->name }}
                                            @else
                                                <span class="badge badge-secondary">No Plan</span>
                                            @endif
                                        </td>
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
                                            <div class="dropdown">
                                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="actionDropdown{{ $request->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $request->id }}">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.requests.show', $request) }}">
                                                            <i class="fas fa-eye text-info"></i> View Details
                                            </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.requests.change-plan', $request->tenant_id) }}">
                                                            <i class="fas fa-exchange-alt text-warning"></i> Change Plan
                                            </a>
                                                    </li>
                                            @if($request->status === 'pending')
                                                        <li>
                                                            <form action="{{ route('admin.requests.approve', $request) }}" method="POST" class="dropdown-item-form">
                                                    @csrf
                                                    @method('PUT')
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-check text-success"></i> Approve Request
                                                    </button>
                                                </form>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('admin.requests.reject', $request) }}" method="POST" class="dropdown-item-form">
                                                    @csrf
                                                    @method('PUT')
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-times text-danger"></i> Reject Request
                                                    </button>
                                                </form>
                                                        </li>
                                            @endif
                                                </ul>
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

<style>
.dropdown-item-form {
    margin: 0;
    padding: 0;
}

.dropdown-item-form .dropdown-item {
    display: block;
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: 0.25rem 1.5rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    white-space: nowrap;
}

.dropdown-item-form .dropdown-item:hover, 
.dropdown-item-form .dropdown-item:focus {
    color: #16181b;
    text-decoration: none;
    background-color: #f8f9fa;
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // Add confirmation dialogs for forms
    $('.dropdown-item-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const buttonText = $(this).find('button').text().trim();
        
        let confirmMessage = `Are you sure you want to ${buttonText.toLowerCase()}?`;
        
        if(confirm(confirmMessage)) {
            form.submit();
        }
    });
});
</script>
@endpush
@endsection 