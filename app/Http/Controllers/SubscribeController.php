<?php

namespace App\Http\Controllers;

use App\Services\PatreonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SubscribeController extends Controller
{
    public function __construct(
        protected PatreonService $patreonService
    ) {}

    /**
     * Show the subscribe page for non-patrons.
     */
    public function index(Request $request): Response
    {
        $tiers = config('patreon.tiers', []);

        $tierInfo = collect($tiers)->map(function ($requests, $cents) {
            return [
                'amount' => '$'.number_format($cents / 100, 2),
                'requests_per_month' => $requests,
            ];
        })->values();

        $user = $request->user();
        $cacheKey = "subscription_refresh_cooldown:{$user->id}";
        $cooldownRemaining = 0;

        if (Cache::has($cacheKey)) {
            $cooldownRemaining = (int) Cache::get($cacheKey) - time();
            if ($cooldownRemaining < 0) {
                $cooldownRemaining = 0;
            }
        }

        return Inertia::render('Subscribe', [
            'tiers' => $tierInfo,
            'subscribeUrl' => config('patreon.subscribe_url'),
            'cooldownRemaining' => $cooldownRemaining,
        ]);
    }

    /**
     * Refresh the user's subscription status from Patreon.
     */
    public function refresh(Request $request): RedirectResponse
    {
        $user = $request->user();
        $cacheKey = "subscription_refresh_cooldown:{$user->id}";
        $cooldownSeconds = 15;

        // Check cooldown
        if (Cache::has($cacheKey)) {
            $remaining = (int) Cache::get($cacheKey) - time();
            if ($remaining > 0) {
                return back()->with('error', "Please wait {$remaining} seconds before refreshing again.");
            }
        }

        // Set cooldown
        Cache::put($cacheKey, time() + $cooldownSeconds, $cooldownSeconds);

        // Refresh membership
        $this->patreonService->updateUserMembership($user);

        // Reload user to check if they're now an active patron
        $user->refresh();

        if ($user->isActivePatron()) {
            return redirect()->route('dashboard')->with('success', 'Welcome! Your subscription is now active.');
        }

        return back()->with('error', 'Subscription not found. Please make sure you have completed your Patreon subscription.');
    }
}
