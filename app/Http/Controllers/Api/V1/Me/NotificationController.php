<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateNotificationsRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->current($request->user()),
        ]);
    }

    public function update(UpdateNotificationsRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $merged = array_replace_recursive(
            $this->current($user),
            $request->validated()['notifications'],
        );

        // Only keep known categories.
        $merged = array_intersect_key($merged, User::NOTIFICATION_DEFAULTS);

        $user->update(['notification_preferences' => $merged]);

        return response()->json(['data' => $merged]);
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function current(User $user): array
    {
        return $user->notification_preferences ?? User::NOTIFICATION_DEFAULTS;
    }
}
