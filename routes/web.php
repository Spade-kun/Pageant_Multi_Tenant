<?php

use App\Http\Controllers\Admin\TenantManagementController;
use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\PlanController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Default route with automatic redirect based on port
Route::get('/', function (Request $request) {
    // Check if this is the admin instance (port 8001)
    if ($request->server('SERVER_PORT') == 8001) {
        // Admin port - redirect to admin login
        return redirect('/login');
    } else {
        // Tenant port - redirect to tenant login
        return redirect('/tenant/login');
    }
});

// Routes based on port
Route::group([], function () {
    // Determine if we're on the admin port
    $isAdminPort = request()->server('SERVER_PORT') == 8001;
    
    if ($isAdminPort) {
        // ADMIN ROUTES (Port 8001)
        
        // Admin Authentication
        Route::middleware('guest')->group(function () {
            Route::get('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
            Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
        });
        
        // Admin Dashboard and protected routes
        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('/dashboard', function () {
                return view('admin.dashboard');
            })->name('dashboard');
            
            // Profile routes
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
            
            Route::prefix('admin')->group(function () {
                // Tenant Management
                Route::get('/tenants', [TenantManagementController::class, 'index'])->name('admin.tenants.index');
                Route::get('/tenants/access', [TenantManagementController::class, 'access'])->name('admin.tenants.access');
                Route::get('/tenants/{tenant}', [TenantManagementController::class, 'show'])->name('admin.tenants.show');
                Route::put('/tenants/{tenant}/approve', [TenantManagementController::class, 'approve'])->name('admin.tenants.approve');
                Route::put('/tenants/{tenant}/reject', [TenantManagementController::class, 'reject'])->name('admin.tenants.reject');
                Route::put('/tenants/{tenant}/enable', [TenantManagementController::class, 'enable'])->name('admin.tenants.enable');
                Route::put('/tenants/{tenant}/disable', [TenantManagementController::class, 'disable'])->name('admin.tenants.disable');
                
                // Admin Plan Management Routes
                Route::get('/plans', [App\Http\Controllers\Admin\PlanController::class, 'index'])->name('admin.plans.index');
                Route::get('/plans/create', [App\Http\Controllers\Admin\PlanController::class, 'create'])->name('admin.plans.create');
                Route::post('/plans', [App\Http\Controllers\Admin\PlanController::class, 'store'])->name('admin.plans.store');
                Route::get('/plans/{plan}', [App\Http\Controllers\Admin\PlanController::class, 'show'])->name('admin.plans.show');
                Route::get('/plans/{plan}/edit', [App\Http\Controllers\Admin\PlanController::class, 'edit'])->name('admin.plans.edit');
                Route::put('/plans/{plan}', [App\Http\Controllers\Admin\PlanController::class, 'update'])->name('admin.plans.update');
                Route::delete('/plans/{plan}', [App\Http\Controllers\Admin\PlanController::class, 'destroy'])->name('admin.plans.destroy');

                // Plan Requests Routes
                Route::get('/requests', [App\Http\Controllers\Admin\PlanRequestController::class, 'index'])->name('admin.requests.index');
                Route::get('/requests/{request}', [App\Http\Controllers\Admin\PlanRequestController::class, 'show'])->name('admin.requests.show');
                Route::put('/requests/{request}/approve', [App\Http\Controllers\Admin\PlanRequestController::class, 'approve'])->name('admin.requests.approve');
                Route::put('/requests/{request}/reject', [App\Http\Controllers\Admin\PlanRequestController::class, 'reject'])->name('admin.requests.reject');
                Route::get('/requests/{tenant}/change-plan', [App\Http\Controllers\Admin\PlanRequestController::class, 'showChangePlan'])->name('admin.requests.change-plan');
                Route::put('/requests/{tenant}/update-plan', [App\Http\Controllers\Admin\PlanRequestController::class, 'updatePlan'])->name('admin.requests.update-plan');
            });
            
            // Logout
            Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
        });
    } else {
        // TENANT ROUTES (Port 8000)
        
        // Tenant Registration - Available to guests
        Route::get('tenant/register', [TenantController::class, 'showRegistrationForm'])->name('register');
        Route::post('tenant/register', [TenantController::class, 'register']);
        Route::get('tenant/register/success', [TenantController::class, 'registrationSuccess'])->name('register.success');
        
        // Tenant Authentication
        Route::middleware('guest:tenant')->group(function () {
            Route::get('/tenant/login', [TenantLoginController::class, 'showLoginForm'])->name('tenant.login');
            Route::post('/tenant/login', [TenantLoginController::class, 'login']);
        });
        
        // Tenant Dashboard with tenant slug
        Route::middleware(['auth:tenant'])->group(function () {
            // Owner Dashboard
            Route::get('/{slug}/dashboard', function ($slug) {
                if (Auth::guard('tenant')->user()->role === 'owner') {
                    return view('tenant.dashboard', ['slug' => $slug]);
                }
                return redirect()->route('tenant.user.dashboard', ['slug' => $slug]);
            })->name('tenant.dashboard');
            
            // User Dashboard
            Route::get('/{slug}/user-dashboard', function ($slug) {
                return view('tenant.user-dashboard', ['slug' => $slug]);
            })->name('tenant.user.dashboard');
            
            // Subscription Routes
            Route::get('/{slug}/subscription/plans', [App\Http\Controllers\Tenant\SubscriptionController::class, 'showPlans'])
                ->name('tenant.subscription.plans');
            Route::put('/{slug}/subscription/update', [App\Http\Controllers\Tenant\SubscriptionController::class, 'update'])
                ->name('tenant.subscription.update');
            Route::post('/{slug}/subscription/request', [App\Http\Controllers\Tenant\SubscriptionController::class, 'requestPlan'])
                ->name('tenant.subscription.request');

            // Contestant Routes
            Route::get('/{slug}/contestants', [App\Http\Controllers\Tenant\ContestantController::class, 'index'])
                ->name('tenant.contestants.index');
            Route::get('/{slug}/contestants/create', [App\Http\Controllers\Tenant\ContestantController::class, 'create'])
                ->name('tenant.contestants.create');
            Route::get('/{slug}/contestants/{id}', [App\Http\Controllers\Tenant\ContestantController::class, 'show'])
                ->name('tenant.contestants.show');
            Route::post('/{slug}/contestants', [App\Http\Controllers\Tenant\ContestantController::class, 'store'])
                ->name('tenant.contestants.store');
            Route::get('/{slug}/contestants/{id}/edit', [App\Http\Controllers\Tenant\ContestantController::class, 'edit'])
                ->name('tenant.contestants.edit');
            Route::put('/{slug}/contestants/{id}', [App\Http\Controllers\Tenant\ContestantController::class, 'update'])
                ->name('tenant.contestants.update');
            Route::delete('/{slug}/contestants/{id}', [App\Http\Controllers\Tenant\ContestantController::class, 'destroy'])
                ->name('tenant.contestants.destroy');

            // Categories Routes
            Route::get('/{slug}/categories', [App\Http\Controllers\Tenant\CategoryController::class, 'index'])
                ->name('tenant.categories.index');
            Route::get('/{slug}/categories/create', [App\Http\Controllers\Tenant\CategoryController::class, 'create'])
                ->name('tenant.categories.create');
            Route::post('/{slug}/categories', [App\Http\Controllers\Tenant\CategoryController::class, 'store'])
                ->name('tenant.categories.store');
            Route::get('/{slug}/categories/{id}/edit', [App\Http\Controllers\Tenant\CategoryController::class, 'edit'])
                ->name('tenant.categories.edit');
            Route::put('/{slug}/categories/{id}', [App\Http\Controllers\Tenant\CategoryController::class, 'update'])
                ->name('tenant.categories.update');
            Route::delete('/{slug}/categories/{id}', [App\Http\Controllers\Tenant\CategoryController::class, 'destroy'])
                ->name('tenant.categories.destroy');

            // Event Routes
            Route::get('/{slug}/events', [App\Http\Controllers\Tenant\EventController::class, 'index'])
                ->name('tenant.events.index');
            Route::get('/{slug}/events/create', [App\Http\Controllers\Tenant\EventController::class, 'create'])
                ->name('tenant.events.create');
            Route::post('/{slug}/events', [App\Http\Controllers\Tenant\EventController::class, 'store'])
                ->name('tenant.events.store');
            Route::get('/{slug}/events/{event}', [App\Http\Controllers\Tenant\EventController::class, 'show'])
                ->name('tenant.events.show');
            Route::get('/{slug}/events/{event}/edit', [App\Http\Controllers\Tenant\EventController::class, 'edit'])
                ->name('tenant.events.edit');
            Route::put('/{slug}/events/{event}', [App\Http\Controllers\Tenant\EventController::class, 'update'])
                ->name('tenant.events.update');
            Route::delete('/{slug}/events/{event}', [App\Http\Controllers\Tenant\EventController::class, 'destroy'])
                ->name('tenant.events.destroy');
            
            // Tenant User Management Routes (requires auth)
            Route::get('/{slug}/users', [App\Http\Controllers\Tenant\UserController::class, 'index'])
                ->name('tenant.users.index');
            
            // Logout
            Route::post('/{slug}/logout', [TenantLoginController::class, 'logout'])->name('tenant.logout');
        });
    }
});

// Tenant Registration Routes (accessible without auth)
Route::middleware(['web'])->group(function () {
    Route::get('/{slug}/register', [App\Http\Controllers\Tenant\RegisterController::class, 'showRegistrationForm'])
        ->name('tenant.register.form');
    Route::post('/{slug}/register', [App\Http\Controllers\Tenant\RegisterController::class, 'register'])
        ->name('tenant.register');
});

// Auth routes (these will be for admin authentication through the default routes file)
require __DIR__.'/auth.php';
