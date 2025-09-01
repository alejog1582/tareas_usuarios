<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the possible status values.
     *
     * @return array<string>
     */
    public static function getStatusOptions(): array
    {
        return ['pending', 'in_progress', 'completed'];
    }

    /**
     * Scope a query to only include pending tasks.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include in progress tasks.
     */
    public function scopeInProgress(Builder $query): void
    {
        $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include completed tasks.
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include tasks for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Check if the task is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the task is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
