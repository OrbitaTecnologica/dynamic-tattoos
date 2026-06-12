<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Public;

use App\Models\Tattoo;
use App\Models\TattooContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Publicly exposed read-only representation of a tattoo's active content.
 * Served from `GET /api/v1/public/tattoos/{shortCode}` so the SPA can render
 * its branded gallery view. Only fields safe for an unauthenticated visitor
 * are surfaced — no user identifiers, no internal IDs, no timestamps.
 *
 * @mixin Tattoo
 */
final class PublicTattooResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var TattooContent|null $content */
        $content = $this->resource->activeContent;

        return [
            'short_code' => $this->resource->short_code,
            'status'     => 'active',
            'title'      => $this->resource->name,
            'destType'   => $this->mapDestType($content),
            'photos'     => $this->buildPhotos($content),
            'video'      => $this->buildVideo($content),
        ];
    }

    private function mapDestType(?TattooContent $content): ?string
    {
        if ($content === null) {
            return null;
        }

        return match ($content->type) {
            TattooContent::TYPE_GALLERY => 'photos',
            TattooContent::TYPE_VIDEO   => 'video',
            default                     => null,
        };
    }

    /**
     * @return list<array{url: string, name: string|null}>
     */
    private function buildPhotos(?TattooContent $content): array
    {
        if ($content === null || ! $content->isGallery()) {
            return [];
        }

        $images = $content->payload['images'] ?? [];

        if (! is_array($images)) {
            return [];
        }

        $out = [];

        foreach ($images as $image) {
            $url = $this->resolveAbsoluteUrl(is_string($image) ? $image : ($image['url'] ?? null));

            if ($url === null) {
                continue;
            }

            $out[] = [
                'url'  => $url,
                'name' => is_array($image) ? ($image['name'] ?? null) : null,
            ];
        }

        return $out;
    }

    /**
     * @return array{url: string, name: string|null, platform: string|null}|null
     */
    private function buildVideo(?TattooContent $content): ?array
    {
        if ($content === null || ! $content->isVideo()) {
            return null;
        }

        $url = $this->resolveAbsoluteUrl($content->payload['url'] ?? null);

        if ($url === null) {
            return null;
        }

        return [
            'url'      => $url,
            'name'     => $content->payload['title'] ?? null,
            'platform' => $content->payload['platform'] ?? null,
        ];
    }

    /**
     * Promotes a stored path (relative or absolute) to an absolute URL the
     * browser can fetch. Already-absolute http(s) URLs pass through untouched.
     */
    private function resolveAbsoluteUrl(mixed $candidate): ?string
    {
        if (! is_string($candidate) || $candidate === '') {
            return null;
        }

        if (Str::startsWith($candidate, ['http://', 'https://'])) {
            return $candidate;
        }

        $path = ltrim($candidate, '/');

        if (Str::startsWith($path, 'storage/')) {
            return url($path);
        }

        // Treat anything else as a relative path on the public disk.
        return url(ltrim(Storage::disk('public')->url($path), '/'));
    }
}
