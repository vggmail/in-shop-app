<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class VendorDatabaseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost(); // e.g. shop1.localhost or shop1.example.com
        $parts = explode('.', $host);
        
        // If we have a subdomain (more than 1 or 2 parts depending on domain structure)
        // For local testing on localhost: shop1.localhost (2 parts)
        if (count($parts) >= 2 && $parts[0] !== 'www' && $parts[0] !== 'localhost' && !filter_var($host, FILTER_VALIDATE_IP)) {
            $subdomain = $parts[0];
            
            // We can either query the central vendor DB or just assume DB name = subdomain
            $dbName = "pos_" . $subdomain; // standard pattern: pos_shop1, pos_shop2
            
            // Switch database connection dynamically
            Config::set('database.connections.mysql.database', $dbName);
            DB::purge('mysql');
            DB::reconnect('mysql');
        }

        return $next($request);
    }
}
