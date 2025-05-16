@extends('layouts.TenantDashboardTemplate')

@section('title', 'System Updates')

@php
// Helper function to create a valid ID from version string
function versionToId($version) {
    return str_replace('.', '', $version);
}
@endphp

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
                            <h4 class="version-history-heading">Release History</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-releases">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>VERSION</th>
                                            <th>BRANCH</th>
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
                                                <span class="badge badge-primary">v{{ $release['version'] }}</span>
                                                @if($release['version'] === $currentVersion)
                                                    <span class="badge badge-success ml-2">Current</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">{{ $release['branch'] ?? 'main' }}</span>
                                            </td>
                                            <td>{{ date('M d, Y H:i', strtotime($release['released_at'])) }}</td>
                                            <td>{{ $release['author'] }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-link p-0" type="button" data-toggle="collapse" data-target="#releaseDesc{{ versionToId($release['version']) }}" aria-expanded="false">
                                                    View details
                                                </button>
                                                <div class="collapse mt-2" id="releaseDesc{{ versionToId($release['version']) }}">
                                                    <div class="card card-body bg-light release-description">
                                                        {!! nl2br(e($release['description'])) !!}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($release['version'] !== $currentVersion)
                                                    <form action="{{ route('tenant.updates.success.post', ['slug' => request()->route('slug')]) }}" method="POST" class="d-inline" target="_blank">
                                                        @csrf
                                                        <input type="hidden" name="version" value="{{ $release['version'] }}">
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ version_compare($release['version'], $currentVersion, '>') ? 'btn-primary' : 'btn-warning' }}"
                                                                onclick="return showUpdateConfirmation('{{ $release['version'] }}', {{ version_compare($release['version'], $currentVersion, '>') }})">
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
                
                <div id="updateProcessInfo" class="mt-3 d-none">
                    <h6>Update Process Information</h6>
                    <ol class="pl-3">
                        <li>Download and extract the update package</li>
                        <li>Create a backup of your current system</li>
                        <li>Overwrite files with the new version</li>
                        <li>Run database migrations for the central database</li>
                        <li>Run database migrations for all tenant databases</li>
                        <li>Clear application caches</li>
                        <li>Update the system version</li>
                    </ol>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This process may take several minutes. Please do not close your browser.
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> During the update process, the server may temporarily restart, causing a brief interruption. This is normal.
                    </div>
                    <div class="alert alert-primary">
                        <i class="fas fa-external-link-alt"></i> The update will open in a new tab. If the new tab shows "This site can't be reached", simply refresh the page or copy the URL into another browser tab.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="updateForm" action="{{ route('tenant.updates.success.post', ['slug' => request()->route('slug')]) }}" method="POST" class="d-none" target="_blank">
                    @csrf
                    <input type="hidden" name="version" id="updateVersion">
                    <button type="submit" class="btn btn-primary" id="installUpdateBtn">Install Update</button>
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
        $('#updateProcessInfo').addClass('d-none');
        $('#updateForm').addClass('d-none');
        $('#updateModal').modal('show');

        $.get('{{ route("tenant.updates.check", ["slug" => request()->route("slug")]) }}')
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
                `<form action="{{ route('tenant.updates.success.post', ['slug' => request()->route('slug')]) }}" method="POST" class="d-inline" target="_blank">
                    @csrf
                    <input type="hidden" name="version" value="${release.version}">
                    <button type="submit" 
                            class="btn btn-sm ${isUpgrade ? 'btn-primary' : 'btn-warning'}"
                            onclick="return showUpdateConfirmation('${release.version}', ${isUpgrade})">
                        ${isUpgrade ? 'Update' : 'Downgrade'}
                    </button>
                </form>`;

            const releaseDate = new Date(release.released_at);
            const formattedDate = releaseDate.toLocaleDateString('en-US', {
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            
            // Create a valid ID by removing dots from version
            const versionId = release.version.replace(/\./g, '');

            tbody.append(`
                <tr>
                    <td>
                        <span class="badge badge-primary">v${release.version}</span>
                        ${isCurrentVersion ? '<span class="badge badge-success ml-2">Current</span>' : ''}
                    </td>
                    <td>
                        <span class="badge badge-secondary">${release.branch || 'main'}</span>
                    </td>
                    <td>${formattedDate}</td>
                    <td>${release.author}</td>
                    <td>
                        <button class="btn btn-sm btn-link p-0" type="button" data-toggle="collapse" data-target="#releaseDesc${versionId}" aria-expanded="false">
                            View details
                        </button>
                        <div class="collapse mt-2" id="releaseDesc${versionId}">
                            <div class="card card-body bg-light release-description">
                                ${release.description.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    </td>
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
    
    function showUpdateConfirmation(version, isUpgrade) {
        $('#updateVersion').val(version);
        $('#updateForm').removeClass('d-none');
        $('#updateProcessInfo').removeClass('d-none');
        $('#updateModalContent').html(`
            <div class="alert alert-${isUpgrade ? 'info' : 'warning'}">
                <h5>Are you sure you want to ${isUpgrade ? 'update' : 'downgrade'} to version ${version}?</h5>
                <p>This will ${isUpgrade ? 'update' : 'downgrade'} your system and run database migrations for both central and tenant databases.</p>
                ${!isUpgrade ? '<p class="text-danger"><strong>WARNING:</strong> Downgrading may cause compatibility issues with your data!</p>' : ''}
            </div>
        `);
        $('#updateModal').modal('show');
        return false;
    }

    $('#checkUpdatesBtn').click(checkForUpdates);
    
    // Add click handlers for update buttons in the table
    $(document).on('click', '[data-update-version]', function(e) {
        e.preventDefault();
        const version = $(this).data('update-version');
        const isUpgrade = $(this).data('is-upgrade') === 'true';
        showUpdateConfirmation(version, isUpgrade);
    });
    
    // Add form submission handler to show loading state
    $('#updateForm').on('submit', function() {
        const $btn = $('#installUpdateBtn');
        $btn.html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Installing...');
        $btn.prop('disabled', true);
        $('#updateProcessInfo').find('button').prop('disabled', true);
        $('.modal-header .close').prop('disabled', true).css('opacity', '0.5');
        $('.modal-footer .btn-secondary').prop('disabled', true).css('opacity', '0.5');
        
        return true;
    });
});
</script>
@endpush

@push('styles')
<style>
    .table-releases th {
        background-color: #343a40;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    
    .table-releases .badge-primary {
        background-color: #4e73df;
        font-size: 0.85rem;
        padding: 0.35rem 0.5rem;
    }
    
    .table-releases .badge-secondary {
        background-color: #6c757d;
        font-size: 0.85rem;
    }
    
    .table-releases .badge-success {
        background-color: #1cc88a;
        font-size: 0.75rem;
    }
    
    .table-releases .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .table-releases .btn-warning {
        background-color: #f6c23e;
        border-color: #f6c23e;
        color: #212529;
    }
    
    .table-releases tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05);
    }
    
    .version-history-heading {
        font-weight: 700;
        color: #4e73df;
        border-bottom: 2px solid #e3e6f0;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .release-description {
        max-height: 300px;
        overflow-y: auto;
        white-space: pre-line;
        font-size: 0.9rem;
    }
</style>
@endpush 