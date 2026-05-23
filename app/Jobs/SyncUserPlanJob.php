<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Plan;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncUserPlanJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [30, 60, 120, 300, 600];

    public function __construct(
        public readonly int $userId,
    ) {
        $this->onQueue('payments');
    }

    public function handle(): void
    {
        $user = User::query()->find($this->userId);

        if ($user === null) {
            return;
        }

        $subscription = $user->subscription('default');
        $plan = null;

        if ($subscription !== null && $subscription->valid() && $subscription->stripe_price !== null) {
            $plan = Plan::query()
                ->where('stripe_price_id', $subscription->stripe_price)
                ->first();
        }

        DB::transaction(static function () use ($user, $plan, $subscription): void {
            $user->forceFill([
                'plan_id' => $plan?->id,
                'plan_expires_at' => $subscription?->ends_at,
            ])->save();
        });

        foreach ($user->tattoos()->select('short_code')->cursor() as $tattoo) {
            Cache::forget("tattoo_content_{$tattoo->short_code}");
        }

        Log::info('billing.plan_sync.completed', [
            'user_id' => $this->userId,
            'plan_id' => $plan?->id,
            'subscription_status' => $subscription?->stripe_status,
            'subscription_price' => $subscription?->stripe_price,
            'attempt' => $this->attempts(),
        ]);
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addHours(2);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('billing.plan_sync.failed', [
            'user_id' => $this->userId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);
    }
}
