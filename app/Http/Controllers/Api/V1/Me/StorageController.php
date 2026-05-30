<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Models\StoragePack;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StorageController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => $user->storageUsage(),
        ]);
    }

    public function checkout(Request $request, StoragePack $pack): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($pack->is_active && $pack->stripe_price_id !== null, 422, 'Pack no disponible para compra.');

        $checkout = $user->checkout([$pack->stripe_price_id => 1], [
            'success_url' => config('app.frontend_url', config('app.url')).'/mi-cuenta?storage=ok',
            'cancel_url' => config('app.frontend_url', config('app.url')).'/mi-cuenta?storage=cancel',
            'metadata' => ['storage_pack_id' => $pack->id],
        ]);

        return response()->json([
            'data' => ['checkout_url' => $checkout->url],
        ]);
    }
}
