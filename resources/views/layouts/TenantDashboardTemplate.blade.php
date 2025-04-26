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

    <div class="wrapper {{ $uiSettings->is_sidebar_collapsed ? 'sidebar-collapse' : '' }} {{ $uiSettings->is_navbar_fixed ? 'navbar-fixed' : '' }} {{ $uiSettings->is_sidebar_fixed ? 'sidebar-fixed' : '' }}">
      <!-- Sidebar -->
      <div class="sidebar {{ $uiSettings->sidebar_position === 'right' ? 'sidebar-right' : '' }}" data-background-color="{{ $uiSettings->sidebar_color }}">
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
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#scoring">
                  <i class="fas fa-star"></i>
                  <p>Scoring</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="scoring">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#">
                        <span class="sub-item">Scoring Criteria</span>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <span class="sub-item">Score Sheets</span>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <span class="sub-item">Results</span>
                      </a>
                    </li>
                  </ul>
                </div>
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

      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
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
          <!-- Navbar Header -->
          <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom {{ $uiSettings->navbar_position === 'bottom' ? 'navbar-bottom' : '' }} {{ $uiSettings->navbar_position === 'left' ? 'navbar-left' : '' }} {{ $uiSettings->navbar_position === 'right' ? 'navbar-right' : '' }}" data-background-color="{{ $uiSettings->navbar_color }}">
            <div class="container-fluid">
              <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <button type="submit" class="btn btn-search pe-1">
                      <i class="fa fa-search search-icon"></i>
                    </button>
                  </div>
                  <input type="text" placeholder="Search ..." class="form-control" />
                </div>
              </nav>

              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                  <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" aria-haspopup="true">
                    <i class="fa fa-search"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-search animated fadeIn">
                    <form class="navbar-left navbar-form nav-search">
                      <div class="input-group">
                        <input type="text" placeholder="Search ..." class="form-control" />
                      </div>
                    </form>
                  </ul>
                </li>
                <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                          <p class="text-muted text-center">No messages</p>
                        </div>
                      </div>
                    </li>
                  </ul>
                </li>
                <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-bell"></i>
                    <span class="notification">0</span>
                  </a>
                  <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
                    <li>
                      <div class="dropdown-title">You have 0 notifications</div>
                    </li>
                    <li>
                      <div class="notif-scroll scrollbar-outer">
                        <div class="notif-center">
                          <p class="text-muted text-center">No notifications</p>
                        </div>
                      </div>
                    </li>
                  </ul>
                </li>
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
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg">
                            <img src="{{ asset('assets/img/profile.jpg') }}" alt="image profile" class="avatar-img rounded" />
                          </div>
                          <div class="u-text">
                            <h4>{{ session('tenant_user.name') }}</h4>
                            <p class="text-muted">{{ session('tenant_user.email') }}</p>
                            <a href="#" class="btn btn-xs btn-secondary btn-sm">View Profile</a>
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">My Profile</a>
                        <a class="dropdown-item" href="#">Account Setting</a>
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
                    </div>
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

        <footer class="footer">
          <div class="container-fluid d-flex justify-content-between">
            <nav class="pull-left">
              <ul class="nav">
                <li class="nav-item">
                  <a class="nav-link" href="#">Help</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#">Licenses</a>
                </li>
              </ul>
            </nav>
            <div class="copyright">
              2024, made with <i class="fa fa-heart heart text-danger"></i> by Pageant Management System
            </div>
          </div>
        </footer>
      </div>
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

    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="{{ asset('assets/js/setting-demo.js') }}"></script>
    <script src="{{ asset('assets/js/demo.js') }}"></script>
    <script>
      $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#177dff",
        fillColor: "rgba(23, 125, 255, 0.14)",
      });

      $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#f3545d",
        fillColor: "rgba(243, 84, 93, .14)",
      });

      $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#ffa534",
        fillColor: "rgba(255, 165, 52, .14)",
      });
    </script>
  </body>
</html>
