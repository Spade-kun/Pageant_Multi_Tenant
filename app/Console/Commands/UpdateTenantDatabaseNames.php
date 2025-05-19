<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class UpdateTenantDatabaseNames extends Command
{
    protected $signature = 'tenants:update-database-names';
    protected $description = 'Update database_name field for all tenants';

    public function handle()
    {
        $tenants = Tenant::whereNull('database_name')->get();
        $count = 0;

        foreach ($tenants as $tenant) {
            $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
            $tenant->update(['database_name' => $databaseName]);
            $count++;
            $this->info("Updated database_name for tenant: {$tenant->pageant_name} ({$databaseName})");
        }

        $this->info("Successfully updated {$count} tenants.");
    }
} 