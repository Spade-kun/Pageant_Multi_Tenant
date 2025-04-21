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
                'slug' => ['required', 'string', 'max:255', 'unique:tenants,slug', 'regex:/^[a-z0-9-]+$/'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:tenant_users'],
                'age' => ['required', 'string', 'max:3'],
                'gender' => ['required', 'string', 'max:20'],
                'address' => ['required', 'string'],
            ]);

            // Log successful validation
            \Log::info('Validation passed', ['pageant_name' => $request->pageant_name]);

            // Use the provided slug
            $slug = $request->slug;
            
            // Check if the slug already exists
            if (Tenant::where('slug', $slug)->exists()) {
                return back()->withErrors(['slug' => 'This URL slug is already taken. Please choose another one.']);
            }

            DB::beginTransaction();

            // Create tenant user first
            $tenantUser = \App\Models\TenantUser::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'age' => $validated['age'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'role' => 'owner',
            ]);

            // Create the tenant
            $tenant = Tenant::create([
                'pageant_name' => $validated['pageant_name'],
                'slug' => $slug,
                'status' => 'pending',
                'owner_id' => $tenantUser->id,
            ]);

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

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Validation failed', ['errors' => $e->errors()]);
            return back()
                ->withInput($request->all())
                ->withErrors($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Tenant registration failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()
                ->withInput($request->all())
                ->withErrors(['error' => 'Registration failed. Please try again. Error: ' . $e->getMessage()]);
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
        return view('auth.tenant-login');
    }
} 