<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\SubscriptionActivatedNotification;
use App\Notifications\SubscriptionCancelledNotification;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendSubscriptionNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 90, 180];

    public function __construct(
        public readonly int $userId,
        public readonly string $eventType,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $user = User::query()->find($this->userId);

        if ($user === null) {
            return;
        }

        $notification = match ($this->eventType) {
            'activated' => new SubscriptionActivatedNotification(),
            'failed' => new PaymentFailedNotification(),
            'cancelled' => new SubscriptionCancelledNotification(),
            default => null,
        };

        if ($notification === null) {
            Log::warning('billing.notification.unknown_event_type', [
                'user_id' => $this->userId,
                'event_type' => $this->eventType,
            ]);

            return;
        }

        $user->notify($notification);

        Log::info('billing.notification.sent', [
            'user_id' => $this->userId,
            'event_type' => $this->eventType,
            'attempt' => $this->attempts(),
        ]);
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addHours(1);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('billing.notification.failed', [
            'user_id' => $this->userId,
            'event_type' => $this->eventType,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);
    }
}
