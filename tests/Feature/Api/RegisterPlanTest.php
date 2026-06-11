<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegisterPlanTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ], $overrides);
    }

    public function test_register_assigns_the_free_plan_when_requested(): void
    {
        $this->seed(PlanSeeder::class);

        $this->postJson('/api/v1/auth/register', $this->payload([
            'email' => 'free@example.com',
            'plan' => 'embajador',
        ]))->assertStatus(202); // Enfoque A: registro sin token hasta verificar email

        $user = User::query()->where('email', 'free@example.com')->firstOrFail();
        $embajador = Plan::query()->where('slug', 'embajador')->firstOrFail();

        $this->assertSame($embajador->id, $user->plan_id);
    }

    public function test_register_does_not_assign_a_paid_plan(): void
    {
        // El plan de pago lo fija el webhook de Stripe tras el pago, no el registro.
        $this->seed(PlanSeeder::class);

        $this->postJson('/api/v1/auth/register', $this->payload([
            'email' => 'paid@example.com',
            'plan' => 'pro',
        ]))->assertStatus(202); // Enfoque A: registro sin token hasta verificar email

        $user = User::query()->where('email', 'paid@example.com')->firstOrFail();

        $this->assertNull($user->plan_id);
    }

    public function test_register_without_plan_leaves_plan_null(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload([
            'email' => 'noplan@example.com',
        ]))->assertStatus(202); // Enfoque A: registro sin token hasta verificar email

        $user = User::query()->where('email', 'noplan@example.com')->firstOrFail();

        $this->assertNull($user->plan_id);
    }

    public function test_register_ignores_unknown_plan_slug(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload([
            'email' => 'unknown@example.com',
            'plan' => 'no-existe',
        ]))->assertStatus(202); // Enfoque A: registro sin token hasta verificar email

        $user = User::query()->where('email', 'unknown@example.com')->firstOrFail();

        $this->assertNull($user->plan_id);
    }
}
