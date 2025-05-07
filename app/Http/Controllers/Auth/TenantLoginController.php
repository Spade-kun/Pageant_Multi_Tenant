<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantLoginController extends Controller
{
    /**
     * The reCAPTCHA service
     *
     * @var RecaptchaService
     */
    protected $recaptchaService;

    /**
     * Create a new controller instance.
     *
     * @param RecaptchaService $recaptchaService
     */
    public function __construct(RecaptchaService $recaptchaService)
    {
        $this->recaptchaService = $recaptchaService;
    }

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
            'g-recaptcha-response' => 'required',
        ]);

        // Verify the reCAPTCHA token
        if (!$this->recaptchaService->verifyV3($request->input('g-recaptcha-response'), 'login')) {
            return back()->withErrors([
                'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.',
            ])->onlyInput('email');
        }

        \Log::info('Login attempt for: ' . $request->email);

        // First, try to find the tenant user in the main database
        $tenantUser = TenantUser::where('email', $request->email)->first();
        $tenant = null;
        $user = null;
        $isOwner = false;

        if ($tenantUser) {
            // User found in central database (likely an owner)
            $tenant = $tenantUser->tenant;
            $isOwner = true;
            \Log::info('User found in central database as owner', [
                'email' => $request->email,
                'tenant_slug' => $tenant ? $tenant->slug : 'null'
            ]);
        }

        // Set up tenant database connection
        if ($tenant) {
            // If we found a tenant user, use their tenant
            $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
        } else {
            // If not found in central database, we need to check all tenant databases
            // This is less efficient but necessary for users registered directly in tenant databases
            $tenants = Tenant::where('status', 'approved')->get();
            \Log::info('Searching in tenant databases', [
                'email' => $request->email,
                'tenant_count' => $tenants->count()
            ]);
            
            foreach ($tenants as $potentialTenant) {
                $databaseName = 'tenant_' . str_replace('-', '_', $potentialTenant->slug);
                
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

                // Check if user exists in this tenant database
                $potentialUser = DB::connection('tenant')
                    ->table('users')
                    ->where('email', $request->email)
                    ->first();

                if ($potentialUser) {
                    $user = $potentialUser;
                    $tenant = $potentialTenant;
                    \Log::info('User found in tenant database', [
                        'email' => $request->email,
                        'tenant_slug' => $tenant->slug,
                        'role' => $user->role
                    ]);
                    break;
                }
            }
        }

        // If we found a tenant user but not a tenant, or if we didn't find a user at all
        if (($tenantUser && !$tenant) || (!$tenantUser && !$user)) {
            return back()->withErrors([
                'email' => 'The provided email does not match our records.',
            ])->onlyInput('email');
        }

        // If tenant is not approved
        if (!$tenant || !$tenant->isApproved()) {
            return back()->withErrors([
                'email' => 'Your account is not approved or the tenant is not active.',
            ])->onlyInput('email');
        }

        // If we found a tenant user but not a user in the tenant database, set up the connection
        if ($tenantUser && !$user) {
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

            // Get user from tenant database
            $user = DB::connection('tenant')
                ->table('users')
                ->where('email', $request->email)
                ->first();
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ])->onlyInput('email');
        }

        // Set the session tenant
        session(['tenant_id' => $tenant->id]);
        session(['tenant_slug' => $tenant->slug]);
        session(['user_role' => $user->role]);

        // Store the user information in the session
        session(['tenant_user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]]);

        // If this is an owner (found in tenant_users), use that for authentication
        if ($isOwner) {
            \Log::info('Authenticating owner with central database record', [
                'email' => $user->email,
                'role' => $user->role
            ]);
            Auth::guard('tenant')->login($tenantUser);
        } else {
            // For users only in tenant database, create a temporary TenantUser for authentication
            \Log::info('Creating temporary TenantUser for auth', [
                'email' => $user->email,
                'role' => $user->role,
                'tenant_id' => $tenant->id
            ]);
            
            // Create or find a tenant user in the central database for this user
            $tempTenantUser = TenantUser::firstOrCreate(
                ['email' => $user->email],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            );
            
            // The password isn't being saved as it's not included in the TenantUser fillable
            // We just use it for the login session
            
            Auth::guard('tenant')->login($tempTenantUser);
            
            \Log::info('Successfully authenticated user', [
                'email' => $tempTenantUser->email,
                'id' => $tempTenantUser->id,
                'tenant_id' => $tenant->id
            ]);
        }

        // Redirect based on user role
        if ($user->role === 'owner') {
            \Log::info('Redirecting owner to dashboard', [
                'role' => $user->role,
                'tenant_slug' => $tenant->slug
            ]);
            return redirect('/' . $tenant->slug . '/dashboard');
        } else if ($user->role === 'judge') {
            \Log::info('Redirecting judge to dashboard', [
                'role' => $user->role,
                'tenant_slug' => $tenant->slug
            ]);
            return redirect('/' . $tenant->slug . '/judge-dashboard');
        } else {
            \Log::info('Redirecting user to dashboard', [
                'role' => $user->role,
                'tenant_slug' => $tenant->slug
            ]);
            return redirect('/' . $tenant->slug . '/user-dashboard');
        }
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/tenant/login');
    }
} 