<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use stdClass;

class GoogleOAuthService
{
    /**
     * Google OAuth Config
     */
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $redirectType;

    /**
     * Constructor
     * 
     * @param string $redirectType Optional parameter to specify admin or tenant redirect
     */
    public function __construct($redirectType = 'tenant')
    {
        $this->clientId = config('services.google.client_id');
        $this->clientSecret = config('services.google.client_secret');
        $this->redirectType = $redirectType;
        
        // Use different redirect URIs for admin and tenant logins
        if ($redirectType === 'admin') {
            // Use a hardcoded full URL for the admin redirect
            if (app()->environment('local')) {
                // Make sure this EXACTLY matches what you've configured in Google Cloud Console
                // $this->redirectUri = 'http://localhost:8000/auth/google/callback';
                $this->redirectUri = 'http://127.0.0.1:8001/auth/google/callback';
            } else {
                $this->redirectUri = 'https://your-production-domain.com/auth/google/callback';
            }
        } else {
            // Use a hardcoded full URL for the tenant redirect
            if (app()->environment('local')) {
                // This was already fixed for tenant logins
                $this->redirectUri = 'http://127.0.0.1:8000/tenant/auth/google/callback';
            } else {
                $this->redirectUri = 'https://your-production-domain.com/tenant/auth/google/callback';
            }
        }
        
        // Log the redirect URI for debugging
        \Log::info('Google OAuth redirect URI (' . $redirectType . '): ' . $this->redirectUri);
    }
    
    /**
     * Set redirect type
     * 
     * @param string $type
     * @return $this
     */
    public function forRedirectType($type)
    {
        // Create a new instance with the specified redirect type
        return new self($type);
    }

    /**
     * Get Google OAuth URL for redirection
     */
    public function getAuthUrl()
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            'response_type' => 'code',
            'access_type' => 'online',
            'prompt' => 'select_account'
        ];

        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken($code)
    {
        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to get access token from Google: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get user information using access token
     */
    public function getUserInfo($accessToken)
    {
        $response = Http::withToken($accessToken)
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (!$response->successful()) {
            throw new Exception('Failed to get user info from Google: ' . $response->body());
        }

        $data = $response->json();
        
        // Create a standardized user object similar to Socialite's
        $user = new stdClass();
        $user->id = $data['sub'] ?? null;
        $user->email = $data['email'] ?? null;
        $user->name = $data['name'] ?? null;
        $user->avatar = $data['picture'] ?? null;
        
        return $user;
    }

    /**
     * Get the redirect URI
     * 
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }
} 