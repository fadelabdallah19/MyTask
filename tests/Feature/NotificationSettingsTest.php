<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.edit'))
            ->assertOk();
    }

    public function test_guest_cannot_view_settings_page(): void
    {
        $this->get(route('settings.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_update_settings(): void
    {
        $user = User::factory()->create([
            'email_enabled' => true,
            'reminder_1h' => true,
        ]);

        $this->actingAs($user)
            ->put(route('settings.update'), [
                'reminder_1h' => 0,
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertTrue($user->email_enabled);
        $this->assertFalse($user->reminder_1h);
    }
}
