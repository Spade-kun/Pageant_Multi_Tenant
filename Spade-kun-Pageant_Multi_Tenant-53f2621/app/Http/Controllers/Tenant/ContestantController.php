<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            'gender' => 'required|in:male,female',
            'representing' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'registration_date' => 'required|date'
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '_' . str_replace(' ', '_', $photo->getClientOriginalName());
            
            // Ensure storage directory exists
            $storage_path = storage_path('app/public/contestants');
            if (!file_exists($storage_path)) {
                mkdir($storage_path, 0755, true);
            }
            
            // Store the file
            $photo->move($storage_path, $photoName);
            $photoPath = 'contestants/' . $photoName;
        }

        DB::connection('tenant')->table('contestants')->insert([
            'name' => $validated['name'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'representing' => $validated['representing'],
            'bio' => $validated['bio'],
            'photo' => $photoPath,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'registration_date' => $validated['registration_date'],
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
            'gender' => 'required|in:male,female',
            'representing' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'registration_date' => 'required|date'
        ]);

        $updateData = [
            'name' => $validated['name'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'representing' => $validated['representing'],
            'bio' => $validated['bio'],
            'is_active' => $request->has('is_active') ? 1 : 0,
            'registration_date' => $validated['registration_date'],
            'updated_at' => now(),
        ];

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '_' . str_replace(' ', '_', $photo->getClientOriginalName());
            
            // Ensure storage directory exists
            $storage_path = storage_path('app/public/contestants');
            if (!file_exists($storage_path)) {
                mkdir($storage_path, 0755, true);
            }
            
            // Store the new file
            $photo->move($storage_path, $photoName);
            $updateData['photo'] = 'contestants/' . $photoName;

            // Delete old photo if exists
            $contestant = DB::connection('tenant')->table('contestants')->find($id);
            if ($contestant->photo) {
                $old_photo_path = storage_path('app/public/' . $contestant->photo);
                if (file_exists($old_photo_path)) {
                    unlink($old_photo_path);
                }
            }
        }

        DB::connection('tenant')->table('contestants')
            ->where('id', $id)
            ->update($updateData);

        return redirect()->route('tenant.contestants.index', ['slug' => $slug])
            ->with('success', 'Contestant updated successfully.');
    }

    public function destroy($slug, $id)
    {
        $this->setTenantConnection($slug);
        
        // Delete photo if exists
        $contestant = DB::connection('tenant')->table('contestants')->find($id);
        if ($contestant && $contestant->photo) {
            $photo_path = storage_path('app/public/' . $contestant->photo);
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
        }
        
        DB::connection('tenant')->table('contestants')->delete($id);
        return redirect()->route('tenant.contestants.index', ['slug' => $slug])
            ->with('success', 'Contestant deleted successfully.');
    }
} 