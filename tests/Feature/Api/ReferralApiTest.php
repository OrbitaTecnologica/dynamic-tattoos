<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Cashier\Events\WebhookHandled;
use Tests\TestCase;

final class ReferralApiTest extends TestCase
{
    use RefreshDatabase;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    private function partnerPlan(): Plan
    {
        return Plan::query()->create([
            'name' => 'Partner', 'slug' => 'partner', 'price' => 29.90, 'billing_cycle' => 'yearly',
            'features' => ['QR de referidos'], 'max_tattoos' => 99, 'is_active' => true, 'is_referral' => true,
        ]);
    }

    public function test_registration_links_referrer_via_code(): void
    {
        $referrer = User::factory()->create(['referral_code' => 'ABC12345']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Nuevo',
            'email' => 'nuevo@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'referral_code' => 'ABC12345',
        ])->assertStatus(202); // registro Enfoque A: sin token hasta verificar email

        $referred = User::query()->where('email', 'nuevo@example.com')->firstOrFail();

        $this->assertSame($referrer->id, $referred->referred_by);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'status' => Referral::STATUS_REGISTERED,
        ]);
    }

    public function test_visit_endpoint_records_scan(): void
    {
        $referrer = User::factory()->create(['referral_code' => 'SHOP0001']);

        $this->postJson('/api/v1/referrals/visit', ['code' => 'SHOP0001'])->assertOk();

        $this->assertDatabaseHas('referral_visits', ['referrer_id' => $referrer->id, 'code' => 'SHOP0001']);
    }

    public function test_monitoring_endpoint_is_gated_to_partner_plan(): void
    {
        $plain = User::factory()->create();
        $this->actingAs($plain, 'sanctum')->getJson('/api/v1/me/referrals')->assertForbidden();

        $partner = User::factory()->create(['plan_id' => $this->partnerPlan()->id]);
        $this->actingAs($partner, 'sanctum')
            ->getJson('/api/v1/me/referrals')
            ->assertOk()
            ->assertJsonStructure(['data' => ['code', 'share_url', 'qr_svg', 'stats' => ['visits', 'registered', 'paid', 'reward_total']]]);
    }

    public function test_paid_referral_marks_conversion_and_reward(): void
    {
        Queue::fake(); // evita SyncUserPlanJob (Stripe)

        $referrer = User::factory()->create(['referral_code' => 'PARTNER1']);
        $referred = User::factory()->create(['stripe_id' => 'cus_referred1', 'referred_by' => $referrer->id]);
        Referral::query()->create([
            'referrer_id' => $referrer->id, 'referred_id' => $referred->id, 'status' => Referral::STATUS_REGISTERED,
        ]);

        event(new WebhookHandled([
            'id' => 'evt_ref_1',
            'type' => 'customer.subscription.created',
            'data' => ['object' => [
                'customer' => 'cus_referred1',
                'current_period_end' => now()->addYear()->timestamp,
            ]],
        ]));

        $this->assertDatabaseHas('referrals', [
            'referred_id' => $referred->id,
            'status' => Referral::STATUS_PAID,
            'reward_cents' => 500,
        ]);
    }

    public function test_paid_referral_uses_plan_specific_reward(): void
    {
        Queue::fake();

        $plan = Plan::query()->create([
            'name' => 'Partner Pro', 'slug' => 'partner-pro', 'price' => 49.90, 'billing_cycle' => 'yearly',
            'features' => [], 'max_tattoos' => 99, 'is_active' => true, 'is_referral' => true, 'referral_reward' => 10.00,
        ]);

        $referrer = User::factory()->create(['referral_code' => 'PARTNERX', 'plan_id' => $plan->id]);
        $referred = User::factory()->create(['stripe_id' => 'cus_referredX', 'referred_by' => $referrer->id]);
        Referral::query()->create([
            'referrer_id' => $referrer->id, 'referred_id' => $referred->id, 'status' => Referral::STATUS_REGISTERED,
        ]);

        event(new WebhookHandled([
            'id' => 'evt_ref_2',
            'type' => 'customer.subscription.created',
            'data' => ['object' => [
                'customer' => 'cus_referredX',
                'current_period_end' => now()->addYear()->timestamp,
            ]],
        ]));

        // 10 € del plan del referente, en vez del global de 5 €.
        $this->assertDatabaseHas('referrals', [
            'referred_id' => $referred->id,
            'status' => Referral::STATUS_PAID,
            'reward_cents' => 1000,
        ]);
    }
}
