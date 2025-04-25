<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EventAssignmentController extends Controller
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
        
        $assignments = DB::connection('tenant')
            ->table('event_contestant_categories as ecc')
            ->join('events as e', 'e.id', '=', 'ecc.event_id')
            ->join('contestants as c', 'c.id', '=', 'ecc.contestant_id')
            ->join('categories as cat', 'cat.id', '=', 'ecc.category_id')
            ->select('ecc.*', 'e.name as event_name', 'c.name as contestant_name', 'cat.name as category_name')
            ->get();

        return view('tenant.event-assignments.index', compact('assignments', 'slug'));
    }

    public function create($slug)
    {
        $this->setTenantConnection($slug);
        
        $events = DB::connection('tenant')->table('events')->get();
        $contestants = DB::connection('tenant')->table('contestants')->where('is_active', true)->get();
        $categories = DB::connection('tenant')->table('categories')->where('is_active', true)->get();
        
        return view('tenant.event-assignments.create', compact('events', 'contestants', 'categories', 'slug'));
    }

    public function store(Request $request, $slug)
    {
        $this->setTenantConnection($slug);
        
        $validated = $request->validate([
            'event_id' => 'required|exists:tenant.events,id',
            'contestant_ids' => 'required|array',
            'contestant_ids.*' => 'exists:tenant.contestants,id',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:tenant.categories,id',
            'status' => 'required|in:registered,confirmed,withdrawn',
            'notes' => 'nullable|string'
        ]);

        $assignments = [];
        foreach ($validated['contestant_ids'] as $contestantId) {
            foreach ($validated['category_ids'] as $categoryId) {
                $assignments[] = [
                    'event_id' => $validated['event_id'],
                    'contestant_id' => $contestantId,
                    'category_id' => $categoryId,
                    'status' => $validated['status'],
                    'notes' => $validated['notes'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        try {
            DB::connection('tenant')->table('event_contestant_categories')->insert($assignments);
            return redirect()->route('tenant.event-assignments.index', ['slug' => $slug])
                ->with('success', 'Event assignments created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Some assignments already exist. Please check for duplicates.')
                ->withInput();
        }
    }

    public function show($slug, $id)
    {
        $this->setTenantConnection($slug);
        
        $assignment = DB::connection('tenant')
            ->table('event_contestant_categories as ecc')
            ->join('events as e', 'e.id', '=', 'ecc.event_id')
            ->join('contestants as c', 'c.id', '=', 'ecc.contestant_id')
            ->join('categories as cat', 'cat.id', '=', 'ecc.category_id')
            ->select('ecc.*', 'e.name as event_name', 'c.name as contestant_name', 'cat.name as category_name')
            ->where('ecc.id', $id)
            ->first();

        return view('tenant.event-assignments.show', compact('assignment', 'slug'));
    }

    public function edit($slug, $id)
    {
        $this->setTenantConnection($slug);
        
        $assignment = DB::connection('tenant')->table('event_contestant_categories')->find($id);
        $events = DB::connection('tenant')->table('events')->get();
        $contestants = DB::connection('tenant')->table('contestants')->where('is_active', true)->get();
        $categories = DB::connection('tenant')->table('categories')->where('is_active', true)->get();
        
        return view('tenant.event-assignments.edit', compact('assignment', 'events', 'contestants', 'categories', 'slug'));
    }

    public function update(Request $request, $slug, $id)
    {
        $this->setTenantConnection($slug);
        
        $validated = $request->validate([
            'event_id' => 'required|exists:tenant.events,id',
            'contestant_id' => 'required|exists:tenant.contestants,id',
            'category_id' => 'required|exists:tenant.categories,id',
            'status' => 'required|in:registered,confirmed,withdrawn',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::connection('tenant')->table('event_contestant_categories')
                ->where('id', $id)
                ->update([
                    'event_id' => $validated['event_id'],
                    'contestant_id' => $validated['contestant_id'],
                    'category_id' => $validated['category_id'],
                    'status' => $validated['status'],
                    'notes' => $validated['notes'],
                    'updated_at' => now(),
                ]);

            return redirect()->route('tenant.event-assignments.index', ['slug' => $slug])
                ->with('success', 'Event assignment updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'This assignment combination already exists.')
                ->withInput();
        }
    }

    public function destroy($slug, $id)
    {
        $this->setTenantConnection($slug);
        
        DB::connection('tenant')->table('event_contestant_categories')->delete($id);
        
        return redirect()->route('tenant.event-assignments.index', ['slug' => $slug])
            ->with('success', 'Event assignment deleted successfully.');
    }
} 