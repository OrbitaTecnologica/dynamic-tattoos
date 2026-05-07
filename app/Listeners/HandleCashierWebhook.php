<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\SendSubscriptionNotificationJob;
use App\Jobs\SyncUserPlanJob;
use App\Models\User;
use Laravel\Cashier\Events\WebhookHandled;

final class HandleCashierWebhook
{
    public function handle(WebhookHandled $event): void
    {
        $type = $event->payload['type'] ?? null;

        if (! is_string($type)) {
            return;
        }

        $userId = $this->resolveUserId($event->payload);

        if ($userId === null) {
            return;
        }

        match ($type) {
            'customer.subscription.created' => $this->handleCreated($userId),
            'customer.subscription.updated' => $this->handleUpdated($userId),
            'customer.subscription.deleted' => $this->handleDeleted($userId),
            'invoice.payment_failed' => $this->handlePaymentFailed($userId),
            default => null,
        };
    }

    private function resolveUserId(array $payload): ?int
    {
        $customerId = $payload['data']['object']['customer'] ?? null;

        if (! is_string($customerId) || $customerId === '') {
            return null;
        }

        /** @var int|null $userId */
        $userId = User::query()
            ->where('stripe_id', $customerId)
            ->value('id');

        return $userId;
    }

    private function handleCreated(int $userId): void
    {
        SyncUserPlanJob::dispatch($userId);
        SendSubscriptionNotificationJob::dispatch($userId, 'activated');
    }

    private function handleUpdated(int $userId): void
    {
        SyncUserPlanJob::dispatch($userId);
    }

    private function handleDeleted(int $userId): void
    {
        SyncUserPlanJob::dispatch($userId);
        SendSubscriptionNotificationJob::dispatch($userId, 'cancelled');
    }

    private function handlePaymentFailed(int $userId): void
    {
        SendSubscriptionNotificationJob::dispatch($userId, 'failed');
    }
}
