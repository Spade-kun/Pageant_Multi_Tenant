@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body text-center p-5">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
            </div>
            
            <h1 class="mb-3">Update Successful!</h1>
            
            <p class="lead mb-4">
                Your system has been successfully updated to version <strong>{{ $version }}</strong>.
            </p>
            
            <div class="mb-4">
                <h5>What's been updated:</h5>
                <ul class="list-unstyled">
                    <li>✅ Application code updated</li>
                    <li>✅ Database migrations applied</li>
                    <li>✅ Dependencies updated</li>
                    <li>✅ Cache cleared</li>
                </ul>
            </div>
            
            <a href="{{ route('tenant.updates.index', ['slug' => $slug]) }}" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-left mr-2"></i> Return to Updates
            </a>
        </div>
    </div>
</div>
@endsection 