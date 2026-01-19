<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            return redirect()->route('admin.requests.index');
        }

        return Inertia::render('Dashboard', [
            'isActivePatron' => $user->isActivePatron(),
            'patronStatus' => $user->patron_status,
            'tierCents' => $user->patron_tier_cents,
            'monthlyLimit' => $user->getMonthlyRequestLimit(),
            'remainingRequests' => $user->getRemainingRequests(),
            'recentRequests' => $user->requests()
                ->latest('requested_at')
                ->take(5)
                ->get()
                ->map(fn ($request) => [
                    'id' => $request->id,
                    'title' => $request->title,
                    'thumbnail' => $request->thumbnail,
                    'youtube_url' => $request->youtube_url,
                    'status' => $request->status,
                    'requested_at' => $request->requested_at->toISOString(),
                ]),
        ]);
    }
}
