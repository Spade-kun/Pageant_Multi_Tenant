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

    public function store(Request $request, $slug)
    {
        $this->setTenantConnection($slug);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:1|max:150',
            'gender' => 'required|string|in:Male,Female',
            'bio' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoPath = $photo->store('contestants', 'public');
            $validated['photo'] = $photoPath;
        }

        DB::connection('tenant')->table('contestants')->insert([
            'name' => $validated['name'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'bio' => $validated['bio'],
            'photo' => $validated['photo'],
            'score' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('tenant.contestants.index', ['slug' => $slug])
            ->with('success', 'Contestant added successfully');
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
            'age' => 'required|integer|min:1|max:150',
            'gender' => 'required|string|in:Male,Female',
            'bio' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $updateData = [
            'name' => $validated['name'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'bio' => $validated['bio'],
            'updated_at' => now()
        ];

        if ($request->hasFile('photo')) {
            // Delete old photo
            $contestant = DB::connection('tenant')->table('contestants')->find($id);
            if ($contestant->photo) {
                Storage::disk('public')->delete($contestant->photo);
            }
            
            // Store new photo
            $photo = $request->file('photo');
            $photoPath = $photo->store('contestants', 'public');
            $updateData['photo'] = $photoPath;
        }

        DB::connection('tenant')->table('contestants')->where('id', $id)->update($updateData);

        return redirect()->route('tenant.contestants.index', ['slug' => $slug])
            ->with('success', 'Contestant updated successfully');
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