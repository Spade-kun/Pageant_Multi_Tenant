<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
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
        $categories = DB::connection('tenant')->table('categories')->get();
        return view('tenant.categories.index', compact('categories', 'slug'));
    }

    public function create($slug)
    {
        return view('tenant.categories.create', compact('slug'));
    }

    public function store(Request $request, $slug)
    {
        $this->setTenantConnection($slug);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        DB::connection('tenant')->table('categories')->insert([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'weight' => $validated['weight'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('tenant.categories.index', ['slug' => $slug])
            ->with('success', 'Category created successfully.');
    }

    public function edit($slug, $id)
    {
        $this->setTenantConnection($slug);
        $category = DB::connection('tenant')->table('categories')->find($id);
        return view('tenant.categories.edit', compact('category', 'slug'));
    }

    public function update(Request $request, $slug, $id)
    {
        $this->setTenantConnection($slug);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        DB::connection('tenant')->table('categories')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'weight' => $validated['weight'],
                'updated_at' => now(),
            ]);

        return redirect()->route('tenant.categories.index', ['slug' => $slug])
            ->with('success', 'Category updated successfully.');
    }

    public function destroy($slug, $id)
    {
        $this->setTenantConnection($slug);
        DB::connection('tenant')->table('categories')->delete($id);
        return redirect()->route('tenant.categories.index', ['slug' => $slug])
            ->with('success', 'Category deleted successfully.');
    }
} 