<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoRequestController extends Controller
{
    /**
     * Display all video requests.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');

        $query = VideoRequest::with('user:id,name,email,avatar,patron_tier_cents')
            ->chronological();

        if ($status === 'pending') {
            $query->pending();
        } elseif ($status === 'done') {
            $query->completed();
        }

        $requests = $query->get()->map(fn ($req) => [
            'id' => $req->id,
            'title' => $req->title,
            'thumbnail' => $req->thumbnail,
            'youtube_url' => $req->youtube_url,
            'youtube_video_id' => $req->youtube_video_id,
            'context' => $req->context,
            'duration_seconds' => $req->duration_seconds,
            'request_cost' => $req->request_cost,
            'status' => $req->status,
            'requested_at' => $req->requested_at->toISOString(),
            'completed_at' => $req->completed_at?->toISOString(),
            'user' => [
                'id' => $req->user->id,
                'name' => $req->user->name,
                'email' => $req->user->email,
                'avatar' => $req->user->avatar,
                'tier_cents' => $req->user->patron_tier_cents,
            ],
        ]);

        $stats = [
            'total' => VideoRequest::count(),
            'pending' => VideoRequest::pending()->count(),
            'done' => VideoRequest::completed()->count(),
        ];

        return Inertia::render('admin/Requests', [
            'requests' => $requests,
            'stats' => $stats,
            'currentFilter' => $status,
        ]);
    }

    /**
     * Mark a video request as done.
     */
    public function markDone(VideoRequest $request): RedirectResponse
    {
        $request->markAsDone();

        return back()->with('success', 'Video marked as done.');
    }

    /**
     * Mark a video request as pending (undo done).
     */
    public function markPending(VideoRequest $request): RedirectResponse
    {
        $request->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        return back()->with('success', 'Video marked as pending.');
    }
}
