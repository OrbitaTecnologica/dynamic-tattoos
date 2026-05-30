<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\StoragePack;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Events\WebhookHandled;
use Tests\TestCase;

final class TechDebtClosureApiTest extends TestCase
{
    use RefreshDatabase;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_avatar_upload_records_storage_usage(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->postJson('/api/v1/me/avatar', [
                'avatar' => UploadedFile::fake()->image('avatar.jpg')->size(1024), // 1 MB
            ])
            ->assertOk();

        $this->assertDatabaseHas('uploads', ['user_id' => $user->id, 'type' => 'avatar']);

        $this->withToken($this->token($user))
            ->getJson('/api/v1/me/storage')
            ->assertOk()
            ->assertJsonPath('data.used_mb', 1);
    }

    public function test_replacing_avatar_purges_previous_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $token = $this->token($user);

        $this->withToken($token)->postJson('/api/v1/me/avatar', [
            'avatar' => UploadedFile::fake()->image('a.jpg')->size(512),
        ])->assertOk();

        $this->withToken($token)->postJson('/api/v1/me/avatar', [
            'avatar' => UploadedFile::fake()->image('b.jpg')->size(512),
        ])->assertOk();

        // Only the latest upload row should remain for that type.
        $this->assertSame(1, $user->uploads()->where('type', 'avatar')->count());
    }

    public function test_team_invitation_can_be_accepted(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'laura@example.com']);

        $this->actingAs($owner, 'sanctum')
            ->postJson('/api/v1/me/team', ['name' => 'Laura', 'email' => 'laura@example.com', 'role' => 'editor'])
            ->assertCreated();

        $member = TeamMember::query()->where('email', 'laura@example.com')->firstOrFail();
        Mail::assertSent(\App\Mail\TeamInvitationMail::class);

        $this->actingAs($invitee, 'sanctum')
            ->postJson('/api/v1/team/invitations/'.$member->invitation_token.'/accept')
            ->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('team_members', [
            'id' => $member->id,
            'member_user_id' => $invitee->id,
            'status' => 'active',
        ]);
    }

    public function test_storage_pack_checkout_webhook_credits_quota(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_test123', 'extra_storage_mb' => 0]);
        $pack = StoragePack::query()->create([
            'size_mb' => 500, 'price' => 3.99, 'label' => 'Recomendado',
            'stripe_price_id' => 'price_abc', 'is_active' => true,
        ]);

        event(new WebhookHandled([
            'id' => 'evt_test_1',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'customer' => 'cus_test123',
                'metadata' => ['storage_pack_id' => (string) $pack->id],
            ]],
        ]));

        $this->assertSame(500, (int) $user->fresh()->extra_storage_mb);
    }

    public function test_login_accepts_two_factor_recovery_code(): void
    {
        $secret = app(TotpService::class)->generateSecret();
        $user = User::factory()->create(['email' => 'rec@example.com', 'password' => 'password']);
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['AAAAA-BBBBB', 'CCCCC-DDDDD'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'rec@example.com',
            'password' => 'password',
            'two_factor_code' => 'AAAAA-BBBBB',
        ])->assertCreated();

        // Recovery code is single-use.
        $remaining = $user->fresh()->two_factor_recovery_codes;
        $this->assertNotContains('AAAAA-BBBBB', $remaining);
        $this->assertContains('CCCCC-DDDDD', $remaining);
    }
}
