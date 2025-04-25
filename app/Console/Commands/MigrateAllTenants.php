<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class MigrateAllTenants extends Command
{
    protected $signature = 'migrate:all-tenants';
    protected $description = 'Run migrations on all tenant databases';

    public function handle()
    {
        $tenants = Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->error('No tenants found in the database.');
            return 1;
        }

        $this->info('Found ' . $tenants->count() . ' tenants. Starting migration...');
        
        $bar = $this->output->createProgressBar($tenants->count());
        $bar->start();

        foreach ($tenants as $tenant) {
            $this->call('migrate:tenant', [
                'slug' => $tenant->slug
            ]);
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All tenant migrations completed successfully.');
    }
} 