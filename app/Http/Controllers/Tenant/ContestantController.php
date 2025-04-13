<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Contestant;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ContestantController extends Controller
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
        $contestants = DB::connection('tenant')->table('contestants')->get();
        
        return view('tenant.contestants.index', compact('contestants', 'slug'));
    }

    public function create($slug)
    {
        return view('tenant.contestants.create', compact('slug'));
    }

    public function show($slug, $id)
    {
        $this->setTenantConnection($slug);
        $contestant = DB::connection('tenant')->table('contestants')->find($id);
        
        if (!$contestant) {
            return redirect()->route('tenant.contestants.index', ['slug' => $slug])
                ->with('error', 'Contestant not found.');
        }

        // Get scoring history if it exists (for future implementation)
        $scoringHistory = [];
        
        return view('tenant.contestants.show', compact('contestant', 'slug', 'scoringHistory'));
    }

    public function store(Request $request)
    {
        $this->setTenantConnection($request->slug);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:18',
            'gender' => 'required|string|in:Male,Female',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'representing' => 'required|string|max:255',
            'is_active' => 'boolean',
            'registration_date' => 'required|date'
        ]);

        try {
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('contestants', 'public');
                $validated['photo'] = $path;
            }

            DB::connection('tenant')->table('contestants')->insert([
                'name' => $validated['name'],
                'age' => $validated['age'],
                'gender' => $validated['gender'],
                'bio' => $validated['bio'],
                'photo' => $validated['photo'] ?? null,
                'representing' => $validated['representing'],
                'is_active' => $validated['is_active'] ?? true,
                'registration_date' => \Carbon\Carbon::parse($validated['registration_date'])->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('tenant.contestants.index', ['slug' => $request->slug])
                ->with('success', 'Contestant created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
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
            'age' => 'required|integer|min:18',
            'gender' => 'required|string|in:Male,Female',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'representing' => 'required|string|max:255',
            'is_active' => 'boolean',
            'registration_date' => 'required|date'
        ]);

        try {
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                $contestant = DB::connection('tenant')->table('contestants')->find($id);
                if ($contestant->photo) {
                    Storage::disk('public')->delete($contestant->photo);
                }
                $path = $request->file('photo')->store('contestants', 'public');
                $validated['photo'] = $path;
            }

            DB::connection('tenant')->table('contestants')->where('id', $id)->update([
                'name' => $validated['name'],
                'age' => $validated['age'],
                'gender' => $validated['gender'],
                'bio' => $validated['bio'],
                'photo' => $validated['photo'] ?? null,
                'representing' => $validated['representing'],
                'is_active' => $validated['is_active'] ?? true,
                'registration_date' => \Carbon\Carbon::parse($validated['registration_date'])->format('Y-m-d'),
                'updated_at' => now()
            ]);

            return redirect()->route('tenant.contestants.index', ['slug' => $slug])
                ->with('success', 'Contestant updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($slug, $id)
    {
        $this->setTenantConnection($slug);
        
        $contestant = DB::connection('tenant')->table('contestants')->find($id);
        
        if ($contestant->photo) {
            Storage::disk('public')->delete($contestant->photo);
        }
        
        DB::connection('tenant')->table('contestants')->where('id', $id)->delete();

        return redirect()->route('tenant.contestants.index', ['slug' => $slug])
            ->with('success', 'Contestant deleted successfully');
    }
} 