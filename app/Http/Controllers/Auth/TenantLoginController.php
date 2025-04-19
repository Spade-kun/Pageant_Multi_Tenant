<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            'password' => 'required',
        ]);

        // Find the tenant by checking which database contains the user
        $tenants = Tenant::all();
        $authenticated = false;
        $tenant = null;
        $user = null;

        foreach ($tenants as $t) {
            // Set the tenant database connection
            $databaseName = 'tenant_' . str_replace('-', '_', $t->slug);
            
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

            // Check if user exists in this tenant's database
            $user = DB::connection('tenant')
                ->table('users')
                ->where('email', $request->email)
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                $authenticated = true;
                $tenant = $t;
                break;
            }
        }

        if ($authenticated && $tenant && $user) {
            // Set the session tenant
            session(['tenant_id' => $tenant->id]);
            session(['tenant_slug' => $tenant->slug]);
            session(['user_role' => $user->role]);

            // Set the tenant database connection for the session
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

            // Store the user information in the session
            session(['tenant_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]]);

            // Manually log in the user
            Auth::guard('tenant')->loginUsingId($user->id);

            // Redirect based on user role with proper tenant slug
            if ($user->role === 'owner') {
                return redirect()->route('tenant.dashboard', ['slug' => $tenant->slug]);
            } else {
                return redirect()->route('tenant.user.dashboard', ['slug' => $tenant->slug]);
            }
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