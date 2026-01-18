<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class SubscribeController extends Controller
{
    /**
     * Show the subscribe page for non-patrons.
     */
    public function __invoke(): Response
    {
        $tiers = config('patreon.tiers', []);

        $tierInfo = collect($tiers)->map(function ($requests, $cents) {
            return [
                'amount' => '$'.number_format($cents / 100, 2),
                'requests_per_month' => $requests,
            ];
        })->values();

        return Inertia::render('Subscribe', [
            'tiers' => $tierInfo,
        ]);
    }
}
