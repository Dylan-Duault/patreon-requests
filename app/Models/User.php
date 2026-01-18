<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
     * Get the number of remaining requests for this month.
     */
    public function getRemainingRequests(): int
    {
        $limit = $this->getMonthlyRequestLimit();

        if ($limit === 0) {
            return 0;
        }

        $usedThisMonth = $this->requests()
            ->whereMonth('requested_at', now()->month)
            ->whereYear('requested_at', now()->year)
            ->count();

        return max(0, $limit - $usedThisMonth);
    }

    /**
     * Check if the user can make a new request.
     */
    public function canMakeRequest(): bool
    {
        return $this->isActivePatron() && $this->getRemainingRequests() > 0;
    }
}
