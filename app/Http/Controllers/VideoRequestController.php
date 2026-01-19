<?php

namespace App\Http\Controllers;

use App\Models\VideoRequest;
use App\Services\YouTubeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoRequestController extends Controller
{
    public function __construct(
        protected YouTubeService $youtubeService
    ) {}

    /**
     * Display the pending video queue.
     */
    public function index(): Response
    {
        $requests = VideoRequest::with('user:id,name,avatar')
            ->pending()
            ->chronological()
            ->get()
            ->map(fn ($request) => [
                'id' => $request->id,
                'title' => $request->title,
                'thumbnail' => $request->thumbnail,
                'youtube_url' => $request->youtube_url,
                'youtube_video_id' => $request->youtube_video_id,
                'requested_at' => $request->requested_at->toISOString(),
                'user' => [
                    'name' => $request->user->name,
                    'avatar' => $request->user->avatar,
                ],
            ]);

        return Inertia::render('Queue', [
            'requests' => $requests,
        ]);
    }

    /**
     * Show the form to create a new request.
     */
    public function create(Request $request): Response
    {
        $user = $request->user();

        if (! $user->canMakeRequest()) {
            return Inertia::render('RequestLimitReached', [
                'monthlyLimit' => $user->getMonthlyRequestLimit(),
            ]);
        }

        return Inertia::render('NewRequest', [
            'remainingRequests' => $user->getRemainingRequests(),
            'monthlyLimit' => $user->getMonthlyRequestLimit(),
        ]);
    }

    /**
     * Store a new video request.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canMakeRequest()) {
            return back()->withErrors([
                'youtube_url' => 'You have reached your monthly request limit.',
            ]);
        }

        $validated = $request->validate([
            'youtube_url' => ['required', 'url', function ($attribute, $value, $fail) {
                if (! $this->youtubeService->isValidUrl($value)) {
                    $fail('Please enter a valid YouTube URL.');
                }
            }],
        ]);

        $videoId = $this->youtubeService->extractVideoId($validated['youtube_url']);
        $videoInfo = $this->youtubeService->getVideoInfo($videoId);
        $normalizedUrl = $this->youtubeService->normalizeUrl($validated['youtube_url']);

        // Check for duplicate pending requests
        $existingRequest = VideoRequest::where('youtube_video_id', $videoId)
            ->pending()
            ->first();

        if ($existingRequest) {
            return back()->withErrors([
                'youtube_url' => 'This video has already been requested and is in the queue.',
            ]);
        }

        VideoRequest::create([
            'user_id' => $user->id,
            'youtube_url' => $normalizedUrl,
            'youtube_video_id' => $videoId,
            'title' => $videoInfo['title'] ?? null,
            'thumbnail' => $videoInfo['thumbnail'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return redirect()->route('my-requests')
            ->with('success', 'Your video request has been submitted!');
    }

    /**
     * Display the user's own requests.
     */
    public function myRequests(Request $request): Response
    {
        $user = $request->user();

        $requests = $user->requests()
            ->latest('requested_at')
            ->get()
            ->map(fn ($req) => [
                'id' => $req->id,
                'title' => $req->title,
                'thumbnail' => $req->thumbnail,
                'youtube_url' => $req->youtube_url,
                'youtube_video_id' => $req->youtube_video_id,
                'status' => $req->status,
                'requested_at' => $req->requested_at->toISOString(),
                'completed_at' => $req->completed_at?->toISOString(),
            ]);

        return Inertia::render('MyRequests', [
            'requests' => $requests,
            'remainingRequests' => $user->getRemainingRequests(),
            'monthlyLimit' => $user->getMonthlyRequestLimit(),
        ]);
    }
}
