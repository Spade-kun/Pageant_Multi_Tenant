<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Tenant\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
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
        $events = DB::connection('tenant')
            ->table('events')
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->paginate(10);
        return view('tenant.events.index', compact('events', 'slug'));
    }

    public function create($slug)
    {
        return view('tenant.events.create', compact('slug'));
    }

    public function store(Request $request, $slug)
    {
        $this->setTenantConnection($slug);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        DB::connection('tenant')->table('events')->insert([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'time' => $validated['time'],
            'venue' => $validated['venue'],
            'status' => $validated['status'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('tenant.events.index', ['slug' => $slug])
            ->with('success', 'Event created successfully.');
    }

    public function show($slug, $id)
    {
        $this->setTenantConnection($slug);
        $event = DB::connection('tenant')->table('events')->find($id);
        return view('tenant.events.show', compact('event', 'slug'));
    }

    public function edit($slug, $id)
    {
        $this->setTenantConnection($slug);
        $event = DB::connection('tenant')->table('events')->find($id);
        return view('tenant.events.edit', compact('event', 'slug'));
    }

    public function update(Request $request, $slug, $id)
    {
        $this->setTenantConnection($slug);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        DB::connection('tenant')->table('events')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'date' => $validated['date'],
                'time' => $validated['time'],
                'venue' => $validated['venue'],
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

        return redirect()->route('tenant.events.index', ['slug' => $slug])
            ->with('success', 'Event updated successfully.');
    }

    public function destroy($slug, $id)
    {
        $this->setTenantConnection($slug);
        DB::connection('tenant')->table('events')->delete($id);
        return redirect()->route('tenant.events.index', ['slug' => $slug])
            ->with('success', 'Event deleted successfully.');
    }
} 