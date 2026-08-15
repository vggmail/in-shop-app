<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Shift;

class CheckActiveShift
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $activeShift = Shift::where('status', 'Open')->first();

        if (!$activeShift) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please open a shift before placing orders.'
                ], 403);
            }

            return redirect()->route('shifts.index')->with('warning', 'Please open a shift before starting sales.');
        }

        return $next($request);
    }
}
