<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use App\Mail\TenantStatusNotification;
use App\Mail\TenantUserRegistrationMail;

class TenantController extends Controller
{
    /**
     * Show the tenant registration form.
     */
    public function showRegistrationForm(Request $request, $slug = null)
    {
        if ($slug) {
            // This is a tenant user registration
            $tenant = Tenant::where('slug', $slug)->firstOrFail();
            return view('tenant.auth.register', compact('tenant'));
        } else {
            // This is a tenant owner registration
        return view('tenant.register');
        }
    }

    /**
     * Handle tenant registration.
     */
    public function register(Request $request, $slug = null)
    {
        if ($slug) {
            // This is a tenant user registration
            $tenant = Tenant::where('slug', $slug)->firstOrFail();
            
            // Set up tenant database connection
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

            // Validate the request
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:tenant.users'],
                'phone' => ['nullable', 'string', 'max:20'],
            ]);

            try {
                DB::beginTransaction();

                // Generate temporary password
                $temporaryPassword = Str::random(10);
                $hashedPassword = Hash::make($temporaryPassword);

                // Create user in tenant database
                DB::connection('tenant')->table('users')->insert([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => $hashedPassword,
                    'role' => 'user',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                // Send registration email
                Mail::to($validated['email'])->send(new TenantUserRegistrationMail(
                    $validated['name'],
                    $validated['email'],
                    $temporaryPassword,
                    $tenant->pageant_name
                ));

                return redirect()->route('tenant.register.success', ['slug' => $slug])
                    ->with('success', 'Registration successful! Please check your email for login details.')
                    ->with('tempPassword', $temporaryPassword);

            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Tenant user registration failed: ' . $e->getMessage());
                return back()
                    ->withInput($request->all())
                    ->withErrors(['error' => 'Registration failed. Please try again.']);
            }
        } else {
            // This is a tenant owner registration
        try {
            // Log the registration attempt
            \Log::info('Tenant registration attempt', ['email' => $request->email]);

            // Validate the request
            $validated = $request->validate([
                'pageant_name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', 'unique:tenants,slug', 'regex:/^[a-z0-9-]+$/'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:tenant_users'],
                'age' => ['required', 'string', 'max:3'],
                'gender' => ['required', 'string', 'max:20'],
                'address' => ['required', 'string'],
            ]);

            DB::beginTransaction();

            // Create tenant user first
                $tenantUser = TenantUser::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'age' => $validated['age'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                    'role' => 'owner',
            ]);

                $tenant = Tenant::create([
                'pageant_name' => $validated['pageant_name'],
                'slug' => $validated['slug'],
                    'status' => 'pending',
                'owner_id' => $tenantUser->id,
                'database_name' => 'tenant_' . str_replace('-', '_', $validated['slug']),
            ]);

                // Update the tenant_user with the tenant_id
                $tenantUser->tenant_id = $tenant->id;
            $tenantUser->save();

                DB::commit();

                return redirect()->route('register.success')
                    ->with('success', 'Registration successful! Please wait for admin approval.')
                    ->with('tenant', [
                        'pageant_name' => $tenant->pageant_name,
                        'slug' => $tenant->slug
                    ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput($request->all())
                ->withErrors($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Tenant registration failed: ' . $e->getMessage());
            return back()
                ->withInput($request->all())
                ->withErrors(['error' => 'Registration failed. Please try again.']);
            }
        }
    }

    /**
     * Show success page after registration.
     */
    public function registrationSuccess($slug = null)
    {
        if ($slug) {
            // This is a tenant user registration success
            $tenant = Tenant::where('slug', $slug)->firstOrFail();
            $tempPassword = session('tempPassword');
            return view('tenant.auth.register-success', compact('tenant', 'tempPassword'));
        } else {
            // This is a tenant owner registration success
        return view('tenant.register-success');
        }
    }

    /**
     * Show approve form for a tenant.
     */
    public function showApproveForm(Tenant $tenant)
    {
        return view('admin.tenants.approve-form', compact('tenant'));
    }

    /**
     * Approve a tenant.
     */
    public function approve(Request $request, Tenant $tenant)
    {
        $request->validate([
            'message' => 'required|string|min:10',
        ]);

        try {
            DB::beginTransaction();

            // Load the owner relationship
            $tenant->load(['users' => function($query) {
                $query->where('role', 'owner');
            }]);

            // Get the owner user
            $owner = $tenant->users->where('role', 'owner')->first();

            if (!$owner) {
                return back()->with('error', 'Cannot approve tenant: Owner not found.');
            }

            // Generate temporary password
            $temporaryPassword = Str::random(10);
            $hashedPassword = Hash::make($temporaryPassword);

            // Update tenant status
            $tenant->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // Set up tenant database connection
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

            // Run migrations on the tenant database
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            // Create owner user in tenant database
            DB::connection('tenant')->table('users')->insert([
                'name' => $owner->name,
                'email' => $owner->email,
                'password' => $hashedPassword,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create user profile in tenant database
            $userId = DB::connection('tenant')->table('users')
                ->where('email', $owner->email)
                ->value('id');

            if ($userId) {
                DB::connection('tenant')->table('user_profiles')->insert([
                    'user_id' => $userId,
                    'age' => $owner->age,
                    'gender' => $owner->gender,
                    'address' => $owner->address,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            // Send approval email to tenant owner
            Mail::to($owner->email)->send(new TenantStatusNotification($tenant, 'approved', $request->message, $temporaryPassword));

            return redirect()->route('admin.tenants.index')
                ->with('success', 'Tenant has been approved successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Tenant approval failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve tenant: ' . $e->getMessage());
        }
    }
} 