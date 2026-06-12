<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AmbassadorTier;
use App\Models\Plan;
use App\Models\Referral;
use App\Models\User;
use App\Services\Referrals\ReferralService;
use Database\Seeders\AmbassadorTierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre el cambio en ReferralService::rewardOnPaid(): solo aplica el
 * multiplicador del tier a referidores con rol `ambassador`. Los `artist`
 * reciben siempre la comisión base de su plan.
 */
final class AmbassadorTierMultiplierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AmbassadorTierSeeder::class);
    }

    private function planWithReward(string $slug, float $reward): Plan
    {
        return Plan::query()->create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'price' => 10,
            'billing_cycle' => 'yearly',
            'features' => [],
            'max_tattoos' => 1,
            'is_active' => true,
            'is_referral' => true,
            'referral_reward' => $reward,
            'payout_mode' => 'credit',
        ]);
    }

    public function test_ambassador_on_silver_gets_125x_reward(): void
    {
        $silver = AmbassadorTier::query()->where('slug', 'silver')->firstOrFail();
        $plan = $this->planWithReward('embajador-test', 10.00);

        $referrer = User::factory()->create([
            'role' => 'ambassador',
            'plan_id' => $plan->id,
            'ambassador_tier_id' => $silver->id,
        ]);
        $referred = User::factory()->create(['referred_by' => $referrer->id]);

        Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'status' => Referral::STATUS_REGISTERED,
        ]);

        app(ReferralService::class)->rewardOnPaid($referred);

        $ref = Referral::query()->where('referred_id', $referred->id)->firstOrFail();
        $this->assertSame(Referral::STATUS_PAID, $ref->status);
        $this->assertSame(1250, $ref->reward_cents, '10 € × 1.25 = 12.50 € = 1250 cents');
    }

    public function test_artist_with_tier_assigned_does_not_get_multiplier(): void
    {
        $silver = AmbassadorTier::query()->where('slug', 'silver')->firstOrFail();
        $plan = $this->planWithReward('artist-test', 12.50);

        // Aunque por error tuviera ambassador_tier_id, su rol manda.
        $referrer = User::factory()->create([
            'role' => 'artist',
            'plan_id' => $plan->id,
            'ambassador_tier_id' => $silver->id,
        ]);
        $referred = User::factory()->create(['referred_by' => $referrer->id]);

        Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'status' => Referral::STATUS_REGISTERED,
        ]);

        app(ReferralService::class)->rewardOnPaid($referred);

        $this->assertSame(1250, Referral::query()->where('referred_id', $referred->id)->value('reward_cents'));
    }

    public function test_paid_referral_triggers_tier_recompute_for_ambassador(): void
    {
        $bronze = AmbassadorTier::query()->where('slug', 'bronze')->firstOrFail();
        $silver = AmbassadorTier::query()->where('slug', 'silver')->firstOrFail();
        $plan = $this->planWithReward('embajador-test2', 5.00);

        $referrer = User::factory()->create([
            'role' => 'ambassador',
            'plan_id' => $plan->id,
            'ambassador_tier_id' => $bronze->id,
        ]);

        // Crear (silver->min_referrals - 1) referrals ya pagados, más 1 pendiente.
        for ($i = 0; $i < (int) $silver->min_referrals - 1; $i++) {
            $r = User::factory()->create();
            Referral::query()->create([
                'referrer_id' => $referrer->id,
                'referred_id' => $r->id,
                'status' => Referral::STATUS_PAID,
            ]);
        }

        $finalReferred = User::factory()->create(['referred_by' => $referrer->id]);
        Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $finalReferred->id,
            'status' => Referral::STATUS_REGISTERED,
        ]);

        app(ReferralService::class)->rewardOnPaid($finalReferred);

        $this->assertSame($silver->id, $referrer->fresh()->ambassador_tier_id);
    }
}
