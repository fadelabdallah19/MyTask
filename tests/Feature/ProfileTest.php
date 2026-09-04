<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_profile(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create(['name' => 'Budi']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Andi',
                'email' => $user->email,
            ])
            ->assertRedirect();

        $this->assertSame('Andi', $user->fresh()->name);
    }

    public function test_user_cannot_use_other_user_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['email' => 'ambil@example.com']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => 'ambil@example.com',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_user_can_change_password_with_correct_current(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->patch(route('profile.password'), [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_user_cannot_change_password_with_wrong_current(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->patch(route('profile.password'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
