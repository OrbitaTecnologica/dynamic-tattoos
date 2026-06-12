<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\AmbassadorTier;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\AmbassadorTierSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MeAmbassadorApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PlanSeeder::class, AmbassadorTierSeeder::class]);
    }

    private function ambassador(): User
    {
        $bronze = AmbassadorTier::query()->where('slug', 'bronze')->firstOrFail();
        $plan = Plan::query()->where('slug', 'embajador')->firstOrFail();

        return User::factory()->create([
            'role' => 'ambassador',
            'plan_id' => $plan->id,
            'ambassador_tier_id' => $bronze->id,
            'ambassador_slug' => 'tester',
            'referral_code' => 'TEST0001',
        ]);
    }

    public function test_summary_returns_tier_info_for_ambassador(): void
    {
        $user = $this->ambassador();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/me/ambassador/summary')
            ->assertOk()
            ->assertJsonPath('data.tier.slug', 'bronze')
            ->assertJsonPath('data.next_tier.slug', 'silver')
            ->assertJsonPath('data.payout_mode', 'credit')
            ->assertJsonPath('data.slug', 'tester')
            ->assertJsonStructure(['data' => ['tier', 'next_tier', 'referrals', 'earnings_cents', 'link_url']]);
    }

    public function test_summary_forbidden_for_non_ambassador(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/me/ambassador/summary')
            ->assertForbidden();
    }

    public function test_ambassador_can_change_slug(): void
    {
        $user = $this->ambassador();

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/me/ambassador/slug', ['slug' => 'mi-nuevo-slug'])
            ->assertOk()
            ->assertJsonPath('data.slug', 'mi-nuevo-slug');

        $this->assertSame('mi-nuevo-slug', $user->fresh()->ambassador_slug);
    }

    public function test_reserved_slugs_are_rejected(): void
    {
        $user = $this->ambassador();

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/me/ambassador/slug', ['slug' => 'admin'])
            ->assertStatus(422);
    }

    public function test_invalid_slug_format_is_rejected(): void
    {
        $user = $this->ambassador();

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/me/ambassador/slug', ['slug' => 'Con MAYÚSCULAS'])
            ->assertStatus(422);
    }

    public function test_non_ambassador_cannot_change_slug(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/me/ambassador/slug', ['slug' => 'whatever'])
            ->assertForbidden();
    }
}
