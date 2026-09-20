<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'test@buildcare.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@buildcare.com',
            'password' => 'password',
        ]);

        $response->assertNoContent();
        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@buildcare.com',
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])->post('/login', [
            'email' => 'test@buildcare.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_authenticated_users_can_fetch_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.role', $user->role);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertNoContent();
        $this->assertGuest();
    }
}
