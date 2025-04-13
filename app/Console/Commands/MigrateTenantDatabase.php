<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateTenantDatabase extends Command
{
    protected $signature = 'migrate:tenant';
    protected $description = 'Run migrations on the tenant database';

    public function handle()
    {
        // Set the tenant database connection
        Config::set('database.connections.tenant', [
            'driver' => env('TENANT_DB_CONNECTION', 'mysql'),
            'host' => env('TENANT_DB_HOST', '127.0.0.1'),
            'port' => env('TENANT_DB_PORT', '3306'),
            'database' => env('TENANT_DB_DATABASE', 'tenant_malaybalay-pageant'),
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
        $this->call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant'
        ]);

        $this->info('Tenant database migrations completed successfully.');
    }
} 