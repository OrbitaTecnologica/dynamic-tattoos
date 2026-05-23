<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\SendSubscriptionNotificationJob;
use App\Jobs\SyncUserPlanJob;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Events\WebhookHandled;

final class HandleCashierWebhook
{
    public function handle(WebhookHandled $event): void
    {
        $eventId = $event->payload['id'] ?? null;
        $type = $event->payload['type'] ?? null;

        if (! is_string($eventId) || $eventId === '' || ! is_string($type) || $type === '') {
            return;
        }

        if (! $this->markAsProcessed($eventId, $type, $event->payload)) {
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
        SyncUserPlanJob::dispatchSync($userId);
        SendSubscriptionNotificationJob::dispatch($userId, 'activated');
    }

    private function handleUpdated(int $userId): void
    {
        SyncUserPlanJob::dispatchSync($userId);
    }

    private function handleDeleted(int $userId): void
    {
        SyncUserPlanJob::dispatchSync($userId);
        SendSubscriptionNotificationJob::dispatch($userId, 'cancelled');
    }

    private function handlePaymentFailed(int $userId): void
    {
        SendSubscriptionNotificationJob::dispatch($userId, 'failed');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function markAsProcessed(string $eventId, string $type, array $payload): bool
    {
        try {
            DB::table('stripe_webhook_events')->insert([
                'stripe_event_id' => $eventId,
                'type' => $type,
                'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        } catch (QueryException $exception) {
            // SQLSTATE 23000 = integrity constraint violation (duplicate unique key).
            if ((string) $exception->getCode() === '23000') {
                return false;
            }

            throw $exception;
        } catch (\JsonException) {
            return false;
        }
    }
}
