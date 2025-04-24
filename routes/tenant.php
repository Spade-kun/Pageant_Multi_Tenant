<?php

use App\Http\Controllers\Tenant\ContestantController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\EventController;
use App\Http\Controllers\Tenant\SubscriptionController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\RegisterController;
use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// Tenant Authentication
Route::middleware('guest:tenant')->group(function () {
    Route::get('/tenant/login', [TenantLoginController::class, 'showLoginForm'])->name('tenant.login');
    Route::post('/tenant/login', [TenantLoginController::class, 'login']);
});

// Tenant Registration
Route::get('tenant/register', [TenantController::class, 'showRegistrationForm'])->name('register');
Route::post('tenant/register', [TenantController::class, 'register']);
Route::get('tenant/register/success', [TenantController::class, 'registrationSuccess'])->name('register.success');

// Tenant Dashboard and protected routes
Route::middleware(['auth:tenant'])->group(function () {
    // Owner Dashboard
    Route::get('/{slug}/dashboard', function ($slug) {
        // Verify tenant exists
        $tenant = \App\Models\Tenant::where('slug', $slug)->firstOrFail();
        
        // Get user from tenant database
        $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        // Get user from session
        $tenantUser = session('tenant_user');
        if (!$tenantUser) {
            return redirect()->route('tenant.login');
        }

        // Get user from tenant database
        $user = DB::connection('tenant')
            ->table('users')
            ->where('email', $tenantUser['email'])
            ->first();

        if (!$user || $user->role !== 'owner') {
            return redirect()->route('tenant.user.dashboard', ['slug' => $slug]);
        }

        return view('tenant.dashboard', ['slug' => $slug]);
    })->name('tenant.dashboard');
    
    // User Dashboard
    Route::get('/{slug}/user-dashboard', function ($slug) {
        // Verify tenant exists
        $tenant = \App\Models\Tenant::where('slug', $slug)->firstOrFail();
        
        // Get user from tenant database
        $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        // Get user from session
        $tenantUser = session('tenant_user');
        if (!$tenantUser) {
            return redirect()->route('tenant.login');
        }

        // Get user from tenant database
        $user = DB::connection('tenant')
            ->table('users')
            ->where('email', $tenantUser['email'])
            ->first();

        if (!$user || $user->role !== 'user') {
            return redirect()->route('tenant.dashboard', ['slug' => $slug]);
        }

        return view('tenant.user-dashboard', ['slug' => $slug]);
    })->name('tenant.user.dashboard');
    
    // Subscription Routes
    Route::get('/{slug}/subscription/plans', [SubscriptionController::class, 'showPlans'])
        ->name('tenant.subscription.plans');
    Route::put('/{slug}/subscription/update', [SubscriptionController::class, 'update'])
        ->name('tenant.subscription.update');
    Route::post('/{slug}/subscription/request', [SubscriptionController::class, 'requestPlan'])
        ->name('tenant.subscription.request');

    // Contestant Routes
    Route::get('/{slug}/contestants', [ContestantController::class, 'index'])
        ->name('tenant.contestants.index');
    Route::get('/{slug}/contestants/create', [ContestantController::class, 'create'])
        ->name('tenant.contestants.create');
    Route::get('/{slug}/contestants/{id}', [ContestantController::class, 'show'])
        ->name('tenant.contestants.show');
    Route::post('/{slug}/contestants', [ContestantController::class, 'store'])
        ->name('tenant.contestants.store');
    Route::get('/{slug}/contestants/{id}/edit', [ContestantController::class, 'edit'])
        ->name('tenant.contestants.edit');
    Route::put('/{slug}/contestants/{id}', [ContestantController::class, 'update'])
        ->name('tenant.contestants.update');
    Route::delete('/{slug}/contestants/{id}', [ContestantController::class, 'destroy'])
        ->name('tenant.contestants.destroy');

    // Categories Routes
    Route::get('/{slug}/categories', [CategoryController::class, 'index'])
        ->name('tenant.categories.index');
    Route::get('/{slug}/categories/create', [CategoryController::class, 'create'])
        ->name('tenant.categories.create');
    Route::post('/{slug}/categories', [CategoryController::class, 'store'])
        ->name('tenant.categories.store');
    Route::get('/{slug}/categories/{id}/edit', [CategoryController::class, 'edit'])
        ->name('tenant.categories.edit');
    Route::put('/{slug}/categories/{id}', [CategoryController::class, 'update'])
        ->name('tenant.categories.update');
    Route::delete('/{slug}/categories/{id}', [CategoryController::class, 'destroy'])
        ->name('tenant.categories.destroy');

    // Event Routes
    Route::get('/{slug}/events', [EventController::class, 'index'])
        ->name('tenant.events.index');
    Route::get('/{slug}/events/create', [EventController::class, 'create'])
        ->name('tenant.events.create');
    Route::post('/{slug}/events', [EventController::class, 'store'])
        ->name('tenant.events.store');
    Route::get('/{slug}/events/{event}', [EventController::class, 'show'])
        ->name('tenant.events.show');
    Route::get('/{slug}/events/{event}/edit', [EventController::class, 'edit'])
        ->name('tenant.events.edit');
    Route::put('/{slug}/events/{event}', [EventController::class, 'update'])
        ->name('tenant.events.update');
    Route::delete('/{slug}/events/{event}', [EventController::class, 'destroy'])
        ->name('tenant.events.destroy');
    
    // Tenant User Management Routes
    Route::get('/{slug}/users', [UserController::class, 'index'])
        ->name('tenant.users.index');
    Route::get('/{slug}/users/register', [RegisterController::class, 'showRegistrationForm'])
        ->name('tenant.register.form');
    Route::post('/{slug}/users/register', [RegisterController::class, 'register'])
        ->name('tenant.register');
    Route::get('/{slug}/users/register-success', [RegisterController::class, 'registrationSuccess'])
        ->name('tenant.register.success');
    
    // Logout
    Route::post('/{slug}/logout', [TenantLoginController::class, 'logout'])->name('tenant.logout');
});
