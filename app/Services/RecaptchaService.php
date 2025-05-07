<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify a reCAPTCHA v3 token
     *
     * @param string $token The token from the client side
     * @param string $action The expected action name
     * @return bool
     */
    public function verifyV3(string $token, string $action): bool
    {
        $secret = config('recaptcha.v3.secret_key');
        $minScore = config('recaptcha.v3.min_score', 0.5);
        
        if (empty($secret)) {
            Log::warning('reCAPTCHA secret key is not set');
            return true; // For development, return true if no secret key is set
        }
        
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);
            
            $result = $response->json();
            
            // Log the result for debugging
            Log::debug('reCAPTCHA verification', $result);
            
            if (!$result['success']) {
                Log::warning('reCAPTCHA verification failed', [
                    'error-codes' => $result['error-codes'] ?? 'No error codes provided'
                ]);
                return false;
            }
            
            // Verify the action matches
            if ($result['action'] !== $action) {
                Log::warning('reCAPTCHA action mismatch', [
                    'expected' => $action,
                    'received' => $result['action'] ?? 'Unknown action'
                ]);
                return false;
            }
            
            // Check the score meets the minimum threshold
            if ($result['score'] < $minScore) {
                Log::warning('reCAPTCHA score too low', [
                    'score' => $result['score'],
                    'minimum' => $minScore
                ]);
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
} 