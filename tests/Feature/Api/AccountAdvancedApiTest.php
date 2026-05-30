<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\StoragePack;
use App\Models\TeamMember;
use App\Models\Upload;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountAdvancedApiTest extends TestCase
{
    use RefreshDatabase;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_two_factor_enable_and_confirm_flow(): void
    {
        $user = User::factory()->create();
        $token = $this->token($user);

        $secret = $this->withToken($token)
            ->postJson('/api/v1/me/2fa/enable')
            ->assertOk()
            ->assertJsonStructure(['data' => ['secret', 'otpauth_url', 'qr_svg', 'recovery_codes']])
            ->json('data.secret');

        $this->withToken($token)
            ->postJson('/api/v1/me/2fa/confirm', ['code' => '000000'])
            ->assertStatus(422);

        $code = app(TotpService::class)->currentCode($secret);

        $this->withToken($token)
            ->postJson('/api/v1/me/2fa/confirm', ['code' => $code])
            ->assertOk();

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_login_requires_code_when_two_factor_enabled(): void
    {
        $user = User::factory()->create(['email' => '2fa@example.com', 'password' => 'password']);
        $secret = app(TotpService::class)->generateSecret();
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now()])->save();

        $this->postJson('/api/v1/auth/login', ['email' => '2fa@example.com', 'password' => 'password'])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['two_factor_code']]]);

        $this->postJson('/api/v1/auth/login', [
            'email' => '2fa@example.com',
            'password' => 'password',
            'two_factor_code' => app(TotpService::class)->currentCode($secret),
        ])->assertCreated();
    }

    public function test_team_index_includes_owner_and_invite_works(): void
    {
        $user = User::factory()->create();
        $token = $this->token($user);

        $this->withToken($token)
            ->getJson('/api/v1/me/team')
            ->assertOk()
            ->assertJsonPath('data.0.role', 'owner');

        $this->withToken($token)
            ->postJson('/api/v1/me/team', ['name' => 'Laura', 'email' => 'laura@example.com', 'role' => 'editor'])
            ->assertCreated()
            ->assertJsonPath('data.role', 'editor')
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_user_cannot_manage_other_owners_member(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $member = TeamMember::query()->create([
            'owner_id' => $owner->id, 'email' => 'm@example.com', 'role' => 'viewer', 'status' => 'pending',
        ]);

        $this->withToken($this->token($intruder))
            ->deleteJson('/api/v1/me/team/'.$member->id)
            ->assertForbidden();
    }

    public function test_storage_usage_and_packs(): void
    {
        $user = User::factory()->create();
        Upload::query()->create(['user_id' => $user->id, 'path' => 'a.jpg', 'bytes' => 2 * 1048576, 'type' => 'avatar']);
        StoragePack::query()->create(['size_mb' => 500, 'price' => 3.99, 'label' => 'Recomendado', 'is_active' => true]);

        $this->withToken($this->token($user))
            ->getJson('/api/v1/me/storage')
            ->assertOk()
            ->assertJsonPath('data.used_mb', 2)
            ->assertJsonPath('data.total_mb', 100);

        $this->withToken($this->token($user))
            ->getJson('/api/v1/storage-packs')
            ->assertOk()
            ->assertJsonPath('data.0.size_mb', 500);
    }

    public function test_activity_feed_records_login(): void
    {
        $user = User::factory()->create(['email' => 'act@example.com', 'password' => 'password']);

        $this->postJson('/api/v1/auth/login', ['email' => 'act@example.com', 'password' => 'password'])
            ->assertCreated();

        $token = $this->token($user);
        $this->withToken($token)
            ->getJson('/api/v1/me/activity')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'security');
    }

    public function test_stats_aggregate(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->getJson('/api/v1/me/stats')
            ->assertOk()
            ->assertJsonStructure(['data' => ['qr_generated', 'total_scans', 'team_members', 'storage' => ['used_mb', 'total_mb', 'percent']]]);
    }
}
