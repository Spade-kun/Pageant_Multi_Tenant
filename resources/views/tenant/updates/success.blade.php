@extends('tenant.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Update Successful</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="mb-3">System Update Completed Successfully!</h3>
                    <p class="lead">Your system has been updated to version {{ $version }}.</p>
                    <p>The following actions have been completed:</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Downloaded new version</li>
                        <li><i class="fas fa-check text-success"></i> Extracted files</li>
                        <li><i class="fas fa-check text-success"></i> Updated system files</li>
                        <li><i class="fas fa-check text-success"></i> Ran database migrations</li>
                        <li><i class="fas fa-check text-success"></i> Updated dependencies</li>
                    </ul>
                    <p class="mt-4">You will be redirected back to the updates page in a few seconds...</p>
                    <div class="mt-4">
                        <a href="{{ route('tenant.updates.index', ['slug' => $slug]) }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Return to Updates
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Redirect back to updates page after 5 seconds
    setTimeout(function() {
        window.location.href = "{{ route('tenant.updates.index', ['slug' => $slug]) }}";
    }, 5000);
</script>
@endpush
@endsection 