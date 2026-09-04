<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_tasks(): void
    {
        $this->get(route('tasks.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_own_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSee($task->title);
    }

    public function test_user_can_create_a_task(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tasks.store'), [
                'title' => 'Belajar Laravel',
                'description' => 'Kerjakan CRUD',
                'priority' => 'high',
                'status' => 'todo',
                'deadline' => '2026-09-10 15:00',
            ])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Belajar Laravel',
        ]);
    }

    public function test_user_can_view_own_task_detail(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee($task->title);
    }

    public function test_user_can_update_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('tasks.update', $task), [
                'title' => 'Judul Baru',
                'description' => $task->description,
                'priority' => 'low',
                'status' => 'in_progress',
                'deadline' => $task->deadline,
            ])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Judul Baru',
            'status' => 'in_progress',
        ]);
    }

    public function test_user_can_delete_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('tasks.index'));

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_view_others_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->get(route('tasks.show', $task))
            ->assertForbidden();
    }

    public function test_user_cannot_update_others_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->put(route('tasks.update', $task), [
                'title' => 'Hacked',
                'description' => 'x',
                'priority' => 'low',
                'status' => 'todo',
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_delete_others_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->delete(route('tasks.destroy', $task))
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_task_creation_requires_title(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tasks.store'), [
                'description' => 'Tanpa judul',
                'priority' => 'medium',
                'status' => 'todo',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_user_can_search_tasks_by_title(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Laporan Keuangan']);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Belanja Bulanan']);

        $response = $this->actingAs($user)
            ->get(route('tasks.index', ['search' => 'Laporan']))
            ->assertOk();

        $response->assertSee('Laporan Keuangan')->assertDontSee('Belanja Bulanan');
    }

    public function test_user_can_filter_tasks_by_status(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Selesai', 'status' => 'completed']);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Belum', 'status' => 'todo']);

        $response = $this->actingAs($user)
            ->get(route('tasks.index', ['status' => 'completed']))
            ->assertOk();

        $response->assertSee('Selesai')->assertDontSee('Belum');
    }

    public function test_user_can_filter_tasks_by_priority(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Penting', 'priority' => 'high']);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Ringan', 'priority' => 'low']);

        $response = $this->actingAs($user)
            ->get(route('tasks.index', ['priority' => 'high']))
            ->assertOk();

        $response->assertSee('Penting')->assertDontSee('Ringan');
    }

    public function test_task_without_deadline_has_no_deadline_status(): void
    {
        $task = Task::factory()->create(['deadline' => null]);

        $this->assertSame('no_deadline', $task->deadlineStatus());
    }

    public function test_completed_task_is_not_overdue(): void
    {
        $task = Task::factory()->create([
            'status' => 'completed',
            'deadline' => Carbon::now()->subDay(),
        ]);

        $this->assertSame('completed', $task->deadlineStatus());
        $this->assertFalse($task->isOverdue());
    }

    public function test_task_past_deadline_is_overdue(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        $task = Task::factory()->create([
            'status' => 'todo',
            'deadline' => Carbon::now()->subHour(),
        ]);

        $this->assertSame('overdue', $task->deadlineStatus());
        $this->assertTrue($task->isOverdue());

        Carbon::setTestNow();
    }

    public function test_task_due_today_is_due_today(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        $task = Task::factory()->create([
            'status' => 'todo',
            'deadline' => Carbon::now()->addHours(2),
        ]);

        $this->assertSame('due_today', $task->deadlineStatus());

        Carbon::setTestNow();
    }

    public function test_task_due_tomorrow_is_due_tomorrow(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        $task = Task::factory()->create([
            'status' => 'todo',
            'deadline' => Carbon::now()->addDay()->addHours(2),
        ]);

        $this->assertSame('due_tomorrow', $task->deadlineStatus());

        Carbon::setTestNow();
    }

    public function test_task_with_future_deadline_is_upcoming(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');
        $task = Task::factory()->create([
            'status' => 'todo',
            'deadline' => Carbon::now()->addWeek(),
        ]);

        $this->assertSame('upcoming', $task->deadlineStatus());

        Carbon::setTestNow();
    }

    public function test_partial_index_returns_table_only(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Cari Ini']);

        $response = $this->actingAs($user)
            ->get(route('tasks.index', ['search' => 'Cari', 'partial' => 1]))
            ->assertOk();

        $response->assertSee('Cari Ini');
        $this->assertStringNotContainsString('Daftar Task', $response->getContent());
    }

    public function test_partial_index_respects_filters(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'title' => 'A', 'priority' => 'high']);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'B', 'priority' => 'low']);

        $response = $this->actingAs($user)
            ->get(route('tasks.index', ['priority' => 'high', 'partial' => 1]))
            ->assertOk();

        $response->assertSee('A')->assertDontSee('>B<');
    }

    public function test_deleting_task_moves_it_to_trash(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('tasks.index'));

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
        $this->assertCount(0, $user->tasks()->get());
        $this->assertCount(1, $user->tasks()->onlyTrashed()->get());
    }

    public function test_trash_does_not_show_active_tasks(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Aktif']);
        $trashed = Task::factory()->create(['user_id' => $user->id, 'title' => 'Terhapus']);
        $trashed->delete();

        $response = $this->actingAs($user)
            ->get(route('tasks.trash'))
            ->assertOk();

        $response->assertSee('Terhapus')->assertDontSee('Aktif');
    }

    public function test_user_can_restore_trashed_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $task->delete();

        $this->actingAs($user)
            ->patch(route('tasks.restore', $task))
            ->assertRedirect();

        $this->assertNotSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_user_can_force_delete_trashed_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $task->delete();

        $this->actingAs($user)
            ->delete(route('tasks.force-delete', $task))
            ->assertRedirect();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_force_delete_others_task(): void
    {
        $other = User::factory()->create();
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $other->id]);
        $task->delete();

        $this->actingAs($user)
            ->delete(route('tasks.force-delete', $task))
            ->assertForbidden();
    }

    public function test_pagination_preserves_filter_query_string(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(10)->create(['user_id' => $user->id, 'title' => 'Cari Task']);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Lain']);

        $response = $this->actingAs($user)
            ->get(route('tasks.index', ['search' => 'Cari']))
            ->assertOk();

        $this->assertStringContainsString('search=Cari', $response->getContent());
    }
}
