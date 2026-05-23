<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Billing\BillingGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BillingPortalApiController extends Controller
{
    public function __invoke(Request $request, BillingGateway $billingGateway): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->stripe_id !== null, 422, 'El usuario no tiene customer de Stripe asociado.');

        $portalUrl = $billingGateway->createPortalUrl($user, route('billing'));

        return response()->json([
            'data' => [
                'portal_url' => $portalUrl,
            ],
        ]);
    }
}
