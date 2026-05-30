<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\UploadManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty(['first_name', 'last_name'])) {
            $user->name = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->name;
        }

        $user->save();

        activity('account')
            ->causedBy($user)
            ->event('profile_updated')
            ->withProperties(['detail' => 'Datos de perfil actualizados'])
            ->log('Perfil actualizado');

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function uploadAvatar(Request $request, UploadManager $uploads): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $upload = $uploads->store($user, $request->file('avatar'), 'avatar', 'avatars');
        $user->update(['avatar_path' => $upload->path]);

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }
}
