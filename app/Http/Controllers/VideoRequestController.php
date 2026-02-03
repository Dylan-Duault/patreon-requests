<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVideoRequestContextRequest;
use App\Models\Setting;
use App\Models\VideoRequest;
use App\Services\YouTubeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if (! Setting::get('show_request_list', true)) {
            abort(404);
        }

        $requests = VideoRequest::pending()
            ->chronological()
            ->get()
            ->map(fn ($request) => [
                'id' => $request->id,
                'title' => $request->title,
                'thumbnail' => $request->thumbnail,
                'youtube_url' => $request->youtube_url,
                'youtube_video_id' => $request->youtube_video_id,
                'requested_at' => $request->requested_at->toISOString(),
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
            'maxDurationMinutes' => config('services.youtube.max_duration_minutes', 20),
        ]);
    }

    /**
     * Check video details including duration and request cost.
     */
    public function checkVideo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'youtube_url' => ['required', 'url'],
        ]);

        if (! $this->youtubeService->isValidUrl($validated['youtube_url'])) {
            return response()->json([
                'error' => 'Invalid YouTube URL',
            ], 422);
        }

        $videoId = $this->youtubeService->extractVideoId($validated['youtube_url']);

        // Check for existing requests
        $existingRequest = VideoRequest::where('youtube_video_id', $videoId)->first();

        if ($existingRequest) {
            return response()->json([
                'error' => $existingRequest->isPending()
                    ? 'This video is already in the queue.'
                    : 'This video has already been requested.',
            ], 422);
        }

        $details = $this->youtubeService->getVideoDetails($videoId);

        if (! $details) {
            return response()->json([
                'error' => 'Could not fetch video details',
            ], 422);
        }

        return response()->json($details);
    }

    /**
     * Store a new video request.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'youtube_url' => ['required', 'url', function ($attribute, $value, $fail) {
                if (! $this->youtubeService->isValidUrl($value)) {
                    $fail('Please enter a valid YouTube URL.');
                }
            }],
            'context' => ['nullable', 'string', 'max:500'],
        ]);

        $videoId = $this->youtubeService->extractVideoId($validated['youtube_url']);
        $videoDetails = $this->youtubeService->getVideoDetails($videoId);
        $normalizedUrl = $this->youtubeService->normalizeUrl($validated['youtube_url']);

        // Check for existing requests
        $existingRequest = VideoRequest::where('youtube_video_id', $videoId)->first();

        if ($existingRequest) {
            if ($existingRequest->isPending()) {
                return back()->withErrors([
                    'youtube_url' => 'This video has already been requested and is in the queue.',
                ]);
            }

            return back()->withErrors([
                'youtube_url' => 'This video has already been requested and was completed.',
            ]);
        }

        // Calculate request cost based on video duration
        $requestCost = $videoDetails['request_cost'] ?? 1;

        // Use transaction with locking for atomic credit deduction
        try {
            DB::transaction(function () use ($user, $normalizedUrl, $videoId, $videoDetails, $requestCost, $validated) {
                // Lock the user's credit transactions to prevent race conditions
                $creditBalance = $user->creditTransactions()->lockForUpdate()->sum('amount');

                if (! $user->isActivePatron() || $creditBalance < $requestCost) {
                    $maxMinutes = config('services.youtube.max_duration_minutes', 20);
                    throw new \Exception(
                        "This video requires {$requestCost} " . ($requestCost === 1 ? 'credit' : 'credits') .
                        " (videos over {$maxMinutes} minutes count as multiple credits). You only have {$creditBalance} remaining."
                    );
                }

                $videoRequest = VideoRequest::create([
                    'user_id' => $user->id,
                    'youtube_url' => $normalizedUrl,
                    'youtube_video_id' => $videoId,
                    'title' => $videoDetails['title'] ?? null,
                    'thumbnail' => $videoDetails['thumbnail'] ?? null,
                    'duration_seconds' => $videoDetails['duration_seconds'] ?? null,
                    'request_cost' => $requestCost,
                    'context' => $validated['context'] ?? null,
                    'status' => 'pending',
                    'requested_at' => now(),
                ]);

                // Debit credits atomically
                $user->debitCreditsForRequest($videoRequest);
            });
        } catch (\Exception $e) {
            return back()->withErrors([
                'youtube_url' => $e->getMessage(),
            ]);
        }

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
                'context' => $req->context,
                'requested_at' => $req->requested_at->toISOString(),
                'completed_at' => $req->completed_at?->toISOString(),
            ]);

        return Inertia::render('MyRequests', [
            'requests' => $requests,
            'remainingRequests' => $user->getRemainingRequests(),
            'monthlyLimit' => $user->getMonthlyRequestLimit(),
        ]);
    }

    /**
     * Update the context of a pending request.
     */
    public function updateContext(UpdateVideoRequestContextRequest $request, VideoRequest $videoRequest): RedirectResponse
    {
        $videoRequest->update([
            'context' => $request->validated('context'),
        ]);

        return back()->with('success', 'Context updated successfully!');
    }
}
