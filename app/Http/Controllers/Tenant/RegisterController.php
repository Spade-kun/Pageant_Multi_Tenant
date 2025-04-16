<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class RegisterController extends Controller
{
    public function showRegistrationForm($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        return view('tenant.auth.register', compact('tenant'));
    }

    public function register(Request $request, $slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        // Generate temporary password
        $tempPassword = Str::random(10);

        // Switch to tenant database
        $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
        
        // Set the complete tenant database connection
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

        // Create user in tenant database
        $user = DB::connection('tenant')->table('users')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($tempPassword),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send email with temporary password
        Mail::to($request->email)->send(new \App\Mail\WelcomeEmail($request->name, $tempPassword));

        return redirect()->route('tenant.login')->with('success', 'Registration successful! Please check your email for your temporary password.');
    }
} 