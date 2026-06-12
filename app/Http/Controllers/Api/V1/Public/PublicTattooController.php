<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Public\PublicTattooResource;
use App\Models\Tattoo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Serves the JSON payload consumed by the SPA gallery view at
 * `GET /t/{shortCode}`. Public on purpose — there is no auth on the QR scan
 * journey — but the controller exposes only safe, read-only fields and rate
 * limits are applied at the route level.
 */
final class PublicTattooController extends Controller
{
    private const CACHE_TTL_SECONDS = 300;

    public function __invoke(Request $request, string $shortCode): JsonResponse
    {
        if (! preg_match('/^[a-zA-Z0-9]{1,12}$/', $shortCode)) {
            abort(404);
        }

        // Cache the serialized array (not the Eloquent model) to avoid
        // "incomplete object" errors when the file/db cache drivers
        // unserialize the row before the model autoloader runs.
        $payload = Cache::remember(
            "public_tattoo_{$shortCode}",
            self::CACHE_TTL_SECONDS,
            static function () use ($shortCode, $request): ?array {
                $tattoo = Tattoo::query()
                    ->active()
                    ->byShortCode($shortCode)
                    ->with(['activeContent'])
                    ->first();

                if ($tattoo === null || $tattoo->activeContent === null) {
                    return null;
                }

                return PublicTattooResource::make($tattoo)->resolve($request);
            }
        );

        if ($payload === null) {
            abort(404);
        }

        return response()->json(['data' => $payload], 200);
    }
}
