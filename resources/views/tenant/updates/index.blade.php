@extends('layouts.TenantDashboardTemplate')

@section('title', 'System Updates')

@section('styles')
<style>
    .sortable {
        cursor: pointer;
        position: relative;
    }
    
    .sortable i {
        margin-left: 5px;
        font-size: 14px;
    }
    
    table.table th.sortable:hover {
        background-color: #f4f4f4;
    }
    
    #releasesTable .badge {
        font-size: 80%;
    }
    
    #releasesTable .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.76563rem;
    }
</style>
@endsection

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
                                            @php
                                                // Find the highest version from releases
                                                $latestVersion = $currentVersion;
                                                if (isset($releases) && !empty($releases)) {
                                                    foreach ($releases as $release) {
                                                        if (version_compare($release['version'], $latestVersion, '>')) {
                                                            $latestVersion = $release['version'];
                                                        }
                                                    }
                                                }
                                            @endphp
                                            {{ $latestVersion }}
                                            @if(version_compare($latestVersion, $currentVersion, '>'))
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
                                <table class="table table-striped" id="releasesTable">
                                    <thead>
                                        <tr>
                                            <th class="sortable" data-sort="version">VERSION <i class="fas fa-sort"></i></th>
                                            <th class="sortable" data-sort="date">RELEASED AT <i class="fas fa-sort"></i></th>
                                            <th>AUTHOR</th>
                                            <th>DESCRIPTION</th>
                                            <th>ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody id="releaseHistory">
                                        @foreach($releases as $release)
                                        <tr data-version="{{ $release['version'] }}" data-date="{{ strtotime($release['released_at']) }}">
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
                                                    <form action="{{ route('tenant.updates.update', ['slug' => request()->route('slug')]) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="version" value="{{ $release['version'] }}">
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ version_compare($release['version'], $currentVersion, '>') ? 'btn-primary' : 'btn-warning' }}"
                                                                onclick="return confirm('Are you sure you want to {{ version_compare($release['version'], $currentVersion, '>') ? 'update to' : 'downgrade to' }} version {{ $release['version'] }}? {{ version_compare($release['version'], $currentVersion, '<') ? 'Downgrading may cause compatibility issues.' : '' }}')">
                                                            {{ version_compare($release['version'], $currentVersion, '>') ? 'Update' : 'Downgrade' }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="badge badge-success">Current Version</span>
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
                <h5 class="modal-title" id="updateModalLabel">Checking for Updates</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="updateSpinner" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Checking for updates. Please wait...</p>
                </div>
                <div id="updateModalContent" class="d-none"></div>
                <form id="updateForm" class="d-none" method="POST" action="{{ route('tenant.updates.update', ['slug' => request()->route('slug')]) }}">
                    @csrf
                    <input type="hidden" name="version" id="updateVersion">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
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
                                <div class="d-flex justify-content-center my-3">
                                    <div class="version-change d-flex align-items-center">
                                        <span class="badge badge-secondary p-2 mr-2">v${response.currentVersion}</span>
                                        <i class="fas fa-arrow-right mx-3"></i>
                                        <span class="badge badge-success p-2 ml-2">v${latestVersion}</span>
                                    </div>
                                </div>
                                <p>Clicking "Update Now" will start the update process. You will see a progress screen during the update. Do not close your browser during this process.</p>
                                <button class="btn btn-primary" id="doUpdateBtn" data-version="${latestVersion}">
                                    <i class="fas fa-download mr-1"></i> Update Now
                                </button>
                            </div>
                        `);
                        
                        // Refresh the page to show the updated releases
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
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

        // Sort releases by version (newest first)
        releases.sort(function(a, b) {
            return compareVersions(b.version, a.version);
        });

        releases.forEach(release => {
            const isCurrentVersion = release.version === currentVersion;
            const isUpgrade = compareVersions(release.version, currentVersion) > 0;
            
            const actionButton = isCurrentVersion ? 
                `<span class="badge badge-success">Current Version</span>` :
                `<form action="{{ route('tenant.updates.update', ['slug' => request()->route('slug')]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="version" value="${release.version}">
                    <button type="submit" 
                            class="btn btn-sm ${isUpgrade ? 'btn-primary' : 'btn-warning'}"
                            onclick="return confirm('Are you sure you want to ${isUpgrade ? 'update to' : 'downgrade to'} version ${release.version}? ${!isUpgrade ? 'Downgrading may cause compatibility issues.' : ''}')">
                        ${isUpgrade ? 'Update' : 'Downgrade'}
                    </button>
                </form>`;

            tbody.append(`
                <tr data-version="${release.version}" data-date="${new Date(release.released_at).getTime()}">
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

    function sortTable(column, direction) {
        const tbody = $('#releaseHistory');
        const rows = tbody.find('tr').get();
        
        rows.sort(function(a, b) {
            let aValue, bValue;
            
            if (column === 'version') {
                aValue = $(a).data('version');
                bValue = $(b).data('version');
                return direction === 'asc' ? 
                    compareVersions(aValue, bValue) : 
                    compareVersions(bValue, aValue);
            } else if (column === 'date') {
                aValue = $(a).data('date');
                bValue = $(b).data('date');
                return direction === 'asc' ? aValue - bValue : bValue - aValue;
            }
            
            return 0;
        });
        
        $.each(rows, function(index, row) {
            tbody.append(row);
        });
        
        // Update sort indicators
        $('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        $(`.sortable[data-sort="${column}"] i`)
            .removeClass('fa-sort')
            .addClass(direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
    }

    // Check for updates button
    $('#checkUpdatesBtn').click(function() {
        checkForUpdates();
    });
    
    // Handle update button in modal
    $(document).on('click', '#doUpdateBtn', function() {
        const version = $(this).data('version');
        const currentVersion = '{{ $currentVersion }}';
        const isUpgrade = compareVersions(version, currentVersion) > 0;
        
        // Show confirmation dialog before proceeding
        if (confirm('Are you sure you want to ' + 
                (isUpgrade ? 'update to' : 'downgrade to') + 
                ' version ' + version + '? ' + 
                (!isUpgrade ? 'Downgrading may cause compatibility issues.' : ''))) {
            $('#updateVersion').val(version);
            $('#updateForm').removeClass('d-none').submit();
        }
    });
    
    // Sortable columns
    $('.sortable').click(function() {
        const column = $(this).data('sort');
        const currentDir = $(this).find('i').hasClass('fa-sort-up') ? 'asc' : 
                         ($(this).find('i').hasClass('fa-sort-down') ? 'desc' : 'none');
        const newDir = currentDir === 'asc' ? 'desc' : 'asc';
        
        sortTable(column, newDir);
    });
});
</script>
@endpush 