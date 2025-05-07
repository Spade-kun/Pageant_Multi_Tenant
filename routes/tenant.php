<?php

use App\Http\Controllers\Tenant\ContestantController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\EventController;
use App\Http\Controllers\Tenant\SubscriptionController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\RegisterController;
use App\Http\Controllers\Tenant\JudgeController;
use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\Tenant\EventAssignmentController;
use App\Http\Controllers\Tenant\UiSettingsController;
use App\Http\Controllers\Tenant\JudgeScoringController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\ScoreController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// Tenant Authentication
Route::middleware('guest:tenant')->group(function () {
    Route::get('/tenant/login', [TenantLoginController::class, 'showLoginForm'])->name('tenant.login');
    Route::post('/tenant/login', [TenantLoginController::class, 'login']);
});

// Tenant Owner/Organizer Registration
Route::get('/tenant/register', [TenantController::class, 'showRegistrationForm'])->name('register');
Route::post('/tenant/register', [TenantController::class, 'register']);
Route::get('/tenant/register/success', [TenantController::class, 'registrationSuccess'])->name('register.success');

// Tenant User Registration 
Route::get('/{slug}/users/register', [TenantController::class, 'showRegistrationForm'])->name('tenant.register.form');
Route::post('/{slug}/users/register', [TenantController::class, 'register'])->name('tenant.register');
Route::get('/{slug}/users/register-success', [TenantController::class, 'registrationSuccess'])->name('tenant.register.success');

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
    
    // Judge Dashboard
    Route::get('/{slug}/judge-dashboard', function ($slug) {
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

        if (!$user || $user->role !== 'judge') {
            return redirect()->route('tenant.dashboard', ['slug' => $slug]);
        }

        // Return the view without trying to fetch assignments
        return view('tenant.judge-dashboard', ['slug' => $slug]);
    })->name('tenant.judge.dashboard');

    // Judge Scoring Routes
    Route::prefix('{slug}/judges/scoring')->name('tenant.judges.scoring.')->group(function () {
        Route::get('/', [JudgeScoringController::class, 'index'])->name('index');
        Route::get('/score/{eventId}/{contestantId}/{categoryId}', [JudgeScoringController::class, 'score'])->name('score');
        Route::post('/store', [JudgeScoringController::class, 'store'])->name('store');
    });
    
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
    Route::get('/{slug}/categories/{id}', [CategoryController::class, 'show'])
        ->name('tenant.categories.show');
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
    
    // Event Assignment Routes
    Route::get('/{slug}/event-assignments', [EventAssignmentController::class, 'index'])
        ->name('tenant.event-assignments.index');
    Route::get('/{slug}/event-assignments/create', [EventAssignmentController::class, 'create'])
        ->name('tenant.event-assignments.create');
    Route::post('/{slug}/event-assignments', [EventAssignmentController::class, 'store'])
        ->name('tenant.event-assignments.store');
    Route::get('/{slug}/event-assignments/{id}', [EventAssignmentController::class, 'show'])
        ->name('tenant.event-assignments.show');
    Route::get('/{slug}/event-assignments/{id}/edit', [EventAssignmentController::class, 'edit'])
        ->name('tenant.event-assignments.edit');
    Route::put('/{slug}/event-assignments/{id}', [EventAssignmentController::class, 'update'])
        ->name('tenant.event-assignments.update');
    Route::delete('/{slug}/event-assignments/{id}', [EventAssignmentController::class, 'destroy'])
        ->name('tenant.event-assignments.destroy');
    
    // Judge Routes
    Route::get('/{slug}/judges', [JudgeController::class, 'index'])
        ->name('tenant.judges.index');
    Route::get('/{slug}/judges/create', [JudgeController::class, 'create'])
        ->name('tenant.judges.create');
    Route::post('/{slug}/judges', [JudgeController::class, 'store'])
        ->name('tenant.judges.store');
    Route::get('/{slug}/judges/{judge}', [JudgeController::class, 'show'])
        ->name('tenant.judges.show');
    Route::get('/{slug}/judges/{judge}/edit', [JudgeController::class, 'edit'])
        ->name('tenant.judges.edit');
    Route::put('/{slug}/judges/{judge}', [JudgeController::class, 'update'])
        ->name('tenant.judges.update');
    Route::delete('/{slug}/judges/{judge}', [JudgeController::class, 'destroy'])
        ->name('tenant.judges.destroy');
    
    // Tenant User Management Routes
    Route::get('/{slug}/users', [UserController::class, 'index'])
        ->name('tenant.users.index');
    
    // UI Settings Routes
    Route::get('/{slug}/ui-settings', [UiSettingsController::class, 'index'])
        ->name('tenant.ui-settings.index');
    Route::put('/{slug}/ui-settings', [UiSettingsController::class, 'update'])
        ->name('tenant.ui-settings.update');
    
    // System Update Routes (Owner Only)
    Route::middleware(['auth:tenant'])->group(function () {
        Route::get('/{slug}/updates', function($slug) {
            // Set up tenant database connection
            $tenant = \App\Models\Tenant::where('slug', $slug)->firstOrFail();
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

            if (auth()->guard('tenant')->user()->role !== 'owner') {
                return redirect()->back()->with('error', 'Only tenant owners can access system updates.');
            }
            return app()->make(App\Http\Controllers\Tenant\UpdateController::class)->index();
        })->name('tenant.updates.index');

        Route::get('/{slug}/updates/check', function($slug) {
            // Set up tenant database connection
            $tenant = \App\Models\Tenant::where('slug', $slug)->firstOrFail();
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

            if (auth()->guard('tenant')->user()->role !== 'owner') {
                return redirect()->back()->with('error', 'Only tenant owners can access system updates.');
            }
            return app()->make(App\Http\Controllers\Tenant\UpdateController::class)->check();
        })->name('tenant.updates.check');

        Route::get('/{slug}/updates/update', function($slug, \Illuminate\Http\Request $request) {
            // Set up tenant database connection
            $tenant = \App\Models\Tenant::where('slug', $slug)->firstOrFail();
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

            if (auth()->guard('tenant')->user()->role !== 'owner') {
                return redirect()->back()->with('error', 'Only tenant owners can access system updates.');
            }
            
            // Create an instance of the UpdateSystemRequest with the input from the request
            $updateRequest = new \App\Http\Requests\Tenant\UpdateSystemRequest();
            $updateRequest->replace($request->all());
            
            // Validate the request
            try {
                $validated = $updateRequest->validate();
                return app()->make(\App\Http\Controllers\Tenant\UpdateController::class)->update($updateRequest, $slug);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('tenant.updates.index', ['slug' => $slug])
                    ->withErrors($e->validator)
                    ->withInput();
            }
        })->name('tenant.updates.update');

        // Success page route
        Route::get('/{slug}/updates/success', function($slug) {
            // Set up tenant database connection
            $tenant = \App\Models\Tenant::where('slug', $slug)->firstOrFail();
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

            if (auth()->guard('tenant')->user()->role !== 'owner') {
                return redirect()->back()->with('error', 'Only tenant owners can access system updates.');
            }
            
            return app()->make(\App\Http\Controllers\Tenant\UpdateController::class)->success(request(), $slug);
        })->name('tenant.updates.success');
    });
    
    // Logout
    Route::post('/{slug}/logout', [TenantLoginController::class, 'logout'])->name('tenant.logout');
});

Route::get('/{slug}/reports/generate', [ReportController::class, 'generateReport'])->name('tenant.reports.generate');

// Score Routes
Route::get('/{slug}/scores', [ScoreController::class, 'index'])->name('tenant.scores.index');
Route::get('/{slug}/scores/{id}', [ScoreController::class, 'show'])->name('tenant.scores.show');

// Google OAuth Routes
Route::get('/tenant/auth/google', [TenantLoginController::class, 'redirectToGoogle'])->name('tenant.google.redirect');
Route::get('/tenant/auth/google/callback', [TenantLoginController::class, 'handleGoogleCallback'])->name('tenant.google.callback');

// Debug route - only available in local environment
if (app()->environment('local')) {
    Route::get('/tenant/auth/google/debug', [TenantLoginController::class, 'debugOAuth'])->name('tenant.google.debug');
}
