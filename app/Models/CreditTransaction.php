<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    use HasFactory;

    public const TYPE_MONTHLY_GRANT = 'monthly_grant';

    public const TYPE_REQUEST = 'request';

    public const TYPE_BONUS = 'bonus';

    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'video_request_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    /**
     * Get the user that owns this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the video request associated with this transaction.
     */
    public function videoRequest(): BelongsTo
    {
        return $this->belongsTo(VideoRequest::class);
    }

    /**
     * Scope a query to only include transactions for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include transactions of a specific type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include transactions from the current month.
     */
    public function scopeForCurrentMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Scope a query to only include credit grants (positive amounts).
     */
    public function scopeGrants(Builder $query): Builder
    {
        return $query->where('amount', '>', 0);
    }

    /**
     * Scope a query to only include debits (negative amounts).
     */
    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('amount', '<', 0);
    }
}
