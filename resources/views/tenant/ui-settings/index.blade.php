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
        const mainHeader = $('.main-header');
        const sidebar = $('.sidebar');
        const wrapper = $('.wrapper');
        const mainPanel = $('.main-panel');
        const pageInner = $('.page-inner');
        
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
        
        // Apply navbar position - completely remove and reapply classes
        wrapper.removeClass('navbar-bottom navbar-top');
        navbar.removeClass('navbar-bottom navbar-top');
        mainHeader.removeClass('navbar-bottom navbar-top');
        
        // Force reflow to ensure CSS changes take effect
        void wrapper[0].offsetWidth;
        void navbar[0].offsetWidth;
        void mainHeader[0].offsetWidth;
        
        // Apply sidebar position first
        wrapper.removeClass('sidebar-right-layout');
        if (sidebarPosition === 'right') {
            wrapper.addClass('sidebar-right-layout');
            sidebar.addClass('sidebar-right')
                  .css({
                      'right': '0',
                      'left': 'auto',
                      'transform': 'none'
                  });
            mainPanel.css({
                'float': 'left',
                'margin-right': '250px',
                'margin-left': '0'
            });
        } else {
            sidebar.removeClass('sidebar-right')
                  .css({
                      'right': '',
                      'left': '0',
                      'transform': ''
                  });
            mainPanel.css({
                'float': '',
                'margin-right': '',
                'margin-left': ''
            });
        }
        
        // Apply fixed states and collapse
        wrapper.toggleClass('navbar-fixed', isNavbarFixed);
        wrapper.toggleClass('sidebar-fixed', isSidebarFixed);
        wrapper.toggleClass('sidebar-collapse', isSidebarCollapsed);
        
        // Now apply navbar positioning
        if (navbarPosition === 'bottom') {
            wrapper.addClass('navbar-bottom');
            navbar.addClass('navbar-bottom');
            mainHeader.addClass('navbar-bottom');
            
            // Adjust navbar width and position based on sidebar
            adjustNavbarForSidebar(navbar, mainHeader, sidebarPosition, isSidebarCollapsed);
        } else {
            wrapper.addClass('navbar-top');
            navbar.addClass('navbar-top');
            mainHeader.addClass('navbar-top');
            
            // Adjust navbar width and position based on sidebar
            adjustNavbarForSidebar(navbar, mainHeader, sidebarPosition, isSidebarCollapsed);
        }

        // Force refresh styles
        setTimeout(() => {
            navbar.addClass('force-refresh').removeClass('force-refresh');
            mainHeader.addClass('force-refresh').removeClass('force-refresh');
            sidebar.addClass('force-refresh').removeClass('force-refresh');
            mainPanel.addClass('force-refresh').removeClass('force-refresh');
            pageInner.addClass('force-refresh').removeClass('force-refresh');
        }, 100);
    }
    
    // Helper function to adjust navbar for sidebar
    function adjustNavbarForSidebar(navbar, mainHeader, sidebarPosition, isSidebarCollapsed) {
        const sidebarWidth = isSidebarCollapsed ? '76px' : '251px'; // 1px extra to avoid overlap
        
        if (sidebarPosition === 'right') {
            navbar.css({
                'width': 'calc(100% - ' + sidebarWidth + ')',
                'right': sidebarWidth,
                'left': '0',
                'z-index': '1029'
            });
            mainHeader.css({
                'width': 'calc(100% - ' + sidebarWidth + ')',
                'right': sidebarWidth,
                'left': '0',
                'z-index': '1029'
            });
        } else {
            navbar.css({
                'width': 'calc(100% - ' + sidebarWidth + ')',
                'left': sidebarWidth,
                'right': '0',
                'z-index': '1029'
            });
            mainHeader.css({
                'width': 'calc(100% - ' + sidebarWidth + ')',
                'left': sidebarWidth,
                'right': '0',
                'z-index': '1029'
            });
        }
        
        // Handle responsive behavior
        if ($(window).width() < 992) {
            navbar.css({
                'width': '100%',
                'left': '0',
                'right': '0',
                'z-index': '1029'
            });
            mainHeader.css({
                'width': '100%',
                'left': '0',
                'right': '0',
                'z-index': '1029'
            });
        }
    }

    // Apply changes when any form control changes
    $('#uiSettingsForm select, #uiSettingsForm input[type="checkbox"]').on('change', function() {
        applyChanges();
    });

    // Responsive adjustments
    $(window).on('resize', function() {
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