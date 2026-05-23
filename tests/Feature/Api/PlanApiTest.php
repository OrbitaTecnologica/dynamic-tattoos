<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_plans_index_returns_only_active_plans_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        Plan::query()->create([
            'name' => 'Active Plan',
            'slug' => 'active-plan',
            'price' => 9.99,
            'billing_cycle' => 'monthly',
            'features' => ['a'],
            'max_tattoos' => 2,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Plan::query()->create([
            'name' => 'Inactive Plan',
            'slug' => 'inactive-plan',
            'price' => 99.99,
            'billing_cycle' => 'yearly',
            'features' => ['b'],
            'max_tattoos' => 20,
            'is_active' => false,
            'sort_order' => 1,
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/v1/plans')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Active Plan');
    }

    public function test_billing_subscription_show_returns_subscription_payload(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Basic',
            'slug' => 'basic',
            'price' => 9.99,
            'billing_cycle' => 'monthly',
            'features' => ['a'],
            'max_tattoos' => 2,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $user = User::factory()->create([
            'plan_id' => $plan->id,
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/v1/billing/subscription')
            ->assertOk()
            ->assertJsonPath('data.status', 'sin_plan')
            ->assertJsonPath('data.plan.id', $plan->id);
    }

    public function test_non_admin_cannot_manage_plans(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $plan = Plan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'price' => 5.00,
            'billing_cycle' => 'monthly',
            'features' => ['a'],
            'max_tattoos' => 1,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/plans')
            ->assertForbidden();

        $this->withToken($token)
            ->postJson('/api/v1/admin/plans', [
                'name' => 'Pro',
                'billing_cycle' => 'monthly',
                'price' => 20,
                'features' => ['f1'],
                'max_tattoos' => 20,
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->assertForbidden();

        $this->withToken($token)
            ->patchJson('/api/v1/admin/plans/' . $plan->id, [
                'name' => 'Starter Updated',
            ])
            ->assertForbidden();

        $this->withToken($token)
            ->deleteJson('/api/v1/admin/plans/' . $plan->id)
            ->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_plan(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $token = $admin->createToken('test')->plainTextToken;

        $createResponse = $this->withToken($token)
            ->postJson('/api/v1/admin/plans', [
                'name' => 'Business',
                'billing_cycle' => 'yearly',
                'price' => 120,
                'features' => ['priority support', 'analytics'],
                'max_tattoos' => 100,
                'is_active' => true,
                'sort_order' => 3,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Business');

        $planId = (int) $createResponse->json('data.id');

        $this->withToken($token)
            ->patchJson('/api/v1/admin/plans/' . $planId, [
                'name' => 'Business Plus',
                'price' => 150,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Business Plus')
            ->assertJsonPath('data.price', 150);

        $this->withToken($token)
            ->deleteJson('/api/v1/admin/plans/' . $planId)
            ->assertNoContent();

        $this->assertDatabaseMissing('plans', [
            'id' => $planId,
        ]);
    }
}
