<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTattooRequest;
use App\Http\Requests\Api\V1\UpdateTattooRequest;
use App\Http\Resources\Api\V1\TattooResource;
use App\Models\Tattoo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TattooController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $tattoos = Tattoo::query()
            ->where('user_id', $user->id)
            ->with('activeContent')
            ->latest()
            ->paginate(15);

        return TattooResource::collection($tattoos);
    }

    public function store(StoreTattooRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $tattoo = Tattoo::query()->create([
            'user_id' => $user->id,
            'name' => (string) $request->input('name'),
            'is_active' => (bool) $request->boolean('is_active', true),
        ]);

        $tattoo->load('activeContent');

        return response()->json([
            'data' => new TattooResource($tattoo),
        ], 201);
    }

    public function show(Request $request, Tattoo $tattoo): JsonResponse
    {
        $this->authorize('view', $tattoo);

        $tattoo->load('activeContent');

        return response()->json([
            'data' => new TattooResource($tattoo),
        ]);
    }

    public function update(UpdateTattooRequest $request, Tattoo $tattoo): JsonResponse
    {
        $this->authorize('update', $tattoo);

        $tattoo->update($request->validated());
        $tattoo->load('activeContent');

        return response()->json([
            'data' => new TattooResource($tattoo),
        ]);
    }

    public function destroy(Request $request, Tattoo $tattoo): JsonResponse
    {
        $this->authorize('delete', $tattoo);

        $tattoo->delete();

        return response()->json([], 204);
    }
}
