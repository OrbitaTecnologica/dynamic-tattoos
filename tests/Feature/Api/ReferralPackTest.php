<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Livewire\Admin\Referrals;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralPackTest extends TestCase
{
    use RefreshDatabase;

    /** @param array<string, mixed> $overrides
     * @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@referral.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'captcha_token' => 'test',
        ], $overrides);
    }

    public function test_register_generates_referral_code_for_every_user(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload());

        $user = User::where('email', 'nuevo@referral.test')->firstOrFail();
        $this->assertNotNull($user->referral_code);
        $this->assertSame(8, strlen($user->referral_code));
    }

    public function test_reregister_of_unverified_email_keeps_referral_attribution(): void
    {
        $referrer = User::factory()->create(['referral_code' => 'REFER123']);

        // Primer intento sin código (p. ej. no le llegó el OTP)
        $this->postJson('/api/v1/auth/register', $this->payload([
            'email' => 'reintento@referral.test',
        ]));
        User::where('email', 'reintento@referral.test')->firstOrFail()
            ->forceFill(['email_verified_at' => null])->save();

        // Reintento con el código del referidor
        $this->postJson('/api/v1/auth/register', $this->payload([
            'email' => 'reintento@referral.test',
            'referral_code' => 'REFER123',
        ]))->assertStatus(202);

        $referred = User::where('email', 'reintento@referral.test')->firstOrFail();
        $this->assertSame($referrer->id, $referred->referred_by);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'status' => Referral::STATUS_REGISTERED,
        ]);
    }

    public function test_admin_recommenders_tab_lists_users_and_generates_codes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $sinCodigo = User::factory()->create(['name' => 'Sin Codigo']);

        Livewire::test(Referrals::class)
            ->call('showTab', 'recomendadores')
            ->assertSee('Sin Codigo')
            ->assertSee('Generar código')
            ->call('generateCode', $sinCodigo->id)
            ->assertDispatched('toast');

        $this->assertNotNull($sinCodigo->fresh()->referral_code);
    }
}
