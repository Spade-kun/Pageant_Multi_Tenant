<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ContestantController extends Controller
{
    private function setTenantConnection($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
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

    public function index($slug)
    {
        $this->setTenantConnection($slug);
        $contestants = DB::connection('tenant')->table('contestants')->get();
        return view('tenant.contestants.index', compact('contestants', 'slug'));
    }

    public function create($slug)
    {
        return view('tenant.contestants.create', compact('slug'));
    }

    public function store(Request $request, $slug)
    {
        $this->setTenantConnection($slug);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:1',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'measurements' => 'required|string',
            'description' => 'nullable|string',
        ]);

        DB::connection('tenant')->table('contestants')->insert([
            'name' => $validated['name'],
            'age' => $validated['age'],
            'height' => $validated['height'],
            'weight' => $validated['weight'],
            'measurements' => $validated['measurements'],
            'description' => $validated['description'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('tenant.contestants.index', ['slug' => $slug])
            ->with('success', 'Contestant created successfully.');
    }

    public function show($slug, $id)
    {
        $this->setTenantConnection($slug);
        $contestant = DB::connection('tenant')->table('contestants')->find($id);
        return view('tenant.contestants.show', compact('contestant', 'slug'));
    }

    public function edit($slug, $id)
    {
        $this->setTenantConnection($slug);
        $contestant = DB::connection('tenant')->table('contestants')->find($id);
        return view('tenant.contestants.edit', compact('contestant', 'slug'));
    }

    public function update(Request $request, $slug, $id)
    {
        $this->setTenantConnection($slug);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:1',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'measurements' => 'required|string',
            'description' => 'nullable|string',
        ]);

        DB::connection('tenant')->table('contestants')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'age' => $validated['age'],
                'height' => $validated['height'],
                'weight' => $validated['weight'],
                'measurements' => $validated['measurements'],
                'description' => $validated['description'],
                'updated_at' => now(),
            ]);

        return redirect()->route('tenant.contestants.index', ['slug' => $slug])
            ->with('success', 'Contestant updated successfully.');
    }

    public function destroy($slug, $id)
    {
        $this->setTenantConnection($slug);
        DB::connection('tenant')->table('contestants')->delete($id);
        return redirect()->route('tenant.contestants.index', ['slug' => $slug])
            ->with('success', 'Contestant deleted successfully.');
    }
} 