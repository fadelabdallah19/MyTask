<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('login'), [
                'email' => 'siapa@contoh.com',
                'password' => 'salah',
            ]);
        }

        $this->post(route('login'), [
            'email' => 'siapa@contoh.com',
            'password' => 'salah',
        ])->assertStatus(429);
    }
}
