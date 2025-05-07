<?php

use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Default route with automatic redirect based on port
Route::get('/', function (Request $request) {
    // Check if this is the admin instance (port 8001)
    if ($request->server('SERVER_PORT') == 8001) {
        // Admin port - redirect to admin login
        return redirect()->route('admin.login');
    } else {
        // Tenant port - redirect to tenant login
        return redirect()->route('tenant.login');
    }
});

// Common Tenant Authentication - Available on both ports
// Route::middleware('guest:tenant')->group(function () {
//     Route::get('/tenant/login', [TenantLoginController::class, 'showLoginForm'])->name('tenant.login');
//     Route::post('/tenant/login', [TenantLoginController::class, 'login']);
// });

// Include admin or tenant routes based on port
Route::group([], function () {
    // Determine if we're on the admin port
    $isAdminPort = request()->server('SERVER_PORT') == 8001;
    
    if ($isAdminPort) {
        require __DIR__.'/admin.php';
    } else {
        require __DIR__.'/tenant.php';
    }
});

// Auth routes (these will be for admin authentication through the default routes file)
require __DIR__.'/auth.php';

// Google OAuth Routes for Admin - accessible on both ports
Route::get('/auth/google', [App\Http\Controllers\Auth\LoginController::class, 'redirectToGoogle'])->name('admin.google.redirect');

// Specialized callback that handles port detection
Route::get('/auth/google/callback', function (Illuminate\Http\Request $request) {
    // Detect which port we're on - default to admin port for Google auth
    $port = $request->server('SERVER_PORT') ?: '8001';
    
    // If we're on port 8001 (admin), set session flag to ensure proper redirection
    if ($port == '8001') {
        session(['is_admin_oauth' => true]);
    }
    
    // Pass to the normal handler
    return app()->make(App\Http\Controllers\Auth\LoginController::class)->handleGoogleCallback($request);
})->name('admin.google.callback');

// Debug route for OAuth - only available in local environment
if (app()->environment('local')) {
    Route::get('/auth/google/debug', [App\Http\Controllers\Auth\LoginController::class, 'debugOAuth'])->name('admin.google.debug');
}
