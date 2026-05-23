<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePlanRequest;
use App\Http\Requests\Api\V1\UpdatePlanRequest;
use App\Http\Resources\Api\V1\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

final class PlanController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $plans = Plan::query()
            ->active()
            ->ordered()
            ->get();

        return PlanResource::collection($plans);
    }

    public function adminIndex(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Plan::class);

        $plans = Plan::query()
            ->ordered()
            ->get();

        return PlanResource::collection($plans);
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $this->authorize('create', Plan::class);

        $validated = $request->validated();

        $plan = Plan::query()->create([
            'name' => (string) $validated['name'],
            'slug' => Str::slug((string) $validated['name']),
            'price' => (float) $validated['price'],
            'billing_cycle' => (string) $validated['billing_cycle'],
            'stripe_price_id' => $validated['stripe_price_id'] ?? null,
            'features' => array_values((array) $validated['features']),
            'max_tattoos' => (int) $validated['max_tattoos'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return response()->json([
            'data' => new PlanResource($plan),
        ], 201);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $this->authorize('update', $plan);

        $validated = $request->validated();

        if (array_key_exists('name', $validated)) {
            $validated['slug'] = Str::slug((string) $validated['name']);
        }

        $plan->update($validated);

        return response()->json([
            'data' => new PlanResource($plan),
        ]);
    }

    public function destroy(Request $request, Plan $plan): JsonResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return response()->json([], 204);
    }
}
