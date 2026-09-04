<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

#[Fillable(['user_id', 'title', 'description', 'priority', 'status', 'deadline'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * Clear per-user caches and write an audit entry whenever a task changes.
     */
    protected static function booted(): void
    {
        static::saved(function (self $task) {
            self::forgetUserStatsCache($task);

            self::audit($task, $task->wasRecentlyCreated ? 'task.created' : 'task.updated');
        });

        static::deleted(function (self $task) {
            self::forgetUserStatsCache($task);

            self::audit($task, $task->isForceDeleting() ? 'task.force_deleted' : 'task.deleted');
        });

        static::restored(function (self $task) {
            self::forgetUserStatsCache($task);

            self::audit($task, 'task.restored');
        });
    }

    /**
     * Log the change to the activity log if there is an authenticated user.
     */
    private static function audit(self $task, string $action): void
    {
        if ($user = auth()->user()) {
            ActivityLog::record($user, $action, ['task_id' => $task->id, 'title' => $task->title]);
        }
    }

    private static function forgetUserStatsCache(self $task): void
    {
        Cache::forget("dashboard:{$task->user_id}");
        Cache::forget("report:{$task->user_id}");
    }

    /**
     * Scope: the current user's tasks.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * A task belongs to one user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
        ];
    }

    public const PRIORITIES = ['low', 'medium', 'high'];

    public const STATUSES = ['todo', 'in_progress', 'completed'];

    /**
     * Categorise a task based on its deadline relative to now.
     *
     * Priority order matters: overdue beats due, due today beats upcoming.
     *
     * @return string one of: no_deadline, completed, overdue, due_today, due_tomorrow, upcoming
     */
    public function deadlineStatus(): string
    {
        if ($this->deadline === null) {
            return 'no_deadline';
        }

        if ($this->status === 'completed') {
            return 'completed';
        }

        $now = now();

        if ($this->deadline->lt($now)) {
            return 'overdue';
        }

        if ($this->deadline->lte($now->copy()->endOfDay())) {
            return 'due_today';
        }

        if ($this->deadline->lte($now->copy()->addDay()->endOfDay())) {
            return 'due_tomorrow';
        }

        return 'upcoming';
    }

    /**
     * Determine if the task is overdue (past deadline and not completed).
     */
    public function isOverdue(): bool
    {
        return $this->deadlineStatus() === 'overdue';
    }
}
