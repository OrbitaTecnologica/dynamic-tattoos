<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Tattoo;
use App\Models\TattooContent;
use App\Models\TattooScan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TattooApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tattoo_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/tattoos')->assertUnauthorized();
    }

    public function test_index_returns_only_authenticated_user_tattoos(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Tattoo::query()->create([
            'user_id' => $owner->id,
            'name' => 'Owner tattoo',
            'is_active' => true,
        ]);

        Tattoo::query()->create([
            'user_id' => $other->id,
            'name' => 'Other tattoo',
            'is_active' => true,
        ]);

        $response = $this->withToken($owner->createToken('test')->plainTextToken)
            ->getJson('/api/v1/tattoos');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonMissingPath('data.1');
    }

    public function test_user_cannot_view_other_user_tattoo(): void
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
            ->assertForbidden();
    }

    public function test_user_can_create_update_and_delete_tattoo(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/v1/tattoos', [
            'name' => 'First tattoo',
            'is_active' => true,
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'First tattoo');

        $tattooId = (int) $createResponse->json('data.id');

        $this->withToken($token)->patchJson('/api/v1/tattoos/' . $tattooId, [
            'name' => 'Updated tattoo',
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated tattoo')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)
            ->deleteJson('/api/v1/tattoos/' . $tattooId)
            ->assertNoContent();

        $this->assertDatabaseMissing('tattoos', ['id' => $tattooId]);
    }

    public function test_activate_content_deactivates_previous_active_content(): void
    {
        $user = User::factory()->create();
        $tattoo = Tattoo::query()->create([
            'user_id' => $user->id,
            'name' => 'Content tattoo',
            'is_active' => true,
        ]);

        $oldContent = TattooContent::query()->create([
            'tattoo_id' => $tattoo->id,
            'type' => TattooContent::TYPE_LINK,
            'payload' => [
                'url' => 'https://example.com',
                'label' => 'Old',
                'open_in_new_tab' => true,
            ],
            'is_active' => true,
            'order' => 0,
        ]);

        $response = $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/tattoos/' . $tattoo->id . '/contents/activate', [
                'type' => TattooContent::TYPE_VIDEO,
                'payload' => [
                    'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'platform' => 'youtube',
                    'autoplay' => false,
                    'title' => 'New active',
                ],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.type', TattooContent::TYPE_VIDEO)
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('tattoo_contents', [
            'id' => $oldContent->id,
            'is_active' => false,
        ]);
    }

    public function test_user_cannot_access_other_user_contents_or_scans(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $tattoo = Tattoo::query()->create([
            'user_id' => $other->id,
            'name' => 'Other tattoo',
            'is_active' => true,
        ]);

        TattooScan::query()->create([
            'tattoo_id' => $tattoo->id,
            'device_type' => 'mobile',
            'browser' => 'Chrome',
            'user_agent' => 'UA',
            'scanned_at' => now(),
        ]);

        $token = $owner->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/tattoos/' . $tattoo->id . '/contents')
            ->assertForbidden();

        $this->withToken($token)
            ->postJson('/api/v1/tattoos/' . $tattoo->id . '/contents/activate', [
                'type' => TattooContent::TYPE_LINK,
                'payload' => [
                    'url' => 'https://example.com',
                ],
            ])
            ->assertForbidden();

        $this->withToken($token)
            ->getJson('/api/v1/tattoos/' . $tattoo->id . '/scans')
            ->assertForbidden();
    }

    public function test_scans_index_returns_scans_for_owned_tattoo(): void
    {
        $user = User::factory()->create();

        $tattoo = Tattoo::query()->create([
            'user_id' => $user->id,
            'name' => 'Scans tattoo',
            'is_active' => true,
        ]);

        TattooScan::query()->create([
            'tattoo_id' => $tattoo->id,
            'device_type' => 'mobile',
            'browser' => 'Chrome',
            'user_agent' => 'UA-1',
            'scanned_at' => now()->subMinute(),
        ]);

        TattooScan::query()->create([
            'tattoo_id' => $tattoo->id,
            'device_type' => 'desktop',
            'browser' => 'Firefox',
            'user_agent' => 'UA-2',
            'scanned_at' => now(),
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/v1/tattoos/' . $tattoo->id . '/scans')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
