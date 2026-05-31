<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\SendSubscriptionNotificationJob;
use App\Jobs\SyncUserPlanJob;
use App\Models\StoragePack;
use App\Models\User;
use App\Services\Referrals\ReferralService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookHandled;

final class HandleCashierWebhook
{
    public function handle(WebhookHandled $event): void
    {
        $eventId = $event->payload['id'] ?? null;
        $type = $event->payload['type'] ?? null;

        if (! is_string($eventId) || $eventId === '' || ! is_string($type) || $type === '') {
            Log::warning('stripe.webhook.invalid_payload', [
                'event_id' => $eventId,
                'type' => $type,
            ]);

            return;
        }

        Log::info('stripe.webhook.received', [
            'event_id' => $eventId,
            'type' => $type,
        ]);

        if (! $this->markAsProcessed($eventId, $type, $event->payload)) {
            Log::info('stripe.webhook.duplicate_ignored', [
                'event_id' => $eventId,
                'type' => $type,
            ]);

            return;
        }

        $userId = $this->resolveUserId($event->payload);

        if ($userId === null) {
            Log::warning('stripe.webhook.user_not_found', [
                'event_id' => $eventId,
                'type' => $type,
            ]);

            return;
        }

        match ($type) {
            'customer.subscription.created' => $this->handleCreated($userId, $event->payload),
            'customer.subscription.updated' => $this->handleUpdated($userId, $event->payload),
            'customer.subscription.deleted' => $this->handleDeleted($userId),
            'invoice.payment_failed' => $this->handlePaymentFailed($userId),
            'checkout.session.completed' => $this->handleCheckoutCompleted($userId, $event->payload),
            default => null,
        };

        Log::info('stripe.webhook.handled', [
            'event_id' => $eventId,
            'type' => $type,
            'user_id' => $userId,
        ]);
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

    /**
     * @param  array<string, mixed>  $payload
     */
    private function handleCreated(int $userId, array $payload): void
    {
        SyncUserPlanJob::dispatch($userId);
        SendSubscriptionNotificationJob::dispatch($userId, 'activated');
        $this->syncRenewal($userId, $payload);

        // El referido ha pagado: recompensa a su referidor (idempotente).
        $referred = User::query()->find($userId);
        if ($referred !== null) {
            app(ReferralService::class)->rewardOnPaid($referred);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function handleUpdated(int $userId, array $payload): void
    {
        SyncUserPlanJob::dispatch($userId);
        $this->syncRenewal($userId, $payload);
    }

    private function handleDeleted(int $userId): void
    {
        SyncUserPlanJob::dispatch($userId);
        SendSubscriptionNotificationJob::dispatch($userId, 'cancelled');

        User::query()->whereKey($userId)->update([
            'renews_at' => null,
            'renewal_reminded_at' => null,
        ]);
    }

    /**
     * Guarda la fecha del próximo cobro (current_period_end). Si la fecha
     * cambió respecto a la registrada, reinicia el flag de recordatorio para
     * que se vuelva a avisar un mes antes del siguiente cobro.
     *
     * @param  array<string, mixed>  $payload
     */
    private function syncRenewal(int $userId, array $payload): void
    {
        $periodEnd = $payload['data']['object']['current_period_end'] ?? null;

        if (! is_int($periodEnd) && ! (is_string($periodEnd) && ctype_digit($periodEnd))) {
            return;
        }

        $renewsAt = Carbon::createFromTimestamp((int) $periodEnd);
        $user = User::query()->find($userId);

        if ($user === null) {
            return;
        }

        $changed = $user->renews_at === null || ! $user->renews_at->equalTo($renewsAt);

        $user->forceFill([
            'renews_at' => $renewsAt,
            'renewal_reminded_at' => $changed ? null : $user->renewal_reminded_at,
        ])->save();
    }

    private function handlePaymentFailed(int $userId): void
    {
        SendSubscriptionNotificationJob::dispatch($userId, 'failed');
    }

    /**
     * Credit a purchased storage pack (one-time checkout) to the user's quota.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleCheckoutCompleted(int $userId, array $payload): void
    {
        $packId = $payload['data']['object']['metadata']['storage_pack_id'] ?? null;

        if ($packId === null) {
            return;
        }

        $pack = StoragePack::query()->find($packId);
        $user = User::query()->find($userId);

        if ($pack === null || $user === null) {
            return;
        }

        $user->forceFill([
            'extra_storage_mb' => (int) $user->extra_storage_mb + $pack->size_mb,
        ])->save();

        activity('billing')
            ->causedBy($user)
            ->event('storage_pack_purchased')
            ->withProperties(['detail' => $pack->label.' · +'.$pack->size_mb.' MB'])
            ->log('Ampliación de almacenamiento');
    }

    /**
     * @param  array<string, mixed>  $payload
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
            Log::error('stripe.webhook.payload_encode_failed', [
                'event_id' => $eventId,
                'type' => $type,
            ]);

            return false;
        }
    }
}
