<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBrowserCache
{
    /**
     * Prevent the browser from caching any HTML responses.
     * This is critical for session-dependent pages (login state, customer data).
     * Without this, the browser serves a stale cached copy of the page,
     * even though the Laravel controller has fresh session data.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply to HTML responses — not to API/JSON/assets
        $contentType = $response->headers->get('Content-Type', '');
        if (
            str_contains($contentType, 'text/html') ||
            $response->headers->get('Content-Type') === null
        ) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
