<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetTenantDatabase
{
    public function handle(Request $request, Closure $next)
    {
        $slug = $request->route('slug');
        
        if ($slug) {
            $tenant = Tenant::where('slug', $slug)->first();
            
            if ($tenant) {
                $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
                
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

                DB::purge('tenant');
                DB::reconnect('tenant');
            }
        }

        return $next($request);
    }
} 