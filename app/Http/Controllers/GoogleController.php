<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user exists in database
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'No admin account found with this Google email.']);
            }

            // Log the user in
            Auth::login($user);

            // Redirect to admin dashboard
            return redirect()->route('admin.dashboard');

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Error authenticating with Google. Please try again.']);
        }
    }
} 