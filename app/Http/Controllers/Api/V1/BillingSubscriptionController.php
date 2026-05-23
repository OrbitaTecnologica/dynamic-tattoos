<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PlanResource;
use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\BillingGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Cashier\Subscription;

final class BillingSubscriptionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => $this->buildSubscriptionPayload($user),
        ]);
    }

    public function cancel(Request $request, BillingGateway $billingGateway): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Subscription|null $subscription */
        $subscription = $user->subscription('default');

        abort_unless($subscription !== null, 422, 'El usuario no tiene suscripcion activa.');
        abort_if($subscription->onGracePeriod(), 422, 'La suscripcion ya esta programada para cancelacion.');

        $billingGateway->cancelSubscription($user, 'default');
        $user->unsetRelation('subscriptions');

        return response()->json([
            'message' => 'Subscription cancellation scheduled.',
            'data' => $this->buildSubscriptionPayload($user),
        ]);
    }

    public function resume(Request $request, BillingGateway $billingGateway): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Subscription|null $subscription */
        $subscription = $user->subscription('default');

        abort_unless($subscription !== null, 422, 'El usuario no tiene suscripcion activa.');
        abort_unless($subscription->onGracePeriod(), 422, 'La suscripcion no esta en periodo de gracia.');

        $billingGateway->resumeSubscription($user, 'default');
        $user->unsetRelation('subscriptions');

        return response()->json([
            'message' => 'Subscription resumed successfully.',
            'data' => $this->buildSubscriptionPayload($user),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSubscriptionPayload(User $user): array
    {
        /** @var Subscription|null $subscription */
        $subscription = $user->subscription('default');
        $plan = $user->plan;

        return [
            'status' => $this->resolveStatus($subscription),
            'plan' => $plan instanceof Plan ? new PlanResource($plan) : null,
            'stripe' => [
                'customer_id' => $user->stripe_id,
                'subscription_id' => $subscription?->stripe_id,
                'subscription_price' => $subscription?->stripe_price,
                'ends_at' => $subscription?->ends_at?->toIso8601String(),
            ],
        ];
    }

    private function resolveStatus(?Subscription $subscription): string
    {
        if ($subscription === null) {
            return 'sin_plan';
        }

        if ($subscription->onGracePeriod()) {
            return 'grace_period';
        }

        if ($subscription->canceled()) {
            return 'cancelada';
        }

        if ($subscription->valid()) {
            return 'activa';
        }

        return 'inactiva';
    }
}
