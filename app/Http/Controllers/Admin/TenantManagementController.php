<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
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
     * Approve a tenant.
     */
    public function approve(Tenant $tenant)
    {
        if ($tenant->isApproved()) {
            return back()->with('error', 'This tenant is already approved.');
        }

        // Get the database name from the tenant and ensure it's valid
        $databaseName = $tenant->database_name ?? 'tenant_' . str_replace('-', '_', $tenant->slug);
        
        try {
            DB::beginTransaction();

            // Update the tenant record with the sanitized database name
            $tenant->update([
                'status' => 'approved',
                'database_name' => $databaseName,
            ]);
            
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
                // Log migration error but continue if tables already exist
                \Log::warning('Migration warning (tables might already exist): ' . $e->getMessage());
            }
            
            // Get the owner user from the tenant users
            $ownerUser = $tenant->users()->where('role', 'owner')->first();
            
            if (!$ownerUser) {
                throw new \Exception('Owner user not found for tenant.');
            }
            
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

            DB::commit();
            
            return back()->with('success', 'Tenant approved successfully. Database created and owner data transferred.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to approve tenant: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Failed to approve tenant: ' . $e->getMessage());
        }
    }

    /**
     * Reject a tenant.
     */
    public function reject(Tenant $tenant)
    {
        if ($tenant->isRejected()) {
            return back()->with('error', 'This tenant is already rejected.');
        }
        
        $tenant->update(['status' => 'rejected']);
        
        return back()->with('success', 'Tenant rejected successfully.');
    }
} 