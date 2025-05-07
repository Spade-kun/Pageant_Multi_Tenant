@extends('layouts.TenantDashboardTemplate')

@section('title', 'System Update Processing')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">System Update Processing</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                                <span class="sr-only">Processing...</span>
                            </div>
                        </div>
                        <h3 class="mb-3">Your system update is being processed</h3>
                        <p class="lead">Please do not close this window or navigate away.</p>
                        <p>You will be redirected automatically when the update is complete.</p>
                        
                        <div class="progress mt-4">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                        </div>

                        <div class="mt-5 text-muted">
                            <p><i class="fas fa-info-circle"></i> Updates can take several minutes to complete depending on your system speed and the size of the update.</p>
                        </div>

                        <form id="updateForm" action="{{ route('tenant.updates.update', ['slug' => request()->route('slug')]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="version" value="{{ request('version') }}">
                            <input type="hidden" name="auto_redirect" value="1">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Submit the form automatically after a short delay
    setTimeout(function() {
        $('#updateForm').submit();
    }, 1500);
});
</script>
@endpush 