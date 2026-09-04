<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertOk();

        $response->assertJsonStructure(['token']);
        $this->assertNotNull($user->fresh()->api_token);
    }

    public function test_api_login_rejects_wrong_credentials(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'salah',
        ])->assertUnprocessable();
    }

    public function test_api_requires_token(): void
    {
        $this->getJson('/api/v1/tasks')->assertUnauthorized();
    }

    public function test_api_rejects_invalid_token(): void
    {
        $this->withToken('token-salah')->getJson('/api/v1/tasks')->assertUnauthorized();
    }

    public function test_api_lists_own_tasks_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $ownTask = Task::factory()->create(['user_id' => $user->id, 'title' => 'Task Saya']);
        Task::factory()->create(['user_id' => $other->id, 'title' => 'Task Orang Lain']);

        $this->actingWithToken($user)
            ->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownTask->id)
            ->assertJsonMissing(['title' => 'Task Orang Lain']);
    }

    public function test_api_shows_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'title' => 'Task Detail']);

        $this->actingWithToken($user)
            ->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Task Detail')
            ->assertJsonPath('data.status', $task->status);
    }

    public function test_api_cannot_access_other_users_task(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $other->id]);

        $this->actingWithToken($user)
            ->getJson("/api/v1/tasks/{$task->id}")
            ->assertNotFound();
    }

    public function test_api_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'siapa@contoh.com',
                'password' => 'salah',
            ]);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'siapa@contoh.com',
            'password' => 'salah',
        ])->assertStatus(429);
    }

    private function actingWithToken(User $user): static
    {
        $user->forceFill(['api_token' => Str::random(80)])->save();

        return $this->withToken($user->api_token);
    }
}
