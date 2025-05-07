<?php

use App\Http\Controllers\Admin\TenantManagementController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PlanRequestController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

// Admin Authentication
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);
    
    // In admin.php, we define a route to handle OAuth logins that might get redirected here
    Route::get('/auth/google/callback', function(Illuminate\Http\Request $request) {
        // Forward to the callback handler in web.php
        return redirect("http://127.0.0.1:8001/auth/google/callback?" . $request->getQueryString());
    });
});

// Admin Dashboard and protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::prefix('admin')->group(function () {
        // Tenant Management
        Route::get('/tenants', [TenantManagementController::class, 'index'])->name('admin.tenants.index');
        Route::get('/tenants/access', [TenantManagementController::class, 'access'])->name('admin.tenants.access');
        Route::get('/tenants/{tenant}', [TenantManagementController::class, 'show'])->name('admin.tenants.show');
        Route::get('/tenants/{tenant}/approve-form', [TenantController::class, 'showApproveForm'])->name('admin.tenants.approve-form');
        Route::put('/tenants/{tenant}/approve', [TenantController::class, 'approve'])->name('admin.tenants.approve');
        Route::get('/tenants/{tenant}/reject', [TenantManagementController::class, 'showRejectForm'])->name('admin.tenants.reject.form');
        Route::put('/tenants/{tenant}/reject', [TenantManagementController::class, 'reject'])->name('admin.tenants.reject');
        Route::put('/tenants/{tenant}/enable', [TenantManagementController::class, 'enable'])->name('admin.tenants.enable');
        Route::put('/tenants/{tenant}/disable', [TenantManagementController::class, 'disable'])->name('admin.tenants.disable');
        Route::post('/tenants/{tenant}/send-approval-email', [TenantManagementController::class, 'sendApprovalEmail'])->name('admin.tenants.send-approval-email');
        
        // Admin Plan Management Routes
        Route::get('/plans', [PlanController::class, 'index'])->name('admin.plans.index');
        Route::get('/plans/create', [PlanController::class, 'create'])->name('admin.plans.create');
        Route::post('/plans', [PlanController::class, 'store'])->name('admin.plans.store');
        Route::get('/plans/{plan}', [PlanController::class, 'show'])->name('admin.plans.show');
        Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('admin.plans.edit');
        Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('admin.plans.update');
        Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('admin.plans.destroy');

        // Plan Requests Routes
        Route::get('/requests', [PlanRequestController::class, 'index'])->name('admin.requests.index');
        Route::get('/requests/{request}', [PlanRequestController::class, 'show'])->name('admin.requests.show');
        Route::put('/requests/{request}/approve', [PlanRequestController::class, 'approve'])->name('admin.requests.approve');
        Route::put('/requests/{request}/reject', [PlanRequestController::class, 'reject'])->name('admin.requests.reject');
        Route::get('/requests/{tenant}/change-plan', [PlanRequestController::class, 'showChangePlan'])->name('admin.requests.change-plan');
        Route::put('/requests/{tenant}/update-plan', [PlanRequestController::class, 'updatePlan'])->name('admin.requests.update-plan');
    });
    
    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy']);
});

// Note: Google OAuth routes are defined in web.php

require __DIR__ . '/auth.php';