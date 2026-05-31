<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegisterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Nuevo Cliente',
            'email' => 'Nuevo@Example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'device_name' => 'frontend-web',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', 'nuevo@example.com')
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user' => ['id', 'email']]]);

        $this->assertDatabaseHas('users', ['email' => 'nuevo@example.com', 'role' => 'user']);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Otro',
            'email' => 'taken@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['email']]]);
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Otro',
            'email' => 'mismatch@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Different1!',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['password']]]);
    }
}
