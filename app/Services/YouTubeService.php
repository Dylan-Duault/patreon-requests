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
