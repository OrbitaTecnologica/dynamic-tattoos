<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

final class SessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $currentId = $request->user()->currentAccessToken()?->getKey();

        $sessions = $user->tokens()
            ->orderByDesc('last_used_at')
            ->get()
            ->map(static fn (PersonalAccessToken $token): array => [
                'id' => $token->id,
                'name' => $token->name,
                'ip_address' => $token->ip_address,
                'user_agent' => $token->user_agent,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'created_at' => $token->created_at?->toIso8601String(),
                'current' => $token->id === $currentId,
            ])
            ->all();

        return response()->json(['data' => $sessions]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $request->user()->tokens()->whereKey($id)->delete();

        return response()->json([], 204);
    }

    public function destroyOthers(Request $request): JsonResponse
    {
        $currentId = $request->user()->currentAccessToken()?->getKey();

        $request->user()->tokens()
            ->when($currentId !== null, fn ($query) => $query->whereKeyNot($currentId))
            ->delete();

        return response()->json([
            'message' => 'Se cerraron las demás sesiones.',
        ]);
    }
}
