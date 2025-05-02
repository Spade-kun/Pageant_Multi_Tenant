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
        $this->setTenantConnection($slug);
        
        $currentTotal = DB::connection('tenant')->table('categories')->sum('percentage');
        $remainingPercentage = 100 - $currentTotal;
        $nextDisplayOrder = DB::connection('tenant')->table('categories')->max('display_order') + 1;

        return view('tenant.categories.create', compact('slug', 'currentTotal', 'remainingPercentage', 'nextDisplayOrder'));
    }

    public function store(Request $request, $slug)
    {
        $this->setTenantConnection($slug);
        
        // First validate basic requirements
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'percentage' => 'required|numeric|min:1|max:100',
            'is_active' => 'required|boolean',
            'display_order' => 'required|integer|min:0'
        ]);

        // Check if display order is unique
        $existingOrder = DB::connection('tenant')
            ->table('categories')
            ->where('display_order', $validated['display_order'])
            ->exists();

        if ($existingOrder) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['display_order' => 'This display order is already taken. Please choose a different order number.']);
        }

        // Calculate total percentage including the new category
        $currentTotal = DB::connection('tenant')
            ->table('categories')
            ->sum('percentage');
        
        $newTotal = $currentTotal + $validated['percentage'];

        if ($newTotal > 100) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['percentage' => 'Total percentage cannot exceed 100%. Current total is ' . $currentTotal . '%. Maximum allowed for this category is ' . (100 - $currentTotal) . '%.']);
        }

        DB::connection('tenant')->table('categories')->insert([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'percentage' => $validated['percentage'],
            'is_active' => $validated['is_active'],
            'display_order' => $validated['display_order'],
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
        
        // First validate basic requirements
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'percentage' => 'required|numeric|min:0.01|max:100',
            'is_active' => 'required|boolean',
            'display_order' => 'required|integer|min:0'
        ]);

        // Check if display order is unique (excluding current category)
        $existingOrder = DB::connection('tenant')
            ->table('categories')
            ->where('display_order', $validated['display_order'])
            ->where('id', '!=', $id)
            ->exists();

        if ($existingOrder) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['display_order' => 'This display order is already taken. Please choose a different order number.']);
        }

        // Get current category percentage
        $currentCategory = DB::connection('tenant')
            ->table('categories')
            ->find($id);

        // Calculate total percentage excluding current category and including the new percentage
        $totalExcludingCurrent = DB::connection('tenant')
            ->table('categories')
            ->where('id', '!=', $id)
            ->sum('percentage');
        
        $newTotal = $totalExcludingCurrent + $validated['percentage'];

        if ($newTotal > 100) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['percentage' => 'Total percentage cannot exceed 100%. Current total (excluding this category) is ' . $totalExcludingCurrent . '%. Maximum allowed for this category is ' . (100 - $totalExcludingCurrent) . '%.']);
        }

        DB::connection('tenant')->table('categories')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'percentage' => $validated['percentage'],
                'is_active' => $validated['is_active'],
                'display_order' => $validated['display_order'],
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