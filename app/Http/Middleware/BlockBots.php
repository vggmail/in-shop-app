<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockBots
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bots = [
            'bot', 'crawler', 'spider', 'curl', 'wget', 'python-requests', 'postman', 'scaper',
            'mediapartners-google', 'slurp', 'yahoo!', 'ask', 'alexa', 'exabot', 'mj12bot',
            'ahrefsbot', 'semrushbot', 'dotbot', 'grapeshot', 'yandex', 'baidu'
        ];

        $userAgent = strtolower($request->header('User-Agent'));

        foreach ($bots as $bot) {
            if (str_contains($userAgent, $bot)) {
                return response('Automated access is restricted.', 403);
            }
        }

        return $next($request);
    }
}
