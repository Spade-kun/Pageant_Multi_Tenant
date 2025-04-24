@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-header">
    <h4 class="page-title">User Dashboard</h4>
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="#">
                <i class="fas fa-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="fas fa-chevron-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Dashboard</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Welcome, {{ session('tenant_user.name') }}!</h4>
            </div>
            <div class="card-body">
                <p>You are logged in as a user.</p>
                <!-- Add your dashboard content here -->
            </div>
        </div>
    </div>
</div>
@endsection 