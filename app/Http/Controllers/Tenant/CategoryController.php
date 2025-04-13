<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategoryController extends Controller
{
    private function setTenantConnection($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $tenant->database_name,
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

    public function store(Request $request)
    {
        $this->setTenantConnection($request->slug);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'percentage' => 'required|numeric|min:1|max:100',
            'is_active' => 'boolean',
            'display_order' => 'integer|min:0'
        ]);

        try {
            DB::connection('tenant')->table('categories')->insert([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'percentage' => $validated['percentage'],
                'is_active' => $validated['is_active'] ?? true,
                'display_order' => $validated['display_order'] ?? 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('tenant.categories.index', ['slug' => $request->slug])
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($slug, $id)
    {
        $this->setTenantConnection($slug);
        $category = DB::connection('tenant')->table('categories')->find($id);
        
        return view('tenant.categories.edit', compact('category', 'slug'));
    }

    public function update(Request $request, $slug, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'percentage' => 'required|numeric|min:1|max:100',
            'is_active' => 'boolean',
            'display_order' => 'integer|min:0'
        ]);

        try {
            $category->update($validated);
            return redirect()->route('tenant.categories.index', ['slug' => $slug])
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($slug, $id)
    {
        $this->setTenantConnection($slug);
        
        DB::connection('tenant')->table('categories')->where('id', $id)->delete();

        return redirect()->route('tenant.categories.index', ['slug' => $slug])
            ->with('success', 'Category deleted successfully');
    }
} 