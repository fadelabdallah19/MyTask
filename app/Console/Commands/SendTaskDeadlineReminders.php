<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDeadlineReminder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

#[Signature('tasks:send-reminders')]
#[Description('Send deadline reminder notifications for tasks nearing their deadline.')]
class SendTaskDeadlineReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Checking tasks for deadline reminders...');

        $now = now();
        $processed = 0;

        $tasks = Task::query()
            ->with('user')
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->get();

        foreach ($tasks as $task) {
            $reminders = $this->remindersNeeded($task, $now);

            foreach ($reminders as $reminderType) {
                $processed += $this->sendAcrossChannels($task, $reminderType);
            }
        }

        $this->info("Queued {$processed} reminder(s).");
    }

    /**
     * Decide which reminder types apply for this task right now.
     *
     * @return array<int, string>
     */
    private function remindersNeeded(Task $task, Carbon $now): array
    {
        $deadline = $task->deadline;
        $user = $task->user;

        if (! $user->email_enabled) {
            return [];
        }

        $needed = [];

        if ($user->reminder_24h && $deadline->lte($now->copy()->addHours(24)) && $deadline->gt($now->copy()->addHours(1))) {
            $needed[] = '24h';
        }

        if ($user->reminder_1h && $deadline->lte($now->copy()->addHours(1)) && $deadline->gt($now)) {
            $needed[] = '1h';
        }

        if ($deadline->lte($now)) {
            $needed[] = 'overdue';
        }

        return $needed;
    }

    /**
     * Send a reminder across the user's enabled channels, avoiding duplicates.
     */
    private function sendAcrossChannels(Task $task, string $reminderType): int
    {
        $user = $task->user;
        $notification = new TaskDeadlineReminder($task, $reminderType);
        $channels = $notification->via($user);

        $sentCount = 0;

        foreach ($channels as $channel) {
            if ($this->alreadySent($task, $reminderType, $channel)) {
                continue;
            }

            Notification::send($user, $notification, [$channel]);
            $this->logNotification($task, $reminderType, $channel);

            $sentCount++;
        }

        return $sentCount;
    }

    /**
     * Check the notifications table to prevent duplicate sends.
     */
    private function alreadySent(Task $task, string $reminderType, string $channel): bool
    {
        return DB::table('notifications')
            ->where('task_id', $task->id)
            ->where('type', $reminderType)
            ->where('channel', $channel)
            ->exists();
    }

    /**
     * Record the notification in the notifications table.
     */
    private function logNotification(Task $task, string $reminderType, string $channel): void
    {
        DB::table('notifications')->insert([
            'user_id' => $task->user_id,
            'task_id' => $task->id,
            'type' => $reminderType,
            'channel' => $channel,
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
