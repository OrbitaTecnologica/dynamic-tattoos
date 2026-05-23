<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use App\Jobs\SendSubscriptionNotificationJob;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_subscription_created_event_is_processed_once_and_syncs_plan(): void
    {
        Queue::fake([SendSubscriptionNotificationJob::class]);

        $user = User::factory()->create([
            'stripe_id' => 'cus_test_001',
        ]);

        $plan = $this->createPlan('price_pro_monthly');

        $payload = $this->subscriptionPayload(
            eventId: 'evt_sub_created_001',
            eventType: 'customer.subscription.created',
            customerId: 'cus_test_001',
            subscriptionId: 'sub_test_001',
            priceId: 'price_pro_monthly',
            status: 'active',
            currentPeriodEnd: now()->addMonth()->timestamp,
        );

        $this->postJson('/stripe/webhook', $payload)->assertOk();
        $this->postJson('/stripe/webhook', $payload)->assertOk();

        $this->assertDatabaseCount('stripe_webhook_events', 1);
        $this->assertDatabaseHas('stripe_webhook_events', [
            'stripe_event_id' => 'evt_sub_created_001',
            'type' => 'customer.subscription.created',
        ]);

        $user->refresh();
        $this->assertSame($plan->id, $user->plan_id);

        Queue::assertPushed(SendSubscriptionNotificationJob::class, 1);
    }

    public function test_subscription_updated_event_updates_local_expiration_and_plan_mapping(): void
    {
        $user = User::factory()->create([
            'stripe_id' => 'cus_test_002',
            'plan_id' => null,
            'plan_expires_at' => null,
        ]);

        $plan = $this->createPlan('price_growth_monthly');
        $periodEnd = now()->addDays(10)->timestamp;

        $payload = $this->subscriptionPayload(
            eventId: 'evt_sub_updated_001',
            eventType: 'customer.subscription.updated',
            customerId: 'cus_test_002',
            subscriptionId: 'sub_test_002',
            priceId: 'price_growth_monthly',
            status: 'active',
            currentPeriodEnd: $periodEnd,
            cancelAtPeriodEnd: true,
        );

        $this->postJson('/stripe/webhook', $payload)->assertOk();

        $user->refresh();

        $this->assertSame($plan->id, $user->plan_id);
        $this->assertNotNull($user->plan_expires_at);
        $this->assertSame(
            now()->setTimestamp($periodEnd)->toIso8601String(),
            $user->plan_expires_at?->toIso8601String(),
        );
    }

    private function createPlan(string $stripePriceId): Plan
    {
        return Plan::query()->create([
            'name' => 'Webhook Plan ' . $stripePriceId,
            'slug' => 'webhook-' . $stripePriceId,
            'price' => 19.99,
            'billing_cycle' => 'monthly',
            'stripe_price_id' => $stripePriceId,
            'features' => ['sync'],
            'max_tattoos' => 10,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function subscriptionPayload(
        string $eventId,
        string $eventType,
        string $customerId,
        string $subscriptionId,
        string $priceId,
        string $status,
        int $currentPeriodEnd,
        bool $cancelAtPeriodEnd = false,
    ): array {
        return [
            'id' => $eventId,
            'type' => $eventType,
            'data' => [
                'object' => [
                    'id' => $subscriptionId,
                    'customer' => $customerId,
                    'status' => $status,
                    'metadata' => [
                        'type' => 'default',
                    ],
                    'cancel_at_period_end' => $cancelAtPeriodEnd,
                    'current_period_end' => $currentPeriodEnd,
                    'items' => [
                        'data' => [
                            [
                                'id' => 'si_' . $subscriptionId,
                                'price' => [
                                    'id' => $priceId,
                                    'product' => 'prod_' . $priceId,
                                ],
                                'quantity' => 1,
                                'current_period_end' => $currentPeriodEnd,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}