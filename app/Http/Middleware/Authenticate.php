<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Check if this is a tenant route 
            if ($request->is('*/user-dashboard') || 
                $request->is('*/judge-dashboard') || 
                $request->is('*/dashboard') ||
                str_starts_with($request->path(), 'tenant/') ||
                preg_match('/^[a-zA-Z0-9\-]+\//', $request->path())) {
                
                return route('tenant.login');
            }
            
            return route('login');
        }

        return null;
    }
} 