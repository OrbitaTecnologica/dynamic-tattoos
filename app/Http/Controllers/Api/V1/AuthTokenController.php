<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthTokenController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $email = mb_strtolower((string) $request->input('email'));

        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user === null || ! Hash::check((string) $request->input('password'), (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $tokenName = (string) $request->input('device_name', 'api-token');
        $plainTextToken = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $plainTextToken,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Token revoked successfully.',
        ]);
    }
}
