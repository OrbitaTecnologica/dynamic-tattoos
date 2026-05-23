<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PlanResource;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Cashier\Subscription;

final class BillingSubscriptionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Subscription|null $subscription */
        $subscription = $user->subscription('default');
        $plan = $user->plan;

        return response()->json([
            'data' => [
                'status' => $this->resolveStatus($subscription),
                'plan' => $plan instanceof Plan ? new PlanResource($plan) : null,
                'stripe' => [
                    'customer_id' => $user->stripe_id,
                    'subscription_id' => $subscription?->stripe_id,
                    'subscription_price' => $subscription?->stripe_price,
                    'ends_at' => $subscription?->ends_at?->toIso8601String(),
                ],
            ],
        ]);
    }

    private function resolveStatus(?Subscription $subscription): string
    {
        if ($subscription === null) {
            return 'sin_plan';
        }

        if ($subscription->onGracePeriod()) {
            return 'grace_period';
        }

        if ($subscription->cancelled()) {
            return 'cancelada';
        }

        if ($subscription->valid()) {
            return 'activa';
        }

        return 'inactiva';
    }
}
