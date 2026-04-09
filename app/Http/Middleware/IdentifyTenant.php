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
        $subdomain = null;

        // Skip IP addresses (e.g., 127.0.0.1) for DB switching, but still share tenant info
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $this->shareFallbackTenant();
            return $next($request);
        }

        if (count($parts) >= 1) {
            // Handle single-word hosts (e.g., http://retail/) where count is 1
            if (count($parts) === 1 && $parts[0] !== 'localhost' && $parts[0] !== '127') {
                $subdomain = $parts[0];
            } 
            // Handle multi-part hosts (e.g., retail.domain.com or retail.localhost)
            else if (count($parts) >= 2) {
                if ($parts[0] !== 'www') {
                    $subdomain = $parts[0];
                }
            }
        }

        // If no subdomain identified (bare localhost or www), share fallback and continue
        if (!$subdomain) {
            $this->shareFallbackTenant();
            return $next($request);
        }

        // Query the central Tenant model (on 'mysql' connection)
        $tenant = \App\Models\Tenant::on('mysql')->where('subdomain', $subdomain)->where('is_active', true)->first();

        if (!$tenant) {
            abort(404, "Shop '$subdomain' not found or inactive.");
        }

        // Configure the 'tenant' connection dynamically: prefix + subdomain
        $prefix = config('database.tenant_prefix', '');
        $dbName = $prefix . $subdomain;

        \Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $dbName);
        \Illuminate\Support\Facades\DB::purge('tenant');
        \Illuminate\Support\Facades\DB::reconnect('tenant');

        // Force all subsequent database calls to use the tenant connection by default
        \Illuminate\Support\Facades\DB::setDefaultConnection('tenant');

        // Inject the tenant model into the request and view sharing
        app()->instance('tenant', $tenant);
        view()->share('tenant_info', $tenant);

        return $next($request);
    }

    /**
     * Share a fallback tenant (first record) for local dev / no-subdomain scenarios.
     */
    private function shareFallbackTenant(): void
    {
        try {
            $tenant = \App\Models\Tenant::first();
            if ($tenant) {
                app()->instance('tenant', $tenant);
                view()->share('tenant_info', $tenant);
            }
        } catch (\Exception $e) {
            // Silently fail if tenants table doesn't exist
        }
    }
}
