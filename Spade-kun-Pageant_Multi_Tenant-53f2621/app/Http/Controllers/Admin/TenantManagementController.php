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
     * Approve a tenant and send notification email.
     * This method handles both approving new tenants and sending/resending 
     * notification emails for already approved tenants.
     */
    public function approve(Tenant $tenant)
    {
        // Check if tenant is already approved
        $alreadyApproved = $tenant->isApproved();
        
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

            // If not already approved, set up the tenant database
            if (!$alreadyApproved) {
                // Get the database name from the tenant and ensure it's valid
                $databaseName = $tenant->database_name ?? 'tenant_' . str_replace('-', '_', $tenant->slug);
                
                // Update the tenant record
                $tenant->update([
                    'status' => 'approved',
                    'database_name' => $databaseName,
                    'owner_id' => $tenant->owner_id ?? $ownerUser->id,
                ]);
                
                // Create and set up the tenant database
                $this->setupTenantDatabase($tenant, $databaseName, $ownerUser);
            } else {
                // Just ensure the owner_id is set if already approved
                $tenant->update([
                    'owner_id' => $tenant->owner_id ?? $ownerUser->id,
                ]);
            }

            DB::commit();
            
            // ALWAYS send email notification to the tenant owner
            $this->sendApprovalEmail($tenant, $ownerUser, $alreadyApproved);
            
            $successMessage = $alreadyApproved
                ? 'Approval email sent successfully.'
                : 'Tenant approved successfully. Database created and owner data transferred.';
                
            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to approve tenant: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Failed to approve tenant: ' . $e->getMessage());
        }
    }
    
    /**
     * Helper function to set up the tenant database
     */
    private function setupTenantDatabase(Tenant $tenant, string $databaseName, $ownerUser)
    {
        // Create a new database for the tenant
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}`");
        
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
        
        // Clear the database connection cache
        DB::purge('tenant');
        
        // Run migrations on the tenant database
        try {
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Migration warning (tables might already exist): ' . $e->getMessage());
        }
        
        // Create owner user in tenant database
        $this->createTenantUser($tenant, $ownerUser);
    }
    
    /**
     * Helper function to create tenant user
     */
    private function createTenantUser(Tenant $tenant, $ownerUser)
    {
        // Check if user already exists in tenant database
        $existingUser = DB::connection('tenant')
            ->table('users')
            ->where('email', $ownerUser->email)
            ->first();

        if (!$existingUser) {
            // Create owner user in tenant database
            $userId = DB::connection('tenant')->table('users')->insertGetId([
                'name' => $ownerUser->name,
                'email' => $ownerUser->email,
                'password' => $ownerUser->password,
                'role' => 'owner',
                'email_verified_at' => $ownerUser->email_verified_at,
                'remember_token' => $ownerUser->remember_token,
                'created_at' => $ownerUser->created_at,
                'updated_at' => $ownerUser->updated_at,
            ]);

            // Check if user_profiles table exists
            if (Schema::connection('tenant')->hasTable('user_profiles')) {
                // Check if profile already exists
                $existingProfile = DB::connection('tenant')
                    ->table('user_profiles')
                    ->where('user_id', $userId)
                    ->first();

                if (!$existingProfile) {
                    // Create owner's user profile
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
        }
    }
    
    /**
     * Helper function to send approval email
     */
    private function sendApprovalEmail(Tenant $tenant, $ownerUser, bool $wasAlreadyApproved)
    {
        try {
            if ($ownerUser && $ownerUser->email) {
                Mail::to($ownerUser->email)
                    ->send(new TenantStatusNotification($tenant, 'approved'));
                
                // Log successful email
                \Log::info('Approval email sent to owner', [
                    'tenant_id' => $tenant->id,
                    'owner_email' => $ownerUser->email,
                    'was_already_approved' => $wasAlreadyApproved
                ]);
            }
        } catch (\Exception $emailException) {
            // Log email error but don't fail the approval process
            \Log::error('Failed to send approval email: ' . $emailException->getMessage());
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