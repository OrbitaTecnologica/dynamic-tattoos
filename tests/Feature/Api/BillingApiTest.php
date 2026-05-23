<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\BillingGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BillingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_billing_api_endpoints_require_authentication(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Basic',
            'slug' => 'basic',
            'price' => 9.99,
            'billing_cycle' => 'monthly',
            'features' => ['one'],
            'max_tattoos' => 2,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->postJson('/api/v1/billing/checkout/' . $plan->id)->assertUnauthorized();
        $this->postJson('/api/v1/billing/portal')->assertUnauthorized();
    }

    public function test_checkout_returns_422_when_plan_has_no_stripe_price_id(): void
    {
        $user = User::factory()->create();

        $plan = Plan::query()->create([
            'name' => 'No Stripe',
            'slug' => 'no-stripe',
            'price' => 9.99,
            'billing_cycle' => 'monthly',
            'stripe_price_id' => null,
            'features' => ['one'],
            'max_tattoos' => 2,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/billing/checkout/' . $plan->id)
            ->assertStatus(422);
    }

    public function test_checkout_returns_422_for_lifetime_plan(): void
    {
        $user = User::factory()->create();

        $plan = Plan::query()->create([
            'name' => 'Lifetime',
            'slug' => 'lifetime',
            'price' => 199.99,
            'billing_cycle' => 'lifetime',
            'stripe_price_id' => 'price_lifetime_123',
            'features' => ['all'],
            'max_tattoos' => 999,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/billing/checkout/' . $plan->id)
            ->assertStatus(422);
    }

    public function test_checkout_returns_redirect_url_when_gateway_succeeds(): void
    {
        $user = User::factory()->create();

        $plan = Plan::query()->create([
            'name' => 'Pro Monthly',
            'slug' => 'pro-monthly',
            'price' => 29.99,
            'billing_cycle' => 'monthly',
            'stripe_price_id' => 'price_123abc',
            'features' => ['all'],
            'max_tattoos' => 20,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->app->instance(BillingGateway::class, new class implements BillingGateway {
            public function createCheckoutUrl(User $user, Plan $plan, string $successUrl, string $cancelUrl): string
            {
                return 'https://checkout.stripe.test/session_123';
            }

            public function createPortalUrl(User $user, string $returnUrl): string
            {
                return 'https://billing.stripe.test/portal';
            }
        });

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/billing/checkout/' . $plan->id)
            ->assertOk()
            ->assertJsonPath('data.checkout_url', 'https://checkout.stripe.test/session_123');
    }

    public function test_portal_returns_422_when_user_has_no_stripe_customer(): void
    {
        $user = User::factory()->create([
            'stripe_id' => null,
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/billing/portal')
            ->assertStatus(422);
    }

    public function test_portal_returns_redirect_url_when_user_has_stripe_customer(): void
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_123abc',
        ]);

        $this->app->instance(BillingGateway::class, new class implements BillingGateway {
            public function createCheckoutUrl(User $user, Plan $plan, string $successUrl, string $cancelUrl): string
            {
                return 'https://checkout.stripe.test/session_123';
            }

            public function createPortalUrl(User $user, string $returnUrl): string
            {
                return 'https://billing.stripe.test/portal_456';
            }
        });

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/billing/portal')
            ->assertOk()
            ->assertJsonPath('data.portal_url', 'https://billing.stripe.test/portal_456');
    }
}
