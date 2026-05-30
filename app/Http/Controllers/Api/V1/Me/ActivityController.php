<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

final class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $filter = $request->query('filter');

        $items = Activity::query()
            ->where('causer_type', $user->getMorphClass())
            ->where('causer_id', $user->id)
            ->when(is_string($filter) && $filter !== '' && $filter !== 'all', fn ($q) => $q->where('log_name', $filter))
            ->latest()
            ->limit(100)
            ->get()
            ->map(static fn (Activity $activity): array => [
                'id' => $activity->id,
                'type' => $activity->log_name,
                'title' => $activity->description,
                'detail' => $activity->properties->get('detail'),
                'created_at' => $activity->created_at?->toIso8601String(),
            ])
            ->all();

        return response()->json(['data' => $items]);
    }
}
