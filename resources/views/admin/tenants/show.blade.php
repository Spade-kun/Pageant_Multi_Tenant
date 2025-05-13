@extends('layouts.DashboardTemplate')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tenant Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.tenants.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Tenants
                        </a>
                    </div>
                </div>
                <div class="card-body">
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
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Tenant Information</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">Pageant Name</th>
                                            <td>{{ $tenant->pageant_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Slug</th>
                                            <td>{{ $tenant->slug }}</td>
                                        </tr>
                                        <tr>
                                            <th>Database Name</th>
                                            <td>{{ $tenant->database_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <span class="badge badge-{{ 
                                                    $tenant->status === 'pending' ? 'warning' : 
                                                    ($tenant->status === 'approved' ? 'success' : 'danger') 
                                                }}">
                                            {{ ucfirst($tenant->status) }}
                                        </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $tenant->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </table>
                            </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Owner Information</h4>
                                </div>
                                <div class="card-body">
                            @if($tenant->owner)
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 30%">Name</th>
                                                <td>{{ $tenant->owner->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>{{ $tenant->owner->email }}</td>
                                            </tr>
                                            <tr>
                                                <th>Age</th>
                                                <td>{{ $tenant->owner->age }}</td>
                                            </tr>
                                            <tr>
                                                <th>Gender</th>
                                                <td>{{ ucfirst($tenant->owner->gender) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Address</th>
                                                <td>{{ $tenant->owner->address }}</td>
                                            </tr>
                                        </table>
                                    @else
                                        <p class="text-muted">No owner information available.</p>
                                    @endif
                            </div>
                            </div>
                        </div>
                    </div>

                    @if($tenant->status === 'pending')
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Actions</h4>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('admin.tenants.approve', $tenant) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this tenant?')">
                                                <i class="fas fa-check"></i> Approve Tenant
                                </button>
                            </form>

                            <a href="{{ route('admin.tenants.reject.form', $tenant) }}" 
                               class="btn btn-danger"
                               onclick="return confirm('Are you sure you want to reject this tenant?')">
                                            <i class="fas fa-times"></i> Reject Tenant
                            </a>
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