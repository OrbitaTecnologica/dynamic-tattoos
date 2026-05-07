<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

final class SubscriptionCheckoutController extends Controller
{
    public function __invoke(Request $request, Plan $plan): mixed
    {
        abort_unless($plan->is_active, 404);
        abort_unless($plan->stripe_price_id !== null, 422, 'El plan no tiene Stripe Price ID configurado.');
        abort_if($plan->billing_cycle === 'lifetime', 422, 'El ciclo lifetime requiere flujo de pago único.');

        return $request->user()
            ->newSubscription('default', $plan->stripe_price_id)
            ->checkout([
                'success_url' => route('billing').'?checkout=success',
                'cancel_url' => route('billing').'?checkout=cancelled',
            ]);
    }
}
