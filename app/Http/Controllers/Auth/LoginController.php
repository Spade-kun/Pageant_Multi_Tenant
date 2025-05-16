<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleOAuthService;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * The Google OAuth service
     *
     * @var GoogleOAuthService
     */
    protected $googleOAuthService;

    /**
     * Create a new controller instance.
     *
     * @param GoogleOAuthService $googleOAuthService
     */
    public function __construct(GoogleOAuthService $googleOAuthService)
    {
        $this->googleOAuthService = $googleOAuthService->forRedirectType('admin');
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return redirect($this->googleOAuthService->getAuthUrl());
    }

    /**
     * Obtain the user information from Google.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            if (!$request->has('code')) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Google authentication failed. Please try again.',
                ]);
            }

            // Get access token
            $token = $this->googleOAuthService->getAccessToken($request->code);
            
            // Get user information
            $googleUser = $this->googleOAuthService->getUserInfo($token['access_token']);
            
            \Log::info('Google login attempt for admin: ' . $googleUser->email);
            
            // Find or create user in central database
            $user = User::where('email', $googleUser->email)->first();
            
            if (!$user) {
                // If user doesn't exist and you want to auto-create:
                // Uncomment the following lines if you want to auto-create users
                // (Otherwise, only existing admin users can log in with Google)
                
                /* 
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make(uniqid()), // Random password
                    'role' => 'user', // Default role, not admin
                ]);
                */
                
                // If you don't want to auto-create users, return error:
                return redirect()->route('login')->withErrors([
                    'email' => 'No account found with this Google email. Please contact an administrator.',
                ]);
            }
            
            // Check if user has admin privileges (optional - remove if not needed)
            if (!$user->hasAdminAccess()) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account does not have admin access.',
                ]);
            }
            
            // Log the user in
            Auth::login($user);
            
            // Check if we're coming from an admin OAuth flow (set by route middleware)
            $isAdminOauth = session('is_admin_oauth', false);
            
            // Always redirect to the absolute admin dashboard URL with port 8001 in local environment
            // This ensures consistent redirection behavior on the first attempt
            if (app()->environment('local')) {
                // Force the absolute URL with port 8001
                \Log::info('Admin OAuth redirecting to absolute URL with port 8001. isAdminOauth=' . ($isAdminOauth ? 'true' : 'false'));
                
                // Clear the session flag
                session()->forget('is_admin_oauth');
                
                return redirect()->away('http://127.0.0.1:8001/admin/dashboard');
            } else {
                // In production, use the normal redirect
                return redirect('/admin/dashboard');
            }
            
        } catch (Exception $e) {
            \Log::error('Google authentication error for admin: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => 'There was an error authenticating with Google. Please try again.',
            ]);
        }
    }

    /**
     * Debug Google OAuth settings
     *
     * @return \Illuminate\Http\Response
     */
    public function debugOAuth()
    {
        if (!app()->environment('local')) {
            abort(404); // Only available in local environment for security
        }
        
        $debug = [
            'redirect_uri' => $this->googleOAuthService->getRedirectUri(),
            'client_id' => config('services.google.client_id'),
            'auth_url' => $this->googleOAuthService->getAuthUrl(),
            'routes' => [
                'redirect_route' => route('admin.google.redirect'),
                'callback_route' => route('admin.google.callback'),
            ],
            'app_url' => config('app.url'),
            'environment' => app()->environment(),
        ];
        
        return response()->json($debug);
    }
} 