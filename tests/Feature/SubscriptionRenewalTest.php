<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\RenewalReminderMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Cashier\Events\WebhookHandled;
use Tests\TestCase;

final class SubscriptionRenewalTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_is_sent_about_a_month_before_renewal(): void
    {
        Mail::fake();

        $due = User::factory()->create(['renews_at' => now()->addDays(20), 'renewal_reminded_at' => null]);
        $faraway = User::factory()->create(['renews_at' => now()->addDays(90), 'renewal_reminded_at' => null]);
        $already = User::factory()->create(['renews_at' => now()->addDays(20), 'renewal_reminded_at' => now()->subDay()]);

        $this->artisan('subscriptions:send-renewal-reminders')->assertSuccessful();

        Mail::assertSent(RenewalReminderMail::class, fn (RenewalReminderMail $m): bool => $m->hasTo($due->email));
        Mail::assertNotSent(RenewalReminderMail::class, fn (RenewalReminderMail $m): bool => $m->hasTo($faraway->email));
        Mail::assertNotSent(RenewalReminderMail::class, fn (RenewalReminderMail $m): bool => $m->hasTo($already->email));

        $this->assertNotNull($due->fresh()->renewal_reminded_at);
    }

    public function test_reminder_is_not_sent_twice(): void
    {
        Mail::fake();
        $user = User::factory()->create(['renews_at' => now()->addDays(15), 'renewal_reminded_at' => null]);

        $this->artisan('subscriptions:send-renewal-reminders');
        $this->artisan('subscriptions:send-renewal-reminders');

        Mail::assertSent(RenewalReminderMail::class, 1);
    }

    public function test_webhook_stores_next_renewal_date(): void
    {
        Queue::fake(); // evita ejecutar SyncUserPlanJob (que llamaría a Stripe)

        $user = User::factory()->create(['stripe_id' => 'cus_renew1', 'renews_at' => null]);
        $periodEnd = now()->addYear()->startOfDay();

        event(new WebhookHandled([
            'id' => 'evt_renew_1',
            'type' => 'customer.subscription.updated',
            'data' => ['object' => [
                'customer' => 'cus_renew1',
                'current_period_end' => $periodEnd->timestamp,
            ]],
        ]));

        $this->assertNotNull($user->fresh()->renews_at);
        $this->assertSame($periodEnd->toDateString(), $user->fresh()->renews_at->toDateString());
    }
}
