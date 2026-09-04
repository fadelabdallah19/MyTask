<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_report(): void
    {
        $this->get(route('report.index'))->assertRedirect(route('login'));
    }

    public function test_report_shows_status_and_priority_counts(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'status' => 'completed', 'priority' => 'low']);
        Task::factory()->create(['user_id' => $user->id, 'status' => 'todo', 'priority' => 'high']);
        Task::factory()->create(['user_id' => $user->id, 'status' => 'todo', 'priority' => 'high']);

        $response = $this->actingAs($user)
            ->get(route('report.index'))
            ->assertOk();

        $this->assertSame(3, $response->viewData('totalTasks'));
        $this->assertEqualsCanonicalizing(['completed' => 1, 'todo' => 2], $response->viewData('byStatus'));
        $this->assertEqualsCanonicalizing(['low' => 1, 'high' => 2], $response->viewData('byPriority'));
    }

    public function test_report_only_counts_own_tasks(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Task::factory()->count(5)->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)
            ->get(route('report.index'))
            ->assertOk();

        $this->assertSame(0, $response->viewData('totalTasks'));
    }

    public function test_report_is_cached_per_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->get(route('report.index'))->assertOk();

        $this->assertTrue(Cache::has("report:{$user->id}"));
        $this->assertFalse(Cache::has("report:{$other->id}"));
    }

    public function test_report_cache_is_cleared_when_task_is_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('report.index'))->assertOk();
        $this->assertTrue(Cache::has("report:{$user->id}"));

        Task::factory()->create(['user_id' => $user->id, 'status' => 'todo', 'priority' => 'high']);
        $this->assertFalse(Cache::has("report:{$user->id}"));
    }
}
