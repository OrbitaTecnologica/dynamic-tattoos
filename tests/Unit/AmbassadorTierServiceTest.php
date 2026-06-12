<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AmbassadorTier;
use App\Models\Referral;
use App\Models\User;
use App\Services\Referrals\AmbassadorTierService;
use Database\Seeders\AmbassadorTierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AmbassadorTierServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AmbassadorTierSeeder::class);
    }

    public function test_default_tier_is_bronze(): void
    {
        $tier = app(AmbassadorTierService::class)->defaultTier();

        $this->assertSame('bronze', $tier->slug);
        $this->assertSame(0, $tier->min_referrals);
    }

    public function test_recompute_returns_null_for_non_ambassadors(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $tier = app(AmbassadorTierService::class)->recompute($user);

        $this->assertNull($tier);
        $this->assertNull($user->fresh()->ambassador_tier_id);
    }

    public function test_recompute_promotes_to_silver_at_threshold(): void
    {
        $bronze = AmbassadorTier::query()->where('slug', 'bronze')->firstOrFail();
        $silver = AmbassadorTier::query()->where('slug', 'silver')->firstOrFail();

        $user = User::factory()->create([
            'role' => 'ambassador',
            'ambassador_tier_id' => $bronze->id,
        ]);

        // 10 referidos pagados (umbral de Plata = 10)
        for ($i = 0; $i < (int) $silver->min_referrals; $i++) {
            $referred = User::factory()->create();
            Referral::query()->create([
                'referrer_id' => $user->id,
                'referred_id' => $referred->id,
                'status' => Referral::STATUS_PAID,
            ]);
        }

        $result = app(AmbassadorTierService::class)->recompute($user);

        $this->assertNotNull($result);
        $this->assertSame('silver', $result->slug);
        $this->assertSame($silver->id, $user->fresh()->ambassador_tier_id);
    }

    public function test_recompute_does_not_demote(): void
    {
        $silver = AmbassadorTier::query()->where('slug', 'silver')->firstOrFail();

        $user = User::factory()->create([
            'role' => 'ambassador',
            'ambassador_tier_id' => $silver->id,
        ]);

        // Cero referidos pagados pero ya está en Plata: no debe degradar.
        $result = app(AmbassadorTierService::class)->recompute($user);

        $this->assertSame('silver', $user->fresh()->tier->slug);
        // El método devuelve el tier que le corresponde por count (Bronce),
        // pero no degrada al usuario.
        $this->assertSame('bronze', $result?->slug);
    }
}
