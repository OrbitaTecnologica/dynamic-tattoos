<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\BillingGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CheckoutSlugTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGateway(string $url): void
    {
        $this->app->instance(BillingGateway::class, new class($url) implements BillingGateway
        {
            public function __construct(private string $url) {}

            public function createCheckoutUrl(User $user, Plan $plan, string $successUrl, string $cancelUrl): string
            {
                return $this->url;
            }

            public function createPortalUrl(User $user, string $returnUrl): string
            {
                return 'https://billing.stripe.test/portal';
            }

            public function cancelSubscription(User $user, string $subscriptionName = 'default'): void {}

            public function resumeSubscription(User $user, string $subscriptionName = 'default'): void {}
        });
    }

    private function paidPlan(): Plan
    {
        return Plan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 65,
            'billing_cycle' => 'yearly',
            'features' => ['x'],
            'max_tattoos' => 5,
            'is_active' => true,
            'sort_order' => 4,
            'stripe_price_id' => 'price_pro_123',
        ]);
    }

    public function test_checkout_resolves_plan_by_slug(): void
    {
        $this->paidPlan();
        $user = User::factory()->create();
        $this->fakeGateway('https://checkout.stripe.test/by_slug');

        $this->withToken($user->createToken('t')->plainTextToken)
            ->postJson('/api/v1/billing/checkout/pro')
            ->assertOk()
            ->assertJsonPath('data.checkout_url', 'https://checkout.stripe.test/by_slug');
    }

    public function test_checkout_still_resolves_plan_by_id(): void
    {
        $plan = $this->paidPlan();
        $user = User::factory()->create();
        $this->fakeGateway('https://checkout.stripe.test/by_id');

        $this->withToken($user->createToken('t')->plainTextToken)
            ->postJson('/api/v1/billing/checkout/'.$plan->id)
            ->assertOk()
            ->assertJsonPath('data.checkout_url', 'https://checkout.stripe.test/by_id');
    }
}
