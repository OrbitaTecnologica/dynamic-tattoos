<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlanSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_five_official_plans_in_order(): void
    {
        $this->seed(PlanSeeder::class);

        $active = Plan::query()->active()->ordered()->get();

        $this->assertCount(5, $active);
        $this->assertSame(
            ['embajador', 'start', 'plus', 'pro', 'empresa'],
            $active->pluck('slug')->all(),
        );

        // Todos son suscripción anual y participan del programa de referidos.
        foreach ($active as $plan) {
            $this->assertSame('yearly', $plan->billing_cycle);
            $this->assertTrue($plan->is_referral, "{$plan->slug} debería tener is_referral");
        }
    }

    public function test_seeder_sets_prices_and_referral_rewards(): void
    {
        $this->seed(PlanSeeder::class);

        $expected = [
            'embajador' => ['price' => 0.0, 'reward' => 5.0],
            'start' => ['price' => 35.0, 'reward' => 7.5],
            'plus' => ['price' => 55.0, 'reward' => 10.0],
            'pro' => ['price' => 65.0, 'reward' => 12.5],
            'empresa' => ['price' => 100.0, 'reward' => 20.0],
        ];

        foreach ($expected as $slug => $vals) {
            $plan = Plan::query()->where('slug', $slug)->firstOrFail();
            $this->assertEquals($vals['price'], (float) $plan->price, "precio de {$slug}");
            $this->assertEquals($vals['reward'], (float) $plan->referral_reward, "comisión de {$slug}");
        }

        // Pro es el plan destacado.
        $this->assertTrue(Plan::query()->where('slug', 'pro')->firstOrFail()->is_featured);
    }

    public function test_seeder_deactivates_legacy_plans(): void
    {
        // Un plan del modelo viejo presente en la BD (p.ej. en el servidor).
        Plan::query()->create([
            'name' => 'Básico',
            'slug' => 'basico',
            'price' => 4.90,
            'billing_cycle' => 'yearly',
            'features' => ['legacy'],
            'max_tattoos' => 1,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->seed(PlanSeeder::class);

        // No se borra (puede haber usuarios con plan_id), pero queda inactivo
        // para no aparecer en GET /plans, y solo quedan 5 activos.
        $this->assertDatabaseHas('plans', ['slug' => 'basico', 'is_active' => false]);
        $this->assertSame(5, Plan::query()->active()->count());
    }
}
