<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_billing_overview_returns_plan_and_status(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Pro', 'slug' => 'pro', 'price' => 9.99, 'billing_cycle' => 'monthly',
            'features' => ['QR ilimitados'], 'max_tattoos' => 99, 'is_active' => true, 'sort_order' => 1,
        ]);
        $user = User::factory()->create(['plan_id' => $plan->id, 'pm_type' => 'visa', 'pm_last_four' => '4242']);

        $this->withToken($this->token($user))
            ->getJson('/api/v1/me/billing')
            ->assertOk()
            ->assertJsonPath('data.status', 'sin_plan')
            ->assertJsonPath('data.plan.name', 'Pro')
            ->assertJsonPath('data.payment_method.last4', '4242');
    }

    public function test_invoices_empty_without_stripe_customer(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->getJson('/api/v1/me/invoices')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_sessions_list_marks_current(): void
    {
        $user = User::factory()->create();

        $this->withToken($this->token($user))
            ->getJson('/api/v1/me/sessions')
            ->assertOk()
            ->assertJsonPath('data.0.current', true);
    }

    public function test_user_can_revoke_another_session(): void
    {
        $user = User::factory()->create();
        $other = $user->createToken('otro-dispositivo')->accessToken;

        $this->withToken($this->token($user))
            ->deleteJson('/api/v1/me/sessions/'.$other->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $other->id]);
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $token = $this->token($user);

        $this->withToken($token)
            ->patchJson('/api/v1/me/password', [
                'current_password' => 'wrong',
                'password' => 'NuevaClave123!',
                'password_confirmation' => 'NuevaClave123!',
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['error' => ['errors' => ['current_password']]]);

        $this->withToken($token)
            ->patchJson('/api/v1/me/password', [
                'current_password' => 'password',
                'password' => 'NuevaClave123!',
                'password_confirmation' => 'NuevaClave123!',
            ])
            ->assertOk();
    }

    public function test_account_deletion_requires_password(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->withToken($this->token($user))
            ->deleteJson('/api/v1/me', ['current_password' => 'password'])
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
