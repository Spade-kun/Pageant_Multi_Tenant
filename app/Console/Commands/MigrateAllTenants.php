<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant;

class MigrateAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:all-tenants {--force : Force the operation to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for all tenant databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenants = Tenant::all();
        $force = $this->option('force');
        
        $this->info('Starting migrations for ' . count($tenants) . ' tenant databases');
        $this->newLine();
        
        $bar = $this->output->createProgressBar(count($tenants));
        $bar->start();
        
        $errors = [];
        $successful = 0;
        
        foreach ($tenants as $tenant) {
            $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
            
            // Configure tenant database connection
            Config::set('database.connections.tenant_migration', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $databaseName,
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);
            
            // Purge any existing connections
            DB::purge('tenant_migration');
            
            try {
                // Run migrations on tenant database
                $command = 'migrate --database=tenant_migration';
                if ($force) {
                    $command .= ' --force';
                }
                
                $output = '';
                $exitCode = \Artisan::call($command, [], $output);
                
                if ($exitCode === 0) {
                    $successful++;
                } else {
                    $errors[] = [
                        'tenant' => $tenant->pageant_name . ' (' . $tenant->slug . ')',
                        'error' => 'Migration failed with exit code ' . $exitCode
                    ];
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'tenant' => $tenant->pageant_name . ' (' . $tenant->slug . ')',
                    'error' => $e->getMessage()
                ];
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('Migration Summary:');
        $this->newLine();
        $this->line('Total tenants: ' . count($tenants));
        $this->line('Successful migrations: ' . $successful);
        $this->line('Failed migrations: ' . count($errors));
        
        if (count($errors) > 0) {
            $this->newLine();
            $this->error('Errors encountered:');
            
            $this->table(
                ['Tenant', 'Error'],
                $errors
            );
            
            return 1;
        }
        
        $this->info('All tenant migrations completed successfully.');
        return 0;
    }
} 