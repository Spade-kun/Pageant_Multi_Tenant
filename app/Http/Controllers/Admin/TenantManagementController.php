<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TenantStatusNotification;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class TenantManagementController extends Controller
{
    /**
     * Display a listing of all tenants.
     */
    public function index()
    {
        $tenants = Tenant::with(['users' => function($query) {
            $query->where('role', 'owner');
        }])->get();
        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Show the detailed view of a tenant.
     */
    public function show(Tenant $tenant)
    {
        return view('admin.tenants.show', compact('tenant'));
    }

    /**
     * Generate a temporary password for the tenant user
     */
    private function generateTemporaryPassword(): string
    {
        $length = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**
     * Approve a tenant and send notification email.
     * This method handles both approving new tenants and sending/resending 
     * notification emails for already approved tenants.
     */
    public function approve(Tenant $tenant)
    {
        try {
            \Log::info('Starting tenant approval process', ['tenant_id' => $tenant->id]);
            
            DB::beginTransaction();
            \Log::info('Transaction started');

            // Get the owner user from the tenant users
            $ownerUser = $tenant->users()->where('role', 'owner')->first();
            \Log::info('Owner user found', ['owner_id' => $ownerUser ? $ownerUser->id : null]);
            
            if (!$ownerUser) {
                throw new \Exception('Owner user not found for tenant.');
            }

            // Update owner_id if it's NULL
            if ($tenant->owner_id === null) {
                $tenant->owner_id = $ownerUser->id;
                \Log::info('Updated owner_id', ['owner_id' => $ownerUser->id]);
            }

                // Get the database name from the tenant and ensure it's valid
                $databaseName = $tenant->database_name ?? 'tenant_' . str_replace('-', '_', $tenant->slug);
            \Log::info('Database name determined', ['database_name' => $databaseName]);
                
                // Update the tenant record
                $tenant->update([
                    'status' => 'approved',
                    'database_name' => $databaseName,
                    'owner_id' => $tenant->owner_id ?? $ownerUser->id,
                ]);
            \Log::info('Tenant record updated');
                
                // Create and set up the tenant database
                $this->setupTenantDatabase($tenant, $databaseName, $ownerUser);
            \Log::info('Tenant database setup completed');

            DB::commit();
            \Log::info('Transaction committed');
            
            // Send approval email to the tenant owner
            $this->sendApprovalEmail($tenant, $ownerUser, false);
            \Log::info('Approval email sent');
            
            return back()->with('success', 'Tenant approved successfully. Database created and approval email sent.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to approve tenant', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tenant_id' => $tenant->id
            ]);
            return back()->with('error', 'Failed to approve tenant: ' . $e->getMessage());
        }
    }

    /**
     * Send approval email to tenant owner
     */
    public function sendApprovalEmail(Tenant $tenant, $ownerUser, $isNewTenant)
    {
        try {
            \Log::info('Starting to send approval email', [
                'tenant_id' => $tenant->id,
                'owner_email' => $ownerUser->email
            ]);

            // Get the temporary password from the tenant database
            $user = DB::connection('tenant')
                ->table('users')
                ->where('email', $ownerUser->email)
                ->first();

            if (!$user) {
                throw new \Exception('User not found in tenant database.');
            }

            \Log::info('User found in tenant database', ['user_id' => $user->id]);

            // Send the email
            Mail::to($ownerUser->email)
                ->send(new TenantStatusNotification($tenant, 'approved', null, $user->password));

            \Log::info('Email sent successfully', [
                'tenant_id' => $tenant->id,
                'owner_email' => $ownerUser->email
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send approval email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw the exception, just log it and continue
            // This way the approval process won't fail if email sending fails
        }
    }
    
    /**
     * Helper function to set up the tenant database
     */
    private function setupTenantDatabase(Tenant $tenant, $databaseName, $ownerUser)
    {
        try {
            \Log::info('Setting up tenant database', ['database_name' => $databaseName]);
        
        // Set the database configuration to the new tenant database
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
            \Log::info('Database configuration set');
        
        // Clear the database connection cache
        DB::purge('tenant');
            \Log::info('Database connection purged');
        
        // Run migrations on the tenant database
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
            \Log::info('Migrations completed');
            
            // Create owner user in tenant database with temporary password
            $this->createTenantUser($tenant, $ownerUser, $this->generateTemporaryPassword());
            \Log::info('Tenant user created');
        } catch (\Exception $e) {
            \Log::error('Failed to setup tenant database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Helper function to create tenant user in tenant database
     */
    private function createTenantUser(Tenant $tenant, $ownerUser, $temporaryPassword)
    {
            // Create owner user in tenant database
        DB::connection('tenant')->table('users')->insert([
                'name' => $ownerUser->name,
                'email' => $ownerUser->email,
            'password' => Hash::make($temporaryPassword),
                'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create user profile
        $userId = DB::connection('tenant')->table('users')
            ->where('email', $ownerUser->email)
            ->value('id');

        if ($userId) {
                    DB::connection('tenant')->table('user_profiles')->insert([
                        'user_id' => $userId,
                        'age' => $ownerUser->age,
                        'gender' => $ownerUser->gender,
                        'address' => $ownerUser->address,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
        }
    }

    /**
     * Show rejection form
     */
    public function showRejectForm(Tenant $tenant)
    {
        if ($tenant->isRejected()) {
            return back()->with('error', 'This tenant is already rejected.');
        }

        return view('admin.tenants.reject-form', compact('tenant'));
    }

    /**
     * Reject a tenant.
     */
    public function reject(Request $request, Tenant $tenant)
    {
        if ($tenant->isRejected()) {
            return back()->with('error', 'This tenant is already rejected.');
        }
        
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Get the owner user from the tenant users
            $ownerUser = $tenant->users()->where('role', 'owner')->first();
            
            if (!$ownerUser) {
                throw new \Exception('Owner user not found for tenant.');
            }
            
            // Update owner_id if it's NULL
            if ($tenant->owner_id === null) {
                $tenant->owner_id = $ownerUser->id;
            }
            
            $tenant->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'owner_id' => $tenant->owner_id ?? $ownerUser->id, // Ensure owner_id is set
            ]);
            
            DB::commit();
            
            // Send email notification to the tenant owner
            try {
                if ($ownerUser && $ownerUser->email) {
                    Mail::to($ownerUser->email)
                        ->send(new TenantStatusNotification($tenant, 'rejected', $validated['rejection_reason']));
                    
                    // Log successful email
                    \Log::info('Rejection email sent to owner', [
                        'tenant_id' => $tenant->id,
                        'owner_email' => $ownerUser->email,
                        'reason' => $validated['rejection_reason']
                    ]);
                }
            } catch (\Exception $emailException) {
                // Log email error but don't fail the rejection process
                \Log::error('Failed to send rejection email: ' . $emailException->getMessage());
            }
            
            return redirect()->route('admin.tenants.index')
                ->with('success', 'Tenant rejected successfully. Email notification has been sent.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to reject tenant: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Failed to reject tenant: ' . $e->getMessage());
        }
    }

    /**
     * Display the tenant access management page.
     */
    public function access()
    {
        $tenants = Tenant::all();
        return view('admin.tenants.access', compact('tenants'));
    }

    /**
     * Enable access for the specified tenant.
     */
    public function enable(Tenant $tenant)
    {
        $tenant->update(['is_active' => true]);
        return redirect()->back()->with('success', 'Tenant access has been enabled.');
    }

    /**
     * Disable access for the specified tenant.
     */
    public function disable(Tenant $tenant)
    {
        $tenant->update(['is_active' => false]);
        return redirect()->back()->with('success', 'Tenant access has been disabled.');
    }
} 