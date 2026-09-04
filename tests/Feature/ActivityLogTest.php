<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_creation_logs_activity(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Buat Log',
            'description' => null,
            'priority' => 'medium',
            'status' => 'todo',
            'deadline' => null,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'task.created',
        ]);
    }

    public function test_soft_delete_and_restore_log_activity(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->delete(route('tasks.destroy', $task));
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'task.deleted']);

        $this->actingAs($user)->patch(route('tasks.restore', $task));
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'task.restored']);
    }

    public function test_login_logs_activity(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'auth.login']);
    }

    public function test_guest_cannot_view_activity_log(): void
    {
        $this->get(route('admin.logs.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_view_activity_log(): void
    {
        $user = User::factory()->create(['role' => Role::User]);

        $this->actingAs($user)->get(route('admin.logs.index'))->assertForbidden();
    }

    public function test_admin_can_view_activity_log(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        ActivityLog::factory()->create(['user_id' => $admin->id, 'action' => 'auth.login']);

        $this->actingAs($admin)
            ->get(route('admin.logs.index'))
            ->assertOk()
            ->assertSee('auth.login');
    }
}
