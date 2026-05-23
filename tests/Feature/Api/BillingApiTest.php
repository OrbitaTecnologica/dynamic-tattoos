<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\BillingGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
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
        $this->postJson('/api/v1/billing/subscription/cancel')->assertUnauthorized();
        $this->postJson('/api/v1/billing/subscription/resume')->assertUnauthorized();
    }

    public function test_cancel_subscription_returns_422_when_user_has_no_subscription(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/billing/subscription/cancel')
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_error');
    }

    public function test_resume_subscription_returns_422_when_subscription_is_not_in_grace_period(): void
    {
        $user = User::factory()->create();

        Subscription::query()->create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_live_active',
            'stripe_status' => 'active',
            'stripe_price' => 'price_123',
            'quantity' => 1,
            'ends_at' => null,
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/billing/subscription/resume')
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_error');
    }

    public function test_cancel_subscription_returns_ok_when_gateway_succeeds(): void
    {
        $user = User::factory()->create();

        Subscription::query()->create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_live_cancel_me',
            'stripe_status' => 'active',
            'stripe_price' => 'price_123',
            'quantity' => 1,
            'ends_at' => null,
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

            public function cancelSubscription(User $user, string $subscriptionName = 'default'): void
            {
                $subscription = $user->subscription($subscriptionName);

                if ($subscription !== null) {
                    $subscription->update([
                        'stripe_status' => 'canceled',
                        'ends_at' => now()->addDays(7),
                    ]);
                }
            }

            public function resumeSubscription(User $user, string $subscriptionName = 'default'): void
            {
                $subscription = $user->subscription($subscriptionName);

                if ($subscription !== null) {
                    $subscription->update([
                        'stripe_status' => 'active',
                        'ends_at' => null,
                    ]);
                }
            }
        });

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/billing/subscription/cancel')
            ->assertOk()
            ->assertJsonPath('message', 'Subscription cancellation scheduled.')
            ->assertJsonPath('data.status', 'grace_period');
    }

    public function test_resume_subscription_returns_ok_when_gateway_succeeds(): void
    {
        $user = User::factory()->create();

        Subscription::query()->create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_live_resume_me',
            'stripe_status' => 'canceled',
            'stripe_price' => 'price_123',
            'quantity' => 1,
            'ends_at' => now()->addDays(4),
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

            public function cancelSubscription(User $user, string $subscriptionName = 'default'): void
            {
                $subscription = $user->subscription($subscriptionName);

                if ($subscription !== null) {
                    $subscription->update([
                        'stripe_status' => 'canceled',
                        'ends_at' => now()->addDays(7),
                    ]);
                }
            }

            public function resumeSubscription(User $user, string $subscriptionName = 'default'): void
            {
                $subscription = $user->subscription($subscriptionName);

                if ($subscription !== null) {
                    $subscription->update([
                        'stripe_status' => 'active',
                        'ends_at' => null,
                    ]);
                }
            }
        });

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/billing/subscription/resume')
            ->assertOk()
            ->assertJsonPath('message', 'Subscription resumed successfully.')
            ->assertJsonPath('data.status', 'activa');
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

            public function cancelSubscription(User $user, string $subscriptionName = 'default'): void
            {
            }

            public function resumeSubscription(User $user, string $subscriptionName = 'default'): void
            {
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

            public function cancelSubscription(User $user, string $subscriptionName = 'default'): void
            {
            }

            public function resumeSubscription(User $user, string $subscriptionName = 'default'): void
            {
            }
        });

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/billing/portal')
            ->assertOk()
            ->assertJsonPath('data.portal_url', 'https://billing.stripe.test/portal_456');
    }
}
