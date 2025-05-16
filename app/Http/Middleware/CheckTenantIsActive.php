<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get tenant slug from route parameter
        $slug = $request->route('slug');
        
        // Find tenant by slug
        $tenant = Tenant::where('slug', $slug)->first();
        
        // If tenant doesn't exist or is not active, show disabled page
        if (!$tenant || !$tenant->is_active) {
            return response()->view('tenant.disabled', ['tenant' => $tenant ?? null]);
        }
        
        return $next($request);
    }
} 