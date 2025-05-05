@extends('layouts.TenantDashboardTemplate')

@section('content')
<div class="page-header mb-4">
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
            <div class="card-header bg-white">
                <h4 class="card-title">Customize Your Dashboard</h4>
            </div>
            <div class="card-body">
                <form id="uiSettingsForm" action="{{ route('tenant.ui-settings.update', ['slug' => session('tenant_slug')]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Logo Upload Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Header Logo</label>
                                <div class="custom-file">
                                    <input type="file" class="form-control @error('header_logo') is-invalid @enderror" 
                                           id="headerLogo" name="header_logo" accept="image/*">
                                    <small class="form-text text-muted">Max file size: 2MB</small>
                                    @error('header_logo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-2">
                                <img id="logoPreview" src="{{ $settings->header_logo ? asset('storage/' . $settings->header_logo) : asset('assets/img/clam_logo.jpg') }}" 
                                     alt="Current Logo" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <!-- Logo Header Color -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Logo Header Color</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="logoHeaderColorPicker" name="logo_header_color" value="{{ $settings->logo_header_color }}" title="Choose logo header color">
                                    <input type="text" class="form-control" id="logoHeaderColorHex" value="{{ $settings->logo_header_color }}">
                                </div>
                            </div>
                        </div>

                        <!-- Navbar Color -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Navbar Color</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="navbarColorPicker" name="navbar_color" value="{{ $settings->navbar_color }}" title="Choose navbar color">
                                    <input type="text" class="form-control" id="navbarColorHex" value="{{ $settings->navbar_color }}">
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Color -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sidebar Color</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="sidebarColorPicker" name="sidebar_color" value="{{ $settings->sidebar_color }}" title="Choose sidebar color">
                                    <input type="text" class="form-control" id="sidebarColorHex" value="{{ $settings->sidebar_color }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Font Customization -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Primary Font</label>
                                <select class="form-control" name="primary_font" id="primaryFont">
                                    <option value="Public Sans" {{ $settings->primary_font === 'Public Sans' ? 'selected' : '' }}>Public Sans</option>
                                    <option value="Roboto" {{ $settings->primary_font === 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                    <option value="Open Sans" {{ $settings->primary_font === 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                    <option value="Lato" {{ $settings->primary_font === 'Lato' ? 'selected' : '' }}>Lato</option>
                                    <option value="Poppins" {{ $settings->primary_font === 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    <option value="Montserrat" {{ $settings->primary_font === 'Montserrat' ? 'selected' : '' }}>Montserrat</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Font Size Scale</label>
                                <select class="form-control" name="font_size_scale" id="fontSizeScale">
                                    <option value="0.9" {{ $settings->font_size_scale === '0.9' ? 'selected' : '' }}>Small</option>
                                    <option value="1.0" {{ $settings->font_size_scale === '1.0' ? 'selected' : '' }}>Medium (Default)</option>
                                    <option value="1.1" {{ $settings->font_size_scale === '1.1' ? 'selected' : '' }}>Large</option>
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
                        <!-- <button type="button" class="btn btn-danger" id="resetDefaults">
                            <i class="fa fa-undo"></i> Reset to Defaults
                        </button> -->
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Add Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&family=Lato:wght@300;400;700&family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<script>
$(document).ready(function() {
    // Logo preview functionality
    $('#headerLogo').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Color picker synchronization
    function syncColorPicker(pickerId, hexId) {
        $(pickerId).on('input', function() {
            $(hexId).val($(this).val());
            applyChanges();
            $(document).trigger('colorChanged'); // Trigger color change event
        });
        $(hexId).on('input', function() {
            $(pickerId).val($(this).val());
            applyChanges();
            $(document).trigger('colorChanged'); // Trigger color change event
        });
    }

    syncColorPicker('#logoHeaderColorPicker', '#logoHeaderColorHex');
    syncColorPicker('#navbarColorPicker', '#navbarColorHex');
    syncColorPicker('#sidebarColorPicker', '#sidebarColorHex');

    // Font preview functionality
    $('#primaryFont').on('change', function() {
        const selectedFont = $(this).val();
        
        // Update CSS variable
        document.documentElement.style.setProperty('--primary-font', `'${selectedFont}', sans-serif`);
        
        // Apply to all elements
        document.body.style.setProperty('font-family', `'${selectedFont}', sans-serif`, 'important');
        
        const elements = document.querySelectorAll('*');
        elements.forEach(element => {
            element.style.setProperty('font-family', `'${selectedFont}', sans-serif`, 'important');
        });
        
        // Force reflow
        void document.documentElement.offsetHeight;
        
        // Save to session storage
        sessionStorage.setItem('selectedFont', selectedFont);
    });

    // Font size scale functionality
    $('#fontSizeScale').on('change', function() {
        const scale = parseFloat($(this).val());
        $('html').css('font-size', `${scale * 100}%`);
    });

    // Reset to defaults
    $('#resetDefaults').click(function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to reset all UI settings to defaults?')) {
            // Add default values based on DashboardTemplate.blade.php
            const defaults = {
                logo_header_color: '#1a2035', // dark
                navbar_color: '#ffffff', // white
                sidebar_color: '#1a2035', // dark
                primary_font: 'Public Sans',
                font_size_scale: '1.0',
                navbar_position: 'top',
                sidebar_position: 'left',
                is_sidebar_collapsed: false,
                is_navbar_fixed: true,
                is_sidebar_fixed: true
            };

            // Apply defaults to form
            $('#logoHeaderColorPicker, #logoHeaderColorHex').val(defaults.logo_header_color);
            $('#navbarColorPicker, #navbarColorHex').val(defaults.navbar_color);
            $('#sidebarColorPicker, #sidebarColorHex').val(defaults.sidebar_color);
            $('#primaryFont').val(defaults.primary_font);
            $('#fontSizeScale').val(defaults.font_size_scale);
            $('#navbarPosition').val(defaults.navbar_position);
            $('#sidebarPosition').val(defaults.sidebar_position);
            $('#sidebarCollapsed').prop('checked', defaults.is_sidebar_collapsed);
            $('#navbarFixed').prop('checked', defaults.is_navbar_fixed);
            $('#sidebarFixed').prop('checked', defaults.is_sidebar_fixed);

            // Trigger change events
            applyChanges();
        }
    });

    // Original applyChanges function with modifications
    function applyChanges() {
        const logoHeader = $('.logo-header');
        const navbar = $('.navbar-header');
        const mainHeader = $('.main-header');
        const sidebar = $('.sidebar');
        const wrapper = $('.wrapper');
        const mainPanel = $('.main-panel');
        const pageInner = $('.page-inner');
        
        // Get current values
        const logoColor = $('#logoHeaderColorPicker').val();
        const navbarColor = $('#navbarColorPicker').val();
        const sidebarColor = $('#sidebarColorPicker').val();
        const navbarPosition = $('#navbarPosition').val();
        const sidebarPosition = $('#sidebarPosition').val();
        const isSidebarCollapsed = $('#sidebarCollapsed').is(':checked');
        const isNavbarFixed = $('#navbarFixed').is(':checked');
        const isSidebarFixed = $('#sidebarFixed').is(':checked');
        const primaryFont = $('#primaryFont').val();
        const fontScale = $('#fontSizeScale').val();
        
        // Apply colors with both inline style and data attribute
        logoHeader.css('background-color', logoColor)
                 .attr('data-background-color', logoColor);
        
        navbar.css('background-color', navbarColor)
              .attr('data-background-color', navbarColor);
        
        sidebar.css('background-color', sidebarColor)
               .attr('data-background-color', sidebarColor);

        // Function to determine if a color is light
        function isLightColor(color) {
            const hex = color.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            const brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000;
            return brightness > 155;
        }

        // Apply dynamic text colors
        if (isLightColor(logoColor)) {
            logoHeader.addClass('text-dynamic-dark').removeClass('text-dynamic-light');
        } else {
            logoHeader.addClass('text-dynamic-light').removeClass('text-dynamic-dark');
        }

        if (isLightColor(navbarColor)) {
            navbar.addClass('text-dynamic-dark').removeClass('text-dynamic-light');
        } else {
            navbar.addClass('text-dynamic-light').removeClass('text-dynamic-dark');
        }

        if (isLightColor(sidebarColor)) {
            sidebar.addClass('text-dynamic-dark').removeClass('text-dynamic-light');
        } else {
            sidebar.addClass('text-dynamic-light').removeClass('text-dynamic-dark');
        }

        // Apply font
        const elements = document.querySelectorAll('body, .sidebar, .navbar, .logo-header, .main-panel, h1, h2, h3, h4, h5, h6, .btn, input, select, textarea, .nav-item, .card, .card-title, .card-body, .page-title, .breadcrumbs, .profile-username, .dropdown-menu, .notification, .nav-search, .form-control');
        elements.forEach(element => {
            element.style.fontFamily = `'${primaryFont}', sans-serif`;
        });
        
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

    // Form submission with file upload
    $('#uiSettingsForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Update logo preview with new image
                    if (response.logo_url) {
                        $('#logoPreview').attr('src', response.logo_url);
                    }
                    
                    // Show success message
                    const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>');
                    $('#uiSettingsForm').before(alert);
                    
                    // Reload page after 1 second
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'Failed to update UI settings. Please try again.';
                
                if (errors && errors.header_logo) {
                    errorMessage = errors.header_logo[0];
                }
                
                const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    errorMessage +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>');
                $('#uiSettingsForm').before(alert);
            }
        });
    });

    // Add change event listeners for all form controls
    $('#uiSettingsForm select, #uiSettingsForm input[type="checkbox"]').on('change', function() {
        applyChanges();
        $(document).trigger('colorChanged'); // Trigger color change event
    });
    
    // Add input event listeners for color pickers
    $('#logoHeaderColorPicker, #navbarColorPicker, #sidebarColorPicker').on('input', function() {
        applyChanges();
        $(document).trigger('colorChanged'); // Trigger color change event
    });
    
    // Apply initial settings
    applyChanges();
});
</script>
@endpush
@endsection 