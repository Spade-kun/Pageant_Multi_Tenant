<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class MigrateTenantDatabase extends Command
{
    protected $signature = 'migrate:tenant {slug?}';
    protected $description = 'Run migrations on the tenant database';

    public function handle()
    {
        $slug = $this->argument('slug');
        
        if (!$slug) {
            $this->error('Please provide a tenant slug');
            return 1;
        }

        // Convert slug to database-friendly format
        $searchSlug = str_replace('_', '-', $slug);
        
        $tenant = Tenant::where('slug', $searchSlug)->first();
        
        if (!$tenant) {
            // Try finding with original slug if not found with converted slug
            $tenant = Tenant::where('slug', $slug)->first();
            
            if (!$tenant) {
                $this->error('Tenant not found');
                return 1;
            }
        }

        // Get the database name
        $databaseName = $tenant->database_name ?? 'tenant_' . str_replace('-', '_', $tenant->slug);

        // Set the tenant database connection
        Config::set('database.connections.tenant', [
            'driver' => env('TENANT_DB_CONNECTION', 'mysql'),
            'host' => env('TENANT_DB_HOST', '127.0.0.1'),
            'port' => env('TENANT_DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('TENANT_DB_USERNAME', 'root'),
            'password' => env('TENANT_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        // Purge and reconnect to ensure we're using the new connection
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Run migrations on the tenant database
        $this->info("Running migrations for tenant: {$tenant->name} (Slug: {$tenant->slug})");
        $this->info("Using database: {$databaseName}");
        
        $this->call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant'
        ]);

        $this->info('Tenant database migrations completed successfully.');
    }
} 