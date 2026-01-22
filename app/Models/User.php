<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'patreon_id',
        'patreon_access_token',
        'patreon_refresh_token',
        'patreon_token_expires_at',
        'patron_status',
        'patron_tier_cents',
        'avatar',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'patreon_access_token',
        'patreon_refresh_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'patreon_token_expires_at' => 'datetime',
            'patron_tier_cents' => 'integer',
            'is_admin' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the video requests for the user.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(VideoRequest::class);
    }

    /**
     * Get the credit transactions for the user.
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    /**
     * Check if the user is an active patron.
     */
    public function isActivePatron(): bool
    {
        return $this->patron_status === 'active_patron' && $this->patron_tier_cents > 0;
    }

    /**
     * Get the monthly request limit based on the user's tier.
     */
    public function getMonthlyRequestLimit(): int
    {
        if (! $this->isActivePatron()) {
            return 0;
        }

        $tiers = config('patreon.tiers', []);
        $tierCents = $this->patron_tier_cents;

        // Find the highest tier that the user qualifies for
        $requestLimit = config('patreon.default_requests', 0);

        foreach ($tiers as $cents => $requests) {
            if ($tierCents >= $cents && $requests > $requestLimit) {
                $requestLimit = $requests;
            }
        }

        return $requestLimit;
    }

    /**
     * Get the user's current credit balance.
     */
    public function getCreditBalance(): int
    {
        return (int) $this->creditTransactions()->sum('amount');
    }

    /**
     * Get the number of remaining requests (alias for credit balance).
     */
    public function getRemainingRequests(): int
    {
        return $this->getCreditBalance();
    }

    /**
     * Check if the user has received their monthly credits for the current month.
     */
    public function hasReceivedMonthlyCredits(): bool
    {
        return $this->creditTransactions()
            ->ofType(CreditTransaction::TYPE_MONTHLY_GRANT)
            ->forCurrentMonth()
            ->exists();
    }

    /**
     * Grant monthly credits to the user.
     */
    public function grantMonthlyCredits(): ?CreditTransaction
    {
        if (! $this->isActivePatron()) {
            return null;
        }

        if ($this->hasReceivedMonthlyCredits()) {
            return null;
        }

        $credits = $this->getMonthlyRequestLimit();

        if ($credits <= 0) {
            return null;
        }

        $transaction = $this->creditTransactions()->create([
            'amount' => $credits,
            'type' => CreditTransaction::TYPE_MONTHLY_GRANT,
            'description' => 'Monthly credit grant for ' . now()->format('F Y'),
        ]);

        // Clear the cache
        $this->clearMonthlyCreditCache();

        return $transaction;
    }

    /**
     * Debit credits for a video request.
     */
    public function debitCreditsForRequest(VideoRequest $request): CreditTransaction
    {
        return $this->creditTransactions()->create([
            'amount' => -$request->request_cost,
            'type' => CreditTransaction::TYPE_REQUEST,
            'description' => 'Video request: ' . ($request->title ?? $request->youtube_video_id),
            'video_request_id' => $request->id,
        ]);
    }

    /**
     * Check if the user can make a new request.
     */
    public function canMakeRequest(int $cost = 1): bool
    {
        return $this->isActivePatron() && $this->getCreditBalance() >= $cost;
    }

    /**
     * Get the cache key for monthly credits check.
     */
    public function getMonthlyCreditCacheKey(): string
    {
        return "user:{$this->id}:monthly_credits:" . now()->format('Y-m');
    }

    /**
     * Clear the monthly credit cache.
     */
    public function clearMonthlyCreditCache(): void
    {
        Cache::forget($this->getMonthlyCreditCacheKey());
    }
}
