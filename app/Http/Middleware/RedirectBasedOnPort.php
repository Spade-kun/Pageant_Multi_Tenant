<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnPort
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAdminPort = $request->server('SERVER_PORT') == 8001;
        $isTenantPort = $request->server('SERVER_PORT') == 8000;

        // If on tenant port and trying to access admin routes
        if ($isTenantPort && str_starts_with($request->path(), 'admin')) {
            return redirect()->route('tenant.login');
        }

        // If on admin port and trying to access tenant routes
        if ($isAdminPort && str_starts_with($request->path(), 'tenant')) {
            return redirect()->route('admin.login');
        }

        // Handle root URL redirects
        if ($request->path() === '/') {
            if ($isAdminPort) {
                return redirect()->route('admin.login');
            } else if ($isTenantPort) {
                // If tenant user is logged in, redirect to appropriate dashboard
                if (Auth::guard('tenant')->check()) {
                    $tenant = session('tenant_slug');
                    $role = session('user_role');
                    
                    if ($role === 'owner') {
                        return redirect()->route('tenant.dashboard', ['slug' => $tenant]);
                    } else {
                        return redirect()->route('tenant.user.dashboard', ['slug' => $tenant]);
                    }
                }
                return redirect()->route('tenant.login');
            }
        }

        return $next($request);
    }
} 