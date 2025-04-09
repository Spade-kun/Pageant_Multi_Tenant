<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class TenantController extends Controller
{
    /**
     * Show the tenant registration form.
     */
    public function showRegistrationForm()
    {
        return view('tenant.register');
    }

    /**
     * Handle tenant registration.
     */
    public function register(Request $request)
    {
        try {
            // Log the registration attempt
            \Log::info('Tenant registration attempt', ['email' => $request->email]);

            // Validate the request
            $validated = $request->validate([
                'pageant_name' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:tenant_users'],
                'age' => ['required', 'string', 'max:3'],
                'gender' => ['required', 'string', 'max:20'],
                'address' => ['required', 'string'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            // Log successful validation
            \Log::info('Validation passed', ['pageant_name' => $request->pageant_name]);

            // Create a slug based on pageant name (remove spaces and special characters)
            $slug = Str::slug($request->pageant_name);
            
            // Check if the slug already exists and append number if needed
            $originalSlug = $slug;
            $count = 1;
            
            while (Tenant::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }

            // Generate a database name (ensure it's lowercase and only contains valid characters)
            $databaseName = 'tenant_' . Str::lower(preg_replace('/[^a-zA-Z0-9-]/', '', $slug));

            // Log the generated slug and database name
            \Log::info('Generated slug and database name', [
                'slug' => $slug,
                'database_name' => $databaseName
            ]);

            DB::beginTransaction();

            try {
                // First, create the tenant user
                $tenantUser = TenantUser::create([
                    'tenant_id' => null, // Will be updated after tenant creation
                    'name' => $request->name,
                    'email' => $request->email,
                    'age' => $request->age,
                    'gender' => $request->gender,
                    'address' => $request->address,
                    'role' => 'owner',
                    'password' => Hash::make($request->password),
                ]);

                if (!$tenantUser) {
                    throw new \Exception('Failed to create tenant user record.');
                }

                // Log successful tenant user creation
                \Log::info('Tenant user created', ['user_id' => $tenantUser->id]);

                // Now create the tenant with the correct owner_id
                $tenant = Tenant::create([
                    'pageant_name' => $request->pageant_name,
                    'slug' => $slug,
                    'owner_id' => $tenantUser->id, // Set the owner_id to the newly created user
                    'status' => 'pending',
                    'database_name' => $databaseName,
                ]);

                if (!$tenant) {
                    throw new \Exception('Failed to create tenant record.');
                }

                // Log successful tenant creation
                \Log::info('Tenant created', ['tenant_id' => $tenant->id]);

                // Update the tenant_user with the tenant_id
                $tenantUser->tenant_id = $tenant->id;
                if (!$tenantUser->save()) {
                    throw new \Exception('Failed to update tenant user with tenant ID.');
                }

                DB::commit();

                // Log successful registration completion
                \Log::info('Tenant registration completed successfully', [
                    'tenant_id' => $tenant->id,
                    'user_id' => $tenantUser->id
                ]);

                // Redirect to success page with tenant information
                return redirect()->route('register.success')
                    ->with('success', 'Registration successful! Please wait for admin approval.')
                    ->with('tenant', [
                        'pageant_name' => $tenant->pageant_name,
                        'slug' => $tenant->slug
                    ]);

            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Tenant registration failed during database operations: ' . $e->getMessage());
                \Log::error($e->getTraceAsString());
                return back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->withErrors(['error' => 'Registration failed. Please try again. Error: ' . $e->getMessage()]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Validation failed', ['errors' => $e->errors()]);
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors($e->validator->errors());
        } catch (\Exception $e) {
            \Log::error('Unexpected error in tenant registration: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    /**
     * Show success page after registration.
     */
    public function registrationSuccess()
    {
        return view('tenant.register-success');
    }

    /**
     * Show login form for tenant users.
     */
    public function showLoginForm()
    {
        return view('tenant.login');
    }
} 