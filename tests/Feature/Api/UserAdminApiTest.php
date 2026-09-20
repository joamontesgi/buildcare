<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->create(['role' => User::ROLE_CLERK]);

        $response = $this->actingAs($admin)->getJson('/api/users');

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_non_admin_cannot_list_users(): void
    {
        $clerk = User::factory()->create(['role' => User::ROLE_CLERK]);

        $this->actingAs($clerk)
            ->getJson('/api/users')
            ->assertForbidden();
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'name' => 'Nuevo Clerk',
            'email' => 'clerk@buildcare.test',
            'password' => 'password123',
            'role' => User::ROLE_CLERK,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.email', 'clerk@buildcare.test');
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->deleteJson("/api/users/{$admin->id}")
            ->assertStatus(422);
    }
}
