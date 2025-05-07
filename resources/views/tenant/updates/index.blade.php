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
                                                    <a href="{{ route('tenant.updates.update', ['slug' => request()->route('slug')]) }}?version={{ $release['version'] }}" 
                                                       class="btn btn-sm {{ version_compare($release['version'], $currentVersion, '>') ? 'btn-primary' : 'btn-warning' }}"
                                                       onclick="return confirm('Are you sure you want to {{ version_compare($release['version'], $currentVersion, '>') ? 'update to' : 'downgrade to' }} version {{ $release['version'] }}? {{ version_compare($release['version'], $currentVersion, '<') ? 'Downgrading may cause compatibility issues.' : '' }}')">
                                                        {{ version_compare($release['version'], $currentVersion, '>') ? 'Update' : 'Downgrade' }}
                                                    </a>
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
                <form id="updateForm" action="{{ route('tenant.updates.update', ['slug' => request()->route('slug')]) }}" method="POST" class="d-none">
                    @csrf
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
    // Sort the table by version by default (descending)
    sortTable('version', 'desc');

    function checkForUpdates() {
        $('#updateSpinner').removeClass('d-none');
        $('#updateModalContent').addClass('d-none');
        $('#updateForm').addClass('d-none');
        $('#updateModal').modal('show');

        $.get('{{ route("tenant.updates.check", ["slug" => request()->route("slug")]) }}')
            .done(function(response) {
                $('#updateSpinner').addClass('d-none');
                $('#updateModalContent').removeClass('d-none');

                if (response.releases && response.releases.length > 0) {
                    updateReleaseHistory(response.releases, response.currentVersion);
                    
                    // Find the latest version
                    let latestVersion = response.currentVersion;
                    response.releases.forEach(function(release) {
                        if (compareVersions(release.version, latestVersion) > 0) {
                            latestVersion = release.version;
                        }
                    });
                    
                    const hasUpdate = compareVersions(latestVersion, response.currentVersion) > 0;
                    
                    if (hasUpdate) {
                        $('#updateModalContent').html(`
                            <div class="alert alert-success">
                                <h4>Update Available!</h4>
                                <p>A new version (${latestVersion}) is available. Your current version is ${response.currentVersion}</p>
                                <button class="btn btn-primary" id="doUpdateBtn" data-version="${latestVersion}">
                                    Update Now
                                </button>
                            </div>
                        `);
                        
                        // Handle update button click
                        $('#doUpdateBtn').on('click', function() {
                            const version = $(this).data('version');
                            
                            // Redirect to the update processing page
                            $('#updateModal').modal('hide');
                            window.location.href = '{{ route("tenant.updates.update", ["slug" => request()->route("slug")]) }}' + '?version=' + version;
                        });
                    } else {
                        $('#updateModalContent').html(`
                            <div class="alert alert-info">
                                <h4>System Up To Date</h4>
                                <p>You are already running the latest version (${response.currentVersion}).</p>
                            </div>
                        `);
                    }
                } else {
                    $('#updateModalContent').html(`
                        <div class="alert alert-warning">
                            <h4>No Releases Found</h4>
                            <p>Could not find any release information. Please check your configuration.</p>
                        </div>
                    `);
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
                `<button type="button" 
                        class="btn btn-sm ${isUpgrade ? 'btn-primary' : 'btn-warning'} update-version-btn"
                        data-version="${release.version}"
                        data-is-upgrade="${isUpgrade}">
                    ${isUpgrade ? 'Update' : 'Downgrade'}
                </button>`;

            tbody.append(`
                <tr>
                    <td>
                        ${release.version}
                        ${isCurrentVersion ? '<span class="badge badge-success ml-2">Current</span>' : ''}
                    </td>
                    <td>${release.released_at}</td>
                    <td>${release.author}</td>
                    <td>${release.description.replace(/\n/g, '<br>')}</td>
                    <td>${actionButton}</td>
                </tr>
            `);
        });
        
        // Add event listeners for update buttons
        $('.update-version-btn').on('click', function() {
            const version = $(this).data('version');
            const isUpgrade = $(this).data('is-upgrade') === true;
            
            if (confirm('Are you sure you want to ' + (isUpgrade ? 'update to' : 'downgrade to') + ' version ' + version + '? ' + 
                       (!isUpgrade ? 'Downgrading may cause compatibility issues.' : ''))) {
                
                // Redirect to processing page
                window.location.href = '{{ route("tenant.updates.update", ["slug" => request()->route("slug")]) }}' + '?version=' + version;
            }
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

    function sortTable(column, direction) {
        // Add table sorting functionality here if needed
    }

    $('#checkUpdatesBtn').click(checkForUpdates);
    
    // Handle updates from buttons in the table
    $('.update-version-btn').on('click', function() {
        const version = $(this).data('version');
        const isUpgrade = $(this).data('is-upgrade') === true;
        
        if (confirm('Are you sure you want to ' + (isUpgrade ? 'update to' : 'downgrade to') + ' version ' + version + '? ' + 
                   (!isUpgrade ? 'Downgrading may cause compatibility issues.' : ''))) {
            
            // Redirect to processing page
            window.location.href = '{{ route("tenant.updates.update", ["slug" => request()->route("slug")]) }}' + '?version=' + version;
        }
    });
});
</script>
@endpush 