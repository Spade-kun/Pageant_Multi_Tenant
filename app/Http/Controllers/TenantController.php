<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\TenantStatusNotification;

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

            // Create the tenant
            $tenant = Tenant::create([
                'pageant_name' => $validated['pageant_name'],
                'slug' => $validated['slug'],
                'status' => 'pending',
                'owner_id' => $tenantUser->id,
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

    /**
     * Show success page after registration.
     */
    public function registrationSuccess()
    {
        return view('tenant.register-success');
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

        // Load the owner relationship
        $tenant->load(['users' => function($query) {
            $query->where('role', 'owner');
        }]);

        // Get the owner user
        $owner = $tenant->users->where('role', 'owner')->first();

        if (!$owner) {
            return back()->with('error', 'Cannot approve tenant: Owner not found.');
        }

        $tenant->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Send approval email to tenant owner
        Mail::to($owner->email)->send(new TenantStatusNotification($tenant, 'approved', $request->message));

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant has been approved successfully.');
    }
} 