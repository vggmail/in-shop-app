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

        // Skip IP addresses (e.g., 127.0.0.1)
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $next($request);
        }

        if (count($parts) >= 2) {
            // Check for localhost development (e.g., shop1.localhost)
            // or production (e.g., shop1.ovinfinity.com)
            if (end($parts) === 'localhost' || count($parts) > 2) {
                // If the first part isn't 'www', it's our subdomain
                if ($parts[0] !== 'www') {
                    $subdomain = $parts[0];
                }
            }
        }

        // If no subdomain identified (bare localhost or www), just continue
        if (!$subdomain) {
            return $next($request);
        }

        // Query the central Tenant model (on 'mysql' connection)
        $tenant = \App\Models\Tenant::on('mysql')->where('subdomain', $subdomain)->where('is_active', true)->first();

        if (!$tenant) {
            abort(404, "Shop '$subdomain' not found or inactive.");
        }

        // Configure the 'tenant' connection dynamically: prefix + subdomain
        $prefix = env('DB_PREFIX', '');
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
}
