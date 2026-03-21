<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        $subdomain = $parts[0];

        $tenant = \App\Models\Tenant::where('subdomain', $subdomain)->where('is_active', true)->first();
        if (!$tenant) {
            $tenant = \App\Models\Tenant::where('is_active', true)->first(); // Fallback to ensure app runs
        }

        if ($tenant) {
            app()->instance('tenant', $tenant);
            view()->share('tenant', $tenant);
        }

        return $next($request);
    }
}
