<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Tattoo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ApiErrorContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_standard_401_error_shape(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthorized')
            ->assertJsonPath('error.status', 401)
            ->assertJsonPath('error.message', 'Unauthenticated.');
    }

    public function test_api_returns_standard_403_error_shape(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $tattoo = Tattoo::query()->create([
            'user_id' => $other->id,
            'name' => 'Private tattoo',
            'is_active' => true,
        ]);

        $this->withToken($owner->createToken('test')->plainTextToken)
            ->getJson('/api/v1/tattoos/' . $tattoo->id)
            ->assertForbidden()
            ->assertJsonPath('error.code', 'forbidden')
            ->assertJsonPath('error.status', 403)
            ->assertJsonStructure([
                'error' => ['code', 'message', 'status'],
            ]);
    }

    public function test_api_returns_standard_404_error_shape(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/v1/tattoos/999999')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found')
            ->assertJsonPath('error.status', 404)
            ->assertJsonPath('error.message', 'Resource not found.');
    }

    public function test_api_returns_standard_422_error_shape_with_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonPath('error.status', 422)
            ->assertJsonStructure([
                'error' => ['code', 'message', 'status', 'errors'],
            ]);

        $this->assertIsArray($response->json('error.errors'));
    }
}