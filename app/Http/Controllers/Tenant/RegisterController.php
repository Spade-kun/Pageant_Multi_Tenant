<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'phone' => $request->phone,
            'tenant_id' => $tenant->id,
        ]);

        // Send welcome email with credentials
        $data = [
            'user' => $user,
            'tenant' => $tenant,
            'tempPassword' => $tempPassword,
        ];

        Mail::send('emails.tenant-welcome', $data, function($message) use ($user, $tenant) {
            $message->to($user->email)
                   ->subject('Welcome to ' . $tenant->name);
        });

        return redirect()->route('tenant.login')
            ->with('success', 'Registration successful! Please check your email for login credentials.');
    }
} 