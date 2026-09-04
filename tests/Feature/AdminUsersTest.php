<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_admin_users_page(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_view_admin_users_page(): void
    {
        $user = User::factory()->create(['role' => Role::User]);

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_admin_can_view_all_users_with_task_counts(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $userA = User::factory()->create(['name' => 'Andi']);
        $userB = User::factory()->create(['name' => 'Budi']);

        Task::factory()->count(3)->create(['user_id' => $userA->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();

        $response->assertSee('Andi');
        $response->assertSee('Budi');
        $response->assertSee('3');
    }

    public function test_nav_hides_users_link_for_non_admin(): void
    {
        $user = User::factory()->create(['role' => Role::User]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Pengguna');
    }

    public function test_nav_shows_users_link_for_admin(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengguna');
    }
}
