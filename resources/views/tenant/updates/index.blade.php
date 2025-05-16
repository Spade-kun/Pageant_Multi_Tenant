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
                                                <span class="badge bg-success">Update Available</span>
                                            @else
                                                <span class="badge bg-info">Up to date</span>
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
                    </div>
                </div>
            </div>
        </div>

        @if(isset($releases) && count($releases) > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Release History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="releasesTable" class="display table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>VERSION</th>
                                        <th>BRANCH</th>
                                        <th>RELEASED AT</th>
                                        <th>AUTHOR</th>
                                        <th>DESCRIPTION</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($releases as $release)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">v{{ $release['version'] }}</span>
                                            @if($release['version'] === $currentVersion)
                                            <span class="badge bg-success ms-2">Current</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $release['branch'] ?? 'main' }}</span>
                                        </td>
                                        <td>{{ date('M d, Y H:i', strtotime($release['released_at'])) }}</td>
                                        <td>{{ $release['author'] }}</td>
                                        <td>
                                            <div class="release-description">
                                                {!! nl2br(e($release['description'])) !!}
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
                </div>
            </div>
        </div>
        @endif
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

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}">
<style>
    /* DataTables Styling */
    .dataTables_wrapper .dataTables_length select {
        width: 75px;
        display: inline-block;
    }

    .dataTables_wrapper .dataTables_filter {
        float: right;
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_filter input {
        width: 300px;
        margin-left: 0.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding-top: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 1rem;
        margin-left: 0.25rem;
        border-radius: 0.25rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0d6efd !important;
        color: white !important;
        border: 1px solid #0d6efd !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #0b5ed7 !important;
        color: white !important;
        border: 1px solid #0b5ed7 !important;
    }

    /* Table Styling */
    .table > :not(caption) > * > * {
        padding: 1rem;
    }

    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    .badge {
        font-size: 0.85rem;
        padding: 0.35rem 0.5rem;
    }

    .release-description {
        max-height: 300px;
        overflow-y: auto;
        white-space: pre-line;
        font-size: 0.9rem;
    }

    /* Responsive adjustments */
    @media screen and (max-width: 767px) {
        .dataTables_wrapper .dataTables_filter input {
            width: 100%;
            margin-left: 0;
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            float: none;
            text-align: left;
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<!-- jQuery -->
<script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
<!-- DataTables JS -->
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#releasesTable').DataTable({
        responsive: true,
        order: [[0, 'desc']], // Sort by version column by default
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columnDefs: [
            {
                targets: 0, // Version column
                render: function(data, type, row) {
                    if (type === 'sort') {
                        // Extract version number for sorting
                        const match = data.match(/v?(\d+\.\d+\.\d+)/);
                        return match ? match[1] : data;
                    }
                    return data;
                }
            },
            {
                targets: 4, // Description column
                orderable: false
            },
            {
                targets: 5, // Action column
                orderable: false
            }
        ],
        language: {
            search: "Search versions:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries to show",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });

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