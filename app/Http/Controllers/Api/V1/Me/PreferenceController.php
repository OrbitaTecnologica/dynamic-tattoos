<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdatePreferencesRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class PreferenceController extends Controller
{
    public function update(UpdatePreferencesRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->update($request->validated());

        return response()->json([
            'data' => [
                'language' => $user->language,
                'timezone' => $user->timezone,
                'currency' => $user->currency,
            ],
        ]);
    }
}
