<?php

use App\Http\Controllers\Admin\TenantManagementController;
use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
        Route::middleware('auth:tenant')->group(function () {
            Route::get('/{slug}/dashboard', function ($slug) {
                return view('tenant.dashboard', ['slug' => $slug]);
            })->name('tenant.dashboard');
            
            // Subscription Routes
            Route::get('/{slug}/subscription/plans', [App\Http\Controllers\Tenant\SubscriptionController::class, 'showPlans'])
                ->name('tenant.subscription.plans');
            Route::put('/{slug}/subscription/update', [App\Http\Controllers\Tenant\SubscriptionController::class, 'update'])
                ->name('tenant.subscription.update');

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
            
            // Logout
            Route::post('/{slug}/logout', [TenantLoginController::class, 'logout'])->name('tenant.logout');
        });
    }
});

// Auth routes (these will be for admin authentication through the default routes file)
require __DIR__.'/auth.php';
