<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class TenantLoginController extends Controller
{
    /**
     * Show the login form for tenants.
     */
    public function showLoginForm()
    {
        return view('auth.tenant-login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find the tenant user
        $tenantUser = TenantUser::where('email', $request->email)->first();
        
        if (!$tenantUser) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }
        
        // Check if the tenant is approved
        $tenant = $tenantUser->tenant;
        
        if (!$tenant->isApproved()) {
            return back()->withErrors([
                'email' => 'Your tenant account is not yet approved. Please wait for admin approval.',
            ])->onlyInput('email');
        }

        // Check if the tenant is active
        if (!$tenant->is_active) {
            return back()->withErrors([
                'email' => 'Access to this tenant is currently disabled. Please contact the administrator for assistance.',
            ])->onlyInput('email');
        }
        
        // Attempt to log in
        if (Auth::guard('tenant')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            
            // Set the tenant database connection
            Config::set('database.connections.tenant', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $tenant->database_name,
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
            ]);
            
            // Redirect to the tenant dashboard with slug
            return redirect()->route('tenant.dashboard', ['slug' => $tenant->slug]);
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::guard('tenant')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('tenant.login');
    }
} 