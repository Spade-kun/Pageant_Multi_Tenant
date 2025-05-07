<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Pageant Management System</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="{{ asset('assets/img/kaiadmin/favicon.ico') }}"
      type="image/x-icon"
    />

    @php
        $tenant = App\Models\Tenant::where('slug', session('tenant_slug'))->first();
        $uiSettings = App\Models\UiSettings::where('tenant_id', $tenant->id)->first();
        if (!$uiSettings) {
            $uiSettings = new App\Models\UiSettings([
                'logo_header_color' => 'dark',
                'navbar_color' => 'white',
                'sidebar_color' => 'dark',
                'navbar_position' => 'top',
                'sidebar_position' => 'left',
                'is_sidebar_collapsed' => false,
                'is_navbar_fixed' => true,
                'is_sidebar_fixed' => true,
                'primary_font' => 'Public Sans',
                'font_size_scale' => '1.0'
            ]);
        }
        $selectedFont = $uiSettings->primary_font ?? 'Public Sans';
        $fontScale = $uiSettings->font_size_scale ?? '1.0';
    @endphp

    <!-- Load all possible Google Fonts upfront -->
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;500;600;700&family=Lato:wght@300;400;700&family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Fonts and icons -->
    <script src="{{ asset('assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
      // Function to apply font to all elements except icons
      function applyFont(fontFamily) {
        document.documentElement.style.setProperty('--primary-font', `'${fontFamily}', sans-serif`);
        
        // Apply to body
        document.body.style.setProperty('font-family', `'${fontFamily}', sans-serif`, 'important');
        
        // Apply to all elements except icons
        const elements = document.querySelectorAll('*:not(.fa):not(.fa-solid):not(.fa-regular):not(.fa-brands):not(.fas):not(.far):not(.fab):not(.icon-*)');
        elements.forEach(element => {
          if (!element.classList.contains('fa') && 
              !element.classList.contains('fas') && 
              !element.classList.contains('far') && 
              !element.classList.contains('fab') &&
              !element.className.includes('icon-')) {
            element.style.setProperty('font-family', `'${fontFamily}', sans-serif`, 'important');
          }
        });
      }

      // Load fonts using WebFont
      WebFont.load({
        google: { 
          families: [
            'Public Sans:300,400,500,600,700',
            'Roboto:300,400,500,600,700',
            'Open Sans:300,400,500,600,700',
            'Lato:300,400,700',
            'Poppins:300,400,500,600,700',
            'Montserrat:300,400,500,600,700'
          ]
        },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["{{ asset('assets/css/fonts.min.css') }}"],
        },
        active: function () {
          sessionStorage.fonts = true;
          // Apply the selected font
          applyFont('{{ $selectedFont }}');
        },
      });

      // Apply font immediately and after page load
      document.addEventListener('DOMContentLoaded', function() {
        applyFont('{{ $selectedFont }}');
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Custom CSS for navbar positioning -->
    <style>
      :root {
        --primary-font: '{{ $selectedFont }}', sans-serif;
      }

      /* Apply primary font to everything except icons */
      *:not(.fa):not(.fa-solid):not(.fa-regular):not(.fa-brands):not(.fas):not(.far):not(.fab):not(.icon-*) {
        font-family: var(--primary-font) !important;
      }

      /* Preserve icon fonts */
      .fa,
      .fas,
      .far,
      .fab,
      .fa-solid,
      .fa-regular,
      .fa-brands,
      [class^="icon-"] {
        font-family: inherit !important;
      }

      /* Specific element overrides */
      body, 
      .sidebar,
      .navbar,
      .logo-header,
      .main-panel,
      h1, h2, h3, h4, h5, h6,
      .btn,
      input,
      select,
      textarea,
      .nav-item > a > p,
      .card,
      .card-title,
      .card-body,
      .page-title,
      .breadcrumbs,
      .profile-username,
      .dropdown-menu,
      .notification,
      .nav-search,
      .form-control {
        font-family: var(--primary-font) !important;
      }

      /* Ensure icons use their proper font families */
      .fa, .fas {
        font-family: 'Font Awesome 5 Free' !important;
        font-weight: 900;
      }
      
      .far {
        font-family: 'Font Awesome 5 Free' !important;
        font-weight: 400;
      }
      
      .fab {
        font-family: 'Font Awesome 5 Brands' !important;
        font-weight: 400;
      }

      [class^="icon-"] {
        font-family: 'simple-line-icons' !important;
      }

      /* Main wrapper styles */
      .wrapper.navbar-bottom .main-panel {
        padding-bottom: 70px !important;
        padding-top: 0 !important;
      }

      .wrapper.navbar-top .main-panel {
        padding-top: 70px !important;
        padding-bottom: 0 !important;
      }

      /* Text contrast helpers */
      [data-background-color="dark"] *,
      [data-background-color="dark2"] *,
      [data-background-color="blue"] *,
      [data-background-color="purple"] *,
      [data-background-color="green"] *,
      [data-background-color="red"] *,
      [data-background-color="orange"] * {
        color: #ffffff !important;
      }

      [data-background-color="white"] *,
      [data-background-color="light-blue"] * {
        color: #1a2035 !important;
      }

      /* Override for specific elements that need different colors */
      .notification {
        color: #ffffff !important;
      }

      .nav-search .form-control {
        color: inherit !important;
      }

      .dropdown-menu {
        color: #1a2035 !important;
      }

      .dropdown-menu * {
        color: #1a2035 !important;
      }

      /* Improve sidebar navigation contrast */
      .sidebar[data-background-color="dark"] .nav .nav-item a,
      .sidebar[data-background-color="dark2"] .nav .nav-item a {
        color: #ffffff !important;
      }

      .sidebar[data-background-color="white"] .nav .nav-item a {
        color: #1a2035 !important;
      }

      /* Improve active state visibility */
      .nav-item.active > a {
        background: rgba(255, 255, 255, 0.15) !important;
      }

      .sidebar[data-background-color="white"] .nav-item.active > a {
        background: rgba(0, 0, 0, 0.05) !important;
      }

      /* Navbar text contrast */
      .navbar[data-background-color="dark"] *,
      .navbar[data-background-color="dark2"] *,
      .navbar[data-background-color="blue"] *,
      .navbar[data-background-color="purple"] *,
      .navbar[data-background-color="green"] *,
      .navbar[data-background-color="red"] *,
      .navbar[data-background-color="orange"] * {
        color: #ffffff !important;
      }

      .navbar[data-background-color="white"] *,
      .navbar[data-background-color="light-blue"] * {
        color: #1a2035 !important;
      }

      /* Logo header text contrast */
      .logo-header[data-background-color="dark"] *,
      .logo-header[data-background-color="dark2"] *,
      .logo-header[data-background-color="blue"] *,
      .logo-header[data-background-color="purple"] *,
      .logo-header[data-background-color="green"] *,
      .logo-header[data-background-color="red"] *,
      .logo-header[data-background-color="orange"] * {
        color: #ffffff !important;
      }

      .logo-header[data-background-color="white"] *,
      .logo-header[data-background-color="light-blue"] * {
        color: #1a2035 !important;
      }

      /* Improve button contrast */
      .btn-toggle {
        background: rgba(255, 255, 255, 0.15) !important;
        color: inherit !important;
      }

      /* Improve dropdown contrast */
      .dropdown-menu {
        background: #ffffff !important;
      }

      .dropdown-menu a:hover {
        background: rgba(0, 0, 0, 0.05) !important;
      }

      /* Improve search contrast */
      .nav-search .form-control {
        background: rgba(255, 255, 255, 0.1) !important;
      }

      .nav-search .form-control::placeholder {
        color: inherit !important;
        opacity: 0.7;
      }

      /* Improve notification badge contrast */
      .notification {
        background: #f25961 !important;
        color: #ffffff !important;
      }

      /* Navbar positioning */
      .navbar-header.navbar-bottom, 
      .main-header.navbar-bottom {
        position: fixed !important;
        bottom: 0 !important;
        top: auto !important;
        border-top: 1px solid rgba(0,0,0,0.1) !important;
        box-shadow: 0 -1px 10px rgba(0,0,0,0.1) !important;
      }

      .navbar-header.navbar-top,
      .main-header.navbar-top {
        position: fixed !important;
        top: 0 !important;
        bottom: auto !important;
        border-bottom: 1px solid rgba(0,0,0,0.1) !important;
        box-shadow: 0 1px 10px rgba(0,0,0,0.1) !important;
      }

      /* Make sure the structure is correct */
      .main-header {
        position: relative;
      }

      /* Fix sidebar-right positioning */
      .sidebar.sidebar-right {
        right: 0 !important;
        left: auto !important;
        transform: none !important;
      }

      /* Fix sidebar-right layout for main panel */
      .wrapper.sidebar-right-layout .main-panel {
        float: left !important;
        margin-right: 250px !important;
        margin-left: 0 !important;
      }

      /* Fix sidebar-right with navbar-bottom */
      .wrapper.sidebar-right-layout.navbar-bottom .main-panel {
        float: left !important;
        margin-right: 250px !important;
        margin-left: 0 !important;
        padding-bottom: 70px !important;
      }

      /* Override any conflicting styles */
      .navbar-header {
        margin-right: 0 !important;
        margin-left: 0 !important;
      }

      /* Adjust navbar width based on sidebar position - more precise calculations */
      .wrapper:not(.sidebar-collapse) .main-header,
      .wrapper:not(.sidebar-collapse) .navbar-header {
        width: calc(100% - 251px) !important; /* 1px extra to avoid any possible overlap */
        z-index: 1029 !important; /* Lower than sidebar z-index */
      }

      .wrapper.sidebar-right-layout:not(.sidebar-collapse) .main-header,
      .wrapper.sidebar-right-layout:not(.sidebar-collapse) .navbar-header {
        margin-left: 0 !important;
        left: 0 !important;
        right: 251px !important; /* 1px extra to avoid any possible overlap */
      }

      .wrapper:not(.sidebar-right-layout):not(.sidebar-collapse) .main-header,
      .wrapper:not(.sidebar-right-layout):not(.sidebar-collapse) .navbar-header {
        margin-right: 0 !important;
        right: 0 !important;
        left: 251px !important; /* 1px extra to avoid any possible overlap */
      }

      /* Collapsed sidebar adjustments */
      .wrapper.sidebar-collapse .main-header,
      .wrapper.sidebar-collapse .navbar-header {
        width: calc(100% - 76px) !important; /* 1px extra to avoid any possible overlap */
      }

      .wrapper.sidebar-collapse.sidebar-right-layout .main-header,
      .wrapper.sidebar-collapse.sidebar-right-layout .navbar-header {
        margin-left: 0 !important;
        left: 0 !important;
        right: 76px !important; /* 1px extra to avoid any possible overlap */
      }

      .wrapper.sidebar-collapse:not(.sidebar-right-layout) .main-header,
      .wrapper.sidebar-collapse:not(.sidebar-right-layout) .navbar-header {
        margin-right: 0 !important;
        right: 0 !important;
        left: 76px !important; /* 1px extra to avoid any possible overlap */
      }

      /* Force proper alignment between sidebar and navbar */
      .navbar-header, .main-header {
        z-index: 1030 !important;
      }

      .sidebar {
        z-index: 1031 !important;
      }

      /* Mobile adjustments */
      @media (max-width: 991.98px) {
        .wrapper .main-header,
        .wrapper .navbar-header {
          width: 100% !important;
          left: 0 !important;
          right: 0 !important;
        }
        
        .sidebar.sidebar-right {
          right: -250px !important;
          left: auto !important;
          transform: none !important;
        }
        
        .sidebar:not(.sidebar-right) {
          left: -250px !important;
          right: auto !important;
          transform: none !important;
        }
        
        .sidebar-open .sidebar.sidebar-right {
          right: 0 !important;
          left: auto !important;
        }
        
        .sidebar-open .sidebar:not(.sidebar-right) {
          left: 0 !important;
          right: auto !important;
        }
        
        .wrapper.sidebar-right-layout .main-panel {
          float: none !important;
          margin-right: 0 !important;
          margin-left: 0 !important;
          width: 100% !important;
        }

        .wrapper .main-panel {
          float: none !important;
          margin-right: 0 !important;
          margin-left: 0 !important;
          width: 100% !important;
        }
        
        /* For mobile, make sure the logo is visible */
        .logo-header {
          display: flex !important;
          align-items: center !important;
          justify-content: space-between !important;
        }

        /* Ensure the logo is visible in both sidebar positions */
        .sidebar-right .logo-header .logo,
        .sidebar:not(.sidebar-right) .logo-header .logo {
          display: flex !important;
          align-items: center !important;
        }
      }

      /* Right sidebar specific fixes */
      .sidebar.sidebar-right .nav-toggle {
        float: left !important;
      }

      .sidebar.sidebar-right .logo-header .logo {
        float: right !important;
      }

      /* Fix toggle button direction for right sidebar */
      .sidebar.sidebar-right .btn-toggle .gg-menu-right:before {
        transform: scaleX(-1);
      }

      .sidebar.sidebar-right .btn-toggle .gg-menu-left:before {
        transform: scaleX(-1);
      }

      /* Properly setup the logo header for right sidebar */
      .sidebar.sidebar-right .logo-header .logo {
        margin-right: 15px !important;
      }

      /* Dynamic text color classes */
      .text-dynamic-dark {
        color: #1a2035 !important;
      }
      
      .text-dynamic-light {
        color: #ffffff !important;
      }
      
      /* Icon color adjustments */
      .text-dynamic-dark i {
        color: #1a2035 !important;
      }
      
      .text-dynamic-light i {
        color: #ffffff !important;
      }
      
      /* Specific adjustments for sidebar */
      .sidebar[data-background-color] .nav .nav-item a {
        color: inherit !important;
      }
      
      .sidebar[data-background-color] .nav .nav-item a i {
        color: inherit !important;
      }
      
      /* Logo text adjustments */
      .logo-header .logo span {
        transition: color 0.3s ease;
      }
      
      .text-dynamic-dark .logo span,
      .text-dynamic-dark .profile-username {
        color: #1a2035 !important;
      }
      
      .text-dynamic-light .logo span,
      .text-dynamic-light .profile-username {
        color: #ffffff !important;
      }
      
      /* Navbar elements */
      .navbar-header .navbar-nav .nav-item > a {
        color: inherit !important;
      }
      
      .navbar-header .navbar-nav .nav-item > a i {
        color: inherit !important;
      }
      
      /* Search bar adjustments */
      .nav-search .form-control {
        color: inherit !important;
        background: rgba(0, 0, 0, 0.1) !important;
      }
      
      .nav-search .form-control::placeholder {
        color: inherit !important;
        opacity: 0.7;
      }
      
      /* Dropdown adjustments */
      .dropdown-menu {
        background: #ffffff !important;
      }
      
      .dropdown-menu * {
        color: #1a2035 !important;
      }

      /* Add these styles to fix the content overlap */
      .main-panel {
        position: relative;
        z-index: 1;
      }

      .main-panel .container {
        background: #fff;
        position: relative;
        z-index: 2;
        min-height: calc(100vh - 70px);
        padding-bottom: 30px;
      }

      .page-inner {
        background: #fff;
        position: relative;
        z-index: 3;
      }

      .card {
        background: #fff;
        position: relative;
        z-index: 4;
        box-shadow: 0 1px 4px 0 rgba(0,0,0,.1);
      }

      /* Apply font scale */
      html {
        font-size: {{ $fontScale * 100 }}% !important;
      }

      /* Remove the hardcoded text-dark class and add dynamic color support */
      .logo span.brand-text {
        font-size: 1.2rem;
        margin-left: 10px;
        font-weight: bold;
      }

      .profile-username {
        transition: color 0.3s ease;
      }

      .profile-username .op-7 {
        opacity: 0.7;
      }
    </style>
  </head>
  <body>
    <div class="wrapper {{ $uiSettings->is_sidebar_collapsed ? 'sidebar-collapse' : '' }} 
                      {{ $uiSettings->is_navbar_fixed ? 'navbar-fixed' : '' }} 
                      {{ $uiSettings->is_sidebar_fixed ? 'sidebar-fixed' : '' }} 
                      {{ $uiSettings->sidebar_position === 'right' ? 'sidebar-right-layout' : '' }}
                      {{ $uiSettings->navbar_position === 'bottom' ? 'navbar-bottom' : 'navbar-top' }}">
      <!-- Sidebar -->
      <div class="sidebar {{ $uiSettings->sidebar_position === 'right' ? 'sidebar-right' : '' }}" 
           data-background-color="{{ $uiSettings->sidebar_color }}"
           style="background-color: {{ $uiSettings->sidebar_color }}; {{ $uiSettings->sidebar_position === 'right' ? 'right: 0; left: auto;' : '' }}">
        <div class="sidebar-logo">
          <!-- Logo Header -->
          <div class="logo-header" 
               data-background-color="{{ $uiSettings->logo_header_color }}"
               style="background-color: {{ $uiSettings->logo_header_color }};">
            <a href="{{ route('tenant.dashboard', ['slug' => session('tenant_slug')]) }}" class="logo">
              <div class="d-flex align-items-center">
                <img
                  src="{{ $uiSettings->header_logo ? asset('storage/' . $uiSettings->header_logo) : asset('assets/img/clam_logo.jpg') }}"
                  alt="Logo"
                  class="navbar-brand rounded-circle"
                  style="width: 40px; height: 40px; object-fit: cover;"
                />
                <span class="brand-text">Clam Agency</span>
              </div>
            </a>
            <div class="nav-toggle">
              <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
              </button>
              <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
              </button>
            </div>
            <button class="topbar-toggler more">
              <i class="gg-more-vertical-alt"></i>
            </button>
          </div>
          <!-- End Logo Header -->
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <!-- Common Dashboard Link for All Roles -->
              

              @php
                $userRole = session('tenant_user.role');
              @endphp

              @if($userRole === 'judge')
              <li class="nav-item {{ request()->routeIs('tenant.judge.dashboard') ? 'active' : '' }}">
                <a href="{{ route('tenant.judge.dashboard', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <!-- Judge Sidebar Items -->
              <li class="nav-item {{ request()->routeIs('tenant.judges.scoring.*') ? 'active' : '' }}">
                <a href="{{ route('tenant.judges.scoring.index', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-clipboard-list"></i>
                  <p>Score Contestants</p>
                </a>
              </li>

              @elseif($userRole === 'user')
              <li class="nav-item {{ request()->routeIs('tenant.user.dashboard') ? 'active' : '' }}">
                <a href="{{ route('tenant.user.dashboard', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              

              @elseif($userRole === 'owner')
              <li class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                <a href="{{ route('tenant.dashboard', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <!-- Owner Sidebar Items -->
              <li class="nav-item">
                <a href="{{ route('tenant.subscription.plans', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-crown"></i>
                  <p>Subscription Plans</p>
                  @php
                    $tenant = App\Models\Tenant::where('slug', session('tenant_slug'))->first();
                    $tenantPlan = $tenant->plan;
                  @endphp
                  
                  @if($tenant->hasNoPlan())
                    <span class="badge badge-danger">No Plan</span>
                  @elseif(session('trial_days_left'))
                    <span class="badge badge-warning">Trial: {{ session('trial_days_left') }} days left</span>
                  @endif
                </a>
              </li>

              <!-- Show upgrade prompt if tenant has no plan -->
              @if($tenant->hasNoPlan())
              <li class="nav-item">
                <div class="alert alert-warning m-2 p-2">
                  <i class="fas fa-exclamation-triangle"></i>
                  <small class="text-black">Upgrade your plan to access premium features</small>
                  <a href="{{ route('tenant.subscription.plans', ['slug' => session('tenant_slug')]) }}" class="btn btn-warning btn-xs btn-block mt-1">
                    <i class="fas fa-arrow-up text-black"></i> <p class="text-black">Upgrade Now</p>
                  </a>
                </div>
              </li>
              @endif

              <!-- Only show Pageant Management section if the tenant's plan allows it -->
              @if(!$tenant->hasNoPlan() && $tenantPlan->pageant_management)
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#pageantManagement">
                  <i class="fas fa-trophy"></i>
                  <p>Pageant Management</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="pageantManagement">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="{{ route('tenant.events.index', ['slug' => session('tenant_slug')]) }}">
                        <span class="sub-item">Events</span>
                      </a>
                    </li>
                    <li>
                      <a href="{{ route('tenant.contestants.index', ['slug' => session('tenant_slug')]) }}">
                        <span class="sub-item">Contestants</span>
                      </a>
                    </li>
                    <li>
                      <a href="{{ route('tenant.categories.index', ['slug' => session('tenant_slug')]) }}">
                        <span class="sub-item">Categories</span>
                      </a>
                    </li>
                    <li>
                      <a href="{{ route('tenant.judges.index', ['slug' => session('tenant_slug')]) }}">
                        <span class="sub-item">Judges</span>
                      </a>
                    </li>
                    <li>
                      <a href="{{ route('tenant.event-assignments.index', ['slug' => session('tenant_slug')]) }}">
                        <span class="sub-item">Event Assignments</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a href="{{ route('tenant.scores.index', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-star"></i>
                  <p>Scoring</p>
                  
                </a>
               
              </li>
              @endif

              <li class="nav-item">
                <a href="{{ route('tenant.users.index', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-users"></i>
                  <p>User Management</p>
                </a>
              </li>

              <!-- Only show Reports module if the tenant's plan allows it -->
              @if(!$tenant->hasNoPlan() && $tenantPlan->reports_module)
              <li class="nav-item">
              <a href="{{ route('tenant.reports.generate', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-chart-bar"></i>
                  <p>Reports</p>
                  
                </a>
                
              </li>
              @endif

              <!-- <li class="nav-item">
                <a href="#">
                  <i class="fas fa-cog"></i>
                  <p>Settings</p>
                </a>
              </li> -->

              @if(auth()->guard('tenant')->user()->role === 'owner')
              <li class="nav-item">
                <a href="{{ route('tenant.ui-settings.index', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-paint-brush"></i>
                  <p>UI Customization</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('tenant.updates.index', ['slug' => session('tenant_slug')]) }}">
                    <i class="fas fa-sync"></i>
                    <p>System Updates</p>
                    @if(isset($isNewVersionAvailable) && $isNewVersionAvailable)
                        <span class="badge badge-success">New!</span>
                    @endif
                </a>
              </li>
              @endif
              @endif
            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->

      <div class="main-panel" style="{{ $uiSettings->sidebar_position === 'right' ? 'float: left; margin-right: 250px; margin-left: 0;' : '' }}">
        <div class="main-header {{ $uiSettings->navbar_position === 'bottom' ? 'navbar-bottom' : 'navbar-top' }}" 
             style="{{ $uiSettings->navbar_position === 'bottom' ? 'position: fixed; bottom: 0; top: auto;' : 'position: fixed; top: 0; bottom: auto;' }}
                    {{ $uiSettings->sidebar_position === 'right' ? 'width: calc(100% - 251px); left: 0; right: 251px;' : 'width: calc(100% - 251px); left: 251px; right: 0;' }}
                    {{ $uiSettings->is_sidebar_collapsed ? ($uiSettings->sidebar_position === 'right' ? 'width: calc(100% - 76px); left: 0; right: 76px;' : 'width: calc(100% - 76px); left: 76px; right: 0;') : '' }}">
          <div class="main-header-logo d-flex d-lg-none">
            <!-- Mobile Logo Header -->
            <div class="logo-header" data-background-color="{{ $uiSettings->logo_header_color }}">
              <a href="{{ route('tenant.dashboard', ['slug' => session('tenant_slug')]) }}" class="logo">
                <img src="{{ asset('assets/img/buksu_logo.png') }}" alt="navbar brand" class="navbar-brand" height="20" />
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                  <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                  <i class="gg-menu-left"></i>
                </button>
              </div>
              <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
              </button>
            </div>
          </div>

          <!-- Navbar Header -->
          <nav class="navbar navbar-header navbar-expand-lg {{ $uiSettings->navbar_position === 'bottom' ? 'navbar-bottom' : 'navbar-top' }}" 
               data-background-color="{{ $uiSettings->navbar_color }}"
               style="background-color: {{ $uiSettings->navbar_color }}; 
                     {{ $uiSettings->navbar_position === 'bottom' ? 'position: fixed; bottom: 0; top: auto;' : 'position: fixed; top: 0; bottom: auto;' }}
                      {{ $uiSettings->sidebar_position === 'right' ? 'width: calc(100% - 251px); left: 0; right: 251px;' : 'width: calc(100% - 251px); left: 251px; right: 0;' }}
                     {{ $uiSettings->is_sidebar_collapsed ? ($uiSettings->sidebar_position === 'right' ? 'width: calc(100% - 76px); left: 0; right: 76px;' : 'width: calc(100% - 76px); left: 76px; right: 0;') : '' }}">
            <div class="container-fluid">
              <nav class="navbar navbar-header-left navbar-expand-lg p-0">
                <div class="nav-search">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <button type="submit" class="btn btn-search pe-1">
                      <i class="fa fa-search search-icon"></i>
                    </button>
                  </div>
                  <input type="text" placeholder="Search ..." class="form-control" />
                  </div>
                </div>
              </nav>

              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <!-- Search Toggle -->
                <li class="nav-item dropdown hidden-caret d-flex d-lg-none">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" role="button">
                    <i class="fa fa-search"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-search animated fadeIn">
                    <form class="navbar-left navbar-form nav-search">
                      <div class="input-group">
                        <input type="text" placeholder="Search ..." class="form-control">
                      </div>
                    </form>
                  </ul>
                </li>

                <!-- Messages -->
                <li class="nav-item dropdown hidden-caret">
                  <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fa fa-envelope"></i>
                  </a>
                  <ul class="dropdown-menu messages-notif-box animated fadeIn" aria-labelledby="messageDropdown">
                    <li>
                      <div class="dropdown-title d-flex justify-content-between align-items-center">
                        Messages
                        <a href="#" class="small">Mark all as read</a>
                      </div>
                    </li>
                    <li>
                      <div class="message-notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <p class="text-muted text-center py-3">No messages</p>
                        </div>
                      </div>
                    </li>
                    <li>
                      <a class="see-all" href="javascript:void(0);">See all messages<i class="fa fa-angle-right"></i></a>
                    </li>
                  </ul>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown hidden-caret">
                  <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fa fa-bell"></i>
                    <span class="notification">0</span>
                  </a>
                  <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
                    <li>
                      <div class="dropdown-title">You have 0 new notifications</div>
                    </li>
                    <li>
                      <div class="notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <p class="text-muted text-center py-3">No notifications</p>
                        </div>
                      </div>
                    </li>
                    <li>
                      <a class="see-all" href="javascript:void(0);">See all notifications<i class="fa fa-angle-right"></i></a>
                    </li>
                  </ul>
                </li>

                <!-- Quick Actions -->
                <li class="nav-item dropdown hidden-caret">
                  <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="fas fa-layer-group"></i>
                  </a>
                  <div class="dropdown-menu quick-actions animated fadeIn">
                    <div class="quick-actions-header">
                      <span class="title mb-1">Quick Actions</span>
                      <span class="subtitle op-7">Shortcuts</span>
                    </div>
                    <div class="quick-actions-scroll scrollbar-outer">
                      <div class="quick-actions-items">
                        <div class="row m-0">
                          <a class="col-6 col-md-4 p-0" href="#">
                            <div class="quick-actions-item">
                              <div class="avatar-item bg-danger rounded-circle">
                                <i class="far fa-calendar-alt"></i>
                              </div>
                              <span class="text">Calendar</span>
                            </div>
                          </a>
                          <a class="col-6 col-md-4 p-0" href="#">
                            <div class="quick-actions-item">
                              <div class="avatar-item bg-warning rounded-circle">
                                <i class="fas fa-map"></i>
                              </div>
                              <span class="text">Reports</span>
                            </div>
                          </a>
                          <a class="col-6 col-md-4 p-0" href="#">
                            <div class="quick-actions-item">
                              <div class="avatar-item bg-info rounded-circle">
                                <i class="fas fa-file-excel"></i>
                              </div>
                              <span class="text">Export</span>
                            </div>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>

                <!-- User Profile -->
                <li class="nav-item topbar-user dropdown hidden-caret">
                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <div class="avatar-sm">
                      <img src="{{ asset('assets/img/profile.jpg') }}" alt="..." class="avatar-img rounded-circle" />
                    </div>
                    <span class="profile-username">
                      <span class="op-7">Hi,</span>
                          <span class="fw-bold">{{ session('tenant_user.name') }}</span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg">
                          <img src="{{ asset('assets/img/profile.jpg') }}" alt="image profile" class="avatar-img rounded">
                          </div>
                          <div class="u-text">
                            <h4>{{ session('tenant_user.name') }}</h4>
                            <p class="text-muted">{{ session('tenant_user.email') }}</p>
                          <a href="#" class="btn btn-secondary btn-sm">View Profile</a>
                        </div>
                        </div>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">My Profile</a>
                      <a class="dropdown-item" href="#">Account Settings</a>
                        <div class="dropdown-divider"></div>
                        @if(auth()->guard('tenant')->check())
                            <form method="POST" action="{{ route('tenant.logout', ['slug' => session('tenant_slug')]) }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        @endif
                      </li>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div>

        <div class="container">
          <div class="page-inner">
            @yield('content')
          </div>
        </div>

      </div>

      <!-- Custom template | Settings Panel -->
      <div class="custom-template d-none">
        <div class="title">Reset UI Layout</div>
        <div class="custom-content">
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary reset-layout-btn">
              <i class="fas fa-sync-alt me-2"></i> Reset Layout
            </button>
              </div>
            </div>
              </div>
    </div>

    <!--   Core JS Files   -->
    <script src="{{ asset('assets/js/core/jquery.3.2.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <!-- jQuery UI -->
    <script src="{{ asset('assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js') }}"></script>
    <!-- jQuery Scrollbar -->
    <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <!-- Custom JS Files -->
    <script src="{{ asset('assets/js/custom-scripts.js') }}"></script>

    <!-- Add script to fix sidebar right/left switching -->
    <script>
      $(document).ready(function() {
        // Function to handle sidebar position toggling
        function handleSidebarPositionReset() {
          // Reset any sidebar related positioning
          $('.sidebar').removeAttr('style').addClass('sidebar-reset');
          setTimeout(function() {
            $('.sidebar').removeClass('sidebar-reset');
            
            // Re-apply proper positioning based on current classes
            if ($('.wrapper').hasClass('sidebar-right-layout')) {
              $('.sidebar').addClass('sidebar-right').css({
                'right': '0',
                'left': 'auto',
                'transform': 'none'
              });
              
              // Adjust main panel
              $('.main-panel').css({
                'float': 'left',
                'margin-right': $('.wrapper').hasClass('sidebar-collapse') ? '76px' : '250px',
                'margin-left': '0'
              });
              
              // Adjust navbar/header
              $('.main-header, .navbar-header').css({
                'left': '0',
                'right': $('.wrapper').hasClass('sidebar-collapse') ? '76px' : '251px',
                'width': 'calc(100% - ' + ($('.wrapper').hasClass('sidebar-collapse') ? '76px' : '251px') + ')'
              });
            } else {
              $('.sidebar').removeClass('sidebar-right').css({
                'left': '0',
                'right': 'auto',
                'transform': 'none'
              });
              
              // Reset main panel
              $('.main-panel').css({
                'float': 'right',
                'margin-left': $('.wrapper').hasClass('sidebar-collapse') ? '76px' : '250px',
                'margin-right': '0'
              });
              
              // Reset navbar/header
              $('.main-header, .navbar-header').css({
                'right': '0',
                'left': $('.wrapper').hasClass('sidebar-collapse') ? '76px' : '251px',
                'width': 'calc(100% - ' + ($('.wrapper').hasClass('sidebar-collapse') ? '76px' : '251px') + ')'
              });
            }
          }, 100);
        }
        
        // Listen for changes to sidebar position
        $(document).on('click', '.sidebar-position-toggle', function() {
          setTimeout(function() {
            handleSidebarPositionReset();
          }, 100);
        });
        
        // Handle sidebar collapse toggle
        $(document).on('click', '.toggle-sidebar, .sidenav-toggler', function() {
          setTimeout(function() {
            handleSidebarPositionReset();
          }, 300);
        });
        
        // Handle reset button click
        $(document).on('click', '.reset-layout-btn', function() {
          setTimeout(function() {
            handleSidebarPositionReset();
          }, 100);
        });
        
        // Initial setup - run after a short delay to ensure all elements are properly initialized
        setTimeout(function() {
          handleSidebarPositionReset();
        }, 300);
        
        // Reinitialize on window resize
        $(window).on('resize', function() {
          handleSidebarPositionReset();
        });

        // Fix for collapsible sidebar menus
        // This handles the menu toggle manually since Bootstrap's collapse might not be working correctly
        $('.nav-item > a[data-bs-toggle="collapse"]').on('click', function(e) {
          e.preventDefault();
          
          var target = $(this).attr('href');
          
          if ($(target).hasClass('show')) {
            // If menu is open, close it
            $(target).removeClass('show');
            $(this).find('.caret').removeClass('caret-rotate');
          } else {
            // Close any open menus first (optional - for accordion style)
            $('.nav-item .collapse.show').removeClass('show');
            $('.nav-item .caret').removeClass('caret-rotate');
            
            // Then open the clicked menu
            $(target).addClass('show');
            $(this).find('.caret').addClass('caret-rotate');
          }
        });
        
        // Check if current page is in a submenu, if so, expand that menu
        var currentPath = window.location.pathname;
        $('.nav-collapse a').each(function() {
          var linkPath = $(this).attr('href');
          if (linkPath && currentPath.includes(linkPath)) {
            $(this).addClass('active');
            $(this).closest('.collapse').addClass('show');
            $(this).closest('.nav-item').find('.caret').addClass('caret-rotate');
          }
        });
      });
    </script>

    <!-- Add some custom CSS for the sidebar menu animation and caret rotation -->
    <style>
      /* Dropdown animation */
      .collapse {
        transition: height 0.3s ease;
      }
      
      /* Caret rotation animation */
      .caret {
        transition: transform 0.3s ease;
      }
      
      .caret-rotate {
        transform: rotate(180deg);
      }

      /* Dropdown items styling */
      .sidebar .nav-item a {
        position: relative;
        display: block;
        padding: 12px 16px;
        color: inherit;
        text-decoration: none;
        transition: all 0.3s;
      }

      .sidebar .nav-item a .caret {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
      }

      .sidebar .nav-collapse {
        padding-left: 20px;
      }

      .sidebar .nav-collapse .sub-item {
        padding: 8px 0;
        display: block;
      }

      /* Ensure collapse items are visible when active */
      .sidebar .collapse.show {
        display: block;
      }

      /* Highlight active item */
      .sidebar .nav-item a.active,
      .sidebar .nav-collapse a.active {
        background: rgba(255, 255, 255, 0.1);
        font-weight: bold;
      }
    </style>

        @stack('scripts')
  </body>
</html>