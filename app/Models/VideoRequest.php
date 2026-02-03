<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'youtube_url',
        'youtube_video_id',
        'title',
        'thumbnail',
        'duration_seconds',
        'request_cost',
        'context',
        'status',
        'rating',
        'requested_at',
        'completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that made the request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed requests.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope a query to order by chronological order (FIFO).
     */
    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderBy('requested_at', 'asc');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is completed.
     */
    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    /**
     * Rate the request and mark as done.
     */
    public function rate(string $rating): bool
    {
        return $this->update([
            'rating' => $rating,
            'status' => 'done',
            'completed_at' => $this->completed_at ?? now(),
        ]);
    }

    /**
     * Get the position of this request in the queue.
     * Returns the position number (1-indexed) if pending, null otherwise.
     */
    public function getQueuePosition(): ?int
    {
        if (! $this->isPending()) {
            return null;
        }

        // Count how many pending requests were requested before this one
        $position = self::pending()
            ->where('requested_at', '<', $this->requested_at)
            ->count();

        // Add 1 to make it 1-indexed (first request is position 1, not 0)
        return $position + 1;
    }
}
