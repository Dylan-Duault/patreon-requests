<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PatreonService
{
    protected string $apiBase = 'https://www.patreon.com/api/oauth2/v2';

    /**
     * Get the user's membership data from Patreon.
     */
    public function getMembership(User $user): ?array
    {
        if (! $user->patreon_access_token) {
            return null;
        }

        // Refresh token if expired
        if ($this->tokenIsExpired($user)) {
            if (! $this->refreshToken($user)) {
                return null;
            }
        }

        $campaignId = config('patreon.campaign_id');

        if (! $campaignId) {
            Log::warning('Patreon campaign ID not configured');

            return null;
        }

        try {
            $response = Http::withToken($user->patreon_access_token)
                ->get("{$this->apiBase}/identity", [
                    'include' => 'memberships.campaign,memberships.currently_entitled_tiers',
                    'fields[member]' => 'patron_status,currently_entitled_amount_cents,pledge_relationship_start,lifetime_support_cents',
                    'fields[campaign]' => 'creation_name',
                    'fields[tier]' => 'title,amount_cents',
                ]);

            if (! $response->successful()) {
                Log::error('Failed to fetch Patreon membership', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            return $this->parseMembershipData($data, $campaignId);
        } catch (\Exception $e) {
            Log::error('Patreon API error', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Parse membership data from Patreon API response.
     */
    protected function parseMembershipData(array $data, string $campaignId): ?array
    {
        $included = $data['included'] ?? [];
        $memberships = collect($included)->filter(function ($item) {
            return ($item['type'] ?? null) === 'member';
        });

        foreach ($memberships as $membership) {
            $campaignData = $membership['relationships']['campaign']['data'] ?? null;

            if ($campaignData && $campaignData['id'] === $campaignId) {
                $attributes = $membership['attributes'] ?? [];

                return [
                    'patron_status' => $attributes['patron_status'] ?? null,
                    'currently_entitled_amount_cents' => $attributes['currently_entitled_amount_cents'] ?? 0,
                    'pledge_relationship_start' => $attributes['pledge_relationship_start'] ?? null,
                    'lifetime_support_cents' => $attributes['lifetime_support_cents'] ?? 0,
                ];
            }
        }

        return null;
    }

    /**
     * Check if the user's token is expired.
     */
    protected function tokenIsExpired(User $user): bool
    {
        if (! $user->patreon_token_expires_at) {
            return false;
        }

        return $user->patreon_token_expires_at->isPast();
    }

    /**
     * Refresh the user's Patreon access token.
     */
    public function refreshToken(User $user): bool
    {
        if (! $user->patreon_refresh_token) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://www.patreon.com/api/oauth2/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->patreon_refresh_token,
                'client_id' => config('services.patreon.client_id'),
                'client_secret' => config('services.patreon.client_secret'),
            ]);

            if (! $response->successful()) {
                Log::error('Failed to refresh Patreon token', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $data = $response->json();

            $user->update([
                'patreon_access_token' => $data['access_token'],
                'patreon_refresh_token' => $data['refresh_token'],
                'patreon_token_expires_at' => now()->addSeconds($data['expires_in']),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to refresh Patreon token', ['message' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Verify a webhook signature from Patreon.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = config('patreon.webhook_secret');

        if (! $secret) {
            return false;
        }

        $signature = $request->header('X-Patreon-Signature');

        if (! $signature) {
            return false;
        }

        $expectedSignature = hash_hmac('md5', $request->getContent(), $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Update user's membership status from Patreon data.
     */
    public function updateUserMembership(User $user): bool
    {
        $membership = $this->getMembership($user);

        if ($membership === null) {
            // If we can't get membership, mark as former patron (they might have disconnected)
            $user->update([
                'patron_status' => null,
                'patron_tier_cents' => 0,
            ]);

            return false;
        }

        $user->update([
            'patron_status' => $membership['patron_status'],
            'patron_tier_cents' => $membership['currently_entitled_amount_cents'],
        ]);

        return true;
    }

    /**
     * Get the tier level from cents amount.
     */
    public function getTierFromCents(int $cents): int
    {
        $tiers = config('patreon.tiers', []);
        $tier = 0;

        foreach ($tiers as $tierCents => $requests) {
            if ($cents >= $tierCents) {
                $tier = max($tier, $requests);
            }
        }

        return $tier;
    }
}
