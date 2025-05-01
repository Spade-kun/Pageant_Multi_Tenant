<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\UiSettings;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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

        $settings = UiSettings::where('tenant_id', $tenant->id)->first();
        
        if (!$settings) {
            $settings = new UiSettings();
            $settings->tenant_id = $tenant->id;
        }

        // Handle logo upload
        if ($request->hasFile('header_logo')) {
            $file = $request->file('header_logo');
            
            // Validate file
            $request->validate([
                'header_logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Delete old logo if exists
            if ($settings->header_logo) {
                $oldPath = str_replace('logos/', '', $settings->header_logo);
                if (Storage::disk('public')->exists('logos/' . $oldPath)) {
                    Storage::disk('public')->delete('logos/' . $oldPath);
                }
            }

            // Generate unique filename
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Ensure the logos directory exists
            if (!Storage::disk('public')->exists('logos')) {
                Storage::disk('public')->makeDirectory('logos');
            }
            
            // Store the file directly in the public disk
            $file->storeAs('logos', $filename, 'public');
            
            // Store the relative path in database
            $settings->header_logo = 'logos/' . $filename;
        }

        // Update settings with new values
        $settings->logo_header_color = $request->logo_header_color;
        $settings->navbar_color = $request->navbar_color;
        $settings->sidebar_color = $request->sidebar_color;
        $settings->navbar_position = $request->navbar_position;
        $settings->sidebar_position = $request->sidebar_position;
        $settings->is_sidebar_collapsed = $request->has('is_sidebar_collapsed');
        $settings->is_navbar_fixed = $request->has('is_navbar_fixed');
        $settings->is_sidebar_fixed = $request->has('is_sidebar_fixed');
        $settings->primary_font = $request->primary_font;
        $settings->font_size_scale = $request->font_size_scale;
        
        $settings->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'UI settings updated successfully',
                'settings' => $settings,
                'logo_url' => $settings->header_logo ? asset('storage/' . $settings->header_logo) : null
            ]);
        }

        return redirect()->route('tenant.ui-settings.index', ['slug' => session('tenant_slug')])
            ->with('success', 'UI settings updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
