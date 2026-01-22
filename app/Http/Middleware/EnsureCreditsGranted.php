<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureCreditsGranted
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
            return $next($request);
        }

        $cacheKey = $user->getMonthlyCreditCacheKey();

        // Check cache first to avoid unnecessary DB queries
        $hasCredits = Cache::remember($cacheKey, 3600, function () use ($user) {
            // Check if already received monthly credits
            if ($user->hasReceivedMonthlyCredits()) {
                return true;
            }

            // Grant monthly credits
            $user->grantMonthlyCredits();

            return true;
        });

        return $next($request);
    }
}
