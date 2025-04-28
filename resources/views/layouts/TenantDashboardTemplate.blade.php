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

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Custom CSS for navbar positioning -->
    <style>
      /* Main wrapper styles */
      .wrapper.navbar-bottom .main-panel {
        padding-bottom: 70px !important;
        padding-top: 0 !important;
      }

      .wrapper.navbar-top .main-panel {
        padding-top: 70px !important;
        padding-bottom: 0 !important;
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

      /* Fix sidebar-right with navbar-bottom */
      .wrapper.sidebar-right-layout.navbar-bottom .main-panel {
        float: left;
        margin-right: 250px;
        margin-left: 0;
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
      .sidebar {
        z-index: 1030 !important;
      }

      /* Mobile adjustments */
      @media (max-width: 991.98px) {
        .wrapper .main-header,
        .wrapper .navbar-header {
          width: 100% !important;
          left: 0 !important;
          right: 0 !important;
        }
      }
    </style>
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

    <div class="wrapper {{ $uiSettings->is_sidebar_collapsed ? 'sidebar-collapse' : '' }} 
                      {{ $uiSettings->is_navbar_fixed ? 'navbar-fixed' : '' }} 
                      {{ $uiSettings->is_sidebar_fixed ? 'sidebar-fixed' : '' }} 
                      {{ $uiSettings->sidebar_position === 'right' ? 'sidebar-right-layout' : '' }}
                      {{ $uiSettings->navbar_position === 'bottom' ? 'navbar-bottom' : 'navbar-top' }}">
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

              @php
                $userRole = session('tenant_user.role');
              @endphp

              @if($userRole === 'judge')
              <!-- Judge Sidebar Items -->
              <li class="nav-item {{ request()->routeIs('tenant.judges.scoring.*') ? 'active' : '' }}">
                <a href="{{ route('tenant.judges.scoring.index', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-clipboard-list"></i>
                  <p>Score Contestants</p>
                </a>
              </li>
              @endif
              
              @if($userRole === 'owner')
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

              <li class="nav-item">
                <a href="#">
                  <i class="fas fa-cog"></i>
                  <p>Settings</p>
                </a>
              </li>

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
              <!-- @if(session('tenant_user.role') === 'judge')
              <li class="nav-item {{ request()->routeIs('tenant.judges.scoring.*') ? 'active' : '' }}">
                <a href="{{ route('tenant.judges.scoring.index', ['slug' => session('tenant_slug')]) }}">
                  <i class="fas fa-clipboard-list"></i>
                  <p>Score Contestants</p>
                </a>
              </li>
              @endif -->
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
        <div class="main-header {{ $uiSettings->navbar_position === 'bottom' ? 'navbar-bottom' : 'navbar-top' }}" 
             style="{{ $uiSettings->navbar_position === 'bottom' ? 'position: fixed; bottom: 0; top: auto;' : 'position: fixed; top: 0; bottom: auto;' }}
                    {{ $uiSettings->sidebar_position === 'right' ? 'width: calc(100% - 251px); left: 0; right: 251px;' : 'width: calc(100% - 251px); left: 251px; right: 0;' }}
                    {{ $uiSettings->is_sidebar_collapsed ? 'width: calc(100% - 76px) !important; ' . ($uiSettings->sidebar_position === 'right' ? 'right: 76px !important;' : 'left: 76px !important;') : '' }}
                    z-index: 1029;">
          <div class="main-header-logo d-flex d-lg-none">
            <!-- Mobile Logo Header -->
            <div class="logo-header" data-background-color="{{ $uiSettings->logo_header_color }}">
              <a href="{{ route('tenant.dashboard', ['slug' => session('tenant_slug')]) }}" class="logo">
                <img src="{{ asset('assets/img/kaiadmin/logo_light.svg') }}" alt="navbar brand" class="navbar-brand" height="20" />
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
               style="{{ $uiSettings->navbar_position === 'bottom' ? 'position: fixed; bottom: 0; top: auto;' : 'position: fixed; top: 0; bottom: auto;' }}
                      {{ $uiSettings->sidebar_position === 'right' ? 'width: calc(100% - 251px); left: 0; right: 251px;' : 'width: calc(100% - 251px); left: 251px; right: 0;' }}
                      {{ $uiSettings->is_sidebar_collapsed ? 'width: calc(100% - 76px) !important; ' . ($uiSettings->sidebar_position === 'right' ? 'right: 76px !important;' : 'left: 76px !important;') : '' }}
                      z-index: 1029;">
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
                  <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                    <div class="avatar-sm float-end">
                      <img src="{{ asset('assets/img/profile.jpg') }}" alt="..." class="avatar-img rounded-circle">
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