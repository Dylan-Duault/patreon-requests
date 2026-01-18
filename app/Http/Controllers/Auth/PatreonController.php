<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PatreonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class PatreonController extends Controller
{
    public function __construct(
        protected PatreonService $patreonService
    ) {}

    /**
     * Redirect to Patreon for OAuth authentication.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('patreon')
            ->scopes(['identity', 'identity[email]', 'campaigns', 'campaigns.members'])
            ->redirect();
    }

    /**
     * Handle the OAuth callback from Patreon.
     */
    public function callback(): RedirectResponse
    {
        try {
            $patreonUser = Socialite::driver('patreon')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Failed to authenticate with Patreon. Please try again.');
        }

        // Find or create user
        $user = User::where('patreon_id', $patreonUser->getId())->first();

        if (! $user) {
            // Check if email already exists (user might have registered differently)
            $user = User::where('email', $patreonUser->getEmail())->first();

            if ($user) {
                // Link Patreon account to existing user
                $user->update([
                    'patreon_id' => $patreonUser->getId(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $patreonUser->getName() ?? $patreonUser->getNickname(),
                    'email' => $patreonUser->getEmail(),
                    'patreon_id' => $patreonUser->getId(),
                    'avatar' => $patreonUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);
            }
        }

        // Update tokens
        $user->update([
            'patreon_access_token' => $patreonUser->token,
            'patreon_refresh_token' => $patreonUser->refreshToken,
            'patreon_token_expires_at' => now()->addSeconds($patreonUser->expiresIn ?? 2592000),
            'avatar' => $patreonUser->getAvatar(),
        ]);

        // Fetch and update membership data
        $this->patreonService->updateUserMembership($user);

        Auth::login($user, true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Log the user out.
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
