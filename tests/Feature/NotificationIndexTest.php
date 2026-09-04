<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_notifications(): void
    {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_sees_only_their_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::factory()->create(['user_id' => $user->id]);
        $myNotif = Notification::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'type' => '24h',
            'channel' => 'mail',
            'sent_at' => now(),
        ]);
        Notification::create([
            'user_id' => $otherUser->id,
            'task_id' => $task->id,
            'type' => 'overdue',
            'channel' => 'mail',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk();

        $response->assertViewHas('notifications', function ($notifications) use ($myNotif) {
            return $notifications->pluck('id')->contains($myNotif->id);
        });

        $this->assertSame(1, $this->actingAs($user)->get(route('notifications.index'))->viewData('notifications')->total());
    }

    public function test_notification_index_is_empty_when_no_logs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Belum ada notifikasi');
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notif = Notification::create([
            'user_id' => $user->id,
            'type' => '24h',
            'channel' => 'mail',
            'sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('notifications.read', $notif))
            ->assertRedirect();

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_user_cannot_mark_others_notification_as_read(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $notif = Notification::create([
            'user_id' => $other->id,
            'type' => '24h',
            'channel' => 'mail',
            'sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('notifications.read', $notif))
            ->assertForbidden();

        $this->assertNull($notif->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $a = Notification::create(['user_id' => $user->id, 'type' => '24h', 'channel' => 'mail', 'sent_at' => now()]);
        $b = Notification::create(['user_id' => $user->id, 'type' => '1h', 'channel' => 'mail', 'sent_at' => now()]);

        $this->actingAs($user)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertNotNull($a->fresh()->read_at);
        $this->assertNotNull($b->fresh()->read_at);
    }

    public function test_index_shows_unread_count(): void
    {
        $user = User::factory()->create();
        Notification::create(['user_id' => $user->id, 'type' => '24h', 'channel' => 'mail', 'sent_at' => now()]);
        Notification::create(['user_id' => $user->id, 'type' => 'overdue', 'channel' => 'mail', 'sent_at' => now()]);
        Notification::create(['user_id' => $user->id, 'type' => '1h', 'channel' => 'mail', 'sent_at' => now(), 'read_at' => now()]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk();

        $this->assertSame(2, $response->viewData('unreadCount'));
    }

    public function test_nav_shows_unread_badge_across_pages(): void
    {
        $user = User::factory()->create();
        Notification::create(['user_id' => $user->id, 'type' => '24h', 'channel' => 'mail', 'sent_at' => now()]);

        $this->actingAs($user)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Notifikasi')
            ->assertSeeInOrder(['Notifikasi', '1']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Notifikasi', '1']);
    }

    public function test_nav_has_no_badge_when_all_read(): void
    {
        $user = User::factory()->create();
        Notification::create([
            'user_id' => $user->id,
            'type' => '24h',
            'channel' => 'mail',
            'sent_at' => now(),
            'read_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Notifikasi')
            ->assertDontSee('bg-red-100 text-red-700');
    }
}
