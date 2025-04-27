@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-header">
    <h4 class="page-title">UI Customization</h4>
</div>

<div class="row">
    <div class="col-md-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Customize Your Dashboard</h4>
            </div>
            <div class="card-body">
                <form id="uiSettingsForm" action="{{ route('tenant.ui-settings.update', ['slug' => session('tenant_slug')]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-4">
                        <!-- Logo Header Color -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Logo Header Color</label>
                                <select class="form-control" name="logo_header_color" id="logoHeaderColor">
                                    <option value="dark" {{ $settings->logo_header_color === 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="blue" {{ $settings->logo_header_color === 'blue' ? 'selected' : '' }}>Blue</option>
                                    <option value="purple" {{ $settings->logo_header_color === 'purple' ? 'selected' : '' }}>Purple</option>
                                    <option value="light-blue" {{ $settings->logo_header_color === 'light-blue' ? 'selected' : '' }}>Light Blue</option>
                                    <option value="green" {{ $settings->logo_header_color === 'green' ? 'selected' : '' }}>Green</option>
                                    <option value="orange" {{ $settings->logo_header_color === 'orange' ? 'selected' : '' }}>Orange</option>
                                    <option value="red" {{ $settings->logo_header_color === 'red' ? 'selected' : '' }}>Red</option>
                                    <option value="white" {{ $settings->logo_header_color === 'white' ? 'selected' : '' }}>White</option>
                                </select>
                            </div>
                        </div>

                        <!-- Navbar Color -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Navbar Color</label>
                                <select class="form-control" name="navbar_color" id="navbarColor">
                                    <option value="dark" {{ $settings->navbar_color === 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="blue" {{ $settings->navbar_color === 'blue' ? 'selected' : '' }}>Blue</option>
                                    <option value="purple" {{ $settings->navbar_color === 'purple' ? 'selected' : '' }}>Purple</option>
                                    <option value="light-blue" {{ $settings->navbar_color === 'light-blue' ? 'selected' : '' }}>Light Blue</option>
                                    <option value="green" {{ $settings->navbar_color === 'green' ? 'selected' : '' }}>Green</option>
                                    <option value="orange" {{ $settings->navbar_color === 'orange' ? 'selected' : '' }}>Orange</option>
                                    <option value="red" {{ $settings->navbar_color === 'red' ? 'selected' : '' }}>Red</option>
                                    <option value="white" {{ $settings->navbar_color === 'white' ? 'selected' : '' }}>White</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sidebar Color -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sidebar Color</label>
                                <select class="form-control" name="sidebar_color" id="sidebarColor">
                                    <option value="dark" {{ $settings->sidebar_color === 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="blue" {{ $settings->sidebar_color === 'blue' ? 'selected' : '' }}>Blue</option>
                                    <option value="white" {{ $settings->sidebar_color === 'white' ? 'selected' : '' }}>White</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Navbar Position -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Navbar Position</label>
                                <select class="form-control" name="navbar_position" id="navbarPosition">
                                    <option value="top" {{ $settings->navbar_position === 'top' ? 'selected' : '' }}>Top</option>
                                    <option value="bottom" {{ $settings->navbar_position === 'bottom' ? 'selected' : '' }}>Bottom</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sidebar Position -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sidebar Position</label>
                                <select class="form-control" name="sidebar_position" id="sidebarPosition">
                                    <option value="left" {{ $settings->sidebar_position === 'left' ? 'selected' : '' }}>Left</option>
                                    <option value="right" {{ $settings->sidebar_position === 'right' ? 'selected' : '' }}>Right</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="sidebarCollapsed" name="is_sidebar_collapsed" {{ $settings->is_sidebar_collapsed ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sidebarCollapsed">Collapse Sidebar by Default</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="navbarFixed" name="is_navbar_fixed" {{ $settings->is_navbar_fixed ? 'checked' : '' }}>
                                <label class="custom-control-label" for="navbarFixed">Fixed Navbar</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="sidebarFixed" name="is_sidebar_fixed" {{ $settings->is_sidebar_fixed ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sidebarFixed">Fixed Sidebar</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-action">
                        <button type="submit" class="btn btn-primary" id="saveChanges">
                            <i class="fa fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Function to apply changes in real-time
    function applyChanges() {
        const logoHeader = $('.logo-header');
        const navbar = $('.navbar-header');
        const sidebar = $('.sidebar');
        const wrapper = $('.wrapper');
        const mainPanel = $('.main-panel');
        const pageInner = $('.page-inner');
        const footer = $('.footer');
        
        // Get current values
        const logoColor = $('#logoHeaderColor').val();
        const navbarColor = $('#navbarColor').val();
        const sidebarColor = $('#sidebarColor').val();
        const navbarPosition = $('#navbarPosition').val();
        const sidebarPosition = $('#sidebarPosition').val();
        const isSidebarCollapsed = $('#sidebarCollapsed').is(':checked');
        const isNavbarFixed = $('#navbarFixed').is(':checked');
        const isSidebarFixed = $('#sidebarFixed').is(':checked');
        
        // Apply colors
        logoHeader.attr('data-background-color', logoColor);
        navbar.attr('data-background-color', navbarColor);
        sidebar.attr('data-background-color', sidebarColor);
        
        // Apply navbar position
        navbar.removeClass('navbar-bottom');
        pageInner.css({
            'padding-bottom': '',
            'padding-top': ''
        });
        footer.css('bottom', '');
        
        if (navbarPosition === 'bottom') {
            navbar.addClass('navbar-bottom');
            pageInner.css({
                'padding-bottom': '85px',
                'padding-top': '20px'
            });
            footer.css('bottom', '62px');
        }
        
        // Apply sidebar position
        if (sidebarPosition === 'right') {
            sidebar.addClass('sidebar-right')
                  .css({
                      'right': '0 !important',
                      'left': 'auto !important',
                      'transform': 'none !important'
                  });
        } else {
            sidebar.removeClass('sidebar-right')
                  .css({
                      'right': '',
                      'left': '0',
                      'transform': 'none'
                  });
        }
        
        // Apply fixed states and collapse
        wrapper.toggleClass('navbar-fixed', isNavbarFixed);
        wrapper.toggleClass('sidebar-fixed', isSidebarFixed);
        wrapper.toggleClass('sidebar-collapse', isSidebarCollapsed);

        // Force refresh styles
        setTimeout(() => {
            navbar.addClass('force-refresh').removeClass('force-refresh');
            sidebar.addClass('force-refresh').removeClass('force-refresh');
            mainPanel.addClass('force-refresh').removeClass('force-refresh');
            pageInner.addClass('force-refresh').removeClass('force-refresh');
            footer.addClass('force-refresh').removeClass('force-refresh');
        }, 100);
    }

    // Apply changes when any form control changes
    $('#uiSettingsForm select, #uiSettingsForm input[type="checkbox"]').on('change', function() {
        applyChanges();
    });

    // Handle form submission
    $('#uiSettingsForm').on('submit', function(e) {
        const form = $(this);
        const submitButton = form.find('button[type="submit"]');
        
        // Disable submit button to prevent double submission
        submitButton.prop('disabled', true);
        
        // Try AJAX submission first
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        'UI settings updated successfully' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>');
                    form.before(alert);
                    
                    // Apply changes immediately
                    applyChanges();
                    
                    // Reload the page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                // Show error message
                const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    'Failed to update UI settings. Please try again.' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>');
                form.before(alert);
                
                // Re-enable submit button
                submitButton.prop('disabled', false);
            }
        });
        
        // Prevent default form submission
        return false;
    });

    // Apply initial settings on page load
    applyChanges();
});
</script>
@endpush
@endsection 