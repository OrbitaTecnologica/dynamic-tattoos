<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_user_can_update_profile_and_name_syncs(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->patchJson('/api/v1/me/profile', [
                'first_name' => 'Miguel',
                'last_name' => 'Novoa',
                'gender' => 'm',
                'city' => 'Madrid',
                'country' => 'España',
                'phones' => [['code' => '+34', 'number' => '612 345 678']],
            ])
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Miguel')
            ->assertJsonPath('data.name', 'Miguel Novoa')
            ->assertJsonPath('data.city', 'Madrid')
            ->assertJsonPath('data.country', 'España')
            ->assertJsonPath('data.phones.0.code', '+34');
    }

    public function test_company_update_creates_and_persists(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->patchJson('/api/v1/me/company', [
                'is_professional' => true,
                'name' => 'Black Ink Studio',
                'vat' => 'ESB87654321',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Black Ink Studio')
            ->assertJsonPath('data.is_professional', true);

        $this->assertDatabaseHas('companies', ['user_id' => $user->id, 'vat' => 'ESB87654321']);
    }

    public function test_preferences_update(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->patchJson('/api/v1/me/preferences', ['language' => 'en', 'currency' => 'USD'])
            ->assertOk()
            ->assertJsonPath('data.language', 'en')
            ->assertJsonPath('data.currency', 'USD');
    }

    public function test_invalid_currency_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->patchJson('/api/v1/me/preferences', ['currency' => 'BTC'])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['currency']]]);
    }

    public function test_notifications_defaults_and_update(): void
    {
        $user = User::factory()->create();
        $token = $this->token($user);

        $this->withToken($token)
            ->getJson('/api/v1/me/notifications')
            ->assertOk()
            ->assertJsonPath('data.qr_created.email', true);

        $this->withToken($token)
            ->patchJson('/api/v1/me/notifications', [
                'notifications' => ['marketing' => ['email' => true, 'push' => true]],
            ])
            ->assertOk()
            ->assertJsonPath('data.marketing.email', true)
            ->assertJsonPath('data.security.email', true);
    }
}
