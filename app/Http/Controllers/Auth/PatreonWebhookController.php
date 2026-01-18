<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PatreonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PatreonWebhookController extends Controller
{
    public function __construct(
        protected PatreonService $patreonService
    ) {}

    /**
     * Handle incoming Patreon webhook.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Verify webhook signature
        if (! $this->patreonService->verifyWebhookSignature($request)) {
            Log::warning('Invalid Patreon webhook signature');

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $trigger = $request->header('X-Patreon-Event');
        $data = $request->json()->all();

        Log::info('Patreon webhook received', [
            'trigger' => $trigger,
            'data' => $data,
        ]);

        return match ($trigger) {
            'members:pledge:create',
            'members:pledge:update',
            'members:pledge:delete' => $this->handlePledgeEvent($data),
            default => response()->json(['status' => 'ignored']),
        };
    }

    /**
     * Handle pledge create/update/delete events.
     */
    protected function handlePledgeEvent(array $data): JsonResponse
    {
        $patronId = $data['data']['relationships']['user']['data']['id'] ?? null;

        if (! $patronId) {
            Log::warning('Patreon webhook missing patron ID');

            return response()->json(['error' => 'Missing patron ID'], 400);
        }

        $user = User::where('patreon_id', $patronId)->first();

        if (! $user) {
            Log::info('Patreon webhook for unknown user', ['patreon_id' => $patronId]);

            return response()->json(['status' => 'user not found']);
        }

        $attributes = $data['data']['attributes'] ?? [];

        $user->update([
            'patron_status' => $attributes['patron_status'] ?? null,
            'patron_tier_cents' => $attributes['currently_entitled_amount_cents'] ?? 0,
        ]);

        Log::info('Updated user membership from webhook', [
            'user_id' => $user->id,
            'patron_status' => $attributes['patron_status'] ?? null,
            'tier_cents' => $attributes['currently_entitled_amount_cents'] ?? 0,
        ]);

        return response()->json(['status' => 'success']);
    }
}
