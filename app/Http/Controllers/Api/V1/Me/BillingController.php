<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PlanResource;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Cashier\Subscription;

final class BillingController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Subscription|null $subscription */
        $subscription = $user->subscription('default');
        $plan = $user->plan;

        return response()->json([
            'data' => [
                'status' => $this->resolveStatus($subscription, $plan),
                'plan' => $plan instanceof Plan ? new PlanResource($plan) : null,
                'has_referrals' => $user->hasReferralPlan(),
                'renews_at' => $user->renews_at?->toIso8601String(),
                'next_billing' => $user->renews_at?->toIso8601String(),
                'ends_at' => $subscription?->ends_at?->toIso8601String(),
                'payment_method' => $user->pm_type !== null ? [
                    'brand' => $user->pm_type,
                    'last4' => $user->pm_last_four,
                ] : null,
            ],
        ]);
    }

    public function invoices(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->hasStripeId()) {
            return response()->json(['data' => []]);
        }

        $invoices = collect($user->invoices())->map(static fn ($invoice): array => [
            'id' => $invoice->id,
            'date' => $invoice->date()?->toIso8601String(),
            'desc' => $invoice->lines->first()?->description ?? 'Suscripción',
            'amount' => $invoice->rawTotal() / 100,
            'currency' => mb_strtoupper((string) $invoice->currency),
            'status' => $invoice->status,
        ])->all();

        return response()->json(['data' => $invoices]);
    }

    private function resolveStatus(?Subscription $subscription, ?Plan $plan): string
    {
        if ($subscription === null) {
            return $plan !== null ? 'activa' : 'sin_plan';
        }

        return match (true) {
            $subscription->onGracePeriod() => 'grace_period',
            $subscription->canceled() => 'cancelada',
            $subscription->valid() => 'activa',
            default => 'inactiva',
        };
    }
}
