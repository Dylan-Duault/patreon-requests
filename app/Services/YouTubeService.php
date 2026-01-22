<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    /**
     * Valid YouTube URL patterns.
     */
    protected array $patterns = [
        '/^https?:\/\/(?:www\.)?youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/',
        '/^https?:\/\/(?:www\.)?youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
        '/^https?:\/\/(?:www\.)?youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
        '/^https?:\/\/youtu\.be\/([a-zA-Z0-9_-]{11})/',
        '/^https?:\/\/(?:www\.)?youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
    ];

    /**
     * Extract the video ID from a YouTube URL.
     */
    public function extractVideoId(string $url): ?string
    {
        $url = trim($url);

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        // Try to extract from query string as fallback
        $parsed = parse_url($url);
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
            if (isset($query['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $query['v'])) {
                return $query['v'];
            }
        }

        return null;
    }

    /**
     * Check if a URL is a valid YouTube URL.
     */
    public function isValidUrl(string $url): bool
    {
        return $this->extractVideoId($url) !== null;
    }

    /**
     * Get video information from YouTube oEmbed API.
     */
    public function getVideoInfo(string $videoId): ?array
    {
        $url = "https://www.youtube.com/watch?v={$videoId}";

        try {
            $response = Http::get('https://www.youtube.com/oembed', [
                'url' => $url,
                'format' => 'json',
            ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            return [
                'title' => $data['title'] ?? null,
                'thumbnail' => $data['thumbnail_url'] ?? "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg",
                'author' => $data['author_name'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to fetch YouTube video info', [
                'video_id' => $videoId,
                'message' => $e->getMessage(),
            ]);

            // Return default thumbnail even if oEmbed fails
            return [
                'title' => null,
                'thumbnail' => "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg",
                'author' => null,
            ];
        }
    }

    /**
     * Get video duration in seconds from YouTube Data API.
     */
    public function getVideoDuration(string $videoId): ?int
    {
        $apiKey = config('services.youtube.api_key');

        if (! $apiKey) {
            Log::warning('YouTube API key not configured');

            return null;
        }

        try {
            $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
                'part' => 'contentDetails',
                'id' => $videoId,
                'key' => $apiKey,
            ]);

            if (! $response->successful()) {
                Log::warning('YouTube API request failed', [
                    'video_id' => $videoId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $data = $response->json();

            if (empty($data['items'])) {
                return null;
            }

            $duration = $data['items'][0]['contentDetails']['duration'] ?? null;

            if (! $duration) {
                return null;
            }

            return $this->parseDuration($duration);
        } catch (\Exception $e) {
            Log::warning('Failed to fetch YouTube video duration', [
                'video_id' => $videoId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse ISO 8601 duration to seconds.
     */
    protected function parseDuration(string $duration): int
    {
        $interval = new \DateInterval($duration);

        return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }

    /**
     * Calculate how many request credits a video costs based on duration.
     */
    public function calculateRequestCost(int $durationSeconds): int
    {
        $maxMinutes = config('services.youtube.max_duration_minutes', 20);
        $maxSeconds = $maxMinutes * 60;

        return (int) ceil($durationSeconds / $maxSeconds);
    }

    /**
     * Get full video details including duration and request cost.
     */
    public function getVideoDetails(string $videoId): ?array
    {
        $info = $this->getVideoInfo($videoId);
        $duration = $this->getVideoDuration($videoId);

        if (! $info) {
            return null;
        }

        $requestCost = $duration ? $this->calculateRequestCost($duration) : 1;

        return [
            'video_id' => $videoId,
            'title' => $info['title'],
            'thumbnail' => $info['thumbnail'],
            'author' => $info['author'],
            'duration_seconds' => $duration,
            'request_cost' => $requestCost,
            'max_duration_minutes' => config('services.youtube.max_duration_minutes', 20),
        ];
    }

    /**
     * Normalize a YouTube URL to standard format.
     */
    public function normalizeUrl(string $url): ?string
    {
        $videoId = $this->extractVideoId($url);

        if (! $videoId) {
            return null;
        }

        return "https://www.youtube.com/watch?v={$videoId}";
    }
}
