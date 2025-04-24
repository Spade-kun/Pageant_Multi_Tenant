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

// // Common Tenant Authentication - Available on both ports
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
