<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\BillingGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BillingCheckoutController extends Controller
{
    public function __invoke(Request $request, Plan $plan, BillingGateway $billingGateway): JsonResponse
    {
        abort_unless($plan->is_active, 404);
        abort_unless($plan->stripe_price_id !== null, 422, 'El plan no tiene Stripe Price ID configurado.');
        abort_if($plan->billing_cycle === 'lifetime', 422, 'El ciclo lifetime requiere flujo de pago único.');

        /** @var User $user */
        $user = $request->user();

        $checkoutUrl = $billingGateway->createCheckoutUrl(
            user: $user,
            plan: $plan,
            successUrl: route('billing') . '?checkout=success',
            cancelUrl: route('billing') . '?checkout=cancelled',
        );

        return response()->json([
            'data' => [
                'checkout_url' => $checkoutUrl,
            ],
        ]);
    }
}
