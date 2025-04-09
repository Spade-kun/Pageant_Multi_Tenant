<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        // Only apply to the root URL
        if ($request->path() === '/') {
            // Check if this is the admin instance (port 8001)
            if ($request->server('SERVER_PORT') == 8001) {
                return redirect()->route('admin.login');
            } else if ($request->server('SERVER_PORT') == 8000) {
                return redirect()->route('tenant.login');
            }
        }

        return $next($request);
    }
} 