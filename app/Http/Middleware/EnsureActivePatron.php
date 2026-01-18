<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActivePatron
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isActivePatron()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'You must be an active patron to access this resource.',
                ], 403);
            }

            return redirect()->route('subscribe');
        }

        return $next($request);
    }
}
