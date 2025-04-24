<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Mail\TenantUserRegistrationMail;

class RegisterController extends Controller
{
    public function showRegistrationForm($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        return view('tenant.auth.register', compact('tenant'));
    }

    public function register(Request $request, $slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        // Log the attempt to help with debugging
        Log::info('User registration attempt for tenant', [
            'tenant_slug' => $slug,
            'email' => $request->email
        ]);

        // Set up tenant database connection first to validate email uniqueness
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

        // Check if email already exists in tenant database
        $existingUser = DB::connection('tenant')
            ->table('users')
            ->where('email', $request->email)
            ->first();

        if ($existingUser) {
            return back()->withErrors([
                'email' => 'The email address is already registered with this pageant.'
            ])->withInput();
        }

        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            // Generate temporary password
            $tempPassword = Str::random(10);

            // Create user in tenant database
            $userId = DB::connection('tenant')->table('users')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($tempPassword),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log successful tenant database user creation
            Log::info('User created in tenant database', [
                'tenant_slug' => $slug,
                'user_id' => $userId,
                'email' => $request->email
            ]);

            // Also create the user in the main tenant_users table
            $tenantUser = \App\Models\TenantUser::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'user',
            ]);

            // Log successful main database user creation
            Log::info('User created in main database', [
                'tenant_id' => $tenant->id,
                'tenant_user_id' => $tenantUser->id,
                'email' => $request->email
            ]);

            // Save temp password to session for development/testing purposes
            session(['temp_password' => $tempPassword]);

            // Send welcome email with temporary password
            try {
                Mail::to($request->email)->send(new TenantUserRegistrationMail(
                    $request->name,
                    $request->email,
                    $tempPassword,
                    $tenant->pageant_name
                ));

                Log::info('Welcome email sent', ['email' => $request->email]);
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email', [
                    'email' => $request->email,
                    'error' => $e->getMessage()
                ]);
                // Continue registration process even if email fails
            }

            return redirect()->route('tenant.register.success', ['slug' => $slug])
                ->with('success', 'Registration successful! Please check your email for login credentials.');

        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'tenant_slug' => $slug,
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'error' => 'Registration failed: ' . $e->getMessage()
            ])->withInput();
        }
    }
    
    /**
     * Show registration success page with temporary password (for development)
     */
    public function registrationSuccess($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $tempPassword = session('temp_password', null);
        
        return view('tenant.auth.register-success', [
            'tenant' => $tenant,
            'tempPassword' => $tempPassword
        ]);
    }
} 