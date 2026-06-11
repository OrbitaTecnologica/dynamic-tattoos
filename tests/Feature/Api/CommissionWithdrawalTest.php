<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CommissionWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    private function planWith(string $slug, float $price): Plan
    {
        return Plan::query()->create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'price' => $price,
            'billing_cycle' => 'yearly',
            'features' => ['x'],
            'max_tattoos' => 99,
            'is_active' => true,
            'is_referral' => true,
            'sort_order' => 5,
        ]);
    }

    private function userOn(Plan $plan, int $earnedCents): User
    {
        $user = User::factory()->create(['plan_id' => $plan->id]);

        Referral::query()->create([
            'referrer_id' => $user->id,
            'referred_id' => User::factory()->create()->id,
            'status' => Referral::STATUS_PAID,
            'reward_cents' => $earnedCents,
        ]);

        return $user;
    }

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_eligible_user_can_request_withdrawal_within_balance(): void
    {
        $user = $this->userOn($this->planWith('empresa', 100), 3000); // 30 €

        $this->withToken($this->token($user))
            ->postJson('/api/v1/me/referrals/withdraw', ['amount' => 20])
            ->assertCreated();

        $this->assertDatabaseHas('commission_withdrawals', [
            'user_id' => $user->id,
            'amount_cents' => 2000,
            'status' => 'requested',
        ]);
    }

    public function test_cannot_withdraw_more_than_available(): void
    {
        $user = $this->userOn($this->planWith('empresa', 100), 1000); // 10 €

        $this->withToken($this->token($user))
            ->postJson('/api/v1/me/referrals/withdraw', ['amount' => 20])
            ->assertStatus(422);

        $this->assertDatabaseCount('commission_withdrawals', 0);
    }

    public function test_plan_not_eligible_for_cash_withdrawal_is_forbidden(): void
    {
        $user = $this->userOn($this->planWith('start', 35), 3000);

        $this->withToken($this->token($user))
            ->postJson('/api/v1/me/referrals/withdraw', ['amount' => 10])
            ->assertStatus(403);
    }

    public function test_available_balance_excludes_previous_non_rejected_withdrawals(): void
    {
        $user = $this->userOn($this->planWith('empresa', 100), 3000); // 30 €
        $user->commissionWithdrawals()->create(['amount_cents' => 2500, 'status' => 'requested']); // queda 5 €

        $token = $this->token($user);

        $this->withToken($token)
            ->postJson('/api/v1/me/referrals/withdraw', ['amount' => 10])
            ->assertStatus(422);

        $this->withToken($token)
            ->postJson('/api/v1/me/referrals/withdraw', ['amount' => 5])
            ->assertCreated();
    }
}
