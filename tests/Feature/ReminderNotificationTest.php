<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDeadlineReminder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_24h_is_sent_and_logged(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'todo',
            'deadline' => '2026-09-03 09:00:00',
        ]);

        $this->artisan('tasks:send-reminders')->assertSuccessful();

        Notification::assertSentTo(
            $user,
            TaskDeadlineReminder::class,
            fn ($notification) => $notification->reminderType === '24h'
        );

        $this->assertDatabaseHas('notifications', [
            'task_id' => $task->id,
            'type' => '24h',
            'channel' => 'mail',
        ]);

        Carbon::setTestNow();
    }

    public function test_reminder_is_not_duplicated(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'todo',
            'deadline' => '2026-09-03 09:00:00',
        ]);

        $this->artisan('tasks:send-reminders')->assertSuccessful();
        $this->artisan('tasks:send-reminders')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 1);

        Carbon::setTestNow();
    }

    public function test_reminder_not_sent_when_email_disabled(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        Notification::fake();

        $user = User::factory()->create(['email_enabled' => false]);
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'todo',
            'deadline' => '2026-09-03 09:00:00',
        ]);

        $this->artisan('tasks:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertDatabaseCount('notifications', 0);

        Carbon::setTestNow();
    }

    public function test_reminder_24h_not_sent_for_completed_task(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        Notification::fake();

        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'deadline' => '2026-09-03 09:00:00',
        ]);

        $this->artisan('tasks:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();

        Carbon::setTestNow();
    }

    public function test_overdue_task_gets_notification(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        Notification::fake();

        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'todo',
            'deadline' => '2026-09-01 09:00:00',
        ]);

        $this->artisan('tasks:send-reminders')->assertSuccessful();

        Notification::assertSentTo(
            $user,
            TaskDeadlineReminder::class,
            fn ($notification) => $notification->reminderType === 'overdue'
        );

        Carbon::setTestNow();
    }

    public function test_user_with_no_email_gets_no_reminders(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        Notification::fake();

        $user = User::factory()->create(['email_enabled' => false]);
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'todo',
            'deadline' => '2026-09-03 09:00:00',
        ]);

        $this->artisan('tasks:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertDatabaseCount('notifications', 0);

        Carbon::setTestNow();
    }

    public function test_email_reminder_not_duplicated(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'todo',
            'deadline' => '2026-09-03 09:00:00',
        ]);

        $this->artisan('tasks:send-reminders')->assertSuccessful();
        $this->artisan('tasks:send-reminders')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', ['task_id' => $task->id, 'type' => '24h', 'channel' => 'mail']);

        Carbon::setTestNow();
    }
}
