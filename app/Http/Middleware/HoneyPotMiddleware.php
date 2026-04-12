<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HoneyPotMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled('honey_pot_field')) {
            \Illuminate\Support\Facades\Log::warning("Bot detected via honey pot field: ". $request->ip());
            abort(403, "Bot detected!");
        }
        return $next($request);
    }
}
