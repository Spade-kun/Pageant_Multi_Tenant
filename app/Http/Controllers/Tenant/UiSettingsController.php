<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\UiSettings;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class UiSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Verify tenant exists
        $tenant = Tenant::where('slug', session('tenant_slug'))->firstOrFail();
        
        // Set up tenant database connection
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

        $settings = UiSettings::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'logo_header_color' => 'dark',
                'navbar_color' => 'white',
                'sidebar_color' => 'dark',
                'navbar_position' => 'top',
                'sidebar_position' => 'left',
                'is_sidebar_collapsed' => false,
                'is_navbar_fixed' => true,
                'is_sidebar_fixed' => true
            ]
        );

        return view('tenant.ui-settings.index', compact('settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // Verify tenant exists
        $tenant = Tenant::where('slug', session('tenant_slug'))->firstOrFail();
        
        // Set up tenant database connection
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

        $settings = UiSettings::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'logo_header_color' => 'dark',
                'navbar_color' => 'white',
                'sidebar_color' => 'dark',
                'navbar_position' => 'top',
                'sidebar_position' => 'left',
                'is_sidebar_collapsed' => false,
                'is_navbar_fixed' => true,
                'is_sidebar_fixed' => true
            ]
        );

        $validated = $request->validate([
            'logo_header_color' => 'required|string',
            'navbar_color' => 'required|string',
            'sidebar_color' => 'required|string',
            'navbar_position' => 'required|in:top,bottom,left,right',
            'sidebar_position' => 'required|in:left,right',
            'is_sidebar_collapsed' => 'boolean',
            'is_navbar_fixed' => 'boolean',
            'is_sidebar_fixed' => 'boolean'
        ]);

        $settings->update($validated);

        return redirect()->back()->with('success', 'UI settings updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
