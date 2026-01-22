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
     * Mark the request as done.
     */
    public function markAsDone(): bool
    {
        return $this->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);
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
}
