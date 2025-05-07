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
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <h5>Current Version</h5>
                                        <h3>{{ $currentVersion }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <h5>Latest Version</h5>
                                        <h3>
                                            {{ $newVersion ?? $currentVersion }}
                                            @if(isset($isNewVersionAvailable) && $isNewVersionAvailable)
                                                <span class="badge badge-success">Update Available</span>
                                            @else
                                                <span class="badge badge-info">Up to date</span>
                                            @endif
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-primary" id="checkUpdatesBtn">
                                <i class="fas fa-sync"></i> Check for Updates
                            </button>
                        </div>

                        @if(isset($releases) && count($releases) > 0)
                        <div class="mt-4">
                            <h4>Release History</h4>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>VERSION</th>
                                            <th>RELEASED AT</th>
                                            <th>AUTHOR</th>
                                            <th>DESCRIPTION</th>
                                            <th>ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody id="releaseHistory">
                                        @foreach($releases as $release)
                                        <tr>
                                            <td>
                                                {{ $release['version'] }}
                                                @if($release['version'] === $currentVersion)
                                                    <span class="badge badge-success ml-2">Current</span>
                                                @endif
                                            </td>
                                            <td>{{ $release['released_at'] }}</td>
                                            <td>{{ $release['author'] }}</td>
                                            <td>{!! nl2br(e($release['description'])) !!}</td>
                                            <td>
                                                @if($release['version'] !== $currentVersion)
                                                    <form action="{{ route('tenant.updates.update', ['slug' => session('tenant_slug')]) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="version" value="{{ $release['version'] }}">
                                                        <input type="hidden" name="redirect_url" value="{{ route('tenant.updates.index', ['slug' => session('tenant_slug')]) }}">
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ version_compare($release['version'], $currentVersion, '>') ? 'btn-primary' : 'btn-warning' }}"
                                                                onclick="return confirm('Are you sure you want to {{ version_compare($release['version'], $currentVersion, '>') ? 'update to' : 'downgrade to' }} version {{ $release['version'] }}? {{ version_compare($release['version'], $currentVersion, '<') ? 'Downgrading may cause compatibility issues.' : '' }}')">
                                                            {{ version_compare($release['version'], $currentVersion, '>') ? 'Update' : 'Downgrade' }}
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
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">System Update Available</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="updateModalContent">
                    <!-- Content will be populated via JavaScript -->
                </div>
                <div id="updateSpinner" class="text-center d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Checking for updates...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="updateForm" action="{{ route('tenant.updates.update', ['slug' => session('tenant_slug')]) }}" method="POST" class="d-none">
                    @csrf
                    <input type="hidden" name="redirect_url" value="{{ route('tenant.updates.index', ['slug' => session('tenant_slug')]) }}">
                    <button type="submit" class="btn btn-primary">Install Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function checkForUpdates() {
        $('#updateSpinner').removeClass('d-none');
        $('#updateModalContent').addClass('d-none');
        $('#updateForm').addClass('d-none');
        $('#updateModal').modal('show');

        $.get('{{ route("tenant.updates.check", ["slug" => session("tenant_slug")]) }}')
            .done(function(response) {
                $('#updateSpinner').addClass('d-none');
                $('#updateModalContent').removeClass('d-none');

                if (response.releases && response.releases.length > 0) {
                    updateReleaseHistory(response.releases, response.currentVersion);
                }
            })
            .fail(function(error) {
                $('#updateSpinner').addClass('d-none');
                $('#updateModalContent').removeClass('d-none');
                $('#updateModalContent').html(`
                    <div class="alert alert-danger">
                        <h4>Error Checking for Updates</h4>
                        <p>${error.responseJSON?.error || 'An unexpected error occurred.'}</p>
                    </div>
                `);
            });
    }

    function updateReleaseHistory(releases, currentVersion) {
        const tbody = $('#releaseHistory');
        tbody.empty();

        releases.forEach(release => {
            const isCurrentVersion = release.version === currentVersion;
            const isUpgrade = compareVersions(release.version, currentVersion) > 0;
            
            const actionButton = isCurrentVersion ? 
                `<span class="badge badge-success">Current Version</span>` :
                `<form action="{{ route('tenant.updates.update', ['slug' => session('tenant_slug')]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="version" value="${release.version}">
                    <input type="hidden" name="redirect_url" value="${encodeURIComponent(route('tenant.updates.index', ['slug' => session('tenant_slug')])).replace(/%2F/g, '/')}">
                    <button type="submit" 
                            class="btn btn-sm ${isUpgrade ? 'btn-primary' : 'btn-warning'}"
                            onclick="return confirm('Are you sure you want to ${isUpgrade ? 'update to' : 'downgrade to'} version ${release.version}? ${!isUpgrade ? 'Downgrading may cause compatibility issues.' : ''}')">
                        ${isUpgrade ? 'Update' : 'Downgrade'}
                    </button>
                </form>`;

            tbody.append(`
                <tr>
                    <td>
                        ${release.version}
                        ${isCurrentVersion ? '<span class="badge badge-success ml-2">Current</span>' : ''}
                    </td>
                    <td>${release.released_at}</td>
                    <td>${release.author}</td>
                    <td>${release.description}</td>
                    <td>${actionButton}</td>
                </tr>
            `);
        });
    }

    function compareVersions(v1, v2) {
        const normalize = v => v.split('.').map(n => parseInt(n, 10));
        const [a1, a2, a3] = normalize(v1);
        const [b1, b2, b3] = normalize(v2);
        
        if (a1 !== b1) return a1 - b1;
        if (a2 !== b2) return a2 - b2;
        return a3 - b3;
    }

    $('#checkUpdatesBtn').click(checkForUpdates);
});
</script>
@endpush 