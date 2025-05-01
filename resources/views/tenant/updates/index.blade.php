@extends('layouts.TenantDashboardTemplate')

@section('title', 'System Updates')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">System Updates</h4>
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

                        @if(session('info'))
                            <div class="alert alert-info">
                                {{ session('info') }}
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-box-content">
                                        <h5>Current Version</h5>
                                        <p class="mb-0">{{ $currentVersion ?? 'Unknown' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-box-content">
                                        <h5>Latest Version</h5>
                                        <p class="mb-0" id="latest-version">
                                            @if($isNewVersionAvailable)
                                                {{ $newVersion }}
                                                <span class="badge bg-success">New!</span>
                                            @else
                                                {{ $currentVersion ?? 'Unknown' }}
                                                <span class="badge bg-info">Up to date</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button id="check-updates" class="btn btn-info">
                                    <i class="fas fa-sync"></i> Check for Updates
                                </button>

                                @if($isNewVersionAvailable)
                                    <form action="{{ route('tenant.updates.update', ['slug' => session('tenant_slug')]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to update the system? This may take a few minutes.')">
                                            <i class="fas fa-download"></i> Install Update
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
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
    $('#check-updates').click(function() {
        const button = $(this);
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i> Checking...');

        $.ajax({
            url: '{{ route("tenant.updates.check", ["slug" => session("tenant_slug")]) }}',
            method: 'GET',
            success: function(response) {
                if (response.hasUpdate) {
                    $('#latest-version').html(
                        response.newVersion + ' <span class="badge bg-success">New!</span>'
                    );
                    location.reload();
                } else {
                    $('#latest-version').html(
                        response.currentVersion + ' <span class="badge bg-info">Up to date</span>'
                    );
                }
            },
            error: function(xhr) {
                alert('Error checking for updates: ' + xhr.responseJSON.error);
            },
            complete: function() {
                button.prop('disabled', false);
                button.html('<i class="fas fa-sync"></i> Check for Updates');
            }
        });
    });
});
</script>
@endpush 