<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_completion_percentage(): void
    {
        $user = User::factory()->create();

        Task::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        Task::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        Task::factory()->create(['user_id' => $user->id, 'status' => 'todo']);
        Task::factory()->create(['user_id' => $user->id, 'status' => 'in_progress']);

        $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $response->assertSee('50%');
    }

    public function test_dashboard_lists_overdue_tasks(): void
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'todo',
            'deadline' => Carbon::now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $response->assertSee('1');
    }

    public function test_dashboard_does_not_show_other_users_tasks(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Task::factory()->create([
            'user_id' => $other->id,
            'title' => 'Task Rahasia Orang Lain',
            'status' => 'todo',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $response->assertDontSee('Task Rahasia Orang Lain');
        $response->assertSee('0');
    }

    public function test_dashboard_shows_recent_notifications_and_unread_count(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        Notification::create(['user_id' => $user->id, 'task_id' => $task->id, 'type' => '24h', 'channel' => 'mail', 'sent_at' => now()]);
        Notification::create(['user_id' => $user->id, 'task_id' => $task->id, 'type' => 'overdue', 'channel' => 'mail', 'sent_at' => now()->addMinute(), 'read_at' => now()]);

        $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $this->assertSame(1, $response->viewData('unreadNotifications'));
        $this->assertCount(2, $response->viewData('recentNotifications'));
        $response->assertSee('Recent Notifications');
        $response->assertSee('1 baru');
    }

    public function test_dashboard_stats_are_cached_per_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $this->assertTrue(Cache::has("dashboard:{$user->id}"));
        $this->assertFalse(Cache::has("dashboard:{$other->id}"));
    }

    public function test_dashboard_cache_is_cleared_when_task_is_created_or_updated(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => 'todo']);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has("dashboard:{$user->id}"));

        $task->update(['status' => 'completed']);
        $this->assertFalse(Cache::has("dashboard:{$user->id}"));
    }

    public function test_dashboard_cache_is_cleared_on_soft_delete_and_restore(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has("dashboard:{$user->id}"));

        $task->delete();
        $this->assertFalse(Cache::has("dashboard:{$user->id}"));

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has("dashboard:{$user->id}"));

        $task->restore();
        $this->assertFalse(Cache::has("dashboard:{$user->id}"));
    }
}
