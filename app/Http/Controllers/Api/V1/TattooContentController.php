<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ActivateTattooContentRequest;
use App\Http\Resources\Api\V1\TattooContentResource;
use App\Models\Tattoo;
use App\Models\TattooContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class TattooContentController extends Controller
{
    public function index(Request $request, Tattoo $tattoo): AnonymousResourceCollection
    {
        $this->authorize('view', $tattoo);

        $contents = $tattoo->contents()
            ->orderByDesc('updated_at')
            ->paginate(20);

        return TattooContentResource::collection($contents);
    }

    public function activate(ActivateTattooContentRequest $request, Tattoo $tattoo): JsonResponse
    {
        $this->authorize('update', $tattoo);

        $payload = $this->normalizePayload(
            type: (string) $request->input('type'),
            payload: (array) $request->input('payload', []),
        );

        $content = DB::transaction(function () use ($tattoo, $request, $payload): TattooContent {
            $tattoo->contents()->update(['is_active' => false]);

            /** @var int $maxOrder */
            $maxOrder = (int) $tattoo->contents()->max('order');

            return $tattoo->contents()->create([
                'type' => (string) $request->input('type'),
                'payload' => $payload,
                'is_active' => true,
                'order' => $maxOrder + 1,
            ]);
        });

        Cache::forget("tattoo_content_{$tattoo->short_code}");

        return response()->json([
            'data' => new TattooContentResource($content),
        ], 201);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(string $type, array $payload): array
    {
        return match ($type) {
            TattooContent::TYPE_LINK => [
                'url' => (string) ($payload['url'] ?? ''),
                'label' => $payload['label'] ?? null,
                'open_in_new_tab' => (bool) ($payload['open_in_new_tab'] ?? true),
            ],
            TattooContent::TYPE_GALLERY => [
                'title' => $payload['title'] ?? null,
                'images' => array_values((array) ($payload['images'] ?? [])),
            ],
            TattooContent::TYPE_VIDEO => [
                'url' => (string) ($payload['url'] ?? ''),
                'platform' => (string) ($payload['platform'] ?? 'youtube'),
                'autoplay' => (bool) ($payload['autoplay'] ?? false),
                'title' => $payload['title'] ?? null,
            ],
            default => [],
        };
    }
}
