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

    <!-- Fonts and icons -->
    <script src="{{ asset('assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
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
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}" />

    <!-- Custom styles for profile section -->
    <style>
      /* Logo Header and Navbar alignment */
      .logo-header {
        height: 60px;
        width: 250px;
        position: fixed;
        z-index: 1001;
        display: flex;
        align-items: center;
        padding: 0 15px;
        transition: all .3s;
      }

      .main-header {
        background: #fff;
        min-height: 60px;
        width: 100%;
        position: relative;
        margin-bottom: 20px;
        border-bottom: 1px solid #ebecec;
      }

      .navbar {
        min-height: 60px;
        transition: all .3s;
        margin-left: 250px;
        width: calc(100% - 250px);
      }

      .navbar.navbar-header {
        background: #fff;
        padding: 0;
        position: fixed;
        z-index: 1000;
        top: 0;
        right: 0;
      }

      /* Wrapper adjustments */
      .wrapper {
        min-height: 100vh;
        position: relative;
        top: 0;
        height: 100vh;
      }

      .main-panel {
        height: 100%;
        min-height: 100%;
        position: relative;
        transition: all .3s;
      }

      .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        width: 250px;
        display: block;
        z-index: 1000;
        color: #ffffff;
        font-weight: 200;
        background: #1a2035;
        transition: all .3s;
      }

      .sidebar.sidebar-right {
        right: 0;
        left: auto;
      }

      /* Sidebar position adjustments */
      .wrapper:not(.sidebar-right-layout) .sidebar {
        left: 0;
        right: auto;
      }

      .wrapper.sidebar-right-layout .sidebar {
        right: 0;
        left: auto;
      }

      .wrapper.sidebar-right-layout .logo-header {
        left: 0;
        right: auto;
      }

      .wrapper.sidebar-right-layout .navbar {
        margin-left: 0;
        margin-right: 250px;
      }

      /* Main content adjustment */
      .main-panel > .content {
        padding: 0 15px;
        min-height: calc(100% - 123px);
        margin-top: 60px;
      }

      /* Profile section improvements */
      .topbar-user .nav-link {
        display: flex;
        align-items: center;
        padding: 10px;
        gap: 10px;
        color: #495057;
      }

      .topbar-user .avatar-sm {
        width: 32px;
        height: 32px;
      }

      .topbar-user .avatar-sm img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
      }

      .topbar-user .profile-info {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
      }

      .topbar-user .profile-greeting {
        color: #8d9498;
      }

      .topbar-user .profile-name {
        color: #495057;
        font-weight: 600;
        max-width: 130px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      /* Navbar search improvements */
      .nav-search {
        flex: 1;
        max-width: 400px;
        padding: 0 15px;
        
      }

      .nav-search .input-group {
        background: #f8f9fa;
        border-radius: 4px;
        overflow: hidden;
      }

      .nav-search .input-group input {
        background: transparent;
        border: none;
        padding: 8px 15px;
        height: 40px;
      }

      .nav-search .input-group input:focus {
        background: #fff;
        box-shadow: none;
      }

      .nav-search .btn-search {
        background: transparent;
        border: none;
        color: #8d9498;
        padding: 8px 15px;
      }

      /* Container adjustments */
      .navbar-header .container-fluid {
        display: flex;
        align-items: center;
        padding: 0 15px;
        height: 60px;
      }

      .navbar-nav {
        display: flex;
        align-items: center;
        gap: 5px;
      }

      /* Adjust colors based on navbar background */
      [data-background-color]:not([data-background-color="white"]) {
        color: #ffffff;
      }

      [data-background-color]:not([data-background-color="white"]) .nav-search input {
        color: #ffffff;
      }

      [data-background-color]:not([data-background-color="white"]) .nav-search .btn-search {
        color: #ffffff;
      }

      [data-background-color]:not([data-background-color="white"]) .profile-greeting {
        color: rgba(255, 255, 255, 0.7);
      }

      [data-background-color]:not([data-background-color="white"]) .profile-name {
        color: #ffffff;
      }

      /* Collapsed sidebar adjustments */
      .wrapper.sidebar-collapse .logo-header {
        width: 70px;
      }

      .wrapper.sidebar-collapse .navbar {
        margin-left: 70px;
        width: calc(100% - 70px);
      }

      .wrapper.sidebar-collapse.sidebar-right-layout .navbar {
        margin-left: 0;
        margin-right: 70px;
      }
    </style>

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
  </head>
  <body>
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
                'is_sidebar_fixed' => true
            ]);
        }
    @endphp

    <div class="wrapper {{ $uiSettings->is_sidebar_collapsed ? 'sidebar-collapse' : '' }} {{ $uiSettings->is_navbar_fixed ? 'navbar-fixed' : '' }} {{ $uiSettings->is_sidebar_fixed ? 'sidebar-fixed' : '' }} {{ $uiSettings->sidebar_position === 'right' ? 'sidebar-right-layout' : '' }}">
      <!-- Sidebar -->
      <div class="sidebar {{ $uiSettings->sidebar_position === 'right' ? 'sidebar-right' : '' }}" 
           data-background-color="{{ $uiSettings->sidebar_color }}"
           style="{{ $uiSettings->sidebar_position === 'right' ? 'right: 0; left: auto !important; transform: none !important;' : '' }}">
        <div class="sidebar-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="{{ $uiSettings->logo_header_color }}">
            <a href="{{ route('tenant.dashboard', ['slug' => session('tenant_slug')]) }}" class="logo">
              <img
                src="{{ asset('assets/img/kaiadmin/logo_light.svg') }}"
                alt="navbar brand"
                class="navbar-brand"
                height="20"
              />
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
              <!-- Tenant Sidebar -->
              <li class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                <a href="{{ route('tenant.dashboard', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              
              @if(auth()->guard('tenant')->user()->role === 'owner')
              <!-- Tenant Owner Sidebar Items -->
              <li class="nav-item">
                <a href="{{ route('tenant.subscription.plans', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-crown"></i>
                  <p>Subscription Plans</p>
                  @php
                    // Get tenant and plan information
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
                <a data-bs-toggle="collapse" href="#scoring">
                  <i class="fas fa-star"></i>
                  <p>Scoring Result</p>
                  
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
                <a data-bs-toggle="collapse" href="#reportsMenu">
                  <i class="fas fa-chart-bar"></i>
                  <p>Reports</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="reportsMenu">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#">
                        <span class="sub-item">Contestant Reports</span>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <span class="sub-item">Event Reports</span>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <span class="sub-item">Analytics</span>
                      </a>
                    </li>
                  </ul>
                </div>
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
              @endif
              @else
              <!-- Regular Tenant User Sidebar Items -->
              <li class="nav-item">
                <a href="#">
                  <i class="fas fa-users"></i>
                  <p>Contestants</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#">
                  <i class="fas fa-calendar-alt"></i>
                  <p>Events</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#">
                  <i class="fas fa-star"></i>
                  <p>Scores</p>
                </a>
              </li>
              @endif
            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->

      <div class="main-panel" style="{{ $uiSettings->sidebar_position === 'right' ? 'float: left; margin-right: 250px; margin-left: 0;' : '' }}">
        @if($uiSettings->navbar_position === 'top')
        <!-- Top Navbar -->
        <nav class="navbar navbar-header navbar-expand-lg" data-background-color="{{ $uiSettings->navbar_color }}">
            <div class="container-fluid">
                <nav class="navbar navbar-header-left navbar-expand-lg p-0">
                    <!-- <div class="nav-search">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button type="submit" class="btn btn-search pe-1">
                                    <i class="fa fa-search search-icon"></i>
                                </button>
                            </div>
                            <input type="text" placeholder="Search ..." class="form-control">
                        </div>
                    </div> -->
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

                    <!-- User Profile -->
                    <li class="nav-item topbar-user dropdown hidden-caret">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="avatar-sm">
                                <img src="{{ asset('assets/img/profile.jpg') }}" alt="..." class="avatar-img rounded-circle border shadow-sm">
                            </div>
                            <div class="profile-info">
                                <span class="profile-greeting">Hi,</span>
                                <span class="profile-name">{{ session('tenant_user.name') }}</span>
                            </div>
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
        @endif

        <div class="container">
          <div class="page-inner">
            @yield('content')
          </div>
        </div>

        @if($uiSettings->navbar_position === 'bottom')
        <!-- Bottom Navbar -->
        <nav class="navbar navbar-header navbar-expand-lg navbar-bottom" data-background-color="{{ $uiSettings->navbar_color }}">
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

              <!-- User Profile -->
              <li class="nav-item topbar-user dropdown hidden-caret">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                    <div class="avatar-sm me-2">
                        <img src="{{ asset('assets/img/profile.jpg') }}" alt="..." class="avatar-img rounded-circle border shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                    </div>
                    <div class="profile-info">
                        <span class="profile-greeting text-muted small">Hi,</span>
                        <span class="profile-name d-block text-dark fw-bold">{{ session('tenant_user.name') }}</span>
                    </div>
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
        @endif
      </div>

      <!-- Custom template | Settings Panel -->
      <!-- <div class="custom-template" style="{{ $uiSettings->sidebar_position === 'right' ? 'left: 0; right: auto;' : '' }}">
        <div class="title">Settings</div>
        <div class="custom-content">
          <div class="switcher">
            <div class="switch-block">
              <h4>Logo Header</h4>
              <div class="btnSwitch">
                <button type="button" class="selected changeLogoHeaderColor" data-color="dark"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="blue"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="purple"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="light-blue"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="green"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="orange"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="red"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="white"></button>
                <br />
                <button type="button" class="changeLogoHeaderColor" data-color="dark2"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="blue2"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="purple2"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="light-blue2"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="green2"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="orange2"></button>
                <button type="button" class="changeLogoHeaderColor" data-color="red2"></button>
              </div>
            </div>
            <div class="switch-block">
              <h4>Navbar Header</h4>
              <div class="btnSwitch">
                <button type="button" class="changeTopBarColor" data-color="dark"></button>
                <button type="button" class="changeTopBarColor" data-color="blue"></button>
                <button type="button" class="changeTopBarColor" data-color="purple"></button>
                <button type="button" class="changeTopBarColor" data-color="light-blue"></button>
                <button type="button" class="changeTopBarColor" data-color="green"></button>
                <button type="button" class="changeTopBarColor" data-color="orange"></button>
                <button type="button" class="changeTopBarColor" data-color="red"></button>
                <button type="button" class="selected changeTopBarColor" data-color="white"></button>
                <br />
                <button type="button" class="changeTopBarColor" data-color="dark2"></button>
                <button type="button" class="changeTopBarColor" data-color="blue2"></button>
                <button type="button" class="changeTopBarColor" data-color="purple2"></button>
                <button type="button" class="changeTopBarColor" data-color="light-blue2"></button>
                <button type="button" class="changeTopBarColor" data-color="green2"></button>
                <button type="button" class="changeTopBarColor" data-color="orange2"></button>
                <button type="button" class="changeTopBarColor" data-color="red2"></button>
              </div>
            </div>
            <div class="switch-block">
              <h4>Sidebar</h4>
              <div class="btnSwitch">
                <button type="button" class="changeSideBarColor" data-color="white"></button>
                <button type="button" class="selected changeSideBarColor" data-color="dark"></button>
                <button type="button" class="changeSideBarColor" data-color="dark2"></button>
              </div>
            </div>
          </div>
        </div>
        <div class="custom-toggle">
          <i class="icon-settings"></i>
        </div>
      </div> -->
    </div>

    <!--   Core JS Files   -->
    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>

    <!-- jQuery Scrollbar -->
    <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>

    <!-- Chart JS -->
    <script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>

    <!-- jQuery Sparkline -->
    <script src="{{ asset('assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>

    <!-- Chart Circle -->
    <script src="{{ asset('assets/js/plugin/chart-circle/circles.min.js') }}"></script>

    <!-- Datatables -->
    <script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>

    <!-- Bootstrap Notify -->
    <script src="{{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <!-- jQuery Vector Maps -->
    <script src="{{ asset('assets/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jsvectormap/world.js') }}"></script>

    <!-- Sweet Alert -->
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>

    <!-- Kaiadmin JS -->
    <script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>

    <!-- Kaiadmin DEMO methods -->
    <script src="{{ asset('assets/js/setting-demo.js') }}"></script>
    <script src="{{ asset('assets/js/demo.js') }}"></script>

    <!-- Custom Scripts -->
    <script>
      $(document).ready(function() {
        // Initialize scrollbars
        $('.scrollbar-inner').scrollbar();

        // Function to update layout based on sidebar position
        function updateLayoutForSidebarPosition(position) {
          if (position === 'right') {
            $('.main-panel').css({
              'float': 'left',
              'margin-right': $('.sidebar').width() + 'px',
              'margin-left': '0'
            });
            $('.custom-template').css({
              'left': '0',
              'right': 'auto'
            });
          } else {
            $('.main-panel').css({
              'float': '',
              'margin-right': '',
              'margin-left': ''
            });
            $('.custom-template').css({
              'left': '',
              'right': ''
            });
          }
        }

        // Initial layout update
        updateLayoutForSidebarPosition('{{ $uiSettings->sidebar_position }}');

        // Update layout when sidebar is toggled
        $('.toggle-sidebar').click(function() {
          $('.wrapper').toggleClass('sidebar-collapse');
          $(this).find('i').toggleClass('gg-menu-right gg-menu-left');
          
          // Adjust margins when sidebar is collapsed
          if ($('.wrapper').hasClass('sidebar-collapse')) {
            if ('{{ $uiSettings->sidebar_position }}' === 'right') {
              $('.main-panel').css('margin-right', '75px');
            }
          } else {
            if ('{{ $uiSettings->sidebar_position }}' === 'right') {
              $('.main-panel').css('margin-right', '250px');
            }
          }
        });

        // Mobile sidebar toggle
        $('.sidenav-toggler').click(function() {
          $('.wrapper').toggleClass('nav-toggle');
          $(this).find('i').toggleClass('gg-menu-left gg-menu-right');
        });

        // Settings panel toggle
        $('.custom-toggle').click(function() {
          $('.custom-template').toggleClass('active');
        });

        // Apply color changes from settings panel
        $('.changeLogoHeaderColor').click(function() {
          if ($(this).attr('data-color') != null) {
            $('.logo-header').attr('data-background-color', $(this).attr('data-color'));
          }
          $(this).parent().find('.selected').removeClass('selected');
          $(this).addClass('selected');
        });

        $('.changeTopBarColor').click(function() {
          if ($(this).attr('data-color') != null) {
            $('.navbar-header').attr('data-background-color', $(this).attr('data-color'));
          }
          $(this).parent().find('.selected').removeClass('selected');
          $(this).addClass('selected');
        });

        $('.changeSideBarColor').click(function() {
          if ($(this).attr('data-color') != null) {
            $('.sidebar').attr('data-background-color', $(this).attr('data-color'));
          }
          $(this).parent().find('.selected').removeClass('selected');
          $(this).addClass('selected');
        });

        // Initialize any additional plugins or components
        @stack('scripts')
      });
    </script>
  </body>
</html>
