<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Tenant\StoreJudgeRequest;
use App\Http\Requests\Tenant\UpdateJudgeRequest;

class JudgeController extends Controller
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
        $judges = DB::connection('tenant')->table('judges')->get();
        return view('tenant.judges.index', compact('judges', 'slug'));
    }

    public function create($slug)
    {
        $this->setTenantConnection($slug);
        // Get all users with role 'user' only (exclude owners and existing judges)
        $users = DB::connection('tenant')
            ->table('users')
            ->where('role', 'user')
            ->get();
        
        return view('tenant.judges.create', [
            'slug' => $slug,
            'users' => $users
        ]);
    }

    public function store(StoreJudgeRequest $request, $slug)
    {
        $this->setTenantConnection($slug);
        
        $request->validate([
            'user_id' => 'required|exists:tenant.users,id',
            'specialty' => 'required|string|max:255',
        ]);
        
        // Verify selected user has the 'user' role
        $user = DB::connection('tenant')
            ->table('users')
            ->where('id', $request->user_id)
            ->where('role', 'user')
            ->first();
            
        if (!$user) {
            return redirect()->back()
                ->withErrors(['user_id' => 'Selected user must have the "user" role.'])
                ->withInput();
        }
        
        // Check if user is already a judge by email
        $existingJudge = DB::connection('tenant')
            ->table('judges')
            ->where('email', $user->email)
            ->first();
            
        if ($existingJudge) {
            return redirect()->back()
                ->withErrors(['user_id' => 'This user is already a judge.'])
                ->withInput();
        }
        
        // Create the judge
        DB::connection('tenant')->table('judges')->insert([
            'name' => $user->name,
            'email' => $user->email,
            'specialty' => $request->specialty,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Update user role to 'judge'
        DB::connection('tenant')
            ->table('users')
            ->where('id', $request->user_id)
            ->update(['role' => 'judge']);
            
        return redirect()->route('tenant.judges.index', ['slug' => $slug])
            ->with('success', 'Judge added successfully.');
    }

    public function show($slug, $judge)
    {
        $this->setTenantConnection($slug);
        $judge = DB::connection('tenant')->table('judges')->find($judge);
        return view('tenant.judges.show', compact('judge', 'slug'));
    }

    public function edit($slug, $judge)
    {
        $this->setTenantConnection($slug);
        $judge = DB::connection('tenant')->table('judges')->find($judge);
        return view('tenant.judges.edit', compact('judge', 'slug'));
    }

    public function update(UpdateJudgeRequest $request, $slug, $judge)
    {
        $this->setTenantConnection($slug);
        
        $validated = $request->validate([
            'specialty' => 'required|string|max:255',
        ]);

        DB::connection('tenant')
            ->table('judges')
            ->where('id', $judge)
            ->update([
                'specialty' => $validated['specialty'],
                'updated_at' => now(),
            ]);

        return redirect()->route('tenant.judges.index', ['slug' => $slug])
            ->with('success', 'Judge updated successfully.');
    }

    public function destroy($slug, $judge)
    {
        $this->setTenantConnection($slug);
        
        // Get the judge
        $judgeRecord = DB::connection('tenant')
            ->table('judges')
            ->where('id', $judge)
            ->first();

        if (!$judgeRecord) {
            return redirect()->route('tenant.judges.index', ['slug' => $slug])
                ->with('error', 'Judge not found.');
        }

        // Update the user's role back to 'user'
        DB::connection('tenant')
            ->table('users')
            ->where('email', $judgeRecord->email)
            ->update(['role' => 'user']);

        // Delete the judge record
        DB::connection('tenant')
            ->table('judges')
            ->where('id', $judge)
            ->delete();

        return redirect()->route('tenant.judges.index', ['slug' => $slug])
            ->with('success', 'Judge removed successfully.');
    }
} 